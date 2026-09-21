<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Record Payment</title>


    <link
        rel="stylesheet"
        href="{{ asset('css/dashboard.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/payment-create.css') }}"
    >


    <script src="https://unpkg.com/lucide@latest"></script>

</head>


<body>


@include('partials.sidebar')


<div class="page">


    <header class="topbar">


        <form
            method="GET"
            action="{{ route('payments.create') }}"
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
                        'payments.index',
                        ['company_id' => $companyId]
                    )
                    : route('payments.index') }}"
            >
                Payments
            </a>


            <span>›</span>

            Record Payment

        </div>



        <div class="create-heading">

            <div>

                <h1>
                    Record Payment
                </h1>

                <p>
                    Record a payment received against an invoice
                </p>

            </div>


            <a
                href="{{ $companyId
                    ? route(
                        'payments.index',
                        ['company_id' => $companyId]
                    )
                    : route('payments.index') }}"
                class="back-btn"
            >

                <i data-lucide="arrow-left"></i>

                Back to Payments

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

                Select an active company before recording a payment.

            </div>

        @elseif($invoices->isEmpty())

            <div class="form-warning">

                There are no invoices with an outstanding balance for this company.

            </div>

        @endif



        <form
            method="POST"
            action="{{ route('payments.store') }}"
            id="paymentForm"
        >

            @csrf


            @if($companyId)

                <input
                    type="hidden"
                    name="company_id"
                    value="{{ $companyId }}"
                >

            @endif



            <div class="payment-grid">


                <section class="form-card">

                    <h2>

                        <i data-lucide="receipt-text"></i>

                        Invoice Information

                    </h2>


                    <label>
                        Invoice *
                    </label>


                    <select
                        name="invoice_id"
                        id="invoiceSelect"
                        required
                        @disabled(!$company)
                    >

                        <option value="">
                            Select Invoice
                        </option>


                        @foreach($invoices as $invoice)

                            <option
                                value="{{ $invoice->id }}"

                                data-customer="{{ $invoice->business_name }}"

                                data-total="{{ $invoice->grand_total }}"

                                data-paid="{{ $invoice->amount_paid }}"

                                data-balance="{{ $invoice->balance_amount }}"

                                data-status="{{ $invoice->status }}"

                                {{
                                    old(
                                        'invoice_id',
                                        $selectedInvoiceId
                                    ) == $invoice->id
                                        ? 'selected'
                                        : ''
                                }}
                            >

                                {{
                                    $invoice->invoice_number
                                }}

                                —

                                {{
                                    $invoice->business_name
                                }}

                            </option>

                        @endforeach

                    </select>



                    <div class="invoice-preview">

                        <div>

                            <span>Customer</span>

                            <strong id="previewCustomer">
                                -
                            </strong>

                        </div>


                        <div>

                            <span>Invoice Total</span>

                            <strong>

                                {{ $company->currency ?? 'LKR' }}

                                <span id="previewTotal">
                                    0.00
                                </span>

                            </strong>

                        </div>


                        <div>

                            <span>Already Paid</span>

                            <strong>

                                {{ $company->currency ?? 'LKR' }}

                                <span id="previewPaid">
                                    0.00
                                </span>

                            </strong>

                        </div>


                        <div>

                            <span>Outstanding Balance</span>

                            <strong class="balance-value">

                                {{ $company->currency ?? 'LKR' }}

                                <span id="previewBalance">
                                    0.00
                                </span>

                            </strong>

                        </div>

                    </div>

                </section>



                <section class="form-card">

                    <h2>

                        <i data-lucide="wallet"></i>

                        Payment Details

                    </h2>


                    <div class="two-columns">


                        <div>

                            <label>
                                Payment Date *
                            </label>

                            <input
                                type="date"
                                name="payment_date"
                                value="{{ old(
                                    'payment_date',
                                    now()->format('Y-m-d')
                                ) }}"
                                required
                            >

                        </div>



                        <div>

                            <label>
                                Amount *
                            </label>

                            <input
                                type="number"
                                name="amount"
                                id="paymentAmount"
                                value="{{ old('amount') }}"
                                min="0.01"
                                step="0.01"
                                placeholder="0.00"
                                required
                            >

                        </div>



                        <div>

                            <label>
                                Payment Method *
                            </label>


                            <select
                                name="payment_method"
                                required
                            >

                                <option value="">
                                    Select Method
                                </option>


                                <option
                                    value="CASH"
                                    {{ old('payment_method') === 'CASH'
                                        ? 'selected'
                                        : '' }}
                                >
                                    Cash
                                </option>


                                <option
                                    value="BANK_TRANSFER"
                                    {{ old('payment_method') === 'BANK_TRANSFER'
                                        ? 'selected'
                                        : '' }}
                                >
                                    Bank Transfer
                                </option>


                                <option
                                    value="CARD"
                                    {{ old('payment_method') === 'CARD'
                                        ? 'selected'
                                        : '' }}
                                >
                                    Card
                                </option>


                                <option
                                    value="CHEQUE"
                                    {{ old('payment_method') === 'CHEQUE'
                                        ? 'selected'
                                        : '' }}
                                >
                                    Cheque
                                </option>


                                <option
                                    value="OTHER"
                                    {{ old('payment_method') === 'OTHER'
                                        ? 'selected'
                                        : '' }}
                                >
                                    Other
                                </option>

                            </select>

                        </div>



                        <div>

                            <label>
                                Reference
                            </label>

                            <input
                                type="text"
                                name="reference"
                                value="{{ old('reference') }}"
                                placeholder="Transfer / cheque / receipt reference"
                            >

                        </div>

                    </div>



                    <label>
                        Notes
                    </label>

                    <textarea
                        name="notes"
                        placeholder="Optional payment notes"
                    >{{ old('notes') }}</textarea>

                </section>

            </div>



            <div class="payment-summary-card">

                <div>

                    <span>
                        Payment Amount
                    </span>

                    <strong>

                        {{ $company->currency ?? 'LKR' }}

                        <span id="paymentPreview">
                            0.00
                        </span>

                    </strong>

                </div>


                <div>

                    <span>
                        Balance After Payment
                    </span>

                    <strong>

                        {{ $company->currency ?? 'LKR' }}

                        <span id="balanceAfterPayment">
                            0.00
                        </span>

                    </strong>

                </div>

            </div>



            <div class="form-actions">

                <a
                    href="{{ $companyId
                        ? route(
                            'payments.index',
                            ['company_id' => $companyId]
                        )
                        : route('payments.index') }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="save-btn"
                    @disabled(
                        !$company ||
                        $invoices->isEmpty()
                    )
                >

                    <i data-lucide="save"></i>

                    Record Payment

                </button>

            </div>

        </form>

    </main>

</div>



<script>

const invoiceSelect =
    document.getElementById('invoiceSelect');

const paymentAmount =
    document.getElementById('paymentAmount');


let currentBalance = 0;



function money(value)
{
    return Number(value || 0)
        .toLocaleString(
            undefined,
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );
}



function updateInvoicePreview()
{
    if (!invoiceSelect) {
        return;
    }


    const option =
        invoiceSelect.options[
            invoiceSelect.selectedIndex
        ];


    if (
        !option ||
        !option.value
    ) {

        currentBalance = 0;

        document.getElementById(
            'previewCustomer'
        ).textContent = '-';

        document.getElementById(
            'previewTotal'
        ).textContent = '0.00';

        document.getElementById(
            'previewPaid'
        ).textContent = '0.00';

        document.getElementById(
            'previewBalance'
        ).textContent = '0.00';

        updatePaymentPreview();

        return;
    }


    currentBalance =
        parseFloat(
            option.dataset.balance
        ) || 0;


    document.getElementById(
        'previewCustomer'
    ).textContent =
        option.dataset.customer || '-';


    document.getElementById(
        'previewTotal'
    ).textContent =
        money(
            option.dataset.total
        );


    document.getElementById(
        'previewPaid'
    ).textContent =
        money(
            option.dataset.paid
        );


    document.getElementById(
        'previewBalance'
    ).textContent =
        money(
            currentBalance
        );


    if (paymentAmount) {

        paymentAmount.max =
            currentBalance.toFixed(2);

    }


    updatePaymentPreview();
}



function updatePaymentPreview()
{
    const payment =
        parseFloat(
            paymentAmount?.value
        ) || 0;


    document.getElementById(
        'paymentPreview'
    ).textContent =
        money(payment);


    const remaining =
        Math.max(
            currentBalance - payment,
            0
        );


    document.getElementById(
        'balanceAfterPayment'
    ).textContent =
        money(remaining);
}



if (invoiceSelect) {

    invoiceSelect.addEventListener(
        'change',
        updateInvoicePreview
    );

}


if (paymentAmount) {

    paymentAmount.addEventListener(
        'input',
        updatePaymentPreview
    );

}


updateInvoicePreview();

lucide.createIcons();

</script>


</body>

</html>