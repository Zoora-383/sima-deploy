<?php

namespace App\Traits;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait SecureImageUpload
{
    /**
     * Re-encode image to strip metadata and upload to S3.
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @param string|null $disk
     * @return string
     */
    protected function secureUpload($file, string $folder, string $disk = 's3'): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $tempFile = tempnam(sys_get_temp_dir(), 'secure_upload');
        $reEncoded = false;

        // Load and re-encode image based on extension using GD if GD extension is loaded
        if (extension_loaded('gd')) {
            switch ($extension) {
                case 'jpeg':
                case 'jpg':
                    $source = @imagecreatefromjpeg($file->getRealPath());
                    if ($source) {
                        imagejpeg($source, $tempFile, 85);
                        imagedestroy($source);
                        $reEncoded = true;
                    }
                    break;
                case 'png':
                    $source = @imagecreatefrompng($file->getRealPath());
                    if ($source) {
                        imagepalettetotruecolor($source);
                        imagealphablending($source, false);
                        imagesavealpha($source, true);
                        imagepng($source, $tempFile, 6);
                        imagedestroy($source);
                        $reEncoded = true;
                    }
                    break;
                case 'webp':
                    $source = @imagecreatefromwebp($file->getRealPath());
                    if ($source) {
                        imagewebp($source, $tempFile, 80);
                        imagedestroy($source);
                        $reEncoded = true;
                    }
                    break;
            }
        }

        $filename = Str::uuid() . '.' . $extension;

        if ($reEncoded && file_exists($tempFile) && filesize($tempFile) > 0) {
            $path = Storage::disk($disk)->putFileAs($folder, new File($tempFile), $filename, 'public');
            unlink($tempFile);
        } else {
            // Fallback to original file if re-encoding fails or GD is not installed
            $path = Storage::disk($disk)->putFileAs($folder, $file, $filename, 'public');
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Delete old image from S3.
     * 
     * @param string|null $imageUrl
     * @param string $disk
     * @return void
     */
    protected function deleteFileFromS3(?string $imageUrl, string $disk = 's3'): void
    {
        if (!$imageUrl) return;

        try {
            $baseUrl = Storage::disk($disk)->url('');
            $filePath = str_replace($baseUrl, '', $imageUrl);
            Storage::disk($disk)->delete($filePath);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to delete file from S3: {$imageUrl} - " . $e->getMessage());
        }
    }
}
