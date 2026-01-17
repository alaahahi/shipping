<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Imports\TripCarImport;
use App\Models\Trip;
use App\Models\TripCompany;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TestCarsImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cars-import {file : Path to Excel file} {--debug : Enable debug mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test importing cars from Excel file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filePath = $this->argument('file');
        $debug = $this->option('debug');

        // إذا كان المسار نسبي، أضف base_path
        if (!file_exists($filePath) && !str_starts_with($filePath, '/') && !preg_match('/^[A-Z]:\\\\/', $filePath)) {
            $filePath = base_path($filePath);
        }

        if ($debug) {
            $this->info('🐛 Debug mode enabled');
            Log::info('TestCarsImport: Starting test', ['file' => $filePath]);
        }

        // التحقق من وجود الملف
        if (!file_exists($filePath)) {
            $this->warn("⚠️  الملف غير موجود في المسار المحدد: {$filePath}");
            
            // محاولة البحث في public/EX
            $exDir = base_path('public/EX');
            if (is_dir($exDir)) {
                $files = glob($exDir . '/*.xlsx');
                if (count($files) > 0) {
                    $this->info("💡 تم العثور على ملفات Excel في public/EX:");
                    foreach ($files as $index => $file) {
                        $this->line("  " . ($index + 1) . ". " . basename($file));
                    }
                    
                    // استخدام أول ملف إذا لم يتم تحديد واحد
                    if (basename($filePath) && preg_match('/برج|سلام/', basename($filePath))) {
                        foreach ($files as $file) {
                            if (strpos(basename($file), 'برج') !== false || strpos(basename($file), 'سلام') !== false) {
                                $filePath = $file;
                                $this->info("✅ استخدام الملف: " . basename($filePath));
                                break;
                            }
                        }
                    } else {
                        $filePath = $files[0];
                        $this->info("✅ استخدام الملف الأول: " . basename($filePath));
                    }
                } else {
                    $this->error("❌ لم يتم العثور على أي ملفات Excel في public/EX");
                    return Command::FAILURE;
                }
            } else {
                $this->error("❌ المجلد public/EX غير موجود");
                return Command::FAILURE;
            }
        }

        $this->info("📁 قراءة الملف: {$filePath}");
        $this->newLine();

        try {
            // قراءة الملف باستخدام PhpSpreadsheet للتحقق من بنيته
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $this->info("📊 عدد الصفوف في الملف: " . $worksheet->getHighestRow());
            $this->info("📊 عدد الأعمدة في الملف: " . $worksheet->getHighestColumn());
            $this->newLine();

            // عرض أول 30 صف للتحليل
            if ($debug) {
                $this->info("🔍 فحص أول 30 صف من الملف:");
                $this->newLine();
                for ($row = 1; $row <= min(30, $worksheet->getHighestRow()); $row++) {
                    $rowData = [];
                    $hasData = false;
                    for ($col = 'A'; $col <= 'H'; $col++) {
                        $cellValue = $worksheet->getCell($col . $row)->getValue();
                        if ($cellValue !== null && trim((string) $cellValue) !== '') {
                            $hasData = true;
                            $rowData[$col] = substr(trim((string) $cellValue), 0, 30);
                        }
                    }
                    
                    if ($hasData || $row <= 15) { // عرض أول 15 صف دائماً
                        $rowInfo = "الصف {$row}: ";
                        if (!empty($rowData)) {
                            $rowInfo .= implode(' | ', array_map(function($col, $val) {
                                return "{$col}:" . substr($val, 0, 15);
                            }, array_keys($rowData), $rowData));
                        } else {
                            $rowInfo .= "(فارغ)";
                        }
                        $this->line($rowInfo);
                    }
                }
                $this->newLine();
            }

            // البحث عن صف S.NO
            $snoRow = $this->findSnoRow($filePath);
            $this->info("🔍 تم العثور على S.NO في الصف: {$snoRow}");
            $this->newLine();

            // قراءة صف الرأس
            $this->info("📋 قراءة صف الرأس (صف {$snoRow}):");
            $maxCol = min(10, $worksheet->getHighestColumn());
            $headers = [];
            $hasHeaders = false;
            for ($col = 'A'; $col <= $maxCol; $col++) {
                $headerValue = $worksheet->getCell($col . $snoRow)->getValue();
                $headerStr = trim((string) $headerValue);
                $headers[] = $headerStr;
                if (!empty($headerStr)) {
                    $hasHeaders = true;
                }
                $this->line("  {$col}: " . ($headerValue ?? '(فارغ)'));
            }
            
            // إذا كان صف S.NO فارغاً، نبحث في الصفوف التالية
            if (!$hasHeaders) {
                $this->warn("⚠️  صف S.NO ({$snoRow}) فارغ، البحث عن صف الرأس في الصفوف التالية...");
                for ($checkRow = $snoRow + 1; $checkRow <= min($snoRow + 5, $worksheet->getHighestRow()); $checkRow++) {
                    $rowHeaders = [];
                    $rowHasData = false;
                    for ($col = 'A'; $col <= $maxCol; $col++) {
                        $cellValue = $worksheet->getCell($col . $checkRow)->getValue();
                        $cellStr = trim((string) $cellValue);
                        if (!empty($cellStr)) {
                            $rowHasData = true;
                            $rowHeaders[] = $cellStr;
                        }
                    }
                    
                    if ($rowHasData && count($rowHeaders) >= 3) {
                        // هذا يبدو أنه صف الرأس
                        $snoRow = $checkRow;
                        $headers = $rowHeaders;
                        $this->info("✅ تم العثور على صف الرأس في الصف: {$snoRow}");
                        $this->info("   الأعمدة: " . implode(', ', $rowHeaders));
                        break;
                    }
                }
            }
            
            $this->newLine();

            // قراءة أول 10 صفوف من البيانات للاختبار
            $this->info("📝 قراءة أول 10 صفوف من البيانات (بدءاً من الصف " . ($snoRow + 1) . "):");
            $this->newLine();
            
            $dataStartRow = $snoRow + 1;
            $dataFound = false;
            
            for ($row = $dataStartRow; $row <= min($dataStartRow + 10, $worksheet->getHighestRow()); $row++) {
                $this->info("--- الصف {$row} ---");
                $rowData = [];
                $hasData = false;
                
                for ($col = 'A'; $col <= $maxCol; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    $headerIndex = ord($col) - ord('A');
                    $headerName = $headers[$headerIndex] ?? '';
                    
                    if ($cellValue !== null && trim((string) $cellValue) !== '') {
                        $hasData = true;
                        $rowData[$headerName] = trim((string) $cellValue);
                        if ($debug) {
                            $this->line("  {$headerName}: " . substr($cellValue, 0, 50));
                        }
                    }
                }
                
                if ($hasData) {
                    $dataFound = true;
                    $this->line("  ✅ يحتوي على بيانات");
                    if ($debug && !empty($rowData)) {
                        $this->line("  البيانات: " . json_encode($rowData, JSON_UNESCAPED_UNICODE));
                    }
                } else {
                    $this->line("  (صف فارغ)");
                }
                $this->newLine();
            }
            
            if (!$dataFound) {
                $this->warn("⚠️  لم يتم العثور على بيانات في أول 10 صفوف بعد S.NO!");
                $this->info("💡 قد تكون البيانات في صفوف أخرى. جاري البحث في الملف كاملاً...");
                
                // البحث عن أول صف يحتوي على بيانات فعلية
                for ($row = 1; $row <= min(50, $worksheet->getHighestRow()); $row++) {
                    $rowHasData = false;
                    $sampleData = [];
                    for ($col = 'A'; $col <= $maxCol; $col++) {
                        $cellValue = $worksheet->getCell($col . $row)->getValue();
                        if ($cellValue !== null && trim((string) $cellValue) !== '') {
                            $rowHasData = true;
                            $sampleData[] = substr(trim((string) $cellValue), 0, 20);
                        }
                    }
                    
                    if ($rowHasData && count($sampleData) >= 3) {
                        $this->info("  ✅ صف {$row} يحتوي على بيانات: " . implode(', ', array_slice($sampleData, 0, 3)));
                        if ($row > $snoRow + 10) {
                            $this->warn("  💡 البيانات تبدأ بعد الصف " . ($snoRow + 1) . " الذي تم العثور عليه");
                        }
                        if ($row < 5) {
                            break; // كفاية للاختبار
                        }
                    }
                }
            }

            // إذا كان debug، اختبار الاستيراد الفعلي
            if ($debug) {
                $this->info("🧪 اختبار الاستيراد الفعلي...");
                $this->newLine();

                // البحث عن owner_id من المستخدم الأول أو استخدام 1 كافتراضي
                $testUser = User::first();
                $ownerId = $testUser ? $testUser->owner_id : 1;

                if (!$testUser) {
                    $this->warn("⚠️  لم يتم العثور على مستخدم، سيتم استخدام owner_id = 1");
                }

                // إنشاء trip وtripCompany مؤقتين للاختبار
                $trip = Trip::create([
                    'sailing_date' => now(),
                    'ship_name' => 'TEST SHIP',
                    'pol' => 'TEST POL',
                    'pod' => 'TEST POD',
                    'owner_id' => $ownerId,
                ]);

                $testCompany = User::where('owner_id', $ownerId)->first();
                if (!$testCompany) {
                    $this->error("❌ لم يتم العثور على شركة للاختبار");
                    $trip->delete();
                    return Command::FAILURE;
                }

                $tripCompany = TripCompany::create([
                    'trip_id' => $trip->id,
                    'company_id' => $testCompany->id,
                    'owner_id' => $ownerId,
                ]);

                $this->info("✅ تم إنشاء Trip ({$trip->id}) و TripCompany ({$tripCompany->id}) للاختبار");
                $this->newLine();

                try {
                    // استيراد البيانات
                    Excel::import(
                        new TripCarImport($trip->id, $tripCompany->id, $ownerId, $filePath),
                        $filePath
                    );

                    // جلب السيارات المستوردة
                    $importedCars = \App\Models\TripCar::where('trip_company_id', $tripCompany->id)->get();
                    
                    $this->info("✅ تم استيراد {$importedCars->count()} سيارة بنجاح!");
                    $this->newLine();

                    // عرض أول 5 سيارات مستوردة
                    if ($importedCars->count() > 0) {
                        $this->info("📋 أول 5 سيارات مستوردة:");
                        foreach ($importedCars->take(5) as $index => $car) {
                            $this->line("  " . ($index + 1) . ". WEIGHT: {$car->weight}, CONSIGNEE: {$car->consignee_name}, CHASSIS: {$car->chassis_no}");
                        }
                    }

                    // حذف البيانات المؤقتة
                    $this->newLine();
                    $this->info("🗑️  حذف البيانات المؤقتة...");
                    \App\Models\TripCar::where('trip_company_id', $tripCompany->id)->delete();
                    $tripCompany->delete();
                    $trip->delete();
                    $this->info("✅ تم حذف البيانات المؤقتة");

                } catch (\Exception $e) {
                    $this->error("❌ خطأ أثناء الاستيراد: " . $e->getMessage());
                    $this->error("📍 الخطأ في: " . $e->getFile() . ":" . $e->getLine());
                    
                    if ($debug) {
                        $this->error("Stack trace:");
                        $this->line($e->getTraceAsString());
                    }

                    // تنظيف البيانات المؤقتة
                    \App\Models\TripCar::where('trip_company_id', $tripCompany->id)->delete();
                    $tripCompany->delete();
                    $trip->delete();
                    
                    return Command::FAILURE;
                }
            }

            $this->newLine();
            $this->info("✅ تم إكمال الاختبار بنجاح!");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ حدث خطأ: " . $e->getMessage());
            if ($debug) {
                $this->error("Stack trace:");
                $this->line($e->getTraceAsString());
            }
            return Command::FAILURE;
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
            
            $maxRows = min(30, $worksheet->getHighestRow());
            $maxCol = min(10, $worksheet->getHighestColumn());
            
            // البحث عن صف يحتوي على S.NO مع أعمدة أخرى (WEIGHT, SHIPPER, etc.)
            for ($row = 1; $row <= $maxRows; $row++) {
                $hasSno = false;
                $hasOtherHeaders = false;
                
                for ($col = 'A'; $col <= $maxCol; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    if ($cellValue !== null) {
                        $cellValueStr = strtoupper(trim((string) $cellValue));
                        
                        // البحث عن S.NO
                        if (preg_match('/^S[.\s\/]*NO[.\s:]*$/i', $cellValueStr) || 
                            $cellValueStr === 'S.NO' || 
                            $cellValueStr === 'S NO' ||
                            $cellValueStr === 'S/NO' ||
                            $cellValueStr === 'S.NO.' ||
                            $cellValueStr === 'S.NO:') {
                            $hasSno = true;
                        }
                        
                        // البحث عن أعمدة أخرى
                        if (preg_match('/^(WEIGHT|SHIPPER|DESCRIPTION|CHASSIS|CONSIGNEE|CODE)$/i', $cellValueStr)) {
                            $hasOtherHeaders = true;
                        }
                    }
                }
                
                // إذا وجدنا S.NO مع أعمدة أخرى، هذا هو صف الرأس
                if ($hasSno && $hasOtherHeaders) {
                    return $row;
                }
            }
            
            // البحث عن S.NO فقط إذا لم نجد صف الرأس الكامل
            for ($row = 1; $row <= $maxRows; $row++) {
                for ($col = 'A'; $col <= $maxCol; $col++) {
                    $cellValue = $worksheet->getCell($col . $row)->getValue();
                    
                    if ($cellValue !== null) {
                        $cellValueStr = strtoupper(trim((string) $cellValue));
                        
                        if (preg_match('/^S[.\s\/]*NO[.\s:]*$/i', $cellValueStr) || 
                            $cellValueStr === 'S.NO' || 
                            $cellValueStr === 'S NO' ||
                            $cellValueStr === 'S/NO' ||
                            $cellValueStr === 'S.NO.' ||
                            $cellValueStr === 'S.NO:') {
                            return $row;
                        }
                    }
                }
            }
            
            return 10; // القيمة الافتراضية
        } catch (\Exception $e) {
            $this->error("خطأ في البحث عن S.NO: " . $e->getMessage());
            return 10;
        }
    }
}
