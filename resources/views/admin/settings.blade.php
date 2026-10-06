@extends('layouts.app')

@section('title', 'Settings')

@section('content')

<div class="space-y-4 animate-fadeIn">

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">System Settings</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            Modify your system information and GCash payment details.
        </p>
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

    <div class="max-w-2xl">

        <!-- System Settings -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 settings-card">

            <div class="mb-4">
                <h2 class="text-base font-bold text-slate-900">System Information</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Manage your system name, logo, and GCash payment details.
                </p>
            </div>

            <form action="{{ route('settings.system.update') }}" method="POST"
                enctype="multipart/form-data" class="space-y-3">

                @csrf

                <!-- Current Logo -->
                <div>
                    <label class="form-label">Current Logo</label>

                    <div class="flex items-center gap-3">
                        @if($settings->system_logo)
                            <img src="{{ asset('storage/' . $settings->system_logo) }}"
                                class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-sm"
                                alt="System Logo">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center text-lg font-bold shadow-sm">
                                {{ strtoupper(substr($settings->system_name ?? 'S', 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <p class="text-sm font-semibold text-slate-700">
                                {{ $settings->system_name }}
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Shown in the system header.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- System Name -->
                <div>
                    <label class="form-label">
                        System Name
                        <span class="text-red-500">*</span>
                    </label>

                    <input type="text" id="system_name" name="system_name"
                        value="{{ old('system_name', $settings->system_name) }}"
                        class="form-input" required>
                </div>

                <!-- Change Logo -->
                <div>
                    <label class="form-label">Change Logo</label>

                    <input type="file" name="system_logo"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="form-input">

                    <p class="text-[11px] text-slate-500 mt-1">
                        Optional. JPG, PNG, or WEBP only.
                    </p>
                </div>

                <!-- GCash Payment Section -->
                <div class="pt-3 border-t border-slate-200">

                    <div class="mb-3">
                        <h3 class="text-sm font-bold text-slate-900">
                            GCash Payment Details
                        </h3>

                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            These details will be shown to subscribers when they pay
                            their monthly bill through GCash.
                        </p>
                    </div>

                    <!-- GCash Name -->
                    <div class="mb-3">
                        <label class="form-label">
                            GCash Account Name
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="gcash_name"
                            value="{{ old('gcash_name', $settings->gcash_name) }}"
                            class="form-input"
                            placeholder="Example: Juan Dela Cruz"
                            required>

                        <p class="text-[11px] text-slate-500 mt-1">
                            Enter the name registered to the ISP's GCash account.
                        </p>
                    </div>

                    <!-- GCash Number -->
                    <div>
                        <label class="form-label">
                            GCash Number
                            <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="gcash_number"
                            value="{{ old('gcash_number', $settings->gcash_number) }}"
                            class="form-input"
                            placeholder="Example: 09171234567"
                            maxlength="30"
                            required>

                        <p class="text-[11px] text-slate-500 mt-1">
                            This number will be displayed to subscribers for payment.
                        </p>
                    </div>

                </div>

                <!-- Save -->
                <button id="saveSettingsBtn" type="submit"
                    class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition">
                    Save System Settings
                </button>

            </form>
        </div>

    </div>
</div>

<style>
.settings-card {
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
    background: white;
    font-size: 14px;
}

.form-input:focus {
    border-color: #0f172a;
    box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.08);
}

.animate-fadeIn {
    animation: fadeIn 0.45s ease-in-out;
}

.animate-alert {
    animation: alertSlide 0.35s ease-in-out;
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
</style>

@endsection