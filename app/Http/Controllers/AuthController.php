<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Subscriber;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        $settings = SystemSetting::first();

        return view('auth.login', compact('settings'));
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        $subscriber = Subscriber::where('username', $request->username)->first();

        if ($subscriber && Hash::check($request->password, $subscriber->password)) {
            $subscriber->syncOverdueStatus();

            if ($subscriber->status === 'disconnected') {
                return back()->with('error', 'Your account is disconnected. Please visit the administrator after paying your overdue balance.');
            }

            $request->session()->regenerate();
            
            session([
                'subscriber_logged_in' => true,
                'subscriber_id' => $subscriber->id
            ]);
            return redirect()->route('subscriber.portal');
        }
        return back()->with('error', 'Invalid credentials');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        session()->forget(['subscriber_logged_in', 'subscriber_id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}