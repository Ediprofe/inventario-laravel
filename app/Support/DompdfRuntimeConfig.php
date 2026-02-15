<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class DompdfRuntimeConfig
{
    public static function apply(): void
    {
        $fontDir = storage_path('fonts');
        $tempDir = storage_path('app/dompdf-temp');

        File::ensureDirectoryExists($fontDir);
        File::ensureDirectoryExists($tempDir);

        config([
            'dompdf.options.font_dir' => $fontDir,
            'dompdf.options.font_cache' => $fontDir,
            'dompdf.options.temp_dir' => $tempDir,
        ]);
    }
}
