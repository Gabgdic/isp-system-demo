@extends('layouts.app')

@section('title', 'Admin Management')

@section('content')

<div class="space-y-4 animate-fadeIn">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Admin Management</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                Update your account and manage admin users.
            </p>
        </div>

        <button type="button" onclick="openCreateModal()"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold shadow-md hover:bg-slate-800 hover:-translate-y-0.5 transition-all duration-200">
            <span class="text-lg leading-none">+</span>
            Create Admin
        </button>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm font-medium animate-alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium animate-alert">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium animate-alert">
            <p class="font-bold mb-1">Please check the following:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        <!-- Update Form -->
        <div class="xl:col-span-1 bg-slate-50 rounded-2xl border border-slate-300 shadow-sm p-4 admin-card">
            <div class="mb-4">
                <h2 class="text-base font-bold text-slate-900">Update Admin Info</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Change your username or password.
                </p>
            </div>

            <form action="{{ route('admin.update') }}" method="POST"
                enctype="multipart/form-data" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">Profile Photo</label>

                    <div class="flex items-center gap-3 mb-2">
                        @if(auth()->user()?->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                                class="w-12 h-12 rounded-full object-cover border border-slate-200 shadow-sm"
                                alt="Profile Photo">
                        @else
                            <div class="w-12 h-12 rounded-full bg-slate-900 text-white flex items-center justify-center text-lg font-bold shadow-sm">
                                {{ strtoupper(substr(auth()->user()?->username ?? 'A', 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <p class="text-sm font-semibold text-slate-700">Account Avatar</p>
                            <p class="text-[11px] text-slate-500">Optional. JPG, PNG, or WEBP only.</p>
                        </div>
                    </div>

                    <input type="file" name="profile_photo"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="form-input">
                </div>

                <div>
                    <label class="form-label">Username</label>
                    <input type="text" name="username"
                        value="{{ auth()->user()?->username }}"
                        class="form-input" required>
                </div>

                <div>
                    <label class="form-label">New Password</label>

                    <div class="relative">
                        <input id="myPassword" type="password" name="password"
                            class="form-input pr-16" placeholder="Enter new password">

                        <button type="button"
                            onclick="togglePassword('myPassword', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">
                            Show
                        </button>
                    </div>

                    <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                        Password must be at least 8 characters and include
                        1 uppercase letter, 1 lowercase letter, 1 number,
                        and 1 special character.
                    </p>
                </div>

                <div>
                    <label class="form-label">Confirm Password</label>

                    <div class="relative">
                        <input id="myPasswordConfirmation" type="password"
                            name="password_confirmation"
                            class="form-input pr-16"
                            placeholder="Confirm new password">

                        <button type="button"
                            onclick="togglePassword('myPasswordConfirmation', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">
                            Show
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 hover:-translate-y-0.5 transition-all duration-200 shadow-md">
                    Save Changes
                </button>
            </form>
        </div>

        <!-- Admin List -->
        <div class="xl:col-span-2 bg-slate-50 rounded-2xl border border-slate-300 shadow-sm overflow-hidden admin-card">
            <div class="px-4 py-3 border-b border-slate-200">
                <h2 class="text-base font-bold text-slate-900">All Admin Accounts</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    List of users with admin access.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Username</th>
                            <th class="px-4 py-3 text-left font-semibold">Role</th>
                            <th class="px-4 py-3 text-left font-semibold">Created</th>
                            <th class="px-4 py-3 text-left font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($admins as $admin)
                            <tr class="hover:bg-slate-100 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        @if($admin->profile_photo)
                                            <img src="{{ asset('storage/' . $admin->profile_photo) }}"
                                                class="w-9 h-9 rounded-full object-cover border border-slate-200 shadow-sm"
                                                alt="Admin Photo">
                                        @else
                                            <div class="w-9 h-9 rounded-full bg-slate-900 text-white flex items-center justify-center text-xs font-bold shadow-sm">
                                                {{ strtoupper(substr($admin->username ?? 'A', 0, 1)) }}
                                            </div>
                                        @endif

                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $admin->username }}</p>
                                            <p class="text-[11px] text-slate-400">
                                                {{ ucwords(str_replace('_', ' ', $admin->role)) }} account
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3">
                                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold">
                                        {{ ucwords(str_replace('_', ' ', $admin->role)) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3 text-slate-500 text-xs">
                                    {{ $admin->created_at ? $admin->created_at->format('M d, Y') : 'N/A' }}
                                </td>

                                <td class="px-4 py-3">
                                    @if(auth()->id() !== $admin->id)
                                        <div class="flex items-center gap-1.5">
                                            <button type="button"
                                                onclick="openEditModal({{ $admin->id }}, '{{ $admin->role }}')"
                                                class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition-all duration-200"
                                                title="Edit">
                                                <svg class="w-4.5 h-4.5" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>

                                            <button type="button"
                                                onclick="openDeleteModal({{ $admin->id }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition-all duration-200"
                                                title="Delete">
                                                <svg class="w-4.5 h-4.5" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Current user</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Admin Modal -->
<div id="createAdminModal"
    class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="modal-box bg-white w-full max-w-md max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl p-5 border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Create Admin</h2>
                <p class="text-xs text-slate-500">Add a new administrator account.</p>
            </div>

            <button type="button" onclick="closeCreateModal()"
                class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form action="{{ route('admin.store') }}" method="POST"
            enctype="multipart/form-data" class="space-y-3">
            @csrf

            <div>
                <label class="form-label">Profile Photo</label>
                <input type="file" name="profile_photo"
                    accept="image/png,image/jpeg,image/jpg,image/webp"
                    class="form-input">

                <p class="text-[11px] text-slate-500 mt-1">
                    Optional. If no photo is uploaded, the first letter of the username will be shown.
                </p>
            </div>

            <div>
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Role</label>
                <select name="role" class="form-input" required>
                    <option value="">Select role</option>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div>
                <label class="form-label">Password</label>

                <div class="relative">
                    <input id="createPassword" type="password" name="password"
                        class="form-input pr-16"
                        placeholder="Enter password" required>

                    <button type="button"
                        onclick="togglePassword('createPassword', this)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">
                        Show
                    </button>
                </div>

                <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                    At least 8 characters with 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character.
                </p>
            </div>

            <div>
                <label class="form-label">Confirm Password</label>

                <div class="relative">
                    <input id="createPasswordConfirmation" type="password"
                        name="password_confirmation"
                        class="form-input pr-16"
                        placeholder="Confirm password" required>

                    <button type="button"
                        onclick="togglePassword('createPasswordConfirmation', this)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">
                        Show
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition">
                Create Admin
            </button>
        </form>
    </div>
</div>

<!-- Edit Admin Modal -->
<div id="editAdminModal"
    class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="modal-box bg-white w-full max-w-md max-h-[90vh] overflow-y-auto rounded-2xl shadow-2xl p-5 border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Edit Admin</h2>
                <p class="text-xs text-slate-500">Change role or reset the password.</p>
            </div>

            <button type="button" onclick="closeEditModal()"
                class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form id="editAdminForm" method="POST" class="space-y-3">
            @csrf
            @method('PUT')

            <div>
                <label class="form-label">Role</label>
                <select id="edit_role" name="role" class="form-input" required>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div>
                <label class="form-label">New Password</label>

                <div class="relative">
                    <input id="edit_password" type="password" name="password"
                        class="form-input pr-16"
                        placeholder="Leave blank to keep current password">

                    <button type="button"
                        onclick="togglePassword('edit_password', this)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">
                        Show
                    </button>
                </div>

                <p class="text-[11px] text-slate-500 mt-1">
                    At least 8 characters with uppercase, lowercase, number, and special character.
                </p>
            </div>

            <div>
                <label class="form-label">Confirm New Password</label>

                <div class="relative">
                    <input id="edit_password_confirmation" type="password"
                        name="password_confirmation"
                        class="form-input pr-16"
                        placeholder="Confirm new password">

                    <button type="button"
                        onclick="togglePassword('edit_password_confirmation', this)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">
                        Show
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition">
                Save Changes
            </button>
        </form>
    </div>
</div>

<!-- Delete Admin Modal -->
<div id="deleteModal"
    class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="modal-box bg-white w-full max-w-md rounded-2xl shadow-2xl p-5 border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-red-600">Delete Admin</h2>
                <p class="text-xs text-slate-500">This action needs your password.</p>
            </div>

            <button type="button" onclick="closeDeleteModal()"
                class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form id="deleteForm" method="POST" class="space-y-3">
            @csrf

            <div>
                <label class="form-label">Your Password</label>

                <div class="relative">
                    <input id="deletePassword" type="password" name="password"
                        class="form-input pr-16"
                        placeholder="Enter your password" required>

                    <button type="button"
                        onclick="togglePassword('deletePassword', this)"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">
                        Show
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
                Confirm Delete
            </button>
        </form>
    </div>
</div>

<style>
.admin-card {
}

.form-label {
    display: block;
    margin-bottom: 4px;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
}

.form-input {
    width: 100%;
    padding: 9px 12px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    color: #334155;
    outline: none;
    transition: all 0.2s ease;
    font-size: 14px;
}

.form-input:focus {
    border-color: #0f172a;
    box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
}

.animate-fadeIn {
    animation: fadeIn 0.45s ease-in-out;
}

.modal-box {
    animation: modalPop 0.25s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes modalPop {
    from {
        opacity: 0;
        transform: scale(0.94) translateY(12px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}
</style>

<script>
function openCreateModal() {
    const modal = document.getElementById('createAdminModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCreateModal() {
    const modal = document.getElementById('createAdminModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openEditModal(id, role) {
    const modal = document.getElementById('editAdminModal');
    const form = document.getElementById('editAdminForm');

    form.action = '/admin/update-role/' + id;

    document.getElementById('edit_role').value = role;
    document.getElementById('edit_password').value = '';
    document.getElementById('edit_password_confirmation').value = '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditModal() {
    const modal = document.getElementById('editAdminModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openDeleteModal(id) {
    const modal = document.getElementById('deleteModal');
    const form = document.getElementById('deleteForm');

    form.action = '/admin/delete/' + id;

    document.getElementById('deletePassword').value = '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function togglePassword(id, button) {
    const input = document.getElementById(id);

    if (input.type === 'password') {
        input.type = 'text';
        button.textContent = 'Hide';
    } else {
        input.type = 'password';
        button.textContent = 'Show';
    }
}
</script>

@endsection