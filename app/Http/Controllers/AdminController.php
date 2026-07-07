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
        // FIX: Fetch both standard admins and super admins
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();

        return view('admin.admin', compact('admins'));
    }

    // UPDATE LOGGED-IN ADMIN PROFILE INFO
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
        // FIX: Validate the dynamic role field sent from the form
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'role' => 'required|string|in:admin,super_admin',
            'password' => 'required|min:6|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $profilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        // FIX: Use $request->role instead of hardcoding 'admin'
        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'profile_photo' => $profilePhotoPath,
        ]);

        return back()->with('success', 'Admin created successfully!');
    }

    // NEW METHOD: UPDATE OTHER ADMINS' ROLES
    // This handles your JS call: form.action = '/admin/update-role/' + id;
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|in:admin,super_admin',
        ]);

        $admin = User::whereIn('role', ['admin', 'super_admin'])->findOrFail($id);
        $admin->role = $request->role;
        $admin->save();

        return back()->with('success', 'Admin role updated successfully.');
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

        // FIX: Scoped to both roles so super admins can be deleted too
        $admin = User::where('id', $id)
            ->whereIn('role', ['admin', 'super_admin'])
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