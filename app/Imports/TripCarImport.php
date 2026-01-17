<?php

namespace App\Imports;

use App\Models\TripCar;
use App\Models\Car;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AccountingCacheService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TripCarImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected $tripId;
    protected $tripCompanyId;
    protected $ownerId;
    protected $accounting;
    protected $headerRow = null; // سيتم تحديده تلقائياً
    protected $filePath;
    public $skippedDuplicates = 0; // عدد السيارات المتخطاة بسبب التكرار

    public function __construct($tripId, $tripCompanyId, $ownerId, $filePath = null)
    {
        $this->tripId = $tripId;
        $this->tripCompanyId = $tripCompanyId;
        $this->ownerId = $ownerId;
        $this->accounting = app(AccountingCacheService::class);
        $this->filePath = $filePath;
        
        // البحث عن صف S.NO تلقائياً
        if ($filePath) {
            $this->headerRow = $this->findSnoRow($filePath);
        }
    }

    /**
     * البحث عن صف S.NO في الملف (صف الرأس)
     */
    protected function findSnoRow($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
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
     * تحديد رقم الصف الذي يحتوي على أسماء الأعمدة
     */
    public function headingRow(): int
    {
        return $this->headerRow ?? 10;
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
                // الحصول على البيانات من Excel
                // الأعمدة المتوقعة: WEIGHT, SHIPPER, DESCRIPTION, CHASSIS NO, CONSIGNEE, CODE
                $weight = $this->getValue($row, 'weight');
                $shipperName = $this->getValue($row, 'shipper') ?? $this->getValue($row, 'shipper_name');
                $description = $this->getValue($row, 'description');
                $chassisNo = $this->getValue($row, 'chassis_no') ?? 
                            $this->getValue($row, 'chassis no') ?? 
                            $this->getValue($row, 'chassisno') ??
                            $this->getValue($row, 'chassis');
                
                // تنظيف رقم الشاسيه أولاً
                if (!empty($chassisNo)) {
                    $chassisNo = strtoupper(trim((string) $chassisNo));
                }
                $consigneeName = $this->getValue($row, 'consignee') ?? $this->getValue($row, 'consignee_name');
                $code = $this->getValue($row, 'code');
                
                // Debug logging for first row
                if ($rowIndex === 0) {
                    Log::info('TripCarImport: First row data', [
                        'weight' => $weight,
                        'shipperName' => $shipperName,
                        'description' => $description,
                        'chassisNo' => $chassisNo,
                        'consigneeName' => $consigneeName,
                        'code' => $code,
                        'row_data' => $row->toArray(),
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
