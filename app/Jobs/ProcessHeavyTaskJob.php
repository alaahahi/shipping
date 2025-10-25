<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProcessHeavyTaskJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $taskType;
    protected $data;

    public $tries = 3;
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct($taskType, $data)
    {
        $this->taskType = $taskType;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            Log::info("معالجة مهمة ثقيلة: {$this->taskType}");

            switch ($this->taskType) {
                case 'image_processing':
                    $this->processImages();
                    break;
                    
                case 'generate_report':
                    $this->generateReport();
                    break;
                    
                case 'export_excel':
                    $this->exportExcel();
                    break;
                    
                case 'bulk_update':
                    $this->bulkUpdate();
                    break;
                    
                default:
                    Log::warning("نوع مهمة غير معروف: {$this->taskType}");
            }

            Log::info("تمت معالجة المهمة بنجاح: {$this->taskType}");
        } catch (\Exception $e) {
            Log::error("فشلت المهمة {$this->taskType}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * معالجة الصور
     */
    protected function processImages()
    {
        if (!isset($this->data['images']) || !is_array($this->data['images'])) {
            return;
        }

        foreach ($this->data['images'] as $imagePath) {
            try {
                // تحسين الصورة
                $image = Image::make(public_path($imagePath));
                
                // تصغير الحجم للأداء
                if ($image->width() > 1920) {
                    $image->resize(1920, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
                
                // ضغط الصورة
                $image->save(null, 85);
                
                // إنشاء نسخة مصغرة
                $thumbnailPath = str_replace('uploads/', 'uploadsResized/', $imagePath);
                $image->fit(300, 300)->save(public_path($thumbnailPath));
                
                Log::info("تمت معالجة الصورة: {$imagePath}");
            } catch (\Exception $e) {
                Log::error("فشلت معالجة الصورة {$imagePath}: " . $e->getMessage());
            }
        }
    }

    /**
     * إنشاء تقرير
     */
    protected function generateReport()
    {
        // منطق إنشاء التقرير
        // يتم تنفيذها في الخلفية لعدم تأخير استجابة المستخدم
        Log::info('جاري إنشاء التقرير...');
    }

    /**
     * تصدير Excel
     */
    protected function exportExcel()
    {
        // منطق تصدير Excel
        // يتم في الخلفية خاصة للملفات الكبيرة
        Log::info('جاري تصدير Excel...');
    }

    /**
     * تحديث جماعي
     */
    protected function bulkUpdate()
    {
        // تحديثات جماعية للبيانات
        Log::info('جاري التحديث الجماعي...');
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error("فشلت المهمة الثقيلة {$this->taskType} بعد كل المحاولات: " . $exception->getMessage());
    }
}

