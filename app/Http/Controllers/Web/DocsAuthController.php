<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $validIdentifier = env('DOCS_IDENTIFIER', 'PUSTIKOM12');
        $validPassword   = env('DOCS_ACCESS_PASSWORD', 'bNVu7MvzWnTE39kgxAfySXUa');

        if ($request->identifier !== $validIdentifier || $request->password !== $validPassword) {
            return back()->withErrors([
                'identifier' => 'Identifier atau password salah.',
            ]);
        }

        $request->session()->put('docs_authenticated', true);

        return redirect()->intended('/docs/api');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('docs_authenticated');

        return redirect()->route('docs.login');
    }
}
