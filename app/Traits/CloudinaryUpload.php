<?php
// app/Traits/CloudinaryUpload.php

namespace App\Traits;

use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Configuration\Configuration;
use Exception;
use Illuminate\Support\Facades\Log;

trait CloudinaryUpload
{
    protected function bootCloudinary(): UploadApi
    {
        Configuration::instance([
            'cloud' => [
                'cloud_name' => config('services.cloudinary.cloud_name'),
                'api_key'    => config('services.cloudinary.api_key'),
                'api_secret' => config('services.cloudinary.api_secret'),
            ],
            'url' => ['secure' => true],
            'api' => [
                'curl_options' => [
                    CURLOPT_CAINFO => 'D:/xampp/php/extras/ssl/cacert.pem',
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                ],
            ],
        ]);

        return new UploadApi();
    }

    /**
     * Upload file ke Cloudinary, return secure_url
     */
    protected function uploadImage($file, string $folder = 'uploads'): string
    {
        try {
            $api = $this->bootCloudinary();

            $response = $api->upload($file->getRealPath(), [
                'folder'         => $folder,
                'resource_type'  => 'image',
                'transformation' => [
                    ['width' => 500, 'height' => 500, 'crop' => 'fill', 'gravity' => 'face'],
                ],
            ]);

            return $response['secure_url'];
        } catch (Exception $e) {
            Log::error("Cloudinary Upload Error: " . $e->getMessage());
            throw new Exception("Gagal mengunggah gambar ke Cloudinary: " . $e->getMessage());
        }
    }

    /**
     * Hapus gambar lama dari Cloudinary berdasarkan URL
     */
    protected function deleteOldImage(?string $imageUrl): void
    {
        if (!$imageUrl) return;
        $pattern = '/\/upload\/(?:v\d+\/)?(.+?)(?:\.[^.]+)?$/';

        if (preg_match($pattern, $imageUrl, $matches)) {
            $publicId = $matches[1];

            try {
                $this->bootCloudinary()->destroy($publicId);
            } catch (Exception $e) {
                Log::warning("Cloudinary delete failed: {$e->getMessage()}");
            }
        }
    }
}
