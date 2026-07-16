<?php

namespace App\Traits;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait SecureImageUpload
{
    /**
     * Boot Cloudinary SDK and return UploadApi instance.
     */
    private function bootCloudinary(): UploadApi
    {
        $config = [
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key'    => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
            'url' => ['secure' => true],
        ];

        // Only set custom SSL cert path if it exists (local dev)
        $certPath = env('CURL_CA_BUNDLE');
        if ($certPath && file_exists($certPath)) {
            $config['api'] = [
                'curl_options' => [
                    CURLOPT_CAINFO => $certPath,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                ],
            ];
        }

        Configuration::instance($config);

        return new UploadApi();
    }

    /**
     * Re-encode image, strip metadata, and upload to Cloudinary.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @param string $disk (ignored, kept for signature compatibility)
     * @return string Cloudinary secure_url
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

        // Upload to Cloudinary
        try {
            $api = $this->bootCloudinary();

            $response = $api->upload($tempFile, [
                'folder'        => $folder,
                'public_id'     => Str::uuid()->toString(),
                'resource_type' => 'image',
                'format'        => 'webp',
                'overwrite'     => true,
            ]);

            unlink($tempFile);

            return $response['secure_url'];
        } catch (\Exception $e) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            Log::error("Cloudinary Upload Error: " . $e->getMessage());
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Gagal mengunggah gambar: ' . $e->getMessage());
        }
    }

    /**
     * For Cloudinary URLs, just return them directly (they are already publicly accessible via signed URL).
     * No presigned URL needed — Cloudinary handles access control via its own URL signing.
     *
     * @param string|null $url
     * @param string $disk (ignored, kept for signature compatibility)
     * @return string|null
     */
    public static function getPresignedUrl(?string $url, string $disk = 's3'): ?string
    {
        // Cloudinary URLs are already accessible, return as-is
        return $url;
    }

    /**
     * Delete image from Cloudinary by extracting public_id from URL.
     *
     * @param string|null $imageUrl
     * @param string $disk (ignored, kept for signature compatibility)
     * @return void
     */
    protected function deleteFileFromS3(?string $imageUrl, string $disk = 's3'): void
    {
        if (!$imageUrl) return;

        // Extract public_id from Cloudinary URL
        // URL format: https://res.cloudinary.com/{cloud}/image/upload/v1234567890/{folder}/{public_id}.webp
        $pattern = '/\/upload\/(?:v\d+\/)?(.+?)(?:\.[^.]+)?$/';

        if (preg_match($pattern, $imageUrl, $matches)) {
            $publicId = $matches[1];

            try {
                $config = [
                    'cloud' => [
                        'cloud_name' => config('services.cloudinary.cloud_name'),
                        'api_key'    => config('services.cloudinary.api_key'),
                        'api_secret' => config('services.cloudinary.api_secret'),
                    ],
                    'url' => ['secure' => true],
                ];

                $certPath = env('CURL_CA_BUNDLE');
                if ($certPath && file_exists($certPath)) {
                    $config['api'] = [
                        'curl_options' => [
                            CURLOPT_CAINFO => $certPath,
                            CURLOPT_SSL_VERIFYPEER => true,
                            CURLOPT_SSL_VERIFYHOST => 2,
                        ],
                    ];
                }

                Configuration::instance($config);
                (new UploadApi())->destroy($publicId);
            } catch (\Exception $e) {
                Log::warning("Cloudinary delete failed: {$imageUrl} - " . $e->getMessage());
            }
        }
    }
}
