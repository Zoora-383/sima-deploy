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
        // 1. Strict whitelist validation for file extensions
        $allowedExtensions = ['jpeg', 'jpg', 'png', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Tipe file tidak diizinkan. Hanya JPEG, JPG, PNG, dan WEBP yang diperbolehkan.');
        }

        // 2. Validate file content (MIME type / magic bytes) to prevent extension spoofing
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $actualMime = finfo_file($finfo, $file->getRealPath());
        finfo_close($finfo);

        if (!in_array($actualMime, $allowedMimeTypes)) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Konten berkas tidak valid atau berbahaya.');
        }

        // 3. Ensure the extension matches the actual MIME type
        $mimeToExtensions = [
            'image/jpeg' => ['jpeg', 'jpg'],
            'image/png'  => ['png'],
            'image/webp' => ['webp'],
        ];
        if (!in_array($extension, $mimeToExtensions[$actualMime] ?? [])) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Ekstensi berkas tidak sesuai dengan jenis konten berkas.');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'secure_upload');

        // Load and convert image to WEBP using GD
        if (!extension_loaded('gd')) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Server tidak mendukung pemrosesan gambar (GD extension missing).');
        }

        $source = null;
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $source = @imagecreatefromjpeg($file->getRealPath());
                break;
            case 'png':
                $source = @imagecreatefrompng($file->getRealPath());
                break;
            case 'webp':
                $source = @imagecreatefromwebp($file->getRealPath());
                break;
        }

        if (!$source) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Gagal memuat berkas gambar. Berkas gambar mungkin rusak atau tidak valid.');
        }

        // Preserve transparency for PNG/WEBP transparent images
        imagepalettetotruecolor($source);
        imagealphablending($source, false);
        imagesavealpha($source, true);

        // Save as WEBP with 80% quality
        $saveSuccess = imagewebp($source, $tempFile, 80);
        imagedestroy($source);

        if (!$saveSuccess || !file_exists($tempFile) || filesize($tempFile) === 0) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Gagal melakukan kompresi dan re-encoding gambar.');
        }

        $filename = Str::uuid() . '.webp';
        $path = Storage::disk($disk)->putFileAs($folder, new File($tempFile), $filename, 'private');
        unlink($tempFile);

        return Storage::disk($disk)->url($path);
    }

    /**
     * Generate a short-lived presigned URL for a private S3 file.
     * 
     * @param string|null $url
     * @param string $disk
     * @return string|null
     */
    public static function getPresignedUrl(?string $url, string $disk = 's3'): ?string
    {
        if (!$url) return null;

        try {
            $baseUrl = Storage::disk($disk)->url('');
            if (strpos($url, $baseUrl) === false) {
                return $url;
            }
            $filePath = str_replace($baseUrl, '', $url);
            $filePath = ltrim($filePath, '/');

            return Storage::disk($disk)->temporaryUrl($filePath, now()->addMinutes(15));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Failed to generate presigned URL: " . $e->getMessage());
            return $url;
        }
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
