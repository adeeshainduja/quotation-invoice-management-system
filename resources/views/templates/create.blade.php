<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>New Template</title>


    <link
        rel="stylesheet"
        href="{{ asset('css/dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/template-create.css') }}"
    >


    <script src="https://unpkg.com/lucide@latest"></script>

</head>


<body>


@include('partials.sidebar')


<div class="page">


    <header class="topbar">

        <form
            method="GET"
            action="{{ route('templates.create') }}"
        >

            <select
                name="company_id"
                class="company-select"
                onchange="this.form.submit()"
            >

                <option value="">
                    Select Company
                </option>


                @foreach($companyList as $item)

                    <option
                        value="{{ $item->id }}"
                        {{ $companyId == $item->id
                            ? 'selected'
                            : '' }}
                    >
                        {{ $item->name }}
                    </option>

                @endforeach

            </select>

        </form>


        <div class="topbar-right">

            <div class="notification">
                <i data-lucide="bell"></i>
                <span></span>
            </div>


            <div class="user">

                <div class="avatar">
                    {{
                        strtoupper(
                            substr(
                                auth()->user()->name,
                                0,
                                2
                            )
                        )
                    }}
                </div>

                <div>

                    <strong>
                        {{ auth()->user()->name }}
                    </strong>

                    <small>
                        Administrator
                    </small>

                </div>

            </div>

        </div>

    </header>



    <main>


        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>


            <a
                href="{{ $companyId
                    ? route(
                        'templates.index',
                        ['company_id' => $companyId]
                    )
                    : route('templates.index') }}"
            >
                Templates
            </a>


            <span>›</span>

            New Template

        </div>



        <div class="create-heading">

            <div>

                <h1>
                    New Template
                </h1>

                <p>
                    Create a quotation or invoice template
                </p>

            </div>


            <a
                href="{{ $companyId
                    ? route(
                        'templates.index',
                        ['company_id' => $companyId]
                    )
                    : route('templates.index') }}"
                class="back-btn"
            >

                <i data-lucide="arrow-left"></i>

                Back to Templates

            </a>

        </div>



        @if($errors->any())

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

        @elseif(!$company)

            <div class="form-errors">
                Select a company before creating a template.
            </div>

        @endif



        <form
            method="POST"
            action="{{ route('templates.store') }}"
        >

            @csrf


            @if($companyId)

                <input
                    type="hidden"
                    name="company_id"
                    value="{{ $companyId }}"
                >

            @endif



            <div class="template-grid">


                <section class="form-card">

                    <h2>
                        <i data-lucide="file-text"></i>
                        Template Information
                    </h2>


                    <label>
                        Template Name *
                    </label>

                    <input
                        type="text"
                        name="template_name"
                        value="{{ old('template_name') }}"
                        placeholder="Example: Standard Invoice"
                        required
                    >


                    <label>
                        Document Type *
                    </label>


                    <select
                        name="document_type"
                        required
                    >

                        <option value="">
                            Select Type
                        </option>

                        <option
                            value="QUOTATION"
                            {{ old('document_type') === 'QUOTATION'
                                ? 'selected'
                                : '' }}
                        >
                            Quotation
                        </option>

                        <option
                            value="INVOICE"
                            {{ old('document_type') === 'INVOICE'
                                ? 'selected'
                                : '' }}
                        >
                            Invoice
                        </option>

                    </select>


                    <label>
                        Header Text
                    </label>

                    <textarea
                        name="header_text"
                        placeholder="Optional header content"
                    >{{ old('header_text') }}</textarea>


                    <label>
                        Footer Text
                    </label>

                    <textarea
                        name="footer_text"
                        placeholder="Optional footer content"
                    >{{ old('footer_text') }}</textarea>

                </section>



                <section class="form-card">

                    <h2>
                        <i data-lucide="settings-2"></i>
                        Display Options
                    </h2>


                    <label class="toggle-row">

                        <div>

                            <strong>
                                Show Company Logo
                            </strong>

                            <span>
                                Display company logo on document
                            </span>

                        </div>


                        <input
                            type="checkbox"
                            name="show_logo"
                            value="1"
                            {{ old('show_logo', 1)
                                ? 'checked'
                                : '' }}
                        >

                    </label>


                    <label class="toggle-row">

                        <div>

                            <strong>
                                Show Bank Details
                            </strong>

                            <span>
                                Display company bank information
                            </span>

                        </div>


                        <input
                            type="checkbox"
                            name="show_bank_details"
                            value="1"
                            {{ old('show_bank_details', 1)
                                ? 'checked'
                                : '' }}
                        >

                    </label>


                    <label class="toggle-row">

                        <div>

                            <strong>
                                Show VAT
                            </strong>

                            <span>
                                Show VAT/tax information
                            </span>

                        </div>


                        <input
                            type="checkbox"
                            name="show_vat"
                            value="1"
                            {{ old('show_vat', 1)
                                ? 'checked'
                                : '' }}
                        >

                    </label>


                    <label class="toggle-row">

                        <div>

                            <strong>
                                Show Signature
                            </strong>

                            <span>
                                Display company signature
                            </span>

                        </div>


                        <input
                            type="checkbox"
                            name="show_signature"
                            value="1"
                            {{ old('show_signature', 1)
                                ? 'checked'
                                : '' }}
                        >

                    </label>


                    <label class="toggle-row default-row">

                        <div>

                            <strong>
                                Default Template
                            </strong>

                            <span>
                                Use this template automatically for this document type
                            </span>

                        </div>


                        <input
                            type="checkbox"
                            name="is_default"
                            value="1"
                            {{ old('is_default')
                                ? 'checked'
                                : '' }}
                        >

                    </label>

                </section>

            </div>



            <section class="form-card terms-card">

                <h2>
                    <i data-lucide="notebook-tabs"></i>
                    Terms & Conditions
                </h2>


                <textarea
                    name="terms_conditions"
                    class="terms-textarea"
                    placeholder="Enter default terms and conditions for this template..."
                >{{ old('terms_conditions') }}</textarea>

            </section>



            <div class="form-actions">

                <a
                    href="{{ $companyId
                        ? route(
                            'templates.index',
                            ['company_id' => $companyId]
                        )
                        : route('templates.index') }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="save-btn"
                    @disabled(!$company)
                >

                    <i data-lucide="save"></i>

                    Save Template

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