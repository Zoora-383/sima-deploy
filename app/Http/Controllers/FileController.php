<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;

class FileController extends Controller
{
    /**
     * Serve private file — hanya bisa diakses pemiliknya.
     * 
     * Dengan Cloudinary, file sudah tersimpan di cloud dan memiliki URL langsung.
     * Controller ini memvalidasi kepemilikan lalu redirect ke Cloudinary URL.
     */
    public function show(Request $request, string $folder, string $filename): RedirectResponse
    {
        // 1. Prevent path traversal attacks
        if (
            str_contains($folder, '..') || str_contains($filename, '..') ||
            str_contains($folder, '/') || str_contains($filename, '/') ||
            str_contains($folder, '\\') || str_contains($filename, '\\')
        ) {
            abort(400, 'Invalid file path.');
        }

        $user = $request->user();
        $path = "{$folder}/{$filename}";

        // Cek kepemilikan — pastikan path ada di profile user ini
        $this->authorizeAccess($user, $path);

        // Redirect ke Cloudinary URL yang tersimpan di profile
        $profile = $user->userProfile;
        return redirect()->away($profile->avatar_url);
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

        $storedUrl = $profile->avatar_url ?? '';

        // Untuk Cloudinary, cek apakah path (folder/filename) ada di dalam URL
        if (empty($storedUrl) || !str_contains($storedUrl, $path)) {
            abort(403, 'Akses ditolak.');
        }
    }
}