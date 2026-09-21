<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $quotation->quotation_number }}</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/quotation-show.css') }}">

    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')

<div class="page">

    <header class="topbar">

        <div class="company-box">
            <i data-lucide="building-2"></i>
            {{ $company->name ?? 'Company' }}
        </div>

        <div class="topbar-right">

            <div class="notification">
                <i data-lucide="bell"></i>
                <span></span>
            </div>

            <div class="user">

                <div class="avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>

                <div>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>Administrator</small>
                </div>

            </div>

        </div>

    </header>


    <main>

        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">Home</a>

            <span>›</span>

            <a href="{{ route('quotations.index', [
                'company_id' => $quotation->company_id
            ]) }}">
                Quotations
            </a>

            <span>›</span>

            {{ $quotation->quotation_number }}

        </div>


        <div class="details-heading">

            <div>

                <div class="title-row">

                    <h1>{{ $quotation->quotation_number }}</h1>

                    <span class="status {{ strtolower($quotation->status) }}">
                        {{ $quotation->status }}
                    </span>

                </div>

                <p>Quotation details and financial information</p>

            </div>


            <div class="heading-actions">

                <a href="{{ route('quotations.index', [
                    'company_id' => $quotation->company_id
                ]) }}">
                    <i data-lucide="arrow-left"></i>
                    Back
                </a>

                @if(Route::has('quotations.edit'))

                    <a href="{{ route('quotations.edit', $quotation->id) }}">
                        <i data-lucide="pencil"></i>
                        Edit
                    </a>

                @endif

            </div>

        </div>


        {{-- MAIN INFORMATION --}}
        <section class="info-grid">

            <div class="info-card">

                <h2>
                    <i data-lucide="user"></i>
                    Customer Information
                </h2>

                <div class="customer-name">
                    {{ $customer->business_name ?? '-' }}
                </div>

                <p>{{ $customer->customer_name ?? '-' }}</p>

                <p>
                    {{ $customer->address_line_1 ?? '' }}
                    {{ $customer->address_line_2 ?? '' }}
                </p>

                <p>
                    {{ $customer->city ?? '' }},
                    {{ $customer->country ?? '' }}
                </p>

                <p>
                    {{ $customer->phone ?? '-' }}
                </p>

                <p>
                    {{ $customer->email ?? '-' }}
                </p>

            </div>


            <div class="info-card">

                <h2>
                    <i data-lucide="file-text"></i>
                    Quotation Information
                </h2>

                <div class="info-row">
                    <span>Quotation Date</span>
                    <strong>
                        {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y') }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Valid Until</span>
                    <strong>
                        {{ $quotation->expiry_date
                            ? \Carbon\Carbon::parse($quotation->expiry_date)->format('d M Y')
                            : '-' }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Reference</span>
                    <strong>{{ $quotation->reference ?: '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Project</span>
                    <strong>{{ $quotation->project_name ?: '-' }}</strong>
                </div>

                <div class="info-row">
                    <span>Created By</span>
                    <strong>{{ $quotation->created_by_name ?? '-' }}</strong>
                </div>

            </div>

        </section>


        {{-- ITEMS --}}
        <section class="items-card">

            <h2>
                <i data-lucide="list"></i>
                Quotation Items
            </h2>

            <div class="table-wrap">

                <table>

                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Item / Description</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Discount</th>
                        <th>Tax</th>
                        <th>Total</th>
                    </tr>
                    </thead>

                    <tbody>

                    @foreach($items as $item)

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <strong>{{ $item->item_name }}</strong>

                                @if($item->description)
                                    <small>{{ $item->description }}</small>
                                @endif
                            </td>

                            <td>{{ number_format($item->quantity, 2) }}</td>

                            <td>{{ $item->unit ?: '-' }}</td>

                            <td>
                                {{ $company->currency ?? 'LKR' }}
                                {{ number_format($item->unit_price, 2) }}
                            </td>

                            <td>

                                @if($item->discount_type === 'PERCENTAGE')

                                    {{ number_format($item->discount_value, 2) }}%

                                @elseif($item->discount_type === 'FIXED')

                                    {{ $company->currency ?? 'LKR' }}
                                    {{ number_format($item->discount_value, 2) }}

                                @else
                                    -
                                @endif

                            </td>

                            <td>
                                {{ number_format($item->tax_percentage, 2) }}%
                            </td>

                            <td>
                                <strong>
                                    {{ $company->currency ?? 'LKR' }}
                                    {{ number_format($item->line_total, 2) }}
                                </strong>
                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        </section>


        <div class="bottom-grid">

            {{-- NOTES --}}
            <section class="info-card">

                <h2>
                    <i data-lucide="notebook"></i>
                    Additional Information
                </h2>

                <h4>Terms & Conditions</h4>

                <div class="text-box">
                    {{ $quotation->terms_conditions ?: 'No terms and conditions.' }}
                </div>


                <h4>Notes</h4>

                <div class="text-box">
                    {{ $quotation->notes ?: 'No additional notes.' }}
                </div>

            </section>


            {{-- SUMMARY --}}
            <section class="summary-card">

                <h2>
                    <i data-lucide="calculator"></i>
                    Summary
                </h2>


                <div class="summary-row">

                    <span>Subtotal</span>

                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($quotation->subtotal, 2) }}
                    </strong>

                </div>


                <div class="summary-row">

                    <span>Discount</span>

                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($quotation->discount_amount, 2) }}
                    </strong>

                </div>


                <div class="summary-row">

                    <span>Tax</span>

                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($quotation->tax_amount, 2) }}
                    </strong>

                </div>


                <div class="summary-row">

                    <span>Additional Charges</span>

                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($quotation->additional_charges, 2) }}
                    </strong>

                </div>


                <div class="summary-total">

                    <span>Total Amount</span>

                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($quotation->grand_total, 2) }}
                    </strong>

                </div>

            </section>

        </div>


        {{-- ACTION BAR --}}
        <section class="action-bar">

            <div>

                <span>Current Status</span>

                <strong>
                    {{ $quotation->status }}
                </strong>

            </div>


            <div class="action-buttons">

                <button type="button">
                    <i data-lucide="copy"></i>
                    Clone
                </button>

                <button
                    type="button"
                    onclick="window.location.href='{{ route('quotations.pdf', $quotation->id) }}'"
                >
                    <i data-lucide="download"></i>
                    Export PDF
                </button>

                @if($quotation->status === 'DRAFT')

                    <button type="button" class="primary">
                        Mark as Sent
                    </button>

                @endif


                @if($quotation->status === 'SENT')

                    <button type="button" class="accept">
                        Accept
                    </button>

                    <button type="button" class="reject">
                        Reject
                    </button>

                @endif


                @if($quotation->status === 'ACCEPTED')

                    <button type="button" class="primary">
                        Convert to Invoice
                    </button>

                @endif

            </div>

        </section>

    </main>

</div>


<script>
    lucide.createIcons();
</script>

</body>
</html>