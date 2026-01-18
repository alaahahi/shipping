<?php

namespace App\Imports;

use App\Models\TripCar;
use App\Models\Car;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AccountingCacheService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TripCarImport implements ToCollection, WithStartRow, SkipsEmptyRows
{
    protected $tripId;
    protected $tripCompanyId;
    protected $ownerId;
    protected $accounting;
    protected $headerRow = null; // سيتم تحديده تلقائياً
    protected $filePath;
    protected $fileExtension;
    public $skippedDuplicates = 0; // عدد السيارات المتخطاة بسبب التكرار

    public function __construct($tripId, $tripCompanyId, $ownerId, $filePath = null)
    {
        $this->tripId = $tripId;
        $this->tripCompanyId = $tripCompanyId;
        $this->ownerId = $ownerId;
        $this->accounting = app(AccountingCacheService::class);
        $this->filePath = $filePath;
        
        // تحديد امتداد الملف
        if ($filePath) {
            $this->fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
            $this->headerRow = $this->findSnoRow($filePath);
        }
    }
    
    /**
     * استيراد مباشر من الملف (نفس منطق المعاينة)
     */
    public function importDirectly()
    {
        if (!$this->filePath) {
            throw new \Exception('File path is required for direct import');
        }
        
        DB::beginTransaction();
        
        try {
            // التحقق من وجود الملف
            if (!file_exists($this->filePath)) {
                throw new \Exception('File not found: ' . $this->filePath);
            }
            
            // قراءة الملف مع تعطيل التحقق من XML
            $readerType = strtoupper($this->fileExtension) === 'XLS' ? 'Xls' : 'Xlsx';
            $reader = IOFactory::createReader($readerType);
            
            // تعطيل التحقق من صحة XML لتجنب أخطاء الـ empty document
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            
            // قمع أخطاء XML
            libxml_use_internal_errors(true);
            
            $spreadsheet = $reader->load($this->filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // مسح أخطاء XML
            libxml_clear_errors();
            
            $userClientTypeId = $this->accounting->userClient();
            $importedCount = 0;
            $skippedCount = 0;
            $skippedDuplicates = 0;
            
            $snoRow = $this->headerRow ?? 10;
            $startRow = $snoRow + 1;
            $maxRows = $worksheet->getHighestRow();
            $maxCol = 'H'; // حتى العمود H (CODE)
            
            Log::info('Direct import starting', [
                'trip_id' => $this->tripId,
                'sno_row' => $snoRow,
                'start_row' => $startRow,
                'max_rows' => $maxRows,
            ]);
            
            // قراءة كل صف
            for ($row = $startRow; $row <= $maxRows; $row++) {
                // قراءة البيانات من الأعمدة
                $sno = trim((string)$worksheet->getCell('A' . $row)->getValue());
                $weight = trim((string)$worksheet->getCell('B' . $row)->getValue());
                $shipperName = trim((string)$worksheet->getCell('C' . $row)->getValue());
                $description = trim((string)$worksheet->getCell('D' . $row)->getValue());
                $chassisNo = trim((string)$worksheet->getCell('E' . $row)->getValue());
                $consigneeName = trim((string)$worksheet->getCell('F' . $row)->getValue());
                $companyName = trim((string)$worksheet->getCell('G' . $row)->getValue());
                $code = trim((string)$worksheet->getCell('H' . $row)->getValue());
                
                // تخطي الصفوف الفارغة
                if (empty($chassisNo) && empty($consigneeName) && empty($description)) {
                    continue;
                }
                
                // تنظيف رقم الشاسيه
                if (!empty($chassisNo)) {
                    $chassisNo = strtoupper(trim($chassisNo));
                }
                
                // تخطي الصفوف التي فيها علامات اقتباس فقط
                if ($this->isQuoteOnlyRow($shipperName, $consigneeName)) {
                    Log::debug('Skipping row with quotes only', ['row' => $row]);
                    $skippedCount++;
                    continue;
                }
                
                // التحقق من CONSIGNEE
                if (empty($consigneeName)) {
                    Log::warning('Skipping row: missing CONSIGNEE', [
                        'row' => $row,
                        'chassis' => $chassisNo,
                    ]);
                    $skippedCount++;
                    continue;
                }
                
                // استيراد السيارة
                $result = $this->importCar([
                    'weight' => $weight,
                    'shipper_name' => $shipperName,
                    'description' => $description,
                    'chassis_no' => $chassisNo,
                    'consignee_name' => $consigneeName,
                    'code' => $code,
                ], $userClientTypeId);
                
                if ($result === 'imported') {
                    $importedCount++;
                } elseif ($result === 'duplicate') {
                    $skippedDuplicates++;
                } else {
                    $skippedCount++;
                }
            }
            
            $this->skippedDuplicates = $skippedDuplicates;
            
            DB::commit();
            
            Log::info('Direct import completed', [
                'trip_id' => $this->tripId,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'duplicates' => $skippedDuplicates,
                'total_rows' => $maxRows - $startRow + 1,
            ]);
            
            return [
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'duplicates' => $skippedDuplicates,
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Direct import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
    
    /**
     * استيراد سيارة واحدة
     */
    protected function importCar($data, $userClientTypeId)
    {
        $chassisNo = $data['chassis_no'];
        $consigneeName = $data['consignee_name'];
        $weight = $data['weight'];
        $shipperName = $data['shipper_name'];
        $description = $data['description'];
        $code = $data['code'];
        
        // البحث عن أو إنشاء Car
        $car = null;
        if (!empty($chassisNo)) {
            $car = Car::where('owner_id', $this->ownerId)
                ->where('vin', $chassisNo)
                ->first();
            
            if (!$car) {
                $car = Car::where('vin', $chassisNo)->first();
            }
        }
        
        // التحقق من التكرار
        if (!empty($chassisNo)) {
            $existingTripCar = TripCar::where('trip_id', $this->tripId)
                ->where('chassis_no', $chassisNo)
                ->first();
            
            if ($existingTripCar) {
                Log::info('Skipping duplicate car', ['chassis' => $chassisNo]);
                return 'duplicate';
            }
        }
        
        // إنشاء Car إذا لم تكن موجودة
        if (!$car && !empty($chassisNo)) {
            try {
                $car = Car::create([
                    'vin' => $chassisNo,
                    'lotnumber' => null,
                    'year' => null,
                    'owner_id' => $this->ownerId,
                ]);
            } catch (\Exception $e) {
                Log::warning('Could not create car', [
                    'chassis' => $chassisNo,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        // البحث عن Consignee
        $consignee = User::where('owner_id', $this->ownerId)
            ->where('type_id', $userClientTypeId)
            ->where('name', 'LIKE', '%' . $consigneeName . '%')
            ->first();
        
        if (!$consignee) {
            $consignee = User::create([
                'name' => $consigneeName,
                'type_id' => $userClientTypeId,
                'owner_id' => $this->ownerId,
            ]);
        }
        
        // إنشاء TripCar
        TripCar::create([
            'trip_id' => $this->tripId,
            'trip_company_id' => $this->tripCompanyId,
            'car_id' => $car ? $car->id : null,
            'weight' => !empty($weight) && is_numeric($weight) ? (float)$weight : null,
            'shipper_name' => !empty($shipperName) ? $shipperName : 'Unknown',
            'description' => $description,
            'chassis_no' => $chassisNo,
            'consignee_name' => $consigneeName,
            'consignee_id' => $consignee->id,
            'code' => $code,
            'owner_id' => $this->ownerId,
        ]);
        
        return 'imported';
    }

    /**
     * البحث عن صف S.NO في الملف (صف الرأس)
     */
    protected function findSnoRow($filePath)
    {
        try {
            // تحديد نوع القارئ بشكل صريح لتجنب مشاكل الكشف التلقائي
            $extension = $this->fileExtension ?? pathinfo($filePath, PATHINFO_EXTENSION);
            $readerType = strtoupper($extension) === 'XLS' ? 'Xls' : 'Xlsx';
            $reader = IOFactory::createReader($readerType);
            
            // تعطيل التحقق من صحة XML
            if (method_exists($reader, 'setReadDataOnly')) {
                $reader->setReadDataOnly(true);
            }
            
            // قمع أخطاء XML
            libxml_use_internal_errors(true);
            
            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // مسح أخطاء XML
            libxml_clear_errors();
            
            // البحث في أول 30 صف
            $maxRows = min(30, $worksheet->getHighestRow());
            $maxCol = min(10, $worksheet->getHighestColumn());
            
            // أولاً: البحث عن صف يحتوي على S.NO في العمود A مع WEIGHT في العمود B
            // هذا يضمن أننا وجدنا صف الرأس وليس رقم تسلسل
            for ($row = 1; $row <= $maxRows; $row++) {
                $hasSno = false;
                $hasWeight = false;
                $hasShipper = false;
                $hasConsignee = false;
                $rowHeaders = [];
                
                // قراءة العمود A (S.NO) والعمود B (WEIGHT) أولاً للتحقق السريع
                $colAValue = $worksheet->getCell('A' . $row)->getValue();
                $colBValue = $worksheet->getCell('B' . $row)->getValue();
                
                // Debug logging للصف 6 تحديداً
                if ($row === 6) {
                    Log::debug("Checking row 6 - A: '{$colAValue}', B: '{$colBValue}'");
                }
                
                if ($colAValue !== null) {
                    $cellAStr = trim((string) $colAValue);
                    $cellAUpper = strtoupper(str_replace([' ', '.', '/', '\\', ':'], '', $cellAStr));
                    
                    // البحث عن S.NO في العمود A (يجب أن يكون نصاً وليس رقم)
                    // التحقق من جميع الاختلافات المحتملة (بدون مسافات وعلامات ترقيم)
                    $isSno = (
                        $cellAUpper === 'SNO' || 
                        strpos($cellAUpper, 'SNO') === 0 ||
                        preg_match('/^S[.\s\/\\\]*NO[.\s:]*$/i', $cellAStr)
                    ) && !is_numeric($cellAStr) && !preg_match('/^[\d\s]+$/', $cellAStr);
                    
                    if ($isSno) {
                        $hasSno = true;
                        Log::debug("Found S.NO pattern in row {$row}, col A: '{$cellAStr}' (upper: '{$cellAUpper}')");
                    }
                }
                
                if ($colBValue !== null) {
                    $cellBStr = trim((string) $colBValue);
                    $cellBUpper = strtoupper($cellBStr);
                    
                    // البحث عن WEIGHT في العمود B
                    if ($cellBUpper === 'WEIGHT' || strpos($cellBUpper, 'WEIGHT') !== false) {
                        $hasWeight = true;
                        Log::debug("Found WEIGHT in row {$row}, col B: '{$cellBStr}'");
                    }
                }
                
                // Debug logging للصف 6 تحديداً
                if ($row === 6) {
                    Log::debug("Row 6 check - hasSno: " . ($hasSno ? 'true' : 'false') . ", hasWeight: " . ($hasWeight ? 'true' : 'false'), [
                        'colA' => $colAValue,
                        'colB' => $colBValue,
                        'cellAStr' => isset($cellAStr) ? $cellAStr : null,
                        'cellAUpper' => isset($cellAUpper) ? $cellAUpper : null,
                    ]);
                }
                
                // إذا وجدنا S.NO في A و WEIGHT في B، هذا هو صف الرأس على الأرجح
                // دعنا نتأكد بفحص باقي الأعمدة
                if ($hasSno && $hasWeight) {
                    // فحص باقي الأعمدة للتأكد
                    for ($col = 'C'; $col <= $maxCol; $col++) {
                        $cellValue = $worksheet->getCell($col . $row)->getValue();
                        if ($cellValue !== null) {
                            $cellValueStr = trim((string) $cellValue);
                            $cellValueUpper = strtoupper($cellValueStr);
                            $rowHeaders[] = $cellValueUpper;
                            
                            if ($cellValueUpper === 'SHIPPER' || strpos($cellValueUpper, 'SHIPPER') !== false) {
                                $hasShipper = true;
                            }
                            if ($cellValueUpper === 'CONSIGNEE' || strpos($cellValueUpper, 'CONSIGNEE') !== false) {
                                $hasConsignee = true;
                            }
                        }
                    }
                    
                    // إذا وجدنا S.NO + WEIGHT + (SHIPPER أو CONSIGNEE)، هذا هو صف الرأس
                    if ($hasSno && $hasWeight && ($hasShipper || $hasConsignee || count($rowHeaders) >= 3)) {
                        Log::info('Found S.NO header row at: ' . $row, [
                            'colA' => $colAValue,
                            'colB' => $colBValue,
                            'headers' => array_slice($rowHeaders, 0, 7),
                            'has_shipper' => $hasShipper,
                            'has_consignee' => $hasConsignee,
                        ]);
                        return $row;
                    } elseif ($hasSno && $hasWeight) {
                        // إذا وجدنا S.NO + WEIGHT فقط (بدون SHIPPER/CONSIGNEE)، هذا أيضاً صف الرأس
                        Log::info('Found S.NO header row at: ' . $row . ' (S.NO + WEIGHT only)', [
                            'colA' => $colAValue,
                            'colB' => $colBValue,
                        ]);
                        return $row;
                    }
                }
            }
            
            // ثانياً: إذا لم نجد صف الرأس الكامل، نبحث عن S.NO فقط
            for ($row = 1; $row <= $maxRows; $row++) {
                for ($col = 'A'; $col <= $maxCol; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    
                    if ($cellValue !== null) {
                        $cellValueStr = strtoupper(trim((string) $cellValue));
                        
                        // البحث عن S.NO أو S NO أو S/NO أو S.NO. أو S.NO:
                        if (preg_match('/^S[.\s\/]*NO[.\s:]*$/i', $cellValueStr) || 
                            $cellValueStr === 'S.NO' || 
                            $cellValueStr === 'S NO' ||
                            $cellValueStr === 'S/NO' ||
                            $cellValueStr === 'S.NO.' ||
                            $cellValueStr === 'S.NO:') {
                            Log::info('Found S.NO at row: ' . $row . ' (without other headers)');
                            return $row;
                        }
                    }
                }
            }
            
            // إذا لم يتم العثور، استخدام القيمة الافتراضية
            Log::warning('S.NO not found, using default row 10');
            return 10;
        } catch (\Exception $e) {
            Log::error('Error finding S.NO row: ' . $e->getMessage());
            return 10; // القيمة الافتراضية
        }
    }

    /**
     * تحديد رقم الصف الذي نبدأ منه القراءة (بعد صف الـ headers)
     */
    public function startRow(): int
    {
        // نبدأ من الصف التالي بعد صف الـ headers
        return ($this->headerRow ?? 10) + 1;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        DB::beginTransaction();

        try {
            $userClientTypeId = $this->accounting->userClient();
            $importedCount = 0;
            $skippedCount = 0;
            $skippedDuplicates = 0; // عدد السيارات المتخطاة بسبب التكرار
            
            Log::info('TripCarImport: Starting import', [
                'trip_id' => $this->tripId,
                'trip_company_id' => $this->tripCompanyId,
                'header_row' => $this->headerRow,
                'total_rows' => $rows->count(),
            ]);
            
            // Log first row for debugging
            if ($rows->count() > 0) {
                Log::info('TripCarImport: First row sample', [
                    'first_row' => $rows->first()->toArray(),
                    'row_keys' => array_keys($rows->first()->toArray()),
                ]);
            }
            
            foreach ($rows as $rowIndex => $row) {
                // قراءة البيانات بالترتيب الثابت للأعمدة (لأن WithHeadingRow لا يعمل بشكل صحيح)
                // الترتيب: S.NO(0), WEIGHT(1), SHIPPER(2), DESCRIPTION(3), CHASSIS NO(4), CONSIGNEE(5), COMPANY(6), CODE(7)
                $rowArray = array_values($row->toArray()); // تحويل إلى array مرقم
                
                $weight = isset($rowArray[1]) ? trim((string)$rowArray[1]) : null;
                $shipperName = isset($rowArray[2]) ? trim((string)$rowArray[2]) : null;
                $description = isset($rowArray[3]) ? trim((string)$rowArray[3]) : null;
                $chassisNo = isset($rowArray[4]) ? trim((string)$rowArray[4]) : null;
                $consigneeName = isset($rowArray[5]) ? trim((string)$rowArray[5]) : null;
                $code = isset($rowArray[7]) ? trim((string)$rowArray[7]) : null;
                
                // تنظيف رقم الشاسيه أولاً
                if (!empty($chassisNo)) {
                    $chassisNo = strtoupper(trim((string) $chassisNo));
                }
                
                // Debug logging for first row
                if ($rowIndex === 0) {
                    Log::info('TripCarImport: First row data (by index)', [
                        'rowArray' => $rowArray,
                        'weight' => $weight,
                        'shipperName' => $shipperName,
                        'description' => $description,
                        'chassisNo' => $chassisNo,
                        'consigneeName' => $consigneeName,
                        'code' => $code,
                    ]);
                }

                // تخطي الصفوف التي تحتوي على علامات اقتباس فقط في CONSIGNEE فقط
                // SHIPPER يمكن أن يكون فارغاً أو علامات اقتباس فقط (لأننا نحصل على اسم الشركة من TripCompany)
                if ($this->isQuoteOnlyRow(null, $consigneeName)) {
                    Log::debug('Skipping row with quotes only in CONSIGNEE', [
                        'row_index' => $rowIndex,
                        'consignee' => $consigneeName,
                        'row_sample' => array_slice($row->toArray(), 0, 5),
                    ]);
                    $skippedCount++;
                    continue;
                }

                // التحقق من البيانات الأساسية - CONSIGNEE فقط مطلوب
                // SHIPPER يمكن أن يكون فارغاً لأننا نحصل عليه من الشركة
                if (empty($consigneeName)) {
                    Log::warning('Skipping row: missing CONSIGNEE', [
                        'row_index' => $rowIndex,
                        'row' => $row->toArray(),
                        'shipper' => $shipperName,
                        'consignee' => $consigneeName,
                    ]);
                    $skippedCount++;
                    continue;
                }

                // البحث عن أو إنشاء Car
                $car = null;
                if (!empty($chassisNo)) {
                    // البحث أولاً عن السيارة الموجودة (البحث بـ vin فقط لأن UNIQUE constraint على vin)
                    $car = Car::where('owner_id', $this->ownerId)
                        ->where('vin', $chassisNo)
                        ->first();

                    if (!$car) {
                        // محاولة البحث بدون owner_id (في حالة وجود UNIQUE constraint على vin فقط)
                        $car = Car::where('vin', $chassisNo)
                            ->first();
                    }

                    if (!$car) {
                        try {
                            // إنشاء Car جديد
                            $maxNo = Car::max('no') ?? 0;
                            $no = $maxNo + 1;

                            $car = Car::create([
                                'owner_id' => $this->ownerId,
                                'vin' => $chassisNo,
                                'car_type' => $description ?? '',
                                'no' => $no,
                                'year_date' => Carbon::now()->format('Y'),
                                'date' => Carbon::now()->format('Y-m-d'),
                            ]);
                        } catch (\Exception $e) {
                            // في حالة وجود خطأ UNIQUE constraint، البحث مرة أخرى
                            if (strpos($e->getMessage(), 'UNIQUE constraint') !== false || 
                                strpos($e->getMessage(), 'duplicate') !== false) {
                                Log::warning('UNIQUE constraint error, trying to find existing car', [
                                    'chassis_no' => $chassisNo,
                                    'owner_id' => $this->ownerId,
                                    'error' => $e->getMessage(),
                                ]);
                                
                                // البحث مرة أخرى
                                $car = Car::where('vin', $chassisNo)->first();
                                
                                if (!$car) {
                                    Log::error('Could not find car after UNIQUE constraint error', [
                                        'chassis_no' => $chassisNo,
                                        'owner_id' => $this->ownerId,
                                    ]);
                                    // تخطي هذا الصف
                                    $skippedCount++;
                                    continue;
                                }
                            } else {
                                // إعادة رفع الخطأ إذا كان نوع مختلف
                                throw $e;
                            }
                        }
                    } else {
                        // إذا كانت السيارة موجودة بالفعل، تحديث car_type إذا كانت فارغة
                        if (empty($car->car_type) && !empty($description)) {
                            $car->update(['car_type' => $description]);
                        }
                    }
                }

                // البحث عن أو إنشاء CONSIGNEE (العميل المستقبل)
                $consignee = User::where('owner_id', $this->ownerId)
                    ->where('name', 'LIKE', "%{$consigneeName}%")
                    ->where('type_id', $userClientTypeId)
                    ->first();

                if (!$consignee) {
                    // إنشاء عميل جديد
                    $consignee = User::create([
                        'name' => $consigneeName,
                        'type_id' => $userClientTypeId,
                        'owner_id' => $this->ownerId,
                        'created' => Carbon::now()->format('Y-m-d'),
                        'year_date' => Carbon::now()->format('Y'),
                    ]);

                    // إنشاء Wallet للعميل
                    Wallet::firstOrCreate(
                        ['user_id' => $consignee->id],
                        ['balance' => 0]
                    );
                }

                // تنظيف وتحويل البيانات قبل الحفظ
                $weightValue = null;
                if (!empty($weight)) {
                    // إزالة أي أحرف غير رقمية وتحويل إلى float
                    $weightCleaned = preg_replace('/[^0-9.]/', '', (string) $weight);
                    $weightValue = is_numeric($weightCleaned) ? (float) $weightCleaned : null;
                }

                // التحقق من وجود سيارة في نفس الرحلة بنفس chassis_no (vin)
                if (!empty($chassisNo)) {
                    $existingTripCar = \App\Models\TripCar::where('trip_id', $this->tripId)
                        ->where('chassis_no', $chassisNo)
                        ->first();
                    
                    if ($existingTripCar) {
                        // السيارة موجودة بالفعل في هذه الرحلة، تخطيها
                        $skippedDuplicates++;
                        Log::debug('Skipping duplicate car in same trip', [
                            'trip_id' => $this->tripId,
                            'chassis_no' => $chassisNo,
                            'existing_trip_car_id' => $existingTripCar->id,
                        ]);
                        continue;
                    }
                }

                // إنشاء TripCar مع معالجة الأخطاء
                try {
                    // الحصول على اسم الشركة من TripCompany
                    $tripCompany = \App\Models\TripCompany::find($this->tripCompanyId);
                    $companyName = $tripCompany && $tripCompany->company ? $tripCompany->company->name : ($shipperName ?? '');
                    
                    TripCar::create([
                        'trip_id' => $this->tripId,
                        'trip_company_id' => $this->tripCompanyId,
                        'car_id' => $car ? $car->id : null,
                        'weight' => $weightValue,
                        'shipper_name' => $this->cleanValue($companyName ?: $shipperName),
                        'description' => $this->cleanValue($description),
                        'chassis_no' => $this->cleanValue($chassisNo),
                        'consignee_name' => $this->cleanValue($consigneeName),
                        'consignee_id' => $consignee->id,
                        'code' => $this->cleanValue($code),
                        'owner_id' => $this->ownerId,
                    ]);
                    
                    $importedCount++;
                } catch (\Exception $e) {
                    Log::error('Error creating TripCar', [
                        'trip_id' => $this->tripId,
                        'row' => $row->toArray(),
                        'error' => $e->getMessage(),
                    ]);
                    $skippedCount++;
                    // الاستمرار في معالجة الصفوف الأخرى بدلاً من إيقاف العملية
                    continue;
                }
            }

            DB::commit();
            
            Log::info('Excel import completed', [
                'trip_id' => $this->tripId,
                'trip_company_id' => $this->tripCompanyId,
                'imported' => $importedCount,
                'skipped' => $skippedCount,
                'skipped_duplicates' => $skippedDuplicates,
                'total_rows' => $rows->count(),
                'header_row' => $this->headerRow,
            ]);
            
            // حفظ عدد السيارات المتخطاة في instance variable للوصول إليه من الخارج
            $this->skippedDuplicates = $skippedDuplicates;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing trip cars', [
                'trip_id' => $this->tripId,
                'trip_company_id' => $this->tripCompanyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * الحصول على قيمة من الصف مع دعم أسماء الأعمدة المختلفة
     */
    protected function getValue($row, $key)
    {
        if (!($row instanceof Collection)) {
            return null;
        }

        // البحث بالاسم مباشرة
        if ($row->has($key)) {
            $value = $row->get($key);
            return $this->cleanValue($value);
        }

        // البحث بأسماء بديلة (مع مسافات، بدون مسافات، case-insensitive)
        $keyVariations = $this->getKeyVariations($key);
        
        foreach ($keyVariations as $variation) {
            if ($row->has($variation)) {
                $value = $row->get($variation);
                return $this->cleanValue($value);
            }
        }

        return null;
    }

    /**
     * الحصول على جميع الاختلافات المحتملة لمفتاح
     */
    protected function getKeyVariations($key)
    {
        return [
            $key,
            str_replace('_', ' ', $key),
            str_replace(' ', '_', $key),
            strtoupper(str_replace('_', ' ', $key)),
            strtolower($key),
            ucwords(str_replace('_', ' ', $key)),
            str_replace('_', '-', $key),
            str_replace('-', '_', $key),
        ];
    }

    /**
     * تنظيف القيمة من علامات الاقتباس والمسافات الزائدة
     */
    protected function cleanValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $cleaned = trim((string) $value);
        
        // إزالة علامات الاقتباس من البداية والنهاية
        $cleaned = trim($cleaned, '"\'');
        
        // إزالة المسافات الزائدة
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        return $cleaned === '' ? null : $cleaned;
    }

    /**
     * التحقق إذا كان الصف يحتوي على علامات اقتباس فقط
     * ملاحظة: SHIPPER يمكن أن يكون فارغاً أو علامات اقتباس فقط (لأننا نحصل على اسم الشركة من TripCompany)
     */
    protected function isQuoteOnlyRow($shipperName, $consigneeName)
    {
        // تخطي الصف فقط إذا كان CONSIGNEE يحتوي على علامات اقتباس فقط
        // SHIPPER يمكن أن يكون فارغاً أو علامات اقتباس فقط
        $consigneeTrimmed = trim((string) $consigneeName, ' "\'');
        
        return (empty($consigneeTrimmed) && !empty($consigneeName));
    }
}
