<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 11px;
            margin: 25px;
            line-height: 1.4;
        }

        .tax-invoice-header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .tax-invoice-header h1 {
            font-size: 22px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 1.5px;
            color: #0f172a;
            text-transform: uppercase;
        }

        .company-subtitle {
            font-size: 11px;
            color: #475569;
            margin-top: 3px;
        }

        .party-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .party-grid td {
            vertical-align: top;
            padding: 8px;
            border: 1px solid #cbd5e1;
        }

        .party-card-title {
            font-size: 11px;
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            margin-bottom: 6px;
            background: #f8fafc;
            margin: -8px -8px 6px -8px;
            padding: 5px 8px;
        }

        .field-row {
            margin-bottom: 3px;
        }

        .field-label {
            font-weight: 600;
            color: #475569;
            display: inline-block;
            width: 110px;
        }

        .field-value {
            font-weight: 600;
            color: #0f172a;
        }

        .details-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            background: #f8fafc;
        }

        .details-grid td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 10.5px;
        }

        .details-grid .title-cell {
            font-weight: 700;
            color: #475569;
            width: 25%;
            background: #f1f5f9;
        }

        .details-grid .val-cell {
            font-weight: 600;
            color: #0f172a;
            width: 25%;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 14px;
        }

        .items th {
            background: #1e293b;
            color: #ffffff;
            padding: 7px 8px;
            text-align: left;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #1e293b;
        }

        .items td {
            padding: 7px 8px;
            border: 1px solid #cbd5e1;
            font-size: 10.5px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .summary-container {
            width: 100%;
            margin-top: 10px;
        }

        .summary-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 6px 10px;
            border: 1px solid #cbd5e1;
            font-size: 11px;
        }

        .summary-table .sub-label {
            font-weight: 600;
            color: #334155;
            background: #f8fafc;
        }

        .summary-table .total-row {
            background: #0f172a;
            color: #ffffff;
            font-weight: 800;
            font-size: 12px;
        }

        .summary-table .total-row td {
            border-color: #0f172a;
            color: #ffffff;
        }

        .words-box {
            margin-top: 12px;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }

        .words-label {
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            font-size: 9.5px;
            margin-bottom: 2px;
        }

        .words-value {
            font-weight: 700;
            color: #0f172a;
            font-size: 11px;
            font-style: italic;
        }

        .payment-box, .bank-box {
            margin-top: 14px;
            border: 1px solid #cbd5e1;
            padding: 8px;
            background: #ffffff;
        }

        .box-title {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10.5px;
            color: #1e293b;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .payment-table th {
            background: #f1f5f9;
            padding: 5px 8px;
            font-size: 10px;
            border: 1px solid #cbd5e1;
            text-align: left;
        }

        .payment-table td {
            padding: 5px 8px;
            font-size: 10px;
            border: 1px solid #cbd5e1;
        }

        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #cbd5e1;
            text-align: center;
            color: #64748b;
            font-size: 9.5px;
        }
    </style>
</head>

<body>

{{-- 1. HEADER --}}
<div class="tax-invoice-header">
    <h1>TAX INVOICE</h1>
    <div class="company-subtitle">
        {{ $company->name }} &bull; VAT Registration No: {{ $company->vat_number ?? '-' }}
    </div>
</div>


{{-- 2. SUPPLIER & PURCHASER SECTIONS --}}
<table class="party-grid">
    <tr>
        {{-- SUPPLIER SECTION --}}
        <td style="width: 50%;">
            <div class="party-card-title">Supplier (Seller)</div>

            <div class="field-row">
                <span class="field-label">Supplier TIN:</span>
                <span class="field-value">{{ $company->tin_number ?? $company->tin ?? '-' }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">VAT Reg. No:</span>
                <span class="field-value">{{ $company->vat_number ?? '-' }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Supplier Name:</span>
                <span class="field-value">{{ $company->name ?? '' }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Address:</span>
                <span class="field-value">
                    {{ $company->address_line_1 ?? '' }}{{ !empty($company->address_line_2) ? ', ' . $company->address_line_2 : '' }}{{ !empty($company->city) ? ', ' . $company->city : '' }}{{ !empty($company->country) ? ', ' . $company->country : '' }}
                </span>
            </div>

            <div class="field-row">
                <span class="field-label">Telephone:</span>
                <span class="field-value">{{ $company->phone ?? '-' }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Email:</span>
                <span class="field-value">{{ $company->email ?? '-' }}</span>
            </div>
        </td>

        {{-- PURCHASER SECTION --}}
        <td style="width: 50%;">
            <div class="party-card-title">Purchaser (Buyer)</div>

            <div class="field-row">
                <span class="field-label">Purchaser TIN:</span>
                <span class="field-value">{{ $customer->tin_number ?? $customer->vat_number ?? $customer->registration_number ?? '-' }}</span>
            </div>

            @if(!empty($customer->vat_number))
                <div class="field-row">
                    <span class="field-label">VAT Reg. No:</span>
                    <span class="field-value">{{ $customer->vat_number }}</span>
                </div>
            @endif

            <div class="field-row">
                <span class="field-label">Purchaser Name:</span>
                <span class="field-value">{{ $customer->business_name ?? ($customer->customer_name ?? '') }}</span>
            </div>

            @if(!empty($customer->business_name) && !empty($customer->customer_name))
                <div class="field-row">
                    <span class="field-label">Attn:</span>
                    <span class="field-value">{{ $customer->customer_name }}</span>
                </div>
            @endif

            <div class="field-row">
                <span class="field-label">Address:</span>
                <span class="field-value">
                    {{ $customer->address_line_1 ?? '' }}{{ !empty($customer->address_line_2) ? ', ' . $customer->address_line_2 : '' }}{{ !empty($customer->city) ? ', ' . $customer->city : '' }}{{ !empty($customer->country) ? ', ' . $customer->country : '' }}
                </span>
            </div>

            <div class="field-row">
                <span class="field-label">Telephone:</span>
                <span class="field-value">{{ $customer->phone ?? '-' }}</span>
            </div>

            <div class="field-row">
                <span class="field-label">Email:</span>
                <span class="field-value">{{ $customer->email ?? '-' }}</span>
            </div>
        </td>
    </tr>
</table>


{{-- 3. INVOICE DETAILS --}}
<table class="details-grid">
    <tr>
        <td class="title-cell">Invoice Number:</td>
        <td class="val-cell"><strong>{{ $invoice->invoice_number }}</strong></td>

        <td class="title-cell">Invoice Date:</td>
        <td class="val-cell">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td class="title-cell">Date of Supply:</td>
        <td class="val-cell">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d/m/Y') }}</td>

        <td class="title-cell">Place of Supply:</td>
        <td class="val-cell">{{ $company->city }}, {{ $company->country }}</td>
    </tr>
    @if($invoice->reference || $invoice->due_date)
        <tr>
            <td class="title-cell">Reference:</td>
            <td class="val-cell">{{ $invoice->reference ?: '-' }}</td>

            <td class="title-cell">Payment Due Date:</td>
            <td class="val-cell">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') : '-' }}</td>
        </tr>
    @endif
</table>


{{-- 4. ITEMS TABLE --}}
<table class="items">
    <thead>
    <tr>
        <th style="width: 8%;">Reference</th>
        <th style="width: 44%;">Description</th>
        <th class="center" style="width: 10%;">Quantity</th>
        <th class="right" style="width: 18%;">Unit Price (Excl. VAT)</th>
        <th class="right" style="width: 20%;">Amount Excluding VAT</th>
    </tr>
    </thead>

    <tbody>
    @php
        $totalExcludingVat = 0;
    @endphp

    @foreach($items as $index => $item)
        @php
            $qty = (float) $item->quantity;
            $price = (float) $item->unit_price;
            $discount = (float) ($item->discount_amount ?? 0);
            $lineExclVat = ($qty * $price) - $discount;
            $totalExcludingVat += $lineExclVat;
        @endphp
        <tr>
            <td class="center">
                {{ $item->source_quotation_item_id ?? ($index + 1) }}
            </td>

            <td>
                <strong>{{ $item->item_name }}</strong>
                @if($item->description)
                    <div style="color: #475569; font-size: 9.5px; margin-top: 2px;">
                        {{ $item->description }}
                    </div>
                @endif
            </td>

            <td class="center">
                {{ number_format($qty, 2) }} {{ $item->unit ?? '' }}
            </td>

            <td class="right">
                {{ $company->currency }} {{ number_format($price, 2) }}
            </td>

            <td class="right">
                {{ $company->currency }} {{ number_format($lineExclVat, 2) }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>


{{-- 5. SUMMARY SECTION --}}
@php
    $vatAmount = (float) ($invoice->tax_amount ?? 0);
    $vatPercentage = (float) ($invoice->tax_percentage ?? $company->tax_percentage ?? $company->vat_percentage ?? 18);
    if ($vatAmount <= 0 && $invoice->grand_total > $totalExcludingVat) {
        $vatAmount = $invoice->grand_total - $totalExcludingVat;
    }
    $totalIncludingVat = (float) $invoice->grand_total;
@endphp

<div class="summary-container">
    <table class="summary-table">
        <tr>
            <td class="sub-label">Total Value of Supply:</td>
            <td class="right">
                {{ $company->currency }} {{ number_format($totalExcludingVat, 2) }}
            </td>
        </tr>

        <tr>
            <td class="sub-label">VAT Amount ({{ number_format($vatPercentage, 2) }}%):</td>
            <td class="right">
                {{ $company->currency }} {{ number_format($vatAmount, 2) }}
            </td>
        </tr>

        @if((float) $invoice->additional_charges > 0)
            <tr>
                <td class="sub-label">Additional Charges:</td>
                <td class="right">
                    {{ $company->currency }} {{ number_format($invoice->additional_charges, 2) }}
                </td>
            </tr>
        @endif

        <tr class="total-row">
            <td>Total Amount Including VAT:</td>
            <td class="right">
                {{ $company->currency }} {{ number_format($totalIncludingVat, 2) }}
            </td>
        </tr>

        @if((float) $invoice->amount_paid > 0)
            <tr>
                <td class="sub-label" style="color: #16a34a;">Amount Paid:</td>
                <td class="right" style="font-weight: 700; color: #16a34a;">
                    {{ $company->currency }} {{ number_format($invoice->amount_paid, 2) }}
                </td>
            </tr>
            <tr>
                <td class="sub-label" style="color: #dc2626;">Balance Amount:</td>
                <td class="right" style="font-weight: 700; color: #dc2626;">
                    {{ $company->currency }} {{ number_format($invoice->balance_amount, 2) }}
                </td>
            </tr>
        @endif
    </table>
</div>


{{-- 6. AMOUNT IN WORDS --}}
<div class="words-box">
    <div class="words-label">Amount in Words:</div>
    <div class="words-value">
        {{ \App\Services\NumberToWordsHelper::spell($totalIncludingVat, $company->currency) }}
    </div>
</div>


{{-- 7. PAYMENT DETAILS --}}
@if(isset($payments) && count($payments))
    <div class="payment-box">
        <div class="box-title">Payment Details</div>
        <table class="payment-table">
            <thead>
            <tr>
                <th>Date</th>
                <th>Payment Method</th>
                <th>Reference No.</th>
                <th class="right">Amount Paid</th>
            </tr>
            </thead>
            <tbody>
            @foreach($payments as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                    <td>{{ ucwords(strtolower(str_replace('_', ' ', $payment->payment_method))) }}</td>
                    <td>{{ $payment->reference ?? '-' }}</td>
                    <td class="right">{{ $company->currency }} {{ number_format($payment->amount, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif


{{-- BANK DETAILS --}}
@if(($template->show_bank_details ?? false) && isset($bank) && $bank)
    <div class="bank-box">
        <div class="box-title">Bank & Remittance Details</div>
        <div style="font-size: 10px;">
            <strong>Bank:</strong> {{ $bank->bank_name }} &bull;
            <strong>Account Name:</strong> {{ $bank->bank_account_name }} &bull;
            <strong>Account Number:</strong> {{ $bank->bank_account_number }}
            @if($bank->bank_branch) &bull; <strong>Branch:</strong> {{ $bank->bank_branch }} @endif
            @if($bank->swift_code) &bull; <strong>SWIFT:</strong> {{ $bank->swift_code }} @endif
        </div>
    </div>
@endif


{{-- TERMS & CONDITIONS --}}
@if(!empty($invoice->terms_conditions))
    <div style="margin-top: 10px; font-size: 9.5px; color: #475569;">
        <strong>Terms & Conditions:</strong><br>
        {!! nl2br(e($invoice->terms_conditions)) !!}
    </div>
@endif


{{-- FOOTER --}}
<div class="footer">
    This is a computer-generated Tax Invoice and requires no physical signature.
    @if(!empty($template->footer_text))
        <br>{!! nl2br(e($template->footer_text)) !!}
    @endif
</div>

</body>
</html>
