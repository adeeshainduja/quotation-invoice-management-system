<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Templates</title>


    <link
        rel="stylesheet"
        href="{{ asset('css/dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/templates.css') }}"
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

            Templates

        </div>



        {{-- PAGE HEADING --}}
        <div class="templates-heading">


            <div>

                <h1>
                    Templates
                </h1>

                <p>
                    Manage quotation and invoice document templates
                </p>

            </div>


            <a
                href="{{ $companyId
                    ? route(
                        'templates.create',
                        ['company_id' => $companyId]
                    )
                    : route('templates.create') }}"
                class="new-template-btn"
            >

                <i data-lucide="plus"></i>

                New Template

            </a>

        </div>



        {{-- SUCCESS --}}
        @if(session('success'))

            <div class="alert-success">

                {{ session('success') }}

            </div>

        @endif



        {{-- FILTER --}}
        <form
            method="GET"
            action="{{ route('templates.index') }}"
            class="template-filter"
        >


            @if($companyId)

                <input
                    type="hidden"
                    name="company_id"
                    value="{{ $companyId }}"
                >

            @endif


            <select name="document_type">

                <option value="">
                    All Document Types
                </option>


                <option
                    value="QUOTATION"
                    {{ request('document_type') === 'QUOTATION'
                        ? 'selected'
                        : '' }}
                >
                    Quotation
                </option>


                <option
                    value="INVOICE"
                    {{ request('document_type') === 'INVOICE'
                        ? 'selected'
                        : '' }}
                >
                    Invoice
                </option>

            </select>


            <button
                type="submit"
                class="filter-btn"
            >

                <i data-lucide="filter"></i>

                Filter

            </button>


            <a
                href="{{ $companyId
                    ? route(
                        'templates.index',
                        ['company_id' => $companyId]
                    )
                    : route('templates.index') }}"
                class="clear-btn"
            >
                Clear
            </a>

        </form>



        {{-- TEMPLATE TABLE --}}
        <section class="templates-table-card">


            <div class="table-wrap">


                <table>


                    <thead>

                    <tr>

                        <th>#</th>

                        <th>
                            Template Name
                        </th>

                        <th>
                            Document
                        </th>

                        <th>
                            Logo
                        </th>

                        <th>
                            Bank
                        </th>

                        <th>
                            VAT
                        </th>

                        <th>
                            Signature
                        </th>

                        <th>
                            Default
                        </th>

                        <th>
                            Actions
                        </th>

                    </tr>

                    </thead>



                    <tbody>


                    @forelse($templates as $template)


                        <tr
                            class="clickable-template-row"
                            onclick="window.location='{{ route(
                                'templates.show',
                                $template->id
                            ) }}'"
                        >


                            <td>

                                {{
                                    $templates->firstItem()
                                    + $loop->index
                                }}

                            </td>



                            {{-- TEMPLATE NAME --}}
                            <td>

                                <a
                                    href="{{ route(
                                        'templates.show',
                                        $template->id
                                    ) }}"
                                    class="template-name-link"
                                    onclick="event.stopPropagation()"
                                >

                                    {{ $template->template_name }}

                                </a>

                            </td>



                            {{-- DOCUMENT TYPE --}}
                            <td>

                                <span
                                    class="document-type {{
                                        strtolower(
                                            $template->document_type
                                        )
                                    }}"
                                >

                                    {{
                                        ucfirst(
                                            strtolower(
                                                $template->document_type
                                            )
                                        )
                                    }}

                                </span>

                            </td>



                            {{-- LOGO --}}
                            <td>

                                @if($template->show_logo)

                                    <i
                                        data-lucide="check"
                                        class="yes-icon"
                                    ></i>

                                @else

                                    <span class="no-value">
                                        -
                                    </span>

                                @endif

                            </td>



                            {{-- BANK --}}
                            <td>

                                @if($template->show_bank_details)

                                    <i
                                        data-lucide="check"
                                        class="yes-icon"
                                    ></i>

                                @else

                                    <span class="no-value">
                                        -
                                    </span>

                                @endif

                            </td>



                            {{-- VAT --}}
                            <td>

                                @if($template->show_vat)

                                    <i
                                        data-lucide="check"
                                        class="yes-icon"
                                    ></i>

                                @else

                                    <span class="no-value">
                                        -
                                    </span>

                                @endif

                            </td>



                            {{-- SIGNATURE --}}
                            <td>

                                @if($template->show_signature)

                                    <i
                                        data-lucide="check"
                                        class="yes-icon"
                                    ></i>

                                @else

                                    <span class="no-value">
                                        -
                                    </span>

                                @endif

                            </td>



                            {{-- DEFAULT --}}
                            <td>

                                @if($template->is_default)

                                    <span class="default-badge">

                                        Default

                                    </span>

                                @else

                                    <span class="not-default">
                                        -
                                    </span>

                                @endif

                            </td>



                            {{-- ACTION --}}
                            <td>

                                <a
                                    href="{{ route(
                                        'templates.show',
                                        $template->id
                                    ) }}"
                                    class="preview-template-btn"
                                    onclick="event.stopPropagation()"
                                    title="Preview Template"
                                >

                                    <i data-lucide="eye"></i>

                                    Preview

                                </a>

                            </td>


                        </tr>



                    @empty


                        <tr>

                            <td
                                colspan="9"
                                class="empty"
                            >

                                @if(!$companyId)

                                    Select a company to view templates.

                                @else

                                    No templates found.

                                @endif

                            </td>

                        </tr>


                    @endforelse


                    </tbody>

                </table>

            </div>



            {{-- PAGINATION --}}
            <div class="table-footer">


                <span>

                    Showing

                    {{
                        $templates->firstItem()
                        ?? 0
                    }}

                    to

                    {{
                        $templates->lastItem()
                        ?? 0
                    }}

                    of

                    {{ $templates->total() }}

                    templates

                </span>


                {{ $templates->links() }}

            </div>

        </section>

    </main>

</div>


<script>

    lucide.createIcons();

</script>


</body>

</html>