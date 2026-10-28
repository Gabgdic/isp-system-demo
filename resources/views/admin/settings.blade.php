@extends('layouts.app')

@section('title', 'Settings')

@section('content')

<div class="space-y-4 animate-fadeIn">

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">System Settings</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            Modify your system details, GCash payment information, and Statement of Account (SOA) & Official Receipt (OR) formats.
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

    <!-- Side-by-Side Responsive Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">

        <!-- CARD 1: System & GCash Information -->
        <div class="bg-slate-50 rounded-2xl border border-slate-300 shadow-sm p-4 settings-card">

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
                                Shown in system header and generated documents (SOA & OR).
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
                            These details will be shown to subscribers when paying through GCash.
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
                    </div>

                </div>

                <!-- Save -->
                <button type="submit"
                    class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition mt-2">
                    Save System Settings
                </button>

            </form>
        </div>

        <!-- CARD 2: SOA and OR Settings -->
        <div class="bg-slate-50 rounded-2xl border border-slate-300 shadow-sm p-4 settings-card">

            <div class="mb-4">
                <h2 class="text-base font-bold text-slate-900">SOA and OR Customization</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    Customize location headers, authorized signatory details, and notice body statements used across SOA and Official Receipts.
                </p>
            </div>

            <form action="{{ route('settings.soa.update') }}" method="POST"
                enctype="multipart/form-data" class="space-y-3">

                @csrf

                <!-- Location Header -->
                <div>
                    <label class="form-label">Location</label>
                    <input type="text" name="soa_location"
                        value="{{ old('soa_location', $settings->soa_location ?? 'Zone 1, Sogod, Southern Leyte') }}"
                        class="form-input"
                        placeholder="e.g. Zone 1, Sogod, Southern Leyte">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <!-- Printed Name -->
                    <div>
                        <label class="form-label">
                            Signatory Name
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="authorized_signatory"
                            value="{{ old('authorized_signatory', $settings->authorized_signatory ?? 'JURISH A. SY') }}"
                            class="form-input uppercase" required>
                    </div>

                    <!-- Signatory Position -->
                    <div>
                        <label class="form-label">
                            Signatory Title
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="signatory_title"
                            value="{{ old('signatory_title', $settings->signatory_title ?? 'Proprietor') }}"
                            class="form-input" required>
                    </div>
                </div>

                <!-- Digital Signature Image -->
                <div>
                    <label class="form-label">Digital Signature</label>

                    @if(isset($settings->authorized_signature) && $settings->authorized_signature)
                        <div class="mb-2 flex items-center gap-3 p-2 bg-slate-50 rounded-xl border border-slate-200">
                            <img src="{{ asset('storage/' . $settings->authorized_signature) }}" class="h-10 object-contain" alt="Signature">
                            <span class="text-[11px] text-slate-500">Current signature graphic loaded.</span>
                        </div>
                    @endif

                    <input type="file" name="authorized_signature"
                        accept="image/png,image/jpeg,image/jpg,image/webp"
                        class="form-input">
                    <p class="text-[11px] text-slate-500 mt-1">Optional transparent PNG or JPG image of signature.</p>
                </div>

                <!-- Body Statement Textarea -->
                <div>
                    <label class="form-label">
                        SOA Statement Body Text
                        <span class="text-red-500">*</span>
                    </label>
                    <textarea name="soa_body_statement" rows="5" class="form-input text-xs leading-relaxed" required>{{ old('soa_body_statement', $settings->soa_body_statement ?? "You are hereby reminded of your monthly subscription due for the billing period stated above. We humbly request that payment be settled on or before the due date to avoid any possible service interruption.\n\nIf payment has already been made before receiving this notice, please disregard this billing statement. We truly appreciate your prompt payment and continued support.") }}</textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Defines the reminder text printed on SOA documents.</p>
                </div>

                <!-- Save SOA and OR Settings -->
                <button type="submit"
                    class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition mt-2">
                    Save SOA & OR Settings
                </button>

            </form>
        </div>

    </div>
</div>

<style>
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