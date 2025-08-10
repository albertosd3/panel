<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PanelAuthController extends Controller
{
    public function showLogin()
    {
        return view('panel.login');
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'pin' => ['required','digits:6'],
        ]);

        $expected = trim((string) config('panel.pin'));
        $given = trim((string) $data['pin']);

        if ($expected === '') {
            return back()->withErrors(['pin' => 'PIN belum diset di konfigurasi.'])->withInput();
        }

        if (hash_equals($expected, $given)) {
            session(['panel_authenticated' => true]);
            return redirect()->route('panel.index');
        }

        return back()
            ->withErrors(['pin' => 'PIN salah.'])
            ->withInput();
    }

    public function logout(Request $request)
    {
        $request->session()->forget('panel_authenticated');
        return redirect()->route('panel.login');
    }
}
