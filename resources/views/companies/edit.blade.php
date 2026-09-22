<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Company - {{ $company->name }}</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer-create.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')

<div class="page">
    @include('partials.topbar')

    <main>

        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>

            <a href="{{ route('companies.index') }}">
                Companies
            </a>

            <span>›</span>

            Edit Company

        </div>


        <div class="create-heading">

            <div>
                <h1>Edit Company</h1>

                <p>
                    Update company information, branding, tax settings, and document prefixes.
                </p>
            </div>

            <a href="{{ route('companies.index') }}"
               class="back-btn">

                <i data-lucide="arrow-left"></i>

                Back to Companies
            </a>

        </div>


        @if ($errors->any())

            <div class="form-errors">
                {{ $errors->first() }}
            </div>

        @endif


        <form
            method="POST"
            action="{{ route('companies.update', $company->id) }}"
            enctype="multipart/form-data"
        >

            @csrf
            @method('PUT')


            <div class="form-grid">

                {{-- COMPANY INFORMATION --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="building-2"></i>
                        Company Information
                    </h2>


                    <label>
                        Company Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $company->name) }}"
                        placeholder="Enter company name"
                        required
                    >


                    <label>
                        Registration Number <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="registration_number"
                        value="{{ old('registration_number', $company->registration_number) }}"
                        placeholder="Enter registration number"
                        required
                    >


                    <label>Website</label>

                    <input
                        type="url"
                        name="website"
                        value="{{ old('website', $company->website) }}"
                        placeholder="https://example.com"
                    >

                </section>


                {{-- CONTACT INFORMATION --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="phone"></i>
                        Contact Information
                    </h2>


                    <label>
                        Email <span>*</span>
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $company->email) }}"
                        placeholder="Enter email address"
                        required
                    >


                    <label>
                        Phone <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone', $company->phone) }}"
                        placeholder="Enter phone number"
                        required
                    >


                    <label>Logo</label>

                    @if(!empty($company->logo_path))
                        <div style="margin-bottom: 8px;">
                            <small style="color: #64748b;">Current Logo: {{ basename($company->logo_path) }}</small>
                        </div>
                    @endif

                    <input
                        type="file"
                        name="logo_path"
                        accept="image/*"
                    >


                    <label>Signature</label>

                    @if(!empty($company->signature_path))
                        <div style="margin-bottom: 8px;">
                            <small style="color: #64748b;">Current Signature: {{ basename($company->signature_path) }}</small>
                        </div>
                    @endif

                    <input
                        type="file"
                        name="signature_path"
                        accept="image/*"
                    >


                    <label>Stamp</label>

                    @if(!empty($company->stamp_path))
                        <div style="margin-bottom: 8px;">
                            <small style="color: #64748b;">Current Stamp: {{ basename($company->stamp_path) }}</small>
                        </div>
                    @endif

                    <input
                        type="file"
                        name="stamp_path"
                        accept="image/*"
                    >

                </section>


                {{-- ADDRESS --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="map-pin"></i>
                        Address
                    </h2>


                    <label>
                        Address Line 1 <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="address_line_1"
                        value="{{ old('address_line_1', $company->address_line_1) }}"
                        placeholder="Enter address line 1"
                        required
                    >


                    <label>Address Line 2</label>

                    <input
                        type="text"
                        name="address_line_2"
                        value="{{ old('address_line_2', $company->address_line_2) }}"
                        placeholder="Enter address line 2"
                    >


                    <label>
                        City <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="city"
                        value="{{ old('city', $company->city) }}"
                        placeholder="Enter city"
                        required
                    >


                    <label>
                        Country <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="country"
                        value="{{ old('country', $company->country ?? 'Sri Lanka') }}"
                        required
                    >

                </section>


                {{-- TAX INFORMATION --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="receipt-text"></i>
                        Tax Information
                    </h2>

                    <label>
                        VAT Registered?
                    </label>

                    @php
                        $isVat = (bool) old('vat_enabled', old('vat_registered', $company->vat_enabled ?? $company->vat_registered ?? false));
                    @endphp

                    <select name="vat_enabled" id="vat_enabled">
                        <option value="0" {{ !$isVat ? 'selected' : '' }}>No</option>
                        <option value="1" {{ $isVat ? 'selected' : '' }}>Yes</option>
                    </select>

                    <label>VAT Number</label>

                    <input
                        type="text"
                        name="vat_number"
                        value="{{ old('vat_number', $company->vat_number) }}"
                        placeholder="Enter VAT number"
                    >

                    <label>TIN Number</label>

                    <input
                        type="text"
                        name="tin_number"
                        value="{{ old('tin_number', $company->tin_number ?? $company->tin ?? '') }}"
                        placeholder="Enter TIN number"
                    >

                    <label>Tax Registration Number</label>

                    <input
                        type="text"
                        name="tax_registration_number"
                        value="{{ old('tax_registration_number', $company->tax_registration_number ?? '') }}"
                        placeholder="Enter Tax Registration number"
                    >

                    <label>Tax Percentage (%)</label>

                    <input
                        type="number"
                        name="tax_percentage"
                        value="{{ old('tax_percentage', old('vat_percentage', $company->tax_percentage ?? $company->vat_percentage ?? '')) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        placeholder="18"
                    >

                </section>


                {{-- NUMBERING & SETTINGS --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="hash"></i>
                        Numbering & Settings
                    </h2>

                    <label>
                        Quotation Prefix <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="quotation_prefix"
                        value="{{ old('quotation_prefix', $company->quotation_prefix) }}"
                        required
                    >


                    <label>Next Quotation Number</label>

                    <input
                        type="number"
                        name="quotation_next_number"
                        value="{{ old('quotation_next_number', $company->quotation_next_number) }}"
                        min="1"
                    >


                    <label>
                        Invoice Prefix <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="invoice_prefix"
                        value="{{ old('invoice_prefix', $company->invoice_prefix) }}"
                        required
                    >


                    <label>Next Invoice Number</label>

                    <input
                        type="number"
                        name="invoice_next_number"
                        value="{{ old('invoice_next_number', $company->invoice_next_number) }}"
                        min="1"
                    >


                    <label>
                        Currency <span>*</span>
                    </label>

                    <select
                        name="currency"
                        required
                    >

                        <option
                            value="LKR"
                            {{ old('currency', $company->currency) === 'LKR' ? 'selected' : '' }}
                        >
                            LKR - Sri Lankan Rupee
                        </option>

                        <option
                            value="AUD"
                            {{ old('currency', $company->currency) === 'AUD' ? 'selected' : '' }}
                        >
                            AUD - Australian Dollar
                        </option>

                        <option
                            value="USD"
                            {{ old('currency', $company->currency) === 'USD' ? 'selected' : '' }}
                        >
                            USD - US Dollar
                        </option>

                    </select>


                    <label>Status</label>

                    <select name="status">

                        <option
                            value="ACTIVE"
                            {{ old('status', $company->status) === 'ACTIVE' ? 'selected' : '' }}
                        >
                            Active
                        </option>

                        <option
                            value="INACTIVE"
                            {{ old('status', $company->status) === 'INACTIVE' ? 'selected' : '' }}
                        >
                            Inactive
                        </option>

                    </select>

                </section>

            </div>


            <div class="form-actions">

                <a href="{{ route('companies.index') }}"
                   class="cancel-btn">
                    Cancel
                </a>


                <button
                    type="submit"
                    class="save-btn"
                >

                    <i data-lucide="save"></i>

                    Update Company

                </button>

            </div>

        </form>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>

</html>
