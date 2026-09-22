<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $invoice->invoice_number }}</title>

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
            <a href="{{ route('invoices.index', ['company_id' => $invoice->company_id]) }}">
                Invoices
            </a>
            <span>›</span>
            {{ $invoice->invoice_number }}
        </div>

        <div class="details-heading">
            <div>
                <div class="title-row">
                    <h1>{{ $invoice->invoice_number }}</h1>

                    <span class="status {{ strtolower($invoice->status) }}">
                        {{ $invoice->status }}
                    </span>
                </div>

                <p>Invoice details, payment history and financial summary</p>
            </div>

            <div class="heading-actions">
                <a href="{{ route('invoices.index', ['company_id' => $invoice->company_id]) }}">
                    <i data-lucide="arrow-left"></i>
                    Back
                </a>

                <a href="{{ route('invoices.pdf', $invoice->id) }}" target="_blank" style="background: #1474e8; color: #fff; border-color: #1474e8;">
                    <i data-lucide="download"></i>
                    Export PDF
                </a>
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
                    {{ $customer->city ?? '' }}{{ !empty($customer->city) && !empty($customer->country) ? ', ' : '' }}{{ $customer->country ?? '' }}
                </p>

                <p>
                    <i data-lucide="phone" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i>
                    {{ $customer->phone ?? '-' }}
                </p>

                <p>
                    <i data-lucide="mail" style="width: 14px; height: 14px; display: inline-block; vertical-align: middle;"></i>
                    {{ $customer->email ?? '-' }}
                </p>
            </div>

            <div class="info-card">
                <h2>
                    <i data-lucide="file-text"></i>
                    Invoice Information
                </h2>

                <div class="info-row">
                    <span>Invoice Date</span>
                    <strong>
                        {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Due Date</span>
                    <strong>
                        {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}
                    </strong>
                </div>

                <div class="info-row">
                    <span>Reference</span>
                    <strong>{{ $invoice->reference ?: '-' }}</strong>
                </div>

                @if($quotation)
                    <div class="info-row">
                        <span>Source Quotation</span>
                        <strong>
                            <a href="{{ route('quotations.show', $quotation->id) }}" style="color: #1474e8; text-decoration: none;">
                                {{ $quotation->quotation_number }}
                            </a>
                        </strong>
                    </div>
                @endif

                <div class="info-row">
                    <span>Created By</span>
                    <strong>{{ $invoice->created_by_name ?? '-' }}</strong>
                </div>
            </div>

        </section>

        {{-- ITEMS --}}
        <section class="items-card">
            <h2>
                <i data-lucide="list"></i>
                Invoice Items
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
                    @forelse($items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $item->item_name }}</strong>
                                @if($item->description)
                                    <small style="display: block; color: #64738e;">{{ $item->description }}</small>
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
                                    {{ $company->currency ?? 'LKR' }} {{ number_format($item->discount_value, 2) }}
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
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64738e; padding: 24px;">No items found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- PAYMENTS HISTORY --}}
        @if(count($payments) > 0)
        <section class="items-card" style="margin-top: 20px;">
            <h2>
                <i data-lucide="credit-card"></i>
                Payment History
            </h2>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Reference</th>
                        <th>Notes</th>
                        <th>Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                            <td><strong>{{ $payment->payment_method }}</strong></td>
                            <td>{{ $payment->reference ?: '-' }}</td>
                            <td>{{ $payment->notes ?: '-' }}</td>
                            <td>
                                <strong style="color: #10b981;">
                                    {{ $company->currency ?? 'LKR' }} {{ number_format($payment->amount, 2) }}
                                </strong>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        <div class="bottom-grid">
            {{-- NOTES --}}
            <section class="info-card">
                <h2>
                    <i data-lucide="notebook"></i>
                    Additional Information
                </h2>

                <h4>Terms & Conditions</h4>
                <div class="text-box">
                    {{ $invoice->terms_conditions ?: 'No terms and conditions.' }}
                </div>

                <h4>Notes</h4>
                <div class="text-box">
                    {{ $invoice->notes ?: 'No additional notes.' }}
                </div>
            </section>

            {{-- SUMMARY --}}
            <section class="summary-card">
                <h2>
                    <i data-lucide="calculator"></i>
                    Financial Summary
                </h2>

                <div class="summary-row">
                    <span>Subtotal</span>
                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($invoice->subtotal, 2) }}
                    </strong>
                </div>

                <div class="summary-row">
                    <span>Discount</span>
                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($invoice->discount_amount, 2) }}
                    </strong>
                </div>

                @if((bool) ($company->vat_enabled ?? $company->vat_registered ?? false))
                <div class="summary-row">
                    <span>VAT Amount ({{ number_format($invoice->tax_percentage ?? $company->tax_percentage ?? $company->vat_percentage ?? 0, 2) }}%)</span>
                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($invoice->tax_amount, 2) }}
                    </strong>
                </div>
                @endif

                @if((float) $invoice->additional_charges > 0)
                <div class="summary-row">
                    <span>Additional Charges</span>
                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($invoice->additional_charges, 2) }}
                    </strong>
                </div>
                @endif

                <div class="summary-total">
                    <span>Grand Total</span>
                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($invoice->grand_total, 2) }}
                    </strong>
                </div>

                <div class="summary-row" style="margin-top: 10px; color: #059669;">
                    <span>Amount Paid</span>
                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($invoice->amount_paid, 2) }}
                    </strong>
                </div>

                <div class="summary-row" style="color: {{ (float)$invoice->balance_amount > 0 ? '#dc2626' : '#059669' }}; font-size: 15px; font-weight: 700;">
                    <span>Balance Due</span>
                    <strong>
                        {{ $company->currency ?? 'LKR' }}
                        {{ number_format($invoice->balance_amount, 2) }}
                    </strong>
                </div>
            </section>
        </div>

        {{-- ACTION BAR --}}
        <section class="action-bar">
            <div>
                <span>Current Status</span>
                <strong>{{ $invoice->status }}</strong>
            </div>

            <div class="action-buttons">
                <a
                    href="{{ route('invoices.pdf', $invoice->id) }}"
                    target="_blank"
                    style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;"
                >
                    <i data-lucide="download"></i>
                    Export PDF
                </a>

                @if(Route::has('payments.create') && (float) $invoice->balance_amount > 0)
                    <a
                        href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}"
                        class="primary"
                        style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;"
                    >
                        <i data-lucide="credit-card"></i>
                        Record Payment
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
