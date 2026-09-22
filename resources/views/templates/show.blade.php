<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        {{ $template->template_name }} - Preview
    </title>


    <link
        rel="stylesheet"
        href="{{ asset('css/dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/template-preview.css') }}"
    >


    <script src="https://unpkg.com/lucide@latest"></script>

</head>


<body>


@include('partials.sidebar')


<div class="page">


    @include('partials.topbar')



    <main>


        {{-- BREADCRUMB --}}
        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>


            <a
                href="{{ route(
                    'templates.index',
                    [
                        'company_id' =>
                            $template->company_id
                    ]
                ) }}"
            >
                Templates
            </a>

            <span>›</span>

            Preview

        </div>



        {{-- HEADER --}}
        <div class="preview-page-heading">


            <div>

                <div class="preview-title-row">

                    <h1>
                        {{ $template->template_name }}
                    </h1>


                    @if($template->is_default)

                        <span class="default-badge">
                            Default
                        </span>

                    @endif

                </div>


                <p>

                    {{
                        ucfirst(
                            strtolower(
                                $template->document_type
                            )
                        )
                    }}

                    Template Preview

                </p>

            </div>


            <a
                href="{{ route(
                    'templates.index',
                    [
                        'company_id' =>
                            $template->company_id
                    ]
                ) }}"
                class="back-btn"
            >

                <i data-lucide="arrow-left"></i>

                Back to Templates

            </a>

        </div>



        {{-- SETTINGS --}}
        <div class="template-settings">

            <div>

                <span>
                    Company
                </span>

                <strong>
                    {{ $company->name }}
                </strong>

            </div>


            <div>

                <span>
                    Type
                </span>

                <strong>
                    {{
                        ucfirst(
                            strtolower(
                                $template->document_type
                            )
                        )
                    }}
                </strong>

            </div>


            <div>

                <span>
                    Logo
                </span>

                <strong>
                    {{ $template->show_logo
                        ? 'Shown'
                        : 'Hidden' }}
                </strong>

            </div>


            <div>

                <span>
                    Bank Details
                </span>

                <strong>
                    {{ $template->show_bank_details
                        ? 'Shown'
                        : 'Hidden' }}
                </strong>

            </div>


            <div>

                <span>
                    VAT
                </span>

                <strong>
                    {{ $template->show_vat
                        ? 'Shown'
                        : 'Hidden' }}
                </strong>

            </div>


            <div>

                <span>
                    Signature
                </span>

                <strong>
                    {{ $template->show_signature
                        ? 'Shown'
                        : 'Hidden' }}
                </strong>

            </div>

        </div>



        {{-- DOCUMENT PREVIEW --}}
        <div class="preview-background">


            <div class="preview-label">

                <i data-lucide="eye"></i>

                Template Preview

            </div>



            <article
                class="template-document"
                style="
                    --primary:
                        {{ $colors['primary'] }};

                    --secondary:
                        {{ $colors['secondary'] }};

                    --text:
                        {{ $colors['text'] }};

                    --accent:
                        {{ $colors['accent'] }};
                "
            >


                <div class="top-color"></div>



                {{-- DOCUMENT HEADER --}}
                <div class="document-header">


                    <div class="company-info">


                        @if(
                            $template->show_logo &&
                            !empty($company->logo_path)
                        )

                            <img
                                src="{{ asset(
                                    'storage/'
                                    . ltrim(
                                        $company->logo_path,
                                        '/'
                                    )
                                ) }}"
                                class="company-logo"
                                alt="Company Logo"
                                onerror="this.style.display='none'"
                            >

                        @endif


                        <div>

                            <h2>
                                {{ $company->name }}
                            </h2>


                            <p>
                                {{ $company->address_line_1 }}
                            </p>


                            @if($company->address_line_2)

                                <p>
                                    {{ $company->address_line_2 }}
                                </p>

                            @endif


                            <p>

                                {{ $company->city }}

                                @if($company->country)

                                    ,
                                    {{ $company->country }}

                                @endif

                            </p>


                            <p>
                                {{ $company->phone }}
                            </p>


                            <p>
                                {{ $company->email }}
                            </p>

                        </div>

                    </div>



                    <div class="document-title">

                        <h1>
                            {{ $template->document_type }}
                        </h1>


                        <strong>

                            @if(
                                $template->document_type
                                === 'INVOICE'
                            )

                                INV-2026-0001

                            @else

                                QT-2026-0001

                            @endif

                        </strong>


                        <span>
                            21 Sep 2026
                        </span>

                    </div>

                </div>



                {{-- HEADER TEXT --}}
                @if($template->header_text)

                    <div class="template-header-text">

                        {!! nl2br(
                            e(
                                $template->header_text
                            )
                        ) !!}

                    </div>

                @endif



                {{-- CUSTOMER --}}
                <div class="customer-section">


                    <div>

                        <span class="small-title">

                            {{
                                $template->document_type
                                === 'INVOICE'
                                    ? 'BILL TO'
                                    : 'QUOTATION FOR'
                            }}

                        </span>


                        <h3>
                            Sample Customer Company
                        </h3>

                        <p>
                            Customer Name
                        </p>

                        <p>
                            123 Sample Road
                        </p>

                        <p>
                            Colombo, Sri Lanka
                        </p>

                    </div>



                    <div class="meta">

                        <p>
                            <span>Reference</span>
                            <strong>REF-001</strong>
                        </p>

                        <p>
                            <span>Currency</span>
                            <strong>
                                {{ $company->currency }}
                            </strong>
                        </p>

                    </div>

                </div>



                {{-- ITEMS --}}
                <div class="items">

                    <table>

                        <thead>

                        <tr>

                            <th>#</th>

                            <th>Description</th>

                            <th class="number">
                                Qty
                            </th>

                            <th class="number">
                                Unit Price
                            </th>

                            <th class="number">
                                Total
                            </th>

                        </tr>

                        </thead>


                        <tbody>

                        <tr>

                            <td>1</td>

                            <td>

                                <strong>
                                    Website Development
                                </strong>

                                <small>
                                    Design and development service
                                </small>

                            </td>

                            <td class="number">
                                1
                            </td>

                            <td class="number">
                                100,000.00
                            </td>

                            <td class="number">
                                100,000.00
                            </td>

                        </tr>


                        <tr>

                            <td>2</td>

                            <td>

                                <strong>
                                    Hosting Service
                                </strong>

                                <small>
                                    Annual managed hosting
                                </small>

                            </td>

                            <td class="number">
                                1
                            </td>

                            <td class="number">
                                25,000.00
                            </td>

                            <td class="number">
                                25,000.00
                            </td>

                        </tr>

                        </tbody>

                    </table>

                </div>



                {{-- TOTAL --}}
                <div class="totals-container">


                    <div></div>


                    <div class="totals">

                        <div>

                            <span>
                                Subtotal
                            </span>

                            <strong>
                                {{ $company->currency }}
                                125,000.00
                            </strong>

                        </div>


                        @if($template->show_vat)

                            <div>

                                <span>
                                    VAT
                                </span>

                                <strong>
                                    {{ $company->currency }}
                                    22,500.00
                                </strong>

                            </div>

                        @endif


                        <div class="grand-total">

                            <span>
                                Total
                            </span>

                            <strong>
                                {{ $company->currency }}
                                147,500.00
                            </strong>

                        </div>

                    </div>

                </div>



                {{-- BANK --}}
                @if(
                    $template->show_bank_details &&
                    $bank
                )

                    <div class="bank-details">

                        <h4>
                            Bank Details
                        </h4>


                        <p>

                            <strong>Bank:</strong>

                            {{ $bank->bank_name }}

                        </p>


                        <p>

                            <strong>Account:</strong>

                            {{ $bank->bank_account_name }}

                        </p>


                        <p>

                            <strong>Account No:</strong>

                            {{ $bank->bank_account_number }}

                        </p>

                    </div>

                @endif



                {{-- TERMS --}}
                @if($template->terms_conditions)

                    <div class="terms">

                        <h4>
                            Terms & Conditions
                        </h4>


                        <p>

                            {!! nl2br(
                                e(
                                    $template->terms_conditions
                                )
                            ) !!}

                        </p>

                    </div>

                @endif



                {{-- SIGNATURE --}}
                @if($template->show_signature)

                    <div class="signature-container">


                        <div></div>


                        <div class="signature">

                            @if($company->signature_path)

                                <img
                                    src="{{ asset(
                                        'storage/'
                                        . ltrim(
                                            $company->signature_path,
                                            '/'
                                        )
                                    ) }}"
                                    alt="Signature"
                                    onerror="this.style.display='none'"
                                >

                            @endif


                            <div class="signature-line"></div>


                            <strong>
                                Authorized Signature
                            </strong>

                        </div>

                    </div>

                @endif



                <div class="document-footer">

                    @if($template->footer_text)

                        {!! nl2br(
                            e(
                                $template->footer_text
                            )
                        ) !!}

                    @else

                        Thank you for your business.

                    @endif

                </div>


            </article>

        </div>

    </main>

</div>


<script>

    lucide.createIcons();

</script>


</body>

</html>