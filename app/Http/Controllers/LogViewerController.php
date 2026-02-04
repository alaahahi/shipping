<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class LogViewerController extends Controller
{
    protected $logsPath;

    public function __construct()
    {
        $this->logsPath = storage_path('logs');
    }

    /**
     * عرض صفحة اللوغ
     */
    public function index()
    {
        return Inertia::render('Admin/LogViewer', [
            'logFiles' => $this->getLogFiles(),
        ]);
    }

    /**
     * جلب قائمة ملفات اللوغ
     */
    protected function getLogFiles()
    {
        $files = [];
        if (!File::isDirectory($this->logsPath)) {
            return $files;
        }
        $items = File::files($this->logsPath);
        foreach ($items as $file) {
            if ($file->getExtension() === 'log') {
                $files[] = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => $file->getMTime(),
                ];
            }
        }
        usort($files, fn($a, $b) => $b['modified'] <=> $a['modified']);
        return $files;
    }

    /**
     * API: جلب محتوى ملف اللوغ
     */
    public function getLog(Request $request)
    {
        $file = $request->get('file', 'laravel.log');
        if (!preg_match('/^[\w\-\.]+\.log$/', $file)) {
            return Response::json(['error' => 'اسم ملف غير صالح'], 400);
        }
        $path = $this->logsPath . DIRECTORY_SEPARATOR . $file;
        if (!File::exists($path) || !File::isFile($path)) {
            return Response::json(['error' => 'الملف غير موجود', 'content' => ''], 200);
        }
        $maxSize = 2 * 1024 * 1024; // 2MB
        $size = File::size($path);
        if ($size > $maxSize) {
            $content = File::get($path, false, null, $size - $maxSize, $maxSize);
            $content = "... (عرض آخر 2 ميجا فقط)\n\n" . $content;
        } else {
            $content = File::get($path);
        }
        return Response::json([
            'content' => $content,
            'file' => $file,
            'size' => $size,
            'logFiles' => $this->getLogFiles(),
        ]);
    }

    /**
     * API: تفريغ ملف اللوغ
     */
    public function clearLog(Request $request)
    {
        $file = $request->input('file', $request->get('file', 'laravel.log'));
        if (!preg_match('/^[\w\-\.]+\.log$/', $file)) {
            return Response::json(['error' => 'اسم ملف غير صالح'], 400);
        }
        $path = $this->logsPath . DIRECTORY_SEPARATOR . $file;
        if (!File::exists($path)) {
            return Response::json(['error' => 'الملف غير موجود'], 404);
        }
        try {
            File::put($path, '');
            return Response::json([
                'message' => 'تم تفريغ اللوغ بنجاح',
                'logFiles' => $this->getLogFiles(),
            ]);
        } catch (\Exception $e) {
            return Response::json(['error' => 'فشل التفريغ: ' . $e->getMessage()], 500);
        }
    }
}
