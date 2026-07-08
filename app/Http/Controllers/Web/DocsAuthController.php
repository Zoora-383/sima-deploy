<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class DocsAuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('docs-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $throttleKey = 'docs-login:' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'identifier' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $validIdentifier = config('services.docs.identifier');
        $validPassword   = config('services.docs.password');

        // Kalau config belum di-set, jangan lanjut auth dengan nilai kosong/null
        if (empty($validIdentifier) || empty($validPassword)) {
            report(new \RuntimeException('DOCS_IDENTIFIER / DOCS_ACCESS_PASSWORD belum dikonfigurasi.'));

            return back()->withErrors([
                'identifier' => 'Konfigurasi login belum tersedia. Hubungi administrator.',
            ]);
        }

        $identifierMatch = hash_equals((string) $validIdentifier, (string) $request->identifier);
        $passwordMatch   = hash_equals((string) $validPassword, (string) $request->password);

        if (!$identifierMatch || !$passwordMatch) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'identifier' => 'Identifier atau password salah.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();
        $request->session()->put('docs_authenticated', true);

        return redirect()->intended('/docs/api');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('docs_authenticated');
        $request->session()->regenerate();

        return redirect()->route('docs.login');
    }
}