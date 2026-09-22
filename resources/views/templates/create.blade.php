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


<div class="page content-wrapper">


    @include('partials.topbar')



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

                <div class="template-colors">

                <h3>
                    <i data-lucide="palette"></i>
                    Template Colours
                </h3>

                <p class="color-help">
                    Choose the colours used when generating this
                    quotation or invoice.
                </p>


                <div class="color-grid">

                    {{-- PRIMARY --}}
                    <div class="color-field">

                        <label>
                            Primary Colour
                        </label>

                        <div class="color-control">

                            <input
                                type="color"
                                id="primaryColor"
                                name="primary_color"
                                value="{{ old(
                                    'primary_color',
                                    '#163B65'
                                ) }}"
                            >

                            <input
                                type="text"
                                id="primaryColorText"
                                value="{{ old(
                                    'primary_color',
                                    '#163B65'
                                ) }}"
                                maxlength="7"
                            >

                        </div>

                        <small>
                            Headers, titles and main branding
                        </small>

                    </div>


                    {{-- SECONDARY --}}
                    <div class="color-field">

                        <label>
                            Secondary Colour
                        </label>

                        <div class="color-control">

                            <input
                                type="color"
                                id="secondaryColor"
                                name="secondary_color"
                                value="{{ old(
                                    'secondary_color',
                                    '#EAF2FB'
                                ) }}"
                            >

                            <input
                                type="text"
                                id="secondaryColorText"
                                value="{{ old(
                                    'secondary_color',
                                    '#EAF2FB'
                                ) }}"
                                maxlength="7"
                            >

                        </div>

                        <small>
                            Backgrounds and highlighted areas
                        </small>

                    </div>


                    {{-- TEXT --}}
                    <div class="color-field">

                        <label>
                            Text Colour
                        </label>

                        <div class="color-control">

                            <input
                                type="color"
                                id="textColor"
                                name="text_color"
                                value="{{ old(
                                    'text_color',
                                    '#0F172A'
                                ) }}"
                            >

                            <input
                                type="text"
                                id="textColorText"
                                value="{{ old(
                                    'text_color',
                                    '#0F172A'
                                ) }}"
                                maxlength="7"
                            >

                        </div>

                        <small>
                            Main document text
                        </small>

                    </div>


                    {{-- ACCENT --}}
                    <div class="color-field">

                        <label>
                            Accent Colour
                        </label>

                        <div class="color-control">

                            <input
                                type="color"
                                id="accentColor"
                                name="accent_color"
                                value="{{ old(
                                    'accent_color',
                                    '#1474E8'
                                ) }}"
                            >

                            <input
                                type="text"
                                id="accentColorText"
                                value="{{ old(
                                    'accent_color',
                                    '#1474E8'
                                ) }}"
                                maxlength="7"
                            >

                        </div>

                        <small>
                            Totals, borders and highlights
                        </small>

                    </div>

                </div>


                {{-- LIVE PREVIEW --}}
                <div
                    class="template-color-preview"
                    id="templateColorPreview"
                >

                    <div class="preview-header">
                        INVOICE
                    </div>

                    <div class="preview-content">

                        <strong>
                            Example Company
                        </strong>

                        <p>
                            Example customer invoice preview
                        </p>

                        <div class="preview-line"></div>

                        <div class="preview-total">
                            Total: LKR 125,000.00
                        </div>

                    </div>

                </div>

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

function setupColorPicker(
    colorId,
    textId,
    callback
) {
    const picker =
        document.getElementById(colorId);

    const text =
        document.getElementById(textId);


    if (!picker || !text) {
        return;
    }


    picker.addEventListener(
        'input',
        function () {

            text.value =
                picker.value.toUpperCase();

            callback();
        }
    );


    text.addEventListener(
        'input',
        function () {

            let value =
                text.value.trim();


            if (!value.startsWith('#')) {

                value =
                    '#' + value;
            }


            if (
                /^#[0-9A-Fa-f]{6}$/.test(value)
            ) {

                picker.value =
                    value;

                callback();
            }
        }
    );
}



function updateTemplatePreview()
{
    const primary =
        document.getElementById(
            'primaryColor'
        )?.value || '#163B65';


    const secondary =
        document.getElementById(
            'secondaryColor'
        )?.value || '#EAF2FB';


    const text =
        document.getElementById(
            'textColor'
        )?.value || '#0F172A';


    const accent =
        document.getElementById(
            'accentColor'
        )?.value || '#1474E8';


    const preview =
        document.getElementById(
            'templateColorPreview'
        );


    if (!preview) {
        return;
    }


    preview
        .querySelector('.preview-header')
        .style.backgroundColor =
            primary;


    preview
        .querySelector('.preview-content')
        .style.backgroundColor =
            secondary;


    preview
        .querySelector('.preview-content')
        .style.color =
            text;


    preview
        .querySelector('.preview-line')
        .style.backgroundColor =
            accent;


    preview
        .querySelector('.preview-total')
        .style.color =
            accent;
}



setupColorPicker(
    'primaryColor',
    'primaryColorText',
    updateTemplatePreview
);


setupColorPicker(
    'secondaryColor',
    'secondaryColorText',
    updateTemplatePreview
);


setupColorPicker(
    'textColor',
    'textColorText',
    updateTemplatePreview
);


setupColorPicker(
    'accentColor',
    'accentColorText',
    updateTemplatePreview
);


updateTemplatePreview();

lucide.createIcons();

</script>


</body>

</html>