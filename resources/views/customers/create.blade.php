<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Customer</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer-create.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')


<div class="page content-wrapper">

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

            Add Customer

        </div>


        <div class="create-heading">

            <div>

                <h1>Add New Customer</h1>

                <p>
                    Enter customer information to add a new customer under the selected company.
                </p>

            </div>


            <a
                href="{{ $companyId
                    ? route('customers.index', ['company_id' => $companyId])
                    : route('customers.index') }}"
                class="back-btn"
            >

                <i data-lucide="arrow-left"></i>

                Back to Customers

            </a>

        </div>


        @if ($errors->any())

            <div class="form-errors">
                {{ $errors->first() }}
            </div>

        @endif


        @if($companyList->isEmpty())

            <div class="form-errors">

                No active company exists.

                <a href="{{ route('companies.create') }}">
                    Add Company
                </a>

            </div>

        @endif


        <form
            method="POST"
            action="{{ route('customers.store') }}"
        >

            @csrf


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
                                {{ old('company_id', $companyId) == $company->id ? 'selected' : '' }}
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
                        value="{{ old('customer_name') }}"
                        placeholder="Enter customer name"
                        required
                    >


                    <label>
                        Business Name <span>*</span>
                    </label>

                    <input
                        type="text"
                        name="business_name"
                        value="{{ old('business_name') }}"
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
                                value="{{ old('registration_number') }}"
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
                                value="{{ old('vat_number') }}"
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
                        value="{{ old('email') }}"
                        placeholder="Enter email address"
                    >


                    <label>
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
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
                        value="{{ old('address_line_1') }}"
                        placeholder="Enter address line 1"
                        required
                    >


                    <label>
                        Address Line 2
                    </label>

                    <input
                        type="text"
                        name="address_line_2"
                        value="{{ old('address_line_2') }}"
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
                                value="{{ old('city') }}"
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
                                    {{ old('country', 'Sri Lanka') === 'Sri Lanka' ? 'selected' : '' }}
                                >
                                    Sri Lanka
                                </option>

                                <option
                                    value="Australia"
                                    {{ old('country') === 'Australia' ? 'selected' : '' }}
                                >
                                    Australia
                                </option>

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
                    >{{ old('notes') }}</textarea>


                    <label>
                        Status
                    </label>


                    <div class="status-options">

                        <label>

                            <input
                                type="radio"
                                name="status"
                                value="ACTIVE"
                                {{ old('status', 'ACTIVE') === 'ACTIVE' ? 'checked' : '' }}
                            >

                            Active

                        </label>


                        <label>

                            <input
                                type="radio"
                                name="status"
                                value="INACTIVE"
                                {{ old('status') === 'INACTIVE' ? 'checked' : '' }}
                            >

                            Inactive

                        </label>

                    </div>

                </section>

            </div>


            <div class="form-actions">

                <a
                    href="{{ $companyId
                        ? route('customers.index', ['company_id' => $companyId])
                        : route('customers.index') }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="save-btn"
                    {{ $companyList->isEmpty() ? 'disabled' : '' }}
                >

                    <i data-lucide="save"></i>

                    Save Customer

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