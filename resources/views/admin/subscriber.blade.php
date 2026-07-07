@extends('layouts.app')

@section('title', 'Subscriber Management')

@section('content')

<div class="space-y-8 animate-fadeIn">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Subscriber Management</h1>
            <p class="text-slate-500 mt-1">Create and manage subscriber accounts.</p>
        </div>

        <button onclick="openCreateSubscriberModal()"
            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-900 text-white text-sm font-semibold shadow-md hover:bg-slate-800 hover:-translate-y-0.5 transition-all duration-200">
            <span class="text-lg leading-none">+</span>
            Create Subscriber
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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="subscriber-card">
            <p class="text-sm text-slate-500 font-medium">Total Subscribers</p>
            <h2 class="text-3xl font-bold text-slate-900 mt-2">{{ $totalSubscribers }}</h2>
        </div>

        <div class="subscriber-card">
            <p class="text-sm text-slate-500 font-medium">Active Subscribers</p>
            <h2 class="text-3xl font-bold text-slate-900 mt-2">
                {{ $activeSubscribers }}
            </h2>
        </div>

        <div class="subscriber-card">
            <p class="text-sm text-slate-500 font-medium">Disconnected</p>
            <h2 class="text-3xl font-bold text-slate-900 mt-2">
                {{ $disconnectedSubscribers }}
            </h2>
        </div>
    </div>

    <!-- Subscriber Table -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-900">All Subscribers</h2>
            <p class="text-sm text-slate-500 mt-1">List of registered subscriber accounts.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Subscriber</th>
                        <th class="px-6 py-4 text-left font-semibold">Contact</th>
                        <th class="px-6 py-4 text-left font-semibold">Plan</th>
                        <th class="px-6 py-4 text-left font-semibold">Monthly Fee</th>
                        <th class="px-6 py-4 text-left font-semibold">Status</th>
                        <th class="px-6 py-4 text-left font-semibold">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($subscribers as $subscriber)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($subscriber->profile_photo)
                                        <img src="{{ asset('storage/' . $subscriber->profile_photo) }}"
                                            class="w-11 h-11 rounded-full object-cover border border-slate-200 shadow-sm"
                                            alt="Subscriber Photo">
                                    @else
                                        <div class="w-11 h-11 rounded-full bg-slate-900 text-white flex items-center justify-center text-sm font-bold shadow-sm">
                                            {{ strtoupper(substr($subscriber->full_name ?? 'S', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $subscriber->full_name }}</p>
                                        <p class="text-xs text-slate-400">{{ '@' . $subscriber->username }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-slate-700">{{ $subscriber->phone_number ?? 'No phone' }}</p>
                                <p class="text-xs text-slate-400">{{ $subscriber->email ?? 'No email' }}</p>
                            </td>

                            <td class="px-6 py-4">
                                {{ $subscriber->plan_name ?? 'No plan' }}
                            </td>

                            <td class="px-6 py-4 font-semibold">
                                ₱{{ number_format($subscriber->monthly_fee, 2) }}
                            </td>

                            <td class="px-6 py-4">
                                @if($subscriber->status === 'active')
                                    <span class="status-badge">Active</span>
                                @elseif($subscriber->status === 'inactive')
                                    <span class="status-badge">Inactive</span>
                                @else
                                    <span class="status-badge">Disconnected</span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">

                                    <button
                                        type="button"
                                        onclick="openEditSubscriberModal(this)"
                                        data-id="{{ $subscriber->id }}"
                                        data-full-name="{{ $subscriber->full_name }}"
                                        data-username="{{ $subscriber->username }}"
                                        data-email="{{ $subscriber->email }}"
                                        data-phone-number="{{ $subscriber->phone_number }}"
                                        data-address="{{ $subscriber->address }}"
                                        data-plan-name="{{ $subscriber->plan_name }}"
                                        data-monthly-fee="{{ $subscriber->monthly_fee }}"
                                        data-status="{{ $subscriber->status }}"
                                        class="p-2 rounded-lg text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition-all duration-200"
                                        title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>

                                    <form action="{{ route('subscriber.delete', $subscriber->id) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this subscriber?')"
                                        class="inline">
                                        @csrf

                                        <button class="p-2 rounded-lg text-slate-500 hover:bg-slate-200 hover:text-slate-900 transition-all duration-200"
                                            title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">
                                No subscribers found. Create your first subscriber account.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscribers->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $subscribers->links() }}
            </div>
        @endif

    </div>
    
</div>

<!-- Create Subscriber Modal -->
<div id="createSubscriberModal"
    class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">

    <div class="modal-box bg-white w-full max-w-3xl rounded-3xl shadow-2xl p-6 border border-slate-200 max-h-[85vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Create Subscriber</h2>
                <p class="text-sm text-slate-500">Register a new subscriber account.</p>
            </div>

            <button onclick="closeCreateSubscriberModal()"
                class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form action="{{ route('subscriber.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div>
                <label class="form-label">Profile Photo</label>
                <input
                    type="file"
                    name="profile_photo"
                    accept="image/png,image/jpeg,image/jpg,image/webp"
                    class="form-input">

                <p class="text-xs text-slate-500 mt-1">
                    Optional. If no photo is uploaded, the first letter of the subscriber name will be shown.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input">
                </div>

                <div>
                    <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                    <input type="text" name="phone_number" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Subscription Plan <span class="text-red-500">*</span></label>
                    <select name="subscription_plan_id" class="form-input" required>
                        <option value="">Select subscription plan</option>

                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">
                                {{ $plan->plan_name }} - ₱{{ number_format($plan->price, 2) }}
                            </option>
                        @endforeach
                    </select>

                    <p class="text-xs text-slate-500 mt-1">
                        Plan price is based on the current settings.
                    </p>
                </div>

                <div>
                    <label class="form-label">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="form-input" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="disconnected">Disconnected</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Address <span class="text-red-500">*</span></label>
                <textarea name="address" rows="3" class="form-input resize-none" required></textarea>
            </div>

            <button class="w-full py-3 rounded-2xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                Create Subscriber
            </button>
        </form>
    </div>
</div>

<!-- Edit Subscriber Modal -->
<div id="editSubscriberModal"
    class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">

    <div class="modal-box bg-white w-full max-w-3xl rounded-3xl shadow-2xl p-6 border border-slate-200 max-h-[85vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Edit Subscriber</h2>
                <p class="text-sm text-slate-500">Update subscriber account information.</p>
            </div>

            <button onclick="closeEditSubscriberModal()"
                class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form id="editSubscriberForm" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="form-label">Change Profile Photo</label>
                <input
                    type="file"
                    name="profile_photo"
                    accept="image/png,image/jpeg,image/jpg,image/webp"
                    class="form-input">

                <p class="text-xs text-slate-500 mt-1">
                    Optional. Leave empty if you do not want to change the current photo.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input id="edit_full_name" type="text" name="full_name" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Username <span class="text-red-500">*</span></label>
                    <input id="edit_username" type="text" name="username" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input id="edit_email" type="email" name="email" class="form-input">
                </div>

                <div>
                    <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                    <input id="edit_phone_number" type="text" name="phone_number" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Subscription Plan <span class="text-red-500">*</span></label>
                    <select id="edit_subscription_plan_id" name="subscription_plan_id" class="form-input" required>
                        <option value="">Select subscription plan</option>

                        @foreach($plans as $plan)
                            <option 
                                value="{{ $plan->id }}"
                                data-plan-name="{{ $plan->plan_name }}"
                                data-price="{{ $plan->price }}">
                                {{ $plan->plan_name }} - ₱{{ number_format($plan->price, 2) }}
                            </option>
                        @endforeach
                    </select>

                    <p class="text-xs text-slate-500 mt-1">
                        Updating the plan will use the current price from Settings.
                    </p>
                </div>

                <div>
                    <label class="form-label">Status <span class="text-red-500">*</span></label>
                    <select id="edit_status" name="status" class="form-input" required>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="disconnected">Disconnected</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="form-label">Address <span class="text-red-500">*</span></label>
                <textarea id="edit_address" name="address" rows="3" class="form-input resize-none" required></textarea>
            </div>

            <button class="w-full py-3 rounded-2xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                Save Changes
            </button>
        </form>
    </div>
</div>

<style>
    .subscriber-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.04);
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
        background: white;
    }

    .form-input:focus {
        border-color: #0f172a;
        box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.08);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        color: #475569;
    }

    .animate-fadeIn {
        animation: fadeIn 0.45s ease-in-out;
    }

    .animate-alert {
        animation: alertSlide 0.35s ease-in-out;
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

    @keyframes alertSlide {
        from {
            opacity: 0;
            transform: translateY(-8px);
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
    function openCreateSubscriberModal() {
        const modal = document.getElementById('createSubscriberModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCreateSubscriberModal() {
        const modal = document.getElementById('createSubscriberModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditSubscriberModal(button) {
        const modal = document.getElementById('editSubscriberModal');
        const form = document.getElementById('editSubscriberForm');

        const id = button.dataset.id;

        form.action = '/subscriber/update/' + id;

        document.getElementById('edit_full_name').value = button.dataset.fullName || '';
        document.getElementById('edit_username').value = button.dataset.username || '';
        document.getElementById('edit_email').value = button.dataset.email || '';
        document.getElementById('edit_phone_number').value = button.dataset.phoneNumber || '';
        document.getElementById('edit_address').value = button.dataset.address || '';
        
        const editPlanSelect = document.getElementById('edit_subscription_plan_id');
        const currentPlanName = button.dataset.planName || '';

        editPlanSelect.value = '';

        Array.from(editPlanSelect.options).forEach(option => {
            if (option.dataset.planName === currentPlanName) {
                editPlanSelect.value = option.value;
            }
        });
        document.getElementById('edit_status').value = button.dataset.status || 'active';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditSubscriberModal() {
        const modal = document.getElementById('editSubscriberModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

@endsection