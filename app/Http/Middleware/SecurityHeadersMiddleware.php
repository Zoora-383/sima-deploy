<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     * Menambahkan HTTP security headers pada setiap response untuk
     * mencegah clickjacking, MIME sniffing, dan kebocoran informasi.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah browser mendeteksi content-type secara otomatis (MIME sniffing)
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Mencegah halaman diload dalam frame/iframe (clickjacking)
        $response->headers->set('X-Frame-Options', 'DENY');

        // Mengontrol informasi referrer yang dikirim
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Menonaktifkan fitur browser yang tidak diperlukan
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Paksa HTTPS di production (hanya aktif jika sudah HTTPS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Hapus header yang membocorkan info server
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
