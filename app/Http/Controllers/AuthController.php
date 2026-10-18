<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                Log::info('User authenticated successfully', ['email' => $credentials['email']]);
                return redirect()->intended(route('urls.index'))->with('success', 'Logged in successfully.');
            }

            Log::warning('Failed authentication attempt', ['email' => $credentials['email']]);
            return back()->withInput($request->only('email'))->with('error', 'Invalid login credentials.');
        } catch (\Throwable $e) {
            Log::error('Authentication exception', ['error' => $e->getMessage()]);
            return back()->withInput($request->only('email'))->with('error', 'An unexpected error occurred during login.');
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::info('User logged out', ['user_id' => isset($user->id) ? $user->id : null]);
            return redirect()->route('login')->with('success', 'You have been logged out.');
        } catch (\Throwable $e) {
            Log::error('Logout exception', ['error' => $e->getMessage()]);
            return redirect()->route('login');
        }
    }
}