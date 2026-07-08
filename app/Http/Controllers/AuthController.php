<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Subscriber; // Added
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Hash; // Added

class AuthController extends Controller
{
    // Show Login Page
    public function showLogin()
    {
        $settings = SystemSetting::first();

        return view('auth.login', compact('settings'));
    }

    // Handle Login (No Guards Method)
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // 1. Check if they are an Admin (Standard Laravel Auth)
        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        // 2. Check if they are a Subscriber (Manual Session Check)
        $subscriber = Subscriber::where('username', $request->username)->first();

        if ($subscriber && Hash::check($request->password, $subscriber->password)) {
            $request->session()->regenerate();
            
            // Set custom session markers for the subscriber
            session([
                'subscriber_logged_in' => true,
                'subscriber_id' => $subscriber->id
            ]);

            return redirect()->route('subscriber.portal');
        }

        // If both checks fail
        return back()->with('error', 'Invalid credentials');
    }

    // Logout
    public function logout(Request $request)
    {
        // Log out admin if logged in
        Auth::logout();

        // Clear subscriber session data if logged in
        session()->forget(['subscriber_logged_in', 'subscriber_id']);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}