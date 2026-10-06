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
        $admins = User::whereIn('role', [
            'admin',
            'super_admin'
        ])->get();

        return view('admin.admin', compact('admins'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
            ],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ], [
            'password.regex' => 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        $user->username = $request->username;

        if ($request->filled('password')) {
            $user->password = Hash::make(
                $request->password
            );
        }

        if ($request->hasFile('profile_photo')) {
            if (
                $user->profile_photo &&
                Storage::disk('public')->exists(
                    $user->profile_photo
                )
            ) {
                Storage::disk('public')->delete(
                    $user->profile_photo
                );
            }

            $user->profile_photo = $request
                ->file('profile_photo')
                ->store(
                    'profile_photos',
                    'public'
                );
        }

        $user->save();

        return back()->with(
            'success',
            'Admin updated successfully.'
        );
    }

    // CREATE ADMIN
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'role' => 'required|string|in:admin,super_admin',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
            ],
            'profile_photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ], [
            'password.regex' => 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        $profilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request
                ->file('profile_photo')
                ->store('profile_photos', 'public');
        }

        User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'profile_photo' => $profilePhotoPath,
        ]);

        return back()->with(
            'success',
            'Admin created successfully!'
        );
    }

    // UPDATE OTHER ADMIN
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role' => 'required|string|in:admin,super_admin',
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/',
            ],
        ], [
            'password.regex' => 'Password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        // Prevent editing your own account
        if ((int) Auth::id() === (int) $id) {
            return back()->with(
                'error',
                'You cannot edit your own account here.'
            );
        }

        $admin = User::whereIn('role', [
            'admin',
            'super_admin'
        ])->find($id);

        if (!$admin) {
            return back()->with(
                'error',
                'Admin not found.'
            );
        }

        $admin->role = $request->role;

        // Only update password if a new password was entered
        if ($request->filled('password')) {
            $admin->password = Hash::make(
                $request->password
            );
        }

        $admin->save();

        return back()->with(
            'success',
            'Admin account updated successfully.'
        );
    }

    // DELETE ADMIN
    public function destroy(Request $request, $id)
    {
        $request->validate([
            'password' => 'required'
        ]);

        if (Auth::id() == $id) {
            return back()->with(
                'error',
                'You cannot delete your own account.'
            );
        }

        if (
            !Hash::check(
                $request->password,
                Auth::user()->password
            )
        ) {
            return back()->with(
                'error',
                'Incorrect password.'
            );
        }

        $admin = User::where('id', $id)
            ->whereIn('role', [
                'admin',
                'super_admin'
            ])
            ->first();

        if (!$admin) {
            return back()->with(
                'error',
                'Admin not found.'
            );
        }

        if (
            $admin->profile_photo &&
            Storage::disk('public')->exists(
                $admin->profile_photo
            )
        ) {
            Storage::disk('public')->delete(
                $admin->profile_photo
            );
        }

        $admin->delete();

        return back()->with(
            'success',
            'Admin deleted successfully.'
        );
    }
}