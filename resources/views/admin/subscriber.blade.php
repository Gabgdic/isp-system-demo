@extends('layouts.app')

@section('title', 'Subscriber Management')

@section('content')

<div class="space-y-4 animate-fadeIn">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Subscriber Management</h1>
            <p class="text-slate-500 mt-1">Create and manage subscriber accounts.</p>
        </div>

        <button type="button" onclick="openCreateSubscriberModal()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold shadow-md hover:bg-slate-800 hover:-translate-y-0.5 transition-all duration-200">
            <span class="text-lg leading-none">+</span>
            Create Subscriber
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
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="subscriber-card">
            <p class="text-sm text-slate-500 font-medium">Total Subscribers</p>
            <h2 class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSubscribers }}</h2>
        </div>

        <div class="subscriber-card">
            <p class="text-sm text-slate-500 font-medium">Active Subscribers</p>
            <h2 class="text-2xl font-bold text-slate-900 mt-1">{{ $activeSubscribers }}</h2>
        </div>

        <div class="subscriber-card">
            <p class="text-sm text-slate-500 font-medium">Inactive Subscribers</p>
            <h2 class="text-2xl font-bold text-slate-900 mt-1">{{ $inactiveSubscribers }}</h2>
        </div>

        <div class="subscriber-card">
            <p class="text-sm text-slate-500 font-medium">Disconnected</p>
            <h2 class="text-2xl font-bold text-slate-900 mt-1">{{ $disconnectedSubscribers }}</h2>
        </div>
    </div>

    <!-- Subscriber Table -->
    <div class="bg-slate-50 rounded-2xl border border-slate-300 shadow-sm overflow-hidden">

        <!-- Table Header + Search -->
        <div class="px-4 py-3 border-b border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">All Subscribers</h2>
                    <p class="text-sm text-slate-500 mt-1">List of registered subscriber accounts.</p>
                </div>

                <div class="relative w-full sm:w-72">
                    <input
                        type="text"
                        id="subscriberSearch"
                        value="{{ $search }}"
                        placeholder="Search name, phone, or plan..."
                        class="form-input pr-10"
                        autocomplete="off"
                    >

                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div id="subscriber-table-content">
            @include('admin.partials.subscribers_table')
        </div>
    </div>
</div>

<!-- Create Subscriber Modal -->
<div id="createSubscriberModal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="modal-box bg-white w-full max-w-3xl rounded-2xl shadow-2xl p-5 border border-slate-200 max-h-[85vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Create Subscriber</h2>
                <p class="text-sm text-slate-500">Register a new subscriber account.</p>
            </div>
            <button type="button" onclick="closeCreateSubscriberModal()" class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">✕</button>
        </div>

        <form method="POST" action="{{ route('subscriber.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf

            <div>
                <label class="form-label">Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-input">
                <p class="text-xs text-slate-500 mt-1">Optional. If no photo is uploaded, the first letter of the subscriber name will be shown.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="full_name" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="createSubscriberPassword" type="password" name="password" class="form-input pr-14" placeholder="Enter password" minlength="8" required>
                        <button type="button" onclick="togglePassword('createSubscriberPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">Show</button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">At least 8 characters with 1 uppercase, 1 lowercase, 1 number, and 1 special character.</p>
                </div>

                <div>
                    <label class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input id="createSubscriberPasswordConfirmation" type="password" name="password_confirmation" class="form-input pr-14" placeholder="Confirm password" minlength="8" required>
                        <button type="button" onclick="togglePassword('createSubscriberPasswordConfirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">Show</button>
                    </div>
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input">
                </div>

                <div>
                    <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                    <input type="text" name="phone_number" class="form-input" required>
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Subscription Plan <span class="text-red-500">*</span></label>
                    <select name="subscription_plan_id" class="form-input" required>
                        <option value="">Select subscription plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->plan_name }} - ₱{{ number_format($plan->price, 2) }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Plan price is based on the current settings.</p>
                </div>
            </div>

            <div>
                <label class="form-label">Address <span class="text-red-500">*</span></label>
                <textarea name="address" rows="2" class="form-input resize-none" required></textarea>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                Create Subscriber
            </button>
        </form>
    </div>
</div>

<!-- Edit Subscriber Modal -->
<div id="editSubscriberModal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="modal-box bg-white w-full max-w-3xl rounded-2xl shadow-2xl p-5 border border-slate-200 max-h-[85vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Edit Subscriber</h2>
                <p class="text-sm text-slate-500">Update subscriber account information.</p>
            </div>

            <button type="button" onclick="closeEditSubscriberModal()" class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form id="editSubscriberForm" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            @method('PUT')

            <div>
                <label class="form-label">Change Profile Photo</label>
                <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/jpg,image/webp" class="form-input">
                <p class="text-xs text-slate-500 mt-1">Optional. Leave empty if you do not want to change the current photo.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Full Name <span class="text-red-500">*</span></label>
                    <input id="edit_full_name" type="text" name="full_name" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Username <span class="text-red-500">*</span></label>
                    <input id="edit_username" type="text" name="username" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">New Password</label>
                    <div class="relative">
                        <input id="editSubscriberPassword" type="password" name="password" class="form-input pr-14" placeholder="Leave blank to keep current password" minlength="8">
                        <button type="button" onclick="togglePassword('editSubscriberPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">Show</button>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">At least 8 characters with 1 uppercase, 1 lowercase, 1 number, and 1 special character. Leave blank to keep the current password.</p>
                </div>

                <div>
                    <label class="form-label">Confirm New Password</label>
                    <div class="relative">
                        <input id="editSubscriberPasswordConfirmation" type="password" name="password_confirmation" class="form-input pr-14" placeholder="Confirm new password" minlength="8">
                        <button type="button" onclick="togglePassword('editSubscriberPasswordConfirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">Show</button>
                    </div>
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input id="edit_email" type="email" name="email" class="form-input">
                </div>

                <div>
                    <label class="form-label">Phone Number <span class="text-red-500">*</span></label>
                    <input id="edit_phone_number" type="text" name="phone_number" class="form-input" required>
                </div>

                <div class="md:col-span-2">
                    <label class="form-label">Subscription Plan <span class="text-red-500">*</span></label>
                    <select id="edit_subscription_plan_id" name="subscription_plan_id" class="form-input" required>
                        <option value="">Select subscription plan</option>
                        @foreach($plans as $plan)
                            <option value="{{ $plan->id }}" data-plan-name="{{ $plan->plan_name }}" data-price="{{ $plan->price }}">
                                {{ $plan->plan_name }} - ₱{{ number_format($plan->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-500 mt-1">Updating the plan will use the current price from Settings.</p>
                </div>
            </div>

            <div>
                <label class="form-label">Address <span class="text-red-500">*</span></label>
                <textarea id="edit_address" name="address" rows="2" class="form-input resize-none" required></textarea>
            </div>

            <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                Save Changes
            </button>
        </form>
    </div>
</div>

<!-- Delete Subscriber Modal -->
<div id="deleteSubscriberModal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="modal-box bg-white w-full max-w-md rounded-2xl shadow-2xl p-5 border border-slate-200">

        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-bold text-red-600">Delete Subscriber</h2>
                <p class="text-sm text-slate-500">Confirm the deletion with your admin password.</p>
            </div>

            <button type="button" onclick="closeDeleteSubscriberModal()" class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4">
            <p class="text-xs text-red-500 font-semibold uppercase tracking-wide">Subscriber</p>
            <p id="deleteSubscriberName" class="font-bold text-slate-900 mt-1"></p>
            <p class="text-xs text-red-600 mt-1">This action cannot be undone.</p>
        </div>

        <form id="deleteSubscriberForm" method="POST" class="space-y-3">
            @csrf

            <div>
                <label class="form-label text-red-600">Your Current Admin Password</label>
                <div class="relative">
                    <input id="deleteSubscriberPassword" type="password" name="password" class="form-input pr-14" placeholder="Enter your admin password" required>
                    <button type="button" onclick="togglePassword('deleteSubscriberPassword', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-500 hover:text-slate-900">Show</button>
                </div>
                <p class="text-xs text-slate-500 mt-1">Your password is required to authorize this deletion.</p>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="closeDeleteSubscriberModal()" class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-700 font-semibold hover:bg-slate-200 transition">
                    Cancel
                </button>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition">
                    Confirm Delete
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.subscriber-card {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 20px;
    padding: 16px;
    box-shadow: 0 6px 24px rgba(15, 23, 42, 0.04);
}

html.dark .subscriber-card {
    background: #1f2937;
    border-color: #374151;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.18);
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-size: 13px;
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
    background: white;
}

.form-input:focus {
    border-color: #0f172a;
    box-shadow: 0 0 0 4px rgba(15, 23, 42, 0.08);
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
    document.getElementById('editSubscriberPassword').value = '';
    document.getElementById('editSubscriberPasswordConfirmation').value = '';

    const editPlanSelect = document.getElementById('edit_subscription_plan_id');
    const planId = button.dataset.subscriptionPlanId;
    const currentPlanName = button.dataset.planName || '';

    editPlanSelect.value = '';

    // First try matching by subscription_plan_id
    if (planId) {
        editPlanSelect.value = planId;
    }

    // Fallback to matching by plan_name if plan_id fails
    if (!editPlanSelect.value && currentPlanName) {
        Array.from(editPlanSelect.options).forEach(function(option) {
            if (option.dataset.planName === currentPlanName) {
                editPlanSelect.value = option.value;
            }
        });
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditSubscriberModal() {
    const modal = document.getElementById('editSubscriberModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openDeleteSubscriberModal(id, name) {
    const modal = document.getElementById('deleteSubscriberModal');
    const form = document.getElementById('deleteSubscriberForm');
    const nameElement = document.getElementById('deleteSubscriberName');

    form.action = '/subscriber/delete/' + id;
    nameElement.textContent = name;
    document.getElementById('deleteSubscriberPassword').value = '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDeleteSubscriberModal() {
    const modal = document.getElementById('deleteSubscriberModal');
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

let subscriberSearchTimeout;
let subscriberTableRequest;

document.addEventListener('click', function(event) {
    const link = event.target.closest('#subscriber-table-content a');
    if (!link || !link.href) return;

    event.preventDefault();
    fetchSubscriberTable(link.href);
});

document.addEventListener('input', function(event) {
    if (event.target.id !== 'subscriberSearch') return;

    clearTimeout(subscriberSearchTimeout);
    subscriberSearchTimeout = setTimeout(() => {
        const url = new URL(@json(route('subscriber')), window.location.origin);
        const search = event.target.value.trim();

        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }

        fetchSubscriberTable(url.toString());
    }, 300);
});

function fetchSubscriberTable(url) {
    const target = document.getElementById('subscriber-table-content');
    if (!target) return;

    subscriberTableRequest?.abort();
    subscriberTableRequest = new AbortController();
    target.setAttribute('aria-busy', 'true');
    target.innerHTML = '<div class="px-4 py-8 text-center text-sm text-slate-500">Loading subscribers...</div>';

    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: subscriberTableRequest.signal
    })
        .then(response => {
            if (!response.ok) throw new Error('Failed to load subscribers.');
            return response.text();
        })
        .then(html => {
            target.innerHTML = html;
            target.removeAttribute('aria-busy');
        })
        .catch(error => {
            if (error.name === 'AbortError') return;
            console.error('Error updating subscribers:', error);
            target.innerHTML = '<div class="px-4 py-8 text-center text-sm text-red-500">Failed to load subscribers.</div>';
            target.removeAttribute('aria-busy');
        });
}
</script>

@endsection