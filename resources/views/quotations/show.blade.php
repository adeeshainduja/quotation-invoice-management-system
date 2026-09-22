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

    @include('partials.topbar')


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

                @if(Route::has('quotations.edit') && $quotation->status !== 'CONVERTED')

                    <a href="{{ route('quotations.edit', $quotation->id) }}">
                        <i data-lucide="pencil"></i>
                        Edit
                    </a>

                @endif

            </div>

        </div>

        @if(session('success'))
            <div style="margin-bottom: 16px; padding: 12px 16px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 7px; color: #065f46; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="check-circle" style="width: 18px; height: 18px; color: #059669;"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div style="margin-bottom: 16px; padding: 12px 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 7px; color: #991b1b; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="alert-triangle" style="width: 18px; height: 18px; color: #dc2626;"></i>
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div style="margin-bottom: 16px; padding: 12px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 7px; color: #1e40af; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="info" style="width: 18px; height: 18px; color: #2563eb;"></i>
                {{ session('info') }}
            </div>
        @endif


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


                @if((bool) ($company->vat_enabled ?? $company->vat_registered ?? false))
                <div class="summary-row">

                    <span>VAT Amount ({{ number_format($quotation->tax_percentage ?? $company->tax_percentage ?? $company->vat_percentage ?? 0, 2) }}%)</span>

                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($quotation->tax_amount, 2) }}
                    </strong>

                </div>
                @endif


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

                <form method="POST" action="{{ route('quotations.clone', $quotation->id) }}" style="display:inline;">
                    @csrf
                    <button type="submit">
                        <i data-lucide="copy"></i>
                        Clone
                    </button>
                </form>

                <button
                    type="button"
                    onclick="window.location.href='{{ route('quotations.pdf', $quotation->id) }}'"
                >
                    <i data-lucide="download"></i>
                    Export PDF
                </button>

                @if($quotation->status === 'DRAFT')
                    <form method="POST" action="{{ route('quotations.send', $quotation->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="primary">
                            <i data-lucide="send"></i>
                            Mark as Sent
                        </button>
                    </form>
                @endif

                @if($quotation->status === 'SENT')
                    <form method="POST" action="{{ route('quotations.accept', $quotation->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="accept">
                            <i data-lucide="check"></i>
                            Accept
                        </button>
                    </form>

                    <form method="POST" action="{{ route('quotations.reject', $quotation->id) }}" onsubmit="return confirm('Are you sure you want to reject this quotation?');" style="display:inline;">
                        @csrf
                        <button type="submit" class="reject">
                            <i data-lucide="x"></i>
                            Reject
                        </button>
                    </form>
                @endif

                @if($quotation->status === 'ACCEPTED')
                    <form method="POST" action="{{ route('quotations.convert', $quotation->id) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="primary">
                            <i data-lucide="receipt"></i>
                            Convert to Invoice
                        </button>
                    </form>
                @endif

                @if(!empty($quotation->converted_invoice_id))
                    <a
                        href="{{ route('invoices.show', $quotation->converted_invoice_id) }}"
                        class="primary"
                        style="display: flex; align-items: center; gap: 7px; min-height: 40px; padding: 0 15px; border: 1px solid #1474e8; border-radius: 7px; background: #1474e8; color: white; text-decoration: none; font-size: 13px; font-weight: 500;"
                    >
                        <i data-lucide="arrow-right"></i>
                        View Invoice
                    </a>
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