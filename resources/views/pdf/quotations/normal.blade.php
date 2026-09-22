<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quotation - {{ $quotation->quotation_number }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            margin: 30px;
        }

        .header {
            width: 100%;
            margin-bottom: 25px;
        }

        .header td {
            vertical-align: top;
        }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
        }

        .title {
            text-align: right;
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
        }

        .quotation-number {
            text-align: right;
            margin-top: 5px;
            color: #64748b;
        }

        .section {
            margin-top: 20px;
        }

        .info-table {
            width: 100%;
        }

        .info-table td {
            width: 50%;
            vertical-align: top;
        }

        .label {
            color: #64748b;
            font-size: 10px;
            margin-bottom: 3px;
        }

        .value {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .items th {
            background: #f1f5f9;
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #cbd5e1;
        }

        .items td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .right {
            text-align: right;
        }

        .summary {
            width: 45%;
            margin-left: auto;
            margin-top: 20px;
            border-collapse: collapse;
        }

        .summary td {
            padding: 6px;
        }

        .summary .total {
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 15px;
            font-weight: bold;
        }

        .terms {
            margin-top: 20px;
            padding: 12px;
            background: #f8fafc;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #64748b;
            font-size: 10px;
        }
    </style>
</head>

<body>

<table class="header">
    <tr>
        <td>
            <div class="company-name">
                {{ $company->name ?? '' }}
            </div>

            <div>
                {{ $company->address_line_1 ?? '' }}
                {{ !empty($company->address_line_2) ? ', ' . $company->address_line_2 : '' }}
            </div>

            <div>
                {{ $company->city ?? '' }}{{ !empty($company->country) ? ', ' . $company->country : '' }}
            </div>

            @if(!empty($company->phone))
                <div>
                    {{ $company->phone }}
                </div>
            @endif

            @if(!empty($company->email))
                <div>
                    {{ $company->email }}
                </div>
            @endif
        </td>

        <td>
            <div class="title">
                QUOTATION
            </div>

            <div class="quotation-number">
                {{ $quotation->quotation_number }}
            </div>
        </td>
    </tr>
</table>


@if(!empty($template->header_text))
    <div class="section">
        {!! nl2br(e($template->header_text)) !!}
    </div>
@endif


<table class="info-table">
    <tr>
        <td>
            <div class="label">
                QUOTATION FOR
            </div>

            <div class="value">
                {{ $customer->business_name ?? ($customer->customer_name ?? '') }}
            </div>

            @if(!empty($customer->business_name) && !empty($customer->customer_name))
                <div>
                    {{ $customer->customer_name }}
                </div>
            @endif

            @if(!empty($customer->address_line_1))
                <div>
                    {{ $customer->address_line_1 }}
                    {{ !empty($customer->address_line_2) ? ', ' . $customer->address_line_2 : '' }}
                </div>
            @endif

            @if(!empty($customer->city))
                <div>
                    {{ $customer->city }}{{ !empty($customer->country) ? ', ' . $customer->country : '' }}
                </div>
            @endif

            @if(!empty($customer->email))
                <div>
                    {{ $customer->email }}
                </div>
            @endif

            @if(!empty($customer->phone))
                <div>
                    {{ $customer->phone }}
                </div>
            @endif
        </td>

        <td>
            <div class="label">
                QUOTATION DATE
            </div>

            <div class="value">
                {{ \Carbon\Carbon::parse($quotation->quotation_date)->format('d M Y') }}
            </div>

            <div class="label">
                VALID UNTIL
            </div>

            <div class="value">
                {{ $quotation->expiry_date
                    ? \Carbon\Carbon::parse($quotation->expiry_date)->format('d M Y')
                    : '-' }}
            </div>

            @if($quotation->reference)
                <div class="label">
                    REFERENCE
                </div>

                <div class="value">
                    {{ $quotation->reference }}
                </div>
            @endif
        </td>
    </tr>
</table>


<table class="items">
    <thead>
    <tr>
        <th>#</th>
        <th>Item</th>
        <th>Qty</th>
        <th>Unit</th>
        <th class="right">Price</th>
        <th class="right">Discount</th>
        <th class="right">Total</th>
    </tr>
    </thead>

    <tbody>
    @foreach($items as $index => $item)
        <tr>
            <td>
                {{ $index + 1 }}
            </td>

            <td>
                <strong>
                    {{ $item->item_name }}
                </strong>

                @if($item->description)
                    <div>
                        {{ $item->description }}
                    </div>
                @endif
            </td>

            <td>
                {{ number_format($item->quantity, 2) }}
            </td>

            <td>
                {{ $item->unit ?? '-' }}
            </td>

            <td class="right">
                {{ $company->currency }}
                {{ number_format($item->unit_price, 2) }}
            </td>

            <td class="right">
                {{ $company->currency }}
                {{ number_format($item->discount_amount ?? 0, 2) }}
            </td>

            <td class="right">
                {{ $company->currency }}
                {{ number_format($item->line_total, 2) }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>


<table class="summary">
    <tr>
        <td>Subtotal</td>
        <td class="right">
            {{ $company->currency }}
            {{ number_format($quotation->subtotal, 2) }}
        </td>
    </tr>

    @if((float) $quotation->discount_amount > 0)
        <tr>
            <td>Discount</td>
            <td class="right">
                {{ $company->currency }}
                {{ number_format($quotation->discount_amount, 2) }}
            </td>
        </tr>
    @endif

    <tr class="total">
        <td>Total Amount</td>
        <td class="right">
            {{ $company->currency }}
            {{ number_format($quotation->grand_total, 2) }}
        </td>
    </tr>
</table>


@if(!empty($quotation->terms_conditions))
    <div class="terms">
        <strong>Terms & Conditions</strong>
        <div>{!! nl2br(e($quotation->terms_conditions)) !!}</div>
    </div>
@endif


@if(!empty($quotation->notes))
    <div class="terms">
        <strong>Notes</strong>
        <div>{!! nl2br(e($quotation->notes)) !!}</div>
    </div>
@endif


@if(!empty($template->footer_text))
    <div class="footer">
        {!! nl2br(e($template->footer_text)) !!}
    </div>
@endif

</body>
</html>
