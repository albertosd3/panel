<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PanelAuthController extends Controller
{
    // Hardcoded PIN - tidak menggunakan database
    private const PANEL_PIN = '666666';
    
    public function showLogin()
    {
        return view('panel.login');
    }

    public function verify(Request $request)
    {
        try {
            // Validate input
            $data = $request->validate([
                'pin' => ['required', 'digits:6'],
            ]);

            $givenPin = trim((string) $data['pin']);
            $expectedPin = self::PANEL_PIN;

            Log::info('Login attempt', [
                'given_pin' => $givenPin,
                'expected_pin' => $expectedPin,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Check PIN
            if (hash_equals($expectedPin, $givenPin)) {
                // Set session
                session(['panel_authenticated' => true]);
                session(['panel_login_time' => now()]);
                
                Log::info('Login successful', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                
                return redirect()->route('panel.shortlinks')
                    ->with('success', 'Login berhasil!');
            }

            // Invalid PIN
            Log::warning('Login failed - invalid PIN', [
                'given_pin' => $givenPin,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return back()
                ->withErrors(['pin' => 'PIN salah. Silakan coba lagi.'])
                ->withInput();

        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return back()
                ->withErrors(['pin' => 'Terjadi kesalahan sistem. Silakan coba lagi.'])
                ->withInput();
        }
    }

    public function logout(Request $request)
    {
        try {
            // Clear session
            $request->session()->forget('panel_authenticated');
            $request->session()->forget('panel_login_time');
            
            Log::info('User logged out', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            
            return redirect()->route('panel.login')
                ->with('success', 'Logout berhasil!');
                
        } catch (\Exception $e) {
            Log::error('Logout error', [
                'error' => $e->getMessage(),
                'ip' => $request->ip()
            ]);
            
            return redirect()->route('panel.login');
        }
    }
}
