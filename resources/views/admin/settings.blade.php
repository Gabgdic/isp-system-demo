@extends('layouts.app')

@section('title', 'Settings')

@section('content')

<div class="space-y-8 animate-fadeIn">

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-slate-900">System Settings</h1>
        <p class="text-slate-500 mt-1">Modify your system logo, system name, and subscription plans.</p>
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

        <!-- System Settings -->
        <div class="xl:col-span-1 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 settings-card">

            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">System Information</h2>
                <p class="text-sm text-slate-500 mt-1">Change the visible system name and logo.</p>
            </div>

            <form action="{{ route('settings.system.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="form-label">Current Logo</label>

                    <div class="flex items-center gap-4">
                        @if($settings->system_logo)
                            <img src="{{ asset('storage/' . $settings->system_logo) }}"
                                class="w-16 h-16 rounded-2xl object-cover border border-slate-200 shadow-sm"
                                alt="System Logo">
                        @else
                            <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl font-bold shadow-sm">
                                {{ strtoupper(substr($settings->system_name ?? 'S', 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <p class="text-sm font-semibold text-slate-700">{{ $settings->system_name }}</p>
                            <p class="text-xs text-slate-500">Shown in the system header.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="form-label">System Name <span class="text-red-500">*</span></label>
                    <input type="text" id="system_name" name="system_name" value="{{ $settings->system_name }}" class="form-input" required>
                </div>

                <div>
                    <label class="form-label">Change Logo</label>
                    <input type="file"
                        name="system_logo"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="form-input">

                    <p class="text-xs text-slate-500 mt-1">
                        Optional. JPG, PNG, or WEBP only.
                    </p>
                </div>

                <button id="saveSettingsBtn" type="submit" class="w-full py-3 rounded-2xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                    Save System Settings
                </button>
            </form>
        </div>

        <!-- Subscription Plans -->
        <div class="xl:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden settings-card">

            <div class="px-6 py-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Subscription Plans</h2>
                    <p class="text-sm text-slate-500 mt-1">Create and modify plan prices anytime.</p>
                </div>

                <button onclick="openCreatePlanModal()"
                    class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-slate-900 text-white text-sm font-semibold shadow-md hover:bg-slate-800 transition">
                    <span class="text-lg leading-none">+</span>
                    Add Plan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Plan</th>
                            <th class="px-6 py-4 text-left font-semibold">Price</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-left font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($plans as $plan)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <p class="font-semibold text-slate-800">{{ $plan->plan_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $plan->description ?? 'No description' }}</p>
                                </td>

                                <td class="px-6 py-4 font-bold text-slate-800">
                                    ₱{{ number_format($plan->price, 2) }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($plan->status === 'active')
                                        <span class="status-badge bg-green-50 text-green-700">Active</span>
                                    @else
                                        <span class="status-badge bg-slate-100 text-slate-600">Inactive</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button
                                            type="button"
                                            onclick="openEditPlanModal(this)"
                                            data-id="{{ $plan->id }}"
                                            data-plan-name="{{ $plan->plan_name }}"
                                            data-price="{{ $plan->price }}"
                                            data-description="{{ $plan->description }}"
                                            data-status="{{ $plan->status }}"
                                            class="px-4 py-2 rounded-xl bg-blue-50 text-blue-600 text-xs font-bold hover:bg-blue-600 hover:text-white transition-all duration-200">
                                            Edit
                                        </button>

                                        <form action="{{ route('settings.plans.delete', $plan->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this plan?')">
                                            @csrf

                                            <button class="px-4 py-2 rounded-xl bg-red-50 text-red-600 text-xs font-bold hover:bg-red-600 hover:text-white transition-all duration-200">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-500">
                                    No subscription plans found. Add your first plan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<!-- Create Plan Modal -->
<div id="createPlanModal"
    class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">

    <div class="modal-box bg-white w-full max-w-md rounded-3xl shadow-2xl p-6 border border-slate-200 max-h-[85vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Add Subscription Plan</h2>
                <p class="text-sm text-slate-500">Create a new plan and set its price.</p>
            </div>

            <button onclick="closeCreatePlanModal()"
                class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form action="{{ route('settings.plans.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="form-label">Plan Name <span class="text-red-500">*</span></label>
                <input type="text" name="plan_name" class="form-input" placeholder="Example: Basic Plan" required>
            </div>

            <div>
                <label class="form-label">Price <span class="text-red-500">*</span></label>
                <input type="number" name="price" step="0.01" min="0" class="form-input" placeholder="700.00" required>
            </div>

            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-input resize-none" placeholder="Optional plan description"></textarea>
            </div>

            <div>
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" class="form-input" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <button class="w-full py-3 rounded-2xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                Save Plan
            </button>
        </form>
    </div>
</div>

<!-- Edit Plan Modal -->
<div id="editPlanModal"
    class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">

    <div class="modal-box bg-white w-full max-w-md rounded-3xl shadow-2xl p-6 border border-slate-200 max-h-[85vh] overflow-y-auto">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Edit Subscription Plan</h2>
                <p class="text-sm text-slate-500">Update plan name, price, or status.</p>
            </div>

            <button onclick="closeEditPlanModal()"
                class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-800 transition">
                ✕
            </button>
        </div>

        <form id="editPlanForm" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="form-label">Plan Name <span class="text-red-500">*</span></label>
                <input id="edit_plan_name" type="text" name="plan_name" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Price <span class="text-red-500">*</span></label>
                <input id="edit_price" type="number" name="price" step="0.01" min="0" class="form-input" required>
            </div>

            <div>
                <label class="form-label">Description</label>
                <textarea id="edit_description" name="description" rows="3" class="form-input resize-none"></textarea>
            </div>

            <div>
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select id="edit_status" name="status" class="form-input" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <button class="w-full py-3 rounded-2xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition">
                Save Changes
            </button>
        </form>
    </div>
</div>

<style>
    .settings-card {
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

    .modal-box::-webkit-scrollbar {
        width: 8px;
    }

    .modal-box::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 999px;
    }

    .modal-box::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }

    .modal-box::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
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
    function openCreatePlanModal() {
        const modal = document.getElementById('createPlanModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCreatePlanModal() {
        const modal = document.getElementById('createPlanModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openEditPlanModal(button) {
        const modal = document.getElementById('editPlanModal');
        const form = document.getElementById('editPlanForm');

        form.action = '/settings/plans/update/' + button.dataset.id;

        document.getElementById('edit_plan_name').value = button.dataset.planName || '';
        document.getElementById('edit_price').value = button.dataset.price || '';
        document.getElementById('edit_description').value = button.dataset.description || '';
        document.getElementById('edit_status').value = button.dataset.status || 'active';

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeEditPlanModal() {
        const modal = document.getElementById('editPlanModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>

@endsection