<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $admins = User::where('role', 'admin')->get();

        return view('admin.admin', compact('admins'));
    }

    // UPDATE LOGGED-IN ADMIN
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user->username = $request->username;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('profile_photo')) {

            // Delete old photo if it exists
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Save new photo
            $user->profile_photo = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $user->save();

        return back()->with('success', 'Admin updated successfully.');
    }

    // CREATE ADMIN
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|min:6|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $profilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'profile_photo' => $profilePhotoPath,
        ]);

        return back()->with('success', 'Admin created successfully!');
    }

    // DELETE ADMIN
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'password' => 'required'
        ]);

        // Prevent deleting yourself
        if (Auth::id() == $id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Check password
        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->with('error', 'Incorrect password.');
        }

        $admin = User::where('id', $id)
            ->where('role', 'admin')
            ->first();

        if (!$admin) {
            return back()->with('error', 'Admin not found.');
        }

        // Delete admin profile photo if it exists
        if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
            Storage::disk('public')->delete($admin->profile_photo);
        }

        $admin->delete();

        return back()->with('success', 'Admin deleted successfully.');
    }
}