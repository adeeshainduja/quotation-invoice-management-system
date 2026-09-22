<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Customer - {{ $customer->customer_name }}</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer-create.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')

@php
    $companyList = $companyList ?? collect();
    $companyId = $companyId ?? $customer->company_id;
@endphp

<div class="page">

    @include('partials.topbar')

    <main>

        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>

            <a href="{{ $companyId
                ? route('customers.index', ['company_id' => $companyId])
                : route('customers.index') }}">
                Customers
            </a>

            <span>›</span>

            <a href="{{ route('customers.show', ['id' => $customer->id, 'company_id' => $customer->company_id]) }}">
                {{ $customer->customer_name }}
            </a>

            <span>›</span>

            Edit Customer

        </div>


        <div class="create-heading">

            <div>

                <h1>Edit Customer</h1>

                <p>
                    Update customer details and contact information for {{ $customer->business_name }}.
                </p>

            </div>

            <a
                href="{{ route('customers.show', ['id' => $customer->id, 'company_id' => $customer->company_id]) }}"
                class="back-btn"
            >
                <i data-lucide="arrow-left"></i>
                Back to Details
            </a>

        </div>


        @if ($errors->any())
            <div class="form-errors">
                {{ $errors->first() }}
            </div>
        @endif

        @if(session('error'))
            <div class="form-errors">
                {{ session('error') }}
            </div>
        @endif


        <form
            method="POST"
            action="{{ route('customers.update', $customer->id) }}"
        >
            @csrf
            @method('PUT')

            <div class="form-grid">

                {{-- BASIC INFORMATION --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="user"></i>
                        Basic Information
                    </h2>

                    <label>
                        Company <span>*</span>
                    </label>

                    <select
                        name="company_id"
                        required
                    >
                        <option value="">
                            Select Company
                        </option>

                        @foreach($companyList as $company)
                            <option
                                value="{{ $company->id }}"
                                {{ old('company_id', $customer->company_id) == $company->id ? 'selected' : '' }}
                            >
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>

                    <label>
                        Customer Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="customer_name"
                        value="{{ old('customer_name', $customer->customer_name) }}"
                        placeholder="Enter customer name"
                        required
                    >

                    <label>
                        Business Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="business_name"
                        value="{{ old('business_name', $customer->business_name) }}"
                        placeholder="Enter business name"
                        required
                    >

                    <div class="two-columns">

                        <div>
                            <label>
                                Registration Number
                            </label>

                            <input
                                type="text"
                                name="registration_number"
                                value="{{ old('registration_number', $customer->registration_number) }}"
                                placeholder="Enter registration number"
                            >
                        </div>

                        <div>
                            <label>
                                VAT Number
                            </label>

                            <input
                                type="text"
                                name="vat_number"
                                value="{{ old('vat_number', $customer->vat_number) }}"
                                placeholder="Enter VAT number"
                            >
                        </div>

                    </div>

                </section>


                {{-- CONTACT INFORMATION --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="phone"></i>
                        Contact Information
                    </h2>

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="{{ old('email', $customer->email) }}"
                        placeholder="Enter email address"
                    >

                    <label>
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone', $customer->phone) }}"
                        placeholder="Enter phone number"
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
                        value="{{ old('address_line_1', $customer->address_line_1) }}"
                        placeholder="Enter address line 1"
                        required
                    >

                    <label>
                        Address Line 2
                    </label>

                    <input
                        type="text"
                        name="address_line_2"
                        value="{{ old('address_line_2', $customer->address_line_2) }}"
                        placeholder="Enter address line 2 (optional)"
                    >

                    <div class="two-columns">

                        <div>
                            <label>
                                City <span>*</span>
                            </label>

                            <input
                                type="text"
                                name="city"
                                value="{{ old('city', $customer->city) }}"
                                placeholder="Enter city"
                                required
                            >
                        </div>

                        <div>
                            <label>
                                Country <span>*</span>
                            </label>

                            <select
                                name="country"
                                required
                            >
                                <option
                                    value="Sri Lanka"
                                    {{ old('country', $customer->country) === 'Sri Lanka' ? 'selected' : '' }}
                                >
                                    Sri Lanka
                                </option>

                                <option
                                    value="Australia"
                                    {{ old('country', $customer->country) === 'Australia' ? 'selected' : '' }}
                                >
                                    Australia
                                </option>

                                @if(!in_array($customer->country, ['Sri Lanka', 'Australia']) && !empty($customer->country))
                                    <option value="{{ $customer->country }}" selected>
                                        {{ $customer->country }}
                                    </option>
                                @endif
                            </select>
                        </div>

                    </div>

                </section>


                {{-- ADDITIONAL INFORMATION --}}
                <section class="form-card">

                    <h2>
                        <i data-lucide="notebook"></i>
                        Additional Information
                    </h2>

                    <label>
                        Internal Notes
                    </label>

                    <textarea
                        name="notes"
                        placeholder="Enter internal notes (optional)"
                    >{{ old('notes', $customer->notes) }}</textarea>

                    <label>
                        Status
                    </label>

                    <div class="status-options">

                        <label>
                            <input
                                type="radio"
                                name="status"
                                value="ACTIVE"
                                {{ old('status', $customer->status) === 'ACTIVE' ? 'checked' : '' }}
                            >
                            Active
                        </label>

                        <label>
                            <input
                                type="radio"
                                name="status"
                                value="INACTIVE"
                                {{ old('status', $customer->status) === 'INACTIVE' ? 'checked' : '' }}
                            >
                            Inactive
                        </label>

                    </div>

                </section>

            </div>

            <div class="form-actions">

                <a
                    href="{{ route('customers.show', ['id' => $customer->id, 'company_id' => $customer->company_id]) }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="save-btn"
                >
                    <i data-lucide="save"></i>
                    Update Customer
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
