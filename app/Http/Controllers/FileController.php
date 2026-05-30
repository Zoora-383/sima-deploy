<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Serve private file — hanya bisa diakses pemiliknya
     */
    public function show(Request $request, string $folder, string $filename): StreamedResponse
    {
        $user = $request->user();
        $path = "{$folder}/{$filename}";

        // Cek apakah file exist di bucket
        if (!Storage::disk('s3')->exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Cek kepemilikan — pastikan path ada di profile user ini
        $this->authorizeAccess($user, $path);

        // Stream file langsung ke response
        return Storage::disk('s3')->response($path);
    }

    /**
     * Cek apakah user boleh akses file ini
     */
    private function authorizeAccess($user, string $path): void
    {
        // Cek dari database — avatar_url milik user ini
        $profile = $user->userProfile;

        if (!$profile) {
            abort(403, 'Akses ditolak.');
        }

        // Ambil path dari URL yang tersimpan di DB
        $storedUrl  = $profile->avatar_url ?? '';
        $storedPath = parse_url($storedUrl, PHP_URL_PATH);
        $storedPath = ltrim($storedPath, '/');

        // Hapus bucket name dari path kalau ada
        // contoh: "sima-app/sima-app/avatars/xxx.jpg" → "sima-app/avatars/xxx.jpg"
        $bucketName = config('filesystems.disks.s3.bucket');
        $storedPath = preg_replace('#^' . preg_quote($bucketName, '#') . '/#', '', $storedPath);

        if ($storedPath !== $path) {
            abort(403, 'Akses ditolak.');
        }
    }
}