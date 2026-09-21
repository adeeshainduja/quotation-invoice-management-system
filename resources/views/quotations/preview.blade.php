<!DOCTYPE html>
<html>
<head>
    <title>Quotation Preview</title>

    <link rel="stylesheet"
          href="{{ asset('css/dashboard.css') }}">

    <style>
        .preview-box {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        th {
            text-align: left;
        }

        .total {
            margin-top: 25px;
            text-align: right;
        }
    </style>
</head>

<body>

<div class="preview-box">

    <h1>Quotation Preview</h1>

    <h2>{{ $company->name }}</h2>

    <p>
        Customer:
        <strong>{{ $customer->business_name }}</strong>
    </p>

    <p>
        Date: {{ $quotation->quotation_date }}
    </p>

    <p>
        Valid Until: {{ $quotation->expiry_date }}
    </p>

    <p>
        Template:
        {{ $template->template_name }}
    </p>


    <table>

        <thead>
        <tr>
            <th>#</th>
            <th>Item</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
        </thead>

        <tbody>

        @foreach($items as $item)

            <tr>
                <td>{{ $loop->iteration }}</td>

                <td>
                    {{ $item->item_name }}

                    <br>

                    <small>
                        {{ $item->description }}
                    </small>
                </td>

                <td>
                    {{ $item->quantity }}
                </td>

                <td>
                    {{ number_format($item->unit_price, 2) }}
                </td>

                <td>
                    {{ number_format($item->line_total, 2) }}
                </td>
            </tr>

        @endforeach

        </tbody>

    </table>


    <div class="total">

        <p>
            Subtotal:
            {{ number_format($quotation->subtotal, 2) }}
        </p>

        <p>
            Discount:
            {{ number_format($quotation->discount_amount, 2) }}
        </p>

        <p>
            Tax:
            {{ number_format($quotation->tax_amount, 2) }}
        </p>

        <h2>
            Total:
            {{ $company->currency ?? 'LKR' }}
            {{ number_format($quotation->grand_total, 2) }}
        </h2>

    </div>


    <button onclick="history.back()">
        ← Back to Quotation
    </button>

</div>

</body>
</html>