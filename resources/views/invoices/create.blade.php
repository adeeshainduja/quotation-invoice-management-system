<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>New Invoice</title>

    <link
        rel="stylesheet"
        href="{{ asset('css/dashboard.css') }}"
    >

    {{-- Reuse same layout/styles as New Quotation --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/quotation-create.css') }}"
    >

    <script src="https://unpkg.com/lucide@latest"></script>
</head>


<body>


{{-- SHARED SIDEBAR --}}
@include('partials.sidebar')


<div class="page">


    {{-- TOP BAR --}}
    <header class="topbar">

        <form
            method="GET"
            action="{{ route('invoices.create') }}"
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
                        {{ $companyId == $item->id ? 'selected' : '' }}
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
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
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


        {{-- BREADCRUMB --}}
        <div class="breadcrumb">

            <a href="{{ route('dashboard') }}">
                Home
            </a>

            <span>›</span>


            <a href="{{ $companyId
                ? route('invoices.index', ['company_id' => $companyId])
                : route('invoices.index') }}">
                Invoices
            </a>


            <span>›</span>

            New Invoice

        </div>



        {{-- HEADING --}}
        <div class="create-heading">

            <div>

                <h1>
                    New Invoice
                </h1>

                <p>
                    Create a new invoice for your customer
                </p>

            </div>


            <a
                href="{{ $companyId
                    ? route('invoices.index', ['company_id' => $companyId])
                    : route('invoices.index') }}"
                class="back-btn"
            >

                <i data-lucide="arrow-left"></i>

                Back to Invoices

            </a>

        </div>



        {{-- ERRORS --}}
        @if($errors->any())

            <div class="form-errors">
                {{ $errors->first() }}
            </div>

        @endif



        {{-- COMPANY WARNING --}}
        @if($companyList->isEmpty())

            <div class="form-errors">

                No active company exists.

                <a href="{{ route('companies.create') }}">
                    Add Company
                </a>

            </div>

        @elseif(!$company)

            <div class="form-errors">
                Select an active company before creating an invoice.
            </div>

        @endif



        {{-- TEMPLATE WARNING --}}
        @if($company && $templates->isEmpty())

            <div class="form-errors">

                No invoice template exists for this company.

                Please create an invoice template before saving.

            </div>

        @endif



        <form
            method="POST"
            action="{{ route('invoices.store') }}"
            id="invoiceForm"
        >

            @csrf


            @if($companyId)

                <input
                    type="hidden"
                    name="company_id"
                    value="{{ $companyId }}"
                >

            @endif



            {{-- TOP GRID --}}
            <div class="top-grid">


                {{-- CUSTOMER --}}
                <section class="form-card">

                    <h2>

                        <i data-lucide="user"></i>

                        Customer Information

                    </h2>


                    <label>
                        Select Customer *
                    </label>


                    <div class="customer-select-row">

                        <select
                            name="customer_id"
                            id="customerSelect"
                            required
                            @disabled(!$company)
                        >

                            <option value="">
                                Search and select a customer...
                            </option>


                            @foreach($customers as $customer)

                                <option
                                    value="{{ $customer->id }}"

                                    data-business="{{ $customer->business_name }}"

                                    data-name="{{ $customer->customer_name }}"

                                    data-address="{{ $customer->address_line_1 }}"

                                    data-city="{{ $customer->city }}"

                                    data-phone="{{ $customer->phone }}"

                                    data-email="{{ $customer->email }}"

                                    {{ old('customer_id') == $customer->id
                                        ? 'selected'
                                        : '' }}
                                >

                                    {{ $customer->business_name }}

                                </option>

                            @endforeach

                        </select>


                        <a
                            href="{{ $companyId
                                ? route(
                                    'customers.create',
                                    ['company_id' => $companyId]
                                )
                                : route('customers.create') }}"
                            class="add-customer-btn"
                        >

                            <i data-lucide="plus"></i>

                            Add New

                        </a>

                    </div>



                    <div
                        class="customer-preview"
                        id="customerPreview"
                    >

                        <strong id="previewBusiness">
                            Select a customer
                        </strong>

                        <span id="previewName"></span>

                        <span id="previewAddress"></span>

                        <span id="previewContact"></span>

                    </div>

                </section>



                {{-- INVOICE DETAILS --}}
                <section class="form-card">

                    <h2>

                        <i data-lucide="receipt-text"></i>

                        Invoice Details

                    </h2>


                    <div class="two-columns">


                        <div>

                            <label>
                                Invoice Number *
                            </label>

                            <input
                                type="text"
                                value="{{ $invoiceNumber ?: 'Select company first' }}"
                                readonly
                            >

                        </div>



                        <div>

                            <label>
                                Template *
                            </label>


                            <select
                                name="template_id"
                                required
                                @disabled(!$company)
                            >

                                <option value="">
                                    Select Template
                                </option>


                                @foreach($templates as $template)

                                    <option
                                        value="{{ $template->id }}"
                                        {{ old(
                                            'template_id',
                                            optional(
                                                $templates->firstWhere(
                                                    'is_default',
                                                    1
                                                )
                                            )->id
                                        ) == $template->id
                                            ? 'selected'
                                            : '' }}
                                    >

                                        {{ $template->template_name }}

                                    </option>

                                @endforeach

                            </select>

                        </div>



                        <div>

                            <label>
                                Invoice Date *
                            </label>

                            <input
                                type="date"
                                name="invoice_date"
                                value="{{ old(
                                    'invoice_date',
                                    now()->format('Y-m-d')
                                ) }}"
                                required
                            >

                        </div>



                        <div>

                            <label>
                                Due Date
                            </label>

                            <input
                                type="date"
                                name="due_date"
                                value="{{ old(
                                    'due_date',
                                    now()->addDays(30)->format('Y-m-d')
                                ) }}"
                            >

                        </div>



                        <div>

                            <label>
                                Currency *
                            </label>

                            <input
                                type="text"
                                value="{{ $company->currency ?? 'LKR' }}"
                                readonly
                            >

                        </div>



                        <div>

                            <label>
                                Reference No.
                            </label>

                            <input
                                type="text"
                                name="reference"
                                value="{{ old('reference') }}"
                                placeholder="Optional reference"
                            >

                        </div>

                    </div>



                    <label>
                        Subject
                    </label>

                    <input
                        type="text"
                        name="subject"
                        value="{{ old('subject') }}"
                        placeholder="Invoice subject"
                    >

                </section>

            </div>



            {{-- ITEMS --}}
            <section class="items-card">

                <div class="items-header">

                    <h2>

                        <i data-lucide="list"></i>

                        Invoice Items

                    </h2>


                    <button
                        type="button"
                        class="add-item-btn"
                        onclick="addItem()"
                        @disabled(!$company)
                    >

                        <i data-lucide="plus"></i>

                        Add Item

                    </button>

                </div>


                <div class="table-wrap">

                    <table id="itemsTable">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>
                                    Item / Description *
                                </th>

                                <th>Qty *</th>

                                <th>Unit</th>

                                <th>Unit Price *</th>

                                <th>Discount</th>

                                <th>Tax %</th>

                                <th>Total</th>

                                <th></th>

                            </tr>

                        </thead>


                        <tbody id="itemsBody">


                        @php
                            $defaultTax =
                                $company && $company->vat_registered
                                    ? ($company->vat_percentage ?? 0)
                                    : 0;

                            $oldItems = old('items', [
                                [
                                    'item_name' => '',
                                    'description' => '',
                                    'quantity' => 1,
                                    'unit' => '',
                                    'unit_price' => 0,
                                    'discount_type' => 'NONE',
                                    'discount_value' => 0,
                                    'tax_percentage' => $defaultTax,
                                ]
                            ]);
                        @endphp



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

                                        <option
                                            value="NONE"
                                            {{ ($item['discount_type'] ?? 'NONE')
                                                === 'NONE'
                                                ? 'selected'
                                                : '' }}
                                        >
                                            None
                                        </option>


                                        <option
                                            value="PERCENTAGE"
                                            {{ ($item['discount_type'] ?? '')
                                                === 'PERCENTAGE'
                                                ? 'selected'
                                                : '' }}
                                        >
                                            %
                                        </option>


                                        <option
                                            value="FIXED"
                                            {{ ($item['discount_type'] ?? '')
                                                === 'FIXED'
                                                ? 'selected'
                                                : '' }}
                                        >
                                            Fixed
                                        </option>

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
                                        {{ !$company || !$company->vat_registered
                                            ? 'readonly'
                                            : '' }}
                                    >

                                </td>



                                <td class="line-total">
                                    0.00
                                </td>



                                <td>

                                    <button
                                        type="button"
                                        class="delete-item"
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



            {{-- BOTTOM --}}
            <div class="bottom-grid">


                <section class="form-card">

                    <h2>

                        <i data-lucide="notebook"></i>

                        Additional Information

                    </h2>


                    <label>
                        Terms & Conditions
                    </label>

                    <textarea
                        name="terms_conditions"
                        placeholder="Enter terms and conditions"
                    >{{ old('terms_conditions') }}</textarea>


                    <label>
                        Notes
                    </label>

                    <textarea
                        name="notes"
                        placeholder="Enter invoice notes"
                    >{{ old('notes') }}</textarea>

                </section>



                {{-- SUMMARY --}}
                <section class="summary-card">

                    <h2>

                        <i data-lucide="calculator"></i>

                        Summary

                    </h2>



                    <div class="summary-row">

                        <span>
                            Subtotal
                        </span>

                        <strong>

                            {{ $company->currency ?? 'LKR' }}

                            <span id="subtotal">
                                0.00
                            </span>

                        </strong>

                    </div>



                    <div class="summary-row">

                        <span>
                            Discount
                        </span>

                        <strong>

                            {{ $company->currency ?? 'LKR' }}

                            <span id="discountTotal">
                                0.00
                            </span>

                        </strong>

                    </div>



                    <div class="summary-row">

                        <span>
                            Tax
                        </span>

                        <strong>

                            {{ $company->currency ?? 'LKR' }}

                            <span id="taxTotal">
                                0.00
                            </span>

                        </strong>

                    </div>



                    <div class="summary-row">

                        <span>
                            Additional Charges
                        </span>

                        <input
                            type="number"
                            name="additional_charges"
                            id="additionalCharges"
                            value="{{ old(
                                'additional_charges',
                                0
                            ) }}"
                            min="0"
                            step="0.01"
                            class="calc"
                        >

                    </div>



                    <div class="summary-total">

                        <span>
                            Total Amount
                        </span>

                        <strong>

                            {{ $company->currency ?? 'LKR' }}

                            <span id="grandTotal">
                                0.00
                            </span>

                        </strong>

                    </div>

                </section>

            </div>



            {{-- ACTIONS --}}
            <div class="form-actions">

                <a
                    href="{{ $companyId
                        ? route(
                            'invoices.index',
                            ['company_id' => $companyId]
                        )
                        : route('invoices.index') }}"
                    class="cancel-btn"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="save-btn"
                    @disabled(
                        !$company ||
                        $templates->isEmpty()
                    )
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

const vatRegistered = @json(
    (bool) ($company->vat_registered ?? false)
);



function addItem()
{
    const body =
        document.getElementById('itemsBody');

    const row =
        document.createElement('tr');

    row.className =
        'item-row';


    row.innerHTML = `

        <td class="row-number">
            ${itemIndex + 1}
        </td>


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

                <option value="NONE">
                    None
                </option>

                <option value="PERCENTAGE">
                    %
                </option>

                <option value="FIXED">
                    Fixed
                </option>

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
                ${vatRegistered ? '' : 'readonly'}
            >

        </td>


        <td class="line-total">
            0.00
        </td>


        <td>

            <button
                type="button"
                class="delete-item"
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
    const rows =
        document.querySelectorAll('.item-row');


    if (rows.length === 1) {
        return;
    }


    button.closest('tr').remove();

    updateNumbers();

    calculateTotals();
}



function updateNumbers()
{
    document
        .querySelectorAll('.item-row')
        .forEach((row, index) => {

            row
                .querySelector('.row-number')
                .textContent =
                    index + 1;

        });
}



function calculateTotals()
{
    let subtotal = 0;

    let discountTotal = 0;

    let taxTotal = 0;


    document
        .querySelectorAll('.item-row')
        .forEach(row => {


            const quantity =
                parseFloat(
                    row.querySelector('.quantity').value
                ) || 0;


            const unitPrice =
                parseFloat(
                    row.querySelector('.unit-price').value
                ) || 0;


            const discountType =
                row.querySelector(
                    '.discount-type'
                ).value;


            const discountValue =
                parseFloat(
                    row.querySelector(
                        '.discount-value'
                    ).value
                ) || 0;


            const taxPercentage =
                parseFloat(
                    row.querySelector('.tax').value
                ) || 0;


            const lineSubtotal =
                quantity * unitPrice;


            let discountAmount = 0;


            if (
                discountType ===
                'PERCENTAGE'
            ) {

                discountAmount =
                    lineSubtotal
                    * discountValue
                    / 100;

            }


            if (
                discountType ===
                'FIXED'
            ) {

                discountAmount =
                    Math.min(
                        discountValue,
                        lineSubtotal
                    );

            }


            const taxable =
                lineSubtotal
                - discountAmount;


            const taxAmount =
                taxable
                * taxPercentage
                / 100;


            const lineTotal =
                taxable
                + taxAmount;


            subtotal +=
                lineSubtotal;


            discountTotal +=
                discountAmount;


            taxTotal +=
                taxAmount;


            row.querySelector(
                '.line-total'
            ).textContent =
                money(lineTotal);

        });


    const additionalCharges =
        parseFloat(
            document.getElementById(
                'additionalCharges'
            ).value
        ) || 0;


    const grandTotal =
        subtotal
        - discountTotal
        + taxTotal
        + additionalCharges;


    document.getElementById(
        'subtotal'
    ).textContent =
        money(subtotal);


    document.getElementById(
        'discountTotal'
    ).textContent =
        money(discountTotal);


    document.getElementById(
        'taxTotal'
    ).textContent =
        money(taxTotal);


    document.getElementById(
        'grandTotal'
    ).textContent =
        money(grandTotal);
}



function money(value)
{
    return value.toLocaleString(
        undefined,
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    );
}



document.addEventListener(
    'input',
    function(event) {

        if (
            event.target.classList.contains(
                'calc'
            )
        ) {

            calculateTotals();

        }

    }
);



document.addEventListener(
    'change',
    function(event) {

        if (
            event.target.classList.contains(
                'calc'
            )
        ) {

            calculateTotals();

        }

    }
);



const customerSelect =
    document.getElementById(
        'customerSelect'
    );



function updateCustomer()
{
    if (!customerSelect) {
        return;
    }


    const selected =
        customerSelect.options[
            customerSelect.selectedIndex
        ];


    if (
        !selected ||
        !selected.value
    ) {

        document.getElementById(
            'previewBusiness'
        ).textContent =
            'Select a customer';


        document.getElementById(
            'previewName'
        ).textContent =
            '';


        document.getElementById(
            'previewAddress'
        ).textContent =
            '';


        document.getElementById(
            'previewContact'
        ).textContent =
            '';

        return;
    }


    document.getElementById(
        'previewBusiness'
    ).textContent =
        selected.dataset.business || '';


    document.getElementById(
        'previewName'
    ).textContent =
        selected.dataset.name || '';


    const address = [

        selected.dataset.address,

        selected.dataset.city

    ].filter(Boolean);


    document.getElementById(
        'previewAddress'
    ).textContent =
        address.join(', ');


    const contact = [

        selected.dataset.phone,

        selected.dataset.email

    ].filter(Boolean);


    document.getElementById(
        'previewContact'
    ).textContent =
        contact.join(' | ');
}



if (customerSelect) {

    customerSelect.addEventListener(
        'change',
        updateCustomer
    );

}



updateCustomer();

calculateTotals();

lucide.createIcons();

</script>


</body>

</html>