@extends('layouts.app')

@section('title', 'Admin Management')

@section('content')

<div class="space-y-8 animate-fadeIn">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <h1 class="text-3xl font-bold text-slate-900">Admin Management</h1>
            <p class="text-slate-500 mt-1">Update your account and manage admin users.</p>
        </div>

        <button onclick="openCreateModal()"
            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-900 text-white text-sm font-semibold shadow-md hover:bg-slate-800 hover:-translate-y-0.5 transition-all duration-200">
            <span class="text-lg leading-none">+</span>
            Create Admin
        </button>

    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 text-sm font-medium animate-alert">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium animate-alert">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium animate-alert">
            <p class="font-bold mb-2">Please check the following:</p>

            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Update Form -->
        <div class="xl:col-span-1 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 admin-card">

            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Update Admin Info</h2>
                <p class="text-sm text-slate-500 mt-1">Change your username or password.</p>
            </div>

            <form action="{{ route('admin.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="form-label">Profile Photo</label>

                    <div class="flex items-center gap-4 mb-3">
                        @if(auth()->user()?->profile_photo)
                            <img src="{{ asset('storage/' . auth()->user()->profile_photo) }}"
                                class="w-16 h-16 rounded-full object-cover border border-slate-200 shadow-sm"
                                alt="Profile Photo">
                        @else
                            <div class="w-16 h-16 rounded-full bg-slate-900 text-white flex items-center justify-center text-xl font-bold shadow-sm">
                                {{ strtoupper(substr(auth()->user()?->username ?? 'A', 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <p class="text-sm font-semibold text-slate-700">Account Avatar</p>
                            <p class="text-xs text-slate-500">Optional. JPG, PNG, or WEBP only.</p>
                        </div>
                    </div>

                    <input
                        type="file"
                        name="profile_photo"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="form-input">
                </div>
                
                <!-- Username -->
                <div>
                    <label class="form-label">Username</label>
                    <input
                        type="text"
                        name="username"
                        value="{{ auth()->user()?->username }}"
                        class="form-input"
                        required>
                </div>

                <!-- Password -->
                <div>
                    <label class="form-label">New Password</label>
                    <input
                        type="password"
                        name="password"
                        class="form-input"
                        placeholder="Enter new password">
                </div>

                <!-- Confirm Password -->
                <div>
                    <label class="form-label">Confirm Password</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-input"
                        placeholder="Confirm new password">
                </div>

                <button class="w-full py-3 rounded-2xl bg-slate-900 text-white font-semibold hover:bg-slate-800 hover:-translate-y-0.5 transition-all duration-200 shadow-md">
                    Save Changes
                </button>
            </form>
        </div>

        <!-- Admin List -->
        <div class="xl:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden admin-card">

            <div class="px-6 py-5 border-b border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">All Admin Accounts</h2>
                <p class="text-sm text-slate-500 mt-1">List of users with admin access.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Username</th>
                            <th class="px-6 py-4 text-left font-semibold">Role</th>
                            <th class="px-6 py-4 text-left font-semibold">Created</th>
                            <th class="px-6 py-4 text-left font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach($admins as $admin)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($admin->profile_photo)
                                            <img src="{{ asset('storage/' . $admin->profile_photo) }}"
                                                class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-sm"
                                                alt="Admin Photo">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                                {{ strtoupper(substr($admin->username ?? 'A', 0, 1)) }}
                                            </div>
                                        @endif

                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $admin->username }}</p>
                                            <p class="text-xs text-slate-400">Admin account</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">
                                        {{ $admin->role }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-slate-500">
                                    {{ $admin->created_at ? $admin->created_at->format('M d, Y') : 'N/A' }}
                                </td>

                                <td class="px-6 py-4">
                                    @if(auth()->id() !== $admin->id)
                                        <button
                                            onclick="openDeleteModal({{ $admin->id }})"
                                            class="px-4 py-2 rounded-xl bg-red-50 text-red-600 text-xs font-bold hover:bg-red-600 hover:text-white transition-all duration-200">
                                            Delete
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Current user</span>
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

    <div class="modal-box bg-white w-full max-w-md rounded-3xl shadow-2xl p-6 border border-slate-200">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Create Admin</h2>
                <p class="text-sm text-slate-500">Add a new administrator account.</p>
            </div>

            <button onclick="closeCreateModal()"
                class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form action="{{ route('admin.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="form-label">Profile Photo</label>
                <input
                    type="file"
                    name="profile_photo"
                    accept="image/png,image/jpeg,image/jpg,image/webp"
                    class="form-input">

                <p class="text-xs text-slate-500 mt-1">
                    Optional. If no photo is uploaded, the first letter of the username will be shown.
                </p>
            </div>

            <div>
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-input" required>
            </div>

            <button class="w-full py-3 rounded-2xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                Create Admin
            </button>
        </form>
    </div>
</div>

<!-- Delete Admin Modal -->
<div id="deleteModal"
    class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">

    <div class="modal-box bg-white w-full max-w-md rounded-3xl shadow-2xl p-6 border border-slate-200">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-red-600">Delete Admin</h2>
                <p class="text-sm text-slate-500">This action needs your password.</p>
            </div>

            <button onclick="closeDeleteModal()"
                class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form id="deleteForm" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="form-label">Your Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-input"
                    placeholder="Enter your password"
                    required>
            </div>

            <button class="w-full py-3 rounded-2xl bg-red-600 text-white font-semibold hover:bg-red-700 transition">
                Confirm Delete
            </button>
        </form>
    </div>
</div>

<style>
    .admin-card {
        transition: all 0.25s ease;
    }

    .admin-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
    }

    .form-input {
        width: 100%;
        padding: 12px 14px;
        border-radius: 16px;
        border: 1px solid #cbd5e1;
        color: #334155;
        outline: none;
        transition: all 0.2s ease;
    }

    .form-input:focus {
        border-color: #0f172a;
        box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.08);
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

    function openDeleteModal(id) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');

        form.action = '/admin/delete/' + id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

@endsection