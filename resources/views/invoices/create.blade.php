<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Invoice</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/invoice-create.css') }}">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

@include('partials.sidebar')

@php
    $currency = $company->currency ?? 'LKR';
    $isVatEnabled = (bool) ($company && ($company->vat_enabled ?? $company->vat_registered));

    $defaultTax = $isVatEnabled
        ? ($company->tax_percentage ?? $company->vat_percentage ?? 0)
        : 0;

    $oldItems = old('items', [[
        'item_name' => '',
        'description' => '',
        'quantity' => 1,
        'unit' => '',
        'unit_price' => 0,
        'discount_type' => 'NONE',
        'discount_value' => 0,
        'tax_percentage' => $defaultTax,
    ]]);
@endphp

<div class="page">

    <header class="topbar">
        <div></div>

        <div class="topbar-right">

            <form method="GET" action="{{ route('invoices.create') }}">
                <select
                    name="company_id"
                    class="company-select"
                    onchange="this.form.submit()"
                >
                    <option value="">Select Company</option>

                    @foreach($companyList as $item)
                        <option
                            value="{{ $item->id }}"
                            {{ $companyId == $item->id ? 'selected' : '' }}
                        >
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </form>

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

            <a href="{{ route('invoices.index', ['company_id' => $companyId]) }}">
                Invoices
            </a>

            <span>›</span>
            New Invoice
        </div>


        <div class="page-heading">
            <div>
                <h1>New Invoice</h1>
                <p>Create invoice, payment and optional schedule</p>
            </div>

            <a
                href="{{ route('invoices.index', ['company_id' => $companyId]) }}"
                class="back-btn"
            >
                <i data-lucide="arrow-left"></i>
                Back
            </a>
        </div>


        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif


        <form
            method="POST"
            action="{{ route('invoices.store') }}"
            id="invoiceForm"
        >
            @csrf

            <input type="hidden" name="company_id" value="{{ $companyId }}">


            <div class="top-grid">

                {{-- CUSTOMER --}}
                <section class="card">

                    <div class="card-title">
                        <i data-lucide="user"></i>

                        <div>
                            <h2>Customer Information</h2>
                            <p>Select customer</p>
                        </div>
                    </div>

                    <label>Customer *</label>

                    <select
                        name="customer_id"
                        id="customerSelect"
                        required
                    >
                        <option value="">Select Customer</option>

                        @foreach($customers as $customer)
                            <option
                                value="{{ $customer->id }}"
                                data-business="{{ $customer->business_name }}"
                                data-name="{{ $customer->customer_name }}"
                                data-phone="{{ $customer->phone }}"
                                data-email="{{ $customer->email }}"
                                data-address="{{ $customer->address_line_1 }}"
                                data-city="{{ $customer->city }}"
                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}
                            >
                                {{ $customer->business_name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="customer-preview">
                        <strong id="previewBusiness">Select a customer</strong>
                        <span id="previewName"></span>
                        <span id="previewAddress"></span>
                        <span id="previewContact"></span>
                    </div>

                </section>


                {{-- INVOICE DETAILS --}}
                <section class="card">

                    <div class="card-title">
                        <i data-lucide="receipt-text"></i>

                        <div>
                            <h2>Invoice Details</h2>
                            <p>Invoice information</p>
                        </div>
                    </div>

                    <div class="form-grid">

                        <div>
                            <label>Invoice Number</label>
                            <input
                                type="text"
                                value="{{ $invoiceNumber ?: 'Select company first' }}"
                                readonly
                            >
                        </div>

                        <div>
                            <label>Template *</label>

                            <select name="template_id" required>
                                <option value="">Select Template</option>

                                @foreach($templates as $template)
                                    <option
                                        value="{{ $template->id }}"
                                        {{ old(
                                            'template_id',
                                            optional(
                                                $templates->firstWhere('is_default', 1)
                                            )->id
                                        ) == $template->id ? 'selected' : '' }}
                                    >
                                        {{ $template->template_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Invoice Date *</label>

                            <input
                                type="date"
                                name="invoice_date"
                                value="{{ old('invoice_date', now()->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div>
                            <label>Due Date</label>

                            <input
                                type="date"
                                name="due_date"
                                value="{{ old('due_date', now()->addDays(30)->format('Y-m-d')) }}"
                            >
                        </div>

                        <div>
                            <label>Reference</label>

                            <input
                                type="text"
                                name="reference"
                                value="{{ old('reference') }}"
                                placeholder="PO / Reference"
                            >
                        </div>

                        <div>
                            <label>Currency</label>
                            <input type="text" value="{{ $currency }}" readonly>
                        </div>

                    </div>

                    <label>Subject</label>

                    <input
                        type="text"
                        name="subject"
                        value="{{ old('subject') }}"
                        placeholder="Invoice subject"
                    >

                </section>


                {{-- OPTIONAL SCHEDULE --}}
                <section class="card">

                    <div class="card-title">
                        <i data-lucide="calendar-sync"></i>

                        <div>
                            <h2>Invoice Schedule</h2>
                            <p>Optional recurring invoice</p>
                        </div>
                    </div>

                    <label class="check-row">
                        <input
                            type="checkbox"
                            name="schedule_enabled"
                            id="scheduleEnabled"
                            value="1"
                            {{ old('schedule_enabled') ? 'checked' : '' }}
                        >
                        Enable recurring invoice
                    </label>

                    <div id="scheduleFields">

                        <label>Frequency</label>

                        <select name="schedule_frequency">
                            <option value="WEEKLY">Weekly</option>
                            <option value="MONTHLY" selected>Monthly</option>
                            <option value="QUARTERLY">Quarterly</option>
                            <option value="YEARLY">Yearly</option>
                        </select>

                        <div class="form-grid">

                            <div>
                                <label>Next Invoice Date</label>

                                <input
                                    type="date"
                                    name="schedule_start_date"
                                    value="{{ old('schedule_start_date', now()->addMonth()->format('Y-m-d')) }}"
                                >
                            </div>

                            <div>
                                <label>End Date</label>

                                <input
                                    type="date"
                                    name="schedule_end_date"
                                    value="{{ old('schedule_end_date') }}"
                                >
                            </div>

                        </div>

                    </div>

                </section>

            </div>


            {{-- ITEMS --}}
            <section class="card items-card">

                <div class="items-heading">

                    <div class="card-title">
                        <i data-lucide="list"></i>

                        <div>
                            <h2>Invoice Items</h2>
                            <p>Add products or services</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="primary-btn"
                        onclick="addItem()"
                    >
                        <i data-lucide="plus"></i>
                        Add Item
                    </button>

                </div>

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
                            <th>Tax %</th>
                            <th>Total</th>
                            <th></th>
                        </tr>
                        </thead>

                        <tbody id="itemsBody">

                        @foreach($oldItems as $index => $item)

                            <tr class="item-row">

                                <td class="row-number">
                                    {{ $index + 1 }}
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][item_name]"
                                        value="{{ $item['item_name'] ?? '' }}"
                                        placeholder="Item name"
                                        required
                                    >

                                    <input
                                        type="text"
                                        name="items[{{ $index }}][description]"
                                        value="{{ $item['description'] ?? '' }}"
                                        placeholder="Description"
                                        class="description"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][quantity]"
                                        value="{{ $item['quantity'] ?? 1 }}"
                                        min="0.01"
                                        step="0.01"
                                        class="calc quantity"
                                        required
                                    >
                                </td>

                                <td>
                                    <input
                                        type="text"
                                        name="items[{{ $index }}][unit]"
                                        value="{{ $item['unit'] ?? '' }}"
                                        placeholder="pcs"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][unit_price]"
                                        value="{{ $item['unit_price'] ?? 0 }}"
                                        min="0"
                                        step="0.01"
                                        class="calc unit-price"
                                        required
                                    >
                                </td>

                                <td>
                                    <select
                                        name="items[{{ $index }}][discount_type]"
                                        class="calc discount-type"
                                    >
                                        <option value="NONE">None</option>
                                        <option value="PERCENTAGE">%</option>
                                        <option value="FIXED">Fixed</option>
                                    </select>

                                    <input
                                        type="number"
                                        name="items[{{ $index }}][discount_value]"
                                        value="{{ $item['discount_value'] ?? 0 }}"
                                        min="0"
                                        step="0.01"
                                        class="calc discount-value"
                                    >
                                </td>

                                <td>
                                    <input
                                        type="number"
                                        name="items[{{ $index }}][tax_percentage]"
                                        value="{{ $item['tax_percentage'] ?? $defaultTax }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        class="calc tax"
                                    >
                                </td>

                                <td class="line-total">
                                    0.00
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="delete-btn"
                                        onclick="removeItem(this)"
                                    >
                                        <i data-lucide="trash-2"></i>
                                    </button>
                                </td>

                            </tr>

                        @endforeach

                        </tbody>

                    </table>

                </div>

            </section>


            <div class="bottom-grid">

                {{-- REQUIRED PAYMENT --}}
                <section class="card">

                    <div class="card-title">
                        <i data-lucide="credit-card"></i>

                        <div>
                            <h2>Payment Details</h2>
                            <p>Record payment for this invoice</p>
                        </div>
                    </div>


                    <div class="form-grid">

                        <div>
                            <label>Payment Method *</label>

                            <select
                                name="payment_method"
                                required
                            >
                                <option value="">Select Method</option>
                                <option value="BANK_TRANSFER">Bank Transfer</option>
                                <option value="CASH">Cash</option>
                                <option value="CARD">Card</option>
                                <option value="CHEQUE">Cheque</option>
                                <option value="OTHER">Other</option>
                            </select>
                        </div>

                        <div>
                            <label>Payment Date *</label>

                            <input
                                type="date"
                                name="payment_date"
                                value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                required
                            >
                        </div>

                        <div>
                            <label>Payment Amount ({{ $currency }}) *</label>

                            <input
                                type="number"
                                name="payment_amount"
                                id="paymentAmount"
                                value="{{ old('payment_amount') }}"
                                min="0.01"
                                step="0.01"
                                class="calc"
                                required
                            >
                        </div>

                        <div>
                            <label>Reference / Transaction ID</label>

                            <input
                                type="text"
                                name="payment_reference"
                                value="{{ old('payment_reference') }}"
                                placeholder="Transaction reference"
                            >
                        </div>

                    </div>

                    <label>Payment Notes</label>

                    <textarea
                        name="payment_notes"
                        placeholder="Payment notes"
                    >{{ old('payment_notes') }}</textarea>

                </section>


                {{-- NOTES --}}
                <section class="card">

                    <div class="card-title">
                        <i data-lucide="notebook-pen"></i>

                        <div>
                            <h2>Additional Information</h2>
                            <p>Notes and terms</p>
                        </div>
                    </div>

                    <label>Terms & Conditions</label>

                    <textarea name="terms_conditions">{{ old('terms_conditions') }}</textarea>

                    <label>Notes</label>

                    <textarea name="notes">{{ old('notes') }}</textarea>

                </section>


                {{-- SUMMARY --}}
                <section class="summary-card">

                    <div class="card-title">
                        <i data-lucide="calculator"></i>

                        <div>
                            <h2>Invoice Summary</h2>
                            <p>Calculated totals</p>
                        </div>
                    </div>

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>{{ $currency }} <span id="subtotal">0.00</span></strong>
                    </div>

                    <div class="summary-row">
                        <span>Discount</span>
                        <strong>{{ $currency }} <span id="discountTotal">0.00</span></strong>
                    </div>

                    @if($isVatEnabled)
                    <div class="summary-row">
                        <span>VAT Amount ({{ $defaultTax }}%)</span>
                        <strong>{{ $currency }} <span id="taxTotal">0.00</span></strong>
                    </div>
                    @else
                    <span id="taxTotal" style="display: none;">0.00</span>
                    @endif

                    <div class="summary-row">
                        <span>Additional Charges</span>

                        <input
                            type="number"
                            name="additional_charges"
                            id="additionalCharges"
                            value="{{ old('additional_charges', 0) }}"
                            min="0"
                            step="0.01"
                            class="calc summary-input"
                        >
                    </div>

                    <div class="summary-row paid-row">
                        <span>Amount Paid</span>
                        <strong>{{ $currency }} <span id="amountPaid">0.00</span></strong>
                    </div>

                    <div class="grand-total">
                        <span>Total Amount</span>
                        <strong>{{ $currency }} <span id="grandTotal">0.00</span></strong>
                    </div>

                    <div class="balance-total">
                        <span>Balance</span>
                        <strong>{{ $currency }} <span id="balanceAmount">0.00</span></strong>
                    </div>

                </section>

            </div>


            <div class="form-actions">

                <a
                    href="{{ route('invoices.index', ['company_id' => $companyId]) }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    formaction="{{ route('invoices.preview') }}"
                    formmethod="POST"
                    formtarget="_blank"
                    class="preview-btn"
                >
                    <i data-lucide="eye"></i>
                    Preview
                </button>

                <button
                    type="submit"
                    class="save-btn"
                >
                    <i data-lucide="save"></i>
                    Save Invoice
                </button>

            </div>
        </form>

    </main>

</div>


<script>

let itemIndex = {{ count($oldItems) }};
const defaultTax = @json((float) $defaultTax);


function addItem()
{
    const body = document.getElementById('itemsBody');
    const row = document.createElement('tr');

    row.className = 'item-row';

    row.innerHTML = `
        <td class="row-number">${itemIndex + 1}</td>

        <td>
            <input
                type="text"
                name="items[${itemIndex}][item_name]"
                placeholder="Item name"
                required
            >

            <input
                type="text"
                name="items[${itemIndex}][description]"
                placeholder="Description"
                class="description"
            >
        </td>

        <td>
            <input
                type="number"
                name="items[${itemIndex}][quantity]"
                value="1"
                min="0.01"
                step="0.01"
                class="calc quantity"
                required
            >
        </td>

        <td>
            <input
                type="text"
                name="items[${itemIndex}][unit]"
                placeholder="pcs"
            >
        </td>

        <td>
            <input
                type="number"
                name="items[${itemIndex}][unit_price]"
                value="0"
                min="0"
                step="0.01"
                class="calc unit-price"
                required
            >
        </td>

        <td>
            <select
                name="items[${itemIndex}][discount_type]"
                class="calc discount-type"
            >
                <option value="NONE">None</option>
                <option value="PERCENTAGE">%</option>
                <option value="FIXED">Fixed</option>
            </select>

            <input
                type="number"
                name="items[${itemIndex}][discount_value]"
                value="0"
                min="0"
                step="0.01"
                class="calc discount-value"
            >
        </td>

        <td>
            <input
                type="number"
                name="items[${itemIndex}][tax_percentage]"
                value="${defaultTax}"
                min="0"
                max="100"
                step="0.01"
                class="calc tax"
            >
        </td>

        <td class="line-total">0.00</td>

        <td>
            <button
                type="button"
                class="delete-btn"
                onclick="removeItem(this)"
            >
                <i data-lucide="trash-2"></i>
            </button>
        </td>
    `;

    body.appendChild(row);

    itemIndex++;

    lucide.createIcons();
    calculateTotals();
}


function removeItem(button)
{
    const rows = document.querySelectorAll('.item-row');

    if (rows.length <= 1) {
        return;
    }

    button.closest('tr').remove();

    updateRows();
    calculateTotals();
}


function updateRows()
{
    document
        .querySelectorAll('.item-row')
        .forEach((row, index) => {

            row.querySelector('.row-number')
                .textContent = index + 1;

        });
}


function calculateTotals()
{
    let subtotal = 0;
    let discountTotal = 0;
    let taxTotal = 0;

    document.querySelectorAll('.item-row')
        .forEach(row => {

            const qty =
                parseFloat(
                    row.querySelector('.quantity').value
                ) || 0;

            const price =
                parseFloat(
                    row.querySelector('.unit-price').value
                ) || 0;

            const type =
                row.querySelector('.discount-type').value;

            const discountValue =
                parseFloat(
                    row.querySelector('.discount-value').value
                ) || 0;

            const taxRate =
                parseFloat(
                    row.querySelector('.tax').value
                ) || 0;


            const lineSubtotal =
                qty * price;


            let discount = 0;

            if (type === 'PERCENTAGE') {

                discount =
                    lineSubtotal *
                    Math.min(discountValue, 100) /
                    100;

            } else if (type === 'FIXED') {

                discount =
                    Math.min(
                        discountValue,
                        lineSubtotal
                    );
            }


            const taxable =
                lineSubtotal - discount;

            const tax =
                taxable * taxRate / 100;

            const lineTotal =
                taxable + tax;


            subtotal += lineSubtotal;
            discountTotal += discount;
            taxTotal += tax;


            row.querySelector('.line-total')
                .textContent =
                    money(lineTotal);

        });


    const additional =
        parseFloat(
            document.getElementById(
                'additionalCharges'
            ).value
        ) || 0;


    const total =
        subtotal
        - discountTotal
        + taxTotal
        + additional;


    const payment =
        parseFloat(
            document.getElementById(
                'paymentAmount'
            ).value
        ) || 0;


    const balance =
        Math.max(
            total - payment,
            0
        );


    document.getElementById('paymentAmount').max =
        Math.max(total, 0).toFixed(2);


    document.getElementById('subtotal').textContent =
        money(subtotal);

    document.getElementById('discountTotal').textContent =
        money(discountTotal);

    document.getElementById('taxTotal').textContent =
        money(taxTotal);

    document.getElementById('grandTotal').textContent =
        money(total);

    document.getElementById('amountPaid').textContent =
        money(payment);

    document.getElementById('balanceAmount').textContent =
        money(balance);
}


function money(value)
{
    return Number(value).toLocaleString(
        undefined,
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    );
}


document.addEventListener('input', event => {

    if (event.target.classList.contains('calc')) {
        calculateTotals();
    }

});


document.addEventListener('change', event => {

    if (event.target.classList.contains('calc')) {
        calculateTotals();
    }

});


const customerSelect =
    document.getElementById('customerSelect');


function updateCustomer()
{
    const option =
        customerSelect.options[
            customerSelect.selectedIndex
        ];


    document.getElementById('previewBusiness').textContent =
        option?.dataset.business || 'Select a customer';

    document.getElementById('previewName').textContent =
        option?.dataset.name || '';

    document.getElementById('previewAddress').textContent =
        [
            option?.dataset.address,
            option?.dataset.city
        ]
        .filter(Boolean)
        .join(', ');

    document.getElementById('previewContact').textContent =
        [
            option?.dataset.phone,
            option?.dataset.email
        ]
        .filter(Boolean)
        .join(' • ');
}


customerSelect.addEventListener(
    'change',
    updateCustomer
);


const scheduleEnabled =
    document.getElementById('scheduleEnabled');

const scheduleFields =
    document.getElementById('scheduleFields');


function updateSchedule()
{
    scheduleFields.style.display =
        scheduleEnabled.checked
            ? 'block'
            : 'none';

    scheduleFields
        .querySelectorAll('input, select')
        .forEach(field => {

            field.disabled =
                !scheduleEnabled.checked;

        });
}


scheduleEnabled.addEventListener(
    'change',
    updateSchedule
);


updateCustomer();
updateSchedule();
calculateTotals();

lucide.createIcons();

</script>

</body>
</html>