<?php

use App\Models\Company;
use App\Models\CompanyTemplate;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

function setupTestEnvironment(): array
{
    $admin = User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);

    $company = Company::create([
        'name' => 'Acme Corp '.uniqid(),
        'registration_number' => 'REG-'.rand(1000, 9999),
        'currency' => 'LKR',
        'address_line_1' => '123 Main St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112345678',
        'email' => 'acme'.uniqid().'@example.com',
        'website' => 'https://example.com',
        'logo_path' => 'logos/test.png',
        'status' => 'ACTIVE',
        'quotation_prefix' => 'QT-',
        'invoice_prefix' => 'INV-',
        'quotation_next_number' => 2,
        'invoice_next_number' => 1,
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'John Doe',
        'business_name' => 'John Business',
        'address_line_1' => '456 Client St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94771234567',
        'email' => 'client'.uniqid().'@example.com',
        'status' => 'ACTIVE',
    ]);

    $quotationTemplate = CompanyTemplate::create([
        'company_id' => $company->id,
        'document_type' => 'QUOTATION',
        'template_name' => 'Standard Quotation Template',
        'is_default' => true,
        'status' => 'ACTIVE',
    ]);

    $invoiceTemplate = CompanyTemplate::create([
        'company_id' => $company->id,
        'document_type' => 'INVOICE',
        'template_name' => 'Standard Invoice Template',
        'is_default' => true,
        'status' => 'ACTIVE',
    ]);

    $quotation = Quotation::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'template_id' => $quotationTemplate->id,
        'quotation_number' => 'QT-2026-0001',
        'quotation_date' => now()->toDateString(),
        'expiry_date' => now()->addDays(30)->toDateString(),
        'reference' => 'REF-001',
        'subtotal' => 1000,
        'discount_type' => 'PERCENTAGE',
        'discount_value' => 10,
        'discount_amount' => 100,
        'tax_percentage' => 0,
        'tax_amount' => 0,
        'vat_enabled' => false,
        'vat_percentage' => 0,
        'vat_amount' => 0,
        'additional_charges' => 0,
        'grand_total' => 900,
        'status' => 'DRAFT',
        'notes' => 'Test notes',
        'terms_conditions' => 'Test terms',
        'created_by' => $admin->id,
        'company_snapshot' => $company->toArray(),
        'customer_snapshot' => $customer->toArray(),
        'template_snapshot' => $quotationTemplate->toArray(),
    ]);

    QuotationItem::create([
        'quotation_id' => $quotation->id,
        'sort_order' => 1,
        'item_name' => 'Item 1',
        'description' => 'Desc 1',
        'quantity' => 2,
        'unit_price' => 500,
        'discount_type' => 'PERCENTAGE',
        'discount_value' => 10,
        'discount_amount' => 100,
        'tax_percentage' => 0,
        'tax_amount' => 0,
        'line_total' => 900,
    ]);

    return compact('admin', 'company', 'customer', 'quotationTemplate', 'invoiceTemplate', 'quotation');
}

test('view quotation details page works', function () {
    $env = setupTestEnvironment();

    $response = $this->actingAs($env['admin'])
        ->get(route('quotations.show', $env['quotation']->id));

    $response->assertStatus(200);
    $response->assertSee($env['quotation']->quotation_number);
    $response->assertSee('John Business');
    $response->assertSee('Item 1');
});

test('edit quotation page loads with prefilled data', function () {
    $env = setupTestEnvironment();

    $response = $this->actingAs($env['admin'])
        ->get(route('quotations.edit', $env['quotation']->id));

    $response->assertStatus(200);
    $response->assertSee($env['quotation']->quotation_number);
    $response->assertSee('John Business');
    $response->assertSee('Item 1');
});

test('update quotation saves modified items and recalculates totals', function () {
    $env = setupTestEnvironment();

    $updateData = [
        'customer_id' => $env['customer']->id,
        'template_id' => $env['quotationTemplate']->id,
        'quotation_date' => now()->toDateString(),
        'expiry_date' => now()->addDays(15)->toDateString(),
        'reference' => 'REF-UPDATED',
        'notes' => 'Updated notes',
        'terms_conditions' => 'Updated terms',
        'items' => [
            [
                'item_name' => 'Updated Item A',
                'description' => 'Desc A',
                'quantity' => 3,
                'unit_price' => 200,
                'discount' => 5,
                'tax' => 0,
            ],
        ],
    ];

    $response = $this->actingAs($env['admin'])
        ->put(route('quotations.update', $env['quotation']->id), $updateData);

    $response->assertRedirect(route('quotations.show', $env['quotation']->id));
    $response->assertSessionHas('success');

    $updatedQuotation = Quotation::find($env['quotation']->id);
    expect($updatedQuotation->reference)->toBe('REF-UPDATED');
    expect((float) $updatedQuotation->subtotal)->toBe(600.0);
    expect((float) $updatedQuotation->discount_amount)->toBe(30.0);
    expect((float) $updatedQuotation->grand_total)->toBe(570.0);

    $items = QuotationItem::where('quotation_id', $env['quotation']->id)->get();
    expect($items)->toHaveCount(1);
    expect($items->first()->item_name)->toBe('Updated Item A');
});

test('clone quotation creates new draft quotation with incremented number and identical items', function () {
    $env = setupTestEnvironment();

    $response = $this->actingAs($env['admin'])
        ->post(route('quotations.clone', $env['quotation']->id));

    $newQuotation = Quotation::where('id', '!=', $env['quotation']->id)->latest('id')->first();
    expect($newQuotation)->not->toBeNull();

    $response->assertRedirect(route('quotations.show', $newQuotation->id));
    $response->assertSessionHas('success');

    expect($newQuotation->status)->toBe('DRAFT');
    expect($newQuotation->reference)->toBeNull();
    expect($newQuotation->quotation_number)->not->toBe($env['quotation']->quotation_number);
    expect((float) $newQuotation->grand_total)->toBe((float) $env['quotation']->grand_total);

    $newItems = QuotationItem::where('quotation_id', $newQuotation->id)->get();
    expect($newItems)->toHaveCount(1);
    expect($newItems->first()->item_name)->toBe('Item 1');
});

test('mark as sent changes status from draft to sent', function () {
    $env = setupTestEnvironment();

    $response = $this->actingAs($env['admin'])
        ->post(route('quotations.send', $env['quotation']->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $updatedQuotation = Quotation::find($env['quotation']->id);
    expect($updatedQuotation->status)->toBe('SENT');
});

test('accept quotation changes status to accepted', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'SENT']);

    $response = $this->actingAs($env['admin'])
        ->post(route('quotations.accept', $env['quotation']->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $updatedQuotation = Quotation::find($env['quotation']->id);
    expect($updatedQuotation->status)->toBe('ACCEPTED');
});

test('reject quotation changes status to rejected', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'SENT']);

    $response = $this->actingAs($env['admin'])
        ->post(route('quotations.reject', $env['quotation']->id));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $updatedQuotation = Quotation::find($env['quotation']->id);
    expect($updatedQuotation->status)->toBe('REJECTED');
});

test('convert quotation to invoice redirects to invoice create page with quotation_id', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'ACCEPTED']);

    $response = $this->actingAs($env['admin'])
        ->post(route('quotations.convert', $env['quotation']->id));

    $response->assertRedirect(route('invoices.create', ['quotation_id' => $env['quotation']->id]));
    $response->assertSessionHas('success');

    // Quotation remains ACCEPTED (not CONVERTED) until invoice is actually created
    $quotationAfter = Quotation::find($env['quotation']->id);
    expect($quotationAfter->status)->toBe('ACCEPTED');
});

test('accept quotation redirects to invoice create page with quotation_id', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'SENT']);

    $response = $this->actingAs($env['admin'])
        ->post(route('quotations.accept', $env['quotation']->id));

    $response->assertRedirect(route('invoices.create', ['quotation_id' => $env['quotation']->id]));
    $response->assertSessionHas('success');

    $quotationAfter = Quotation::find($env['quotation']->id);
    expect($quotationAfter->status)->toBe('ACCEPTED');
});

test('invoice create page is pre-filled when quotation_id is provided', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'ACCEPTED']);

    $response = $this->actingAs($env['admin'])
        ->get(route('invoices.create', ['quotation_id' => $env['quotation']->id]));

    $response->assertStatus(200);
    $response->assertSee($env['quotation']->quotation_number);
    $response->assertSee('Pre-filled from Quotation');
    $response->assertSee('Item 1');
});

test('creating invoice with quotation_id marks quotation as CONVERTED and links invoice', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'ACCEPTED']);

    $response = $this->actingAs($env['admin'])
        ->post(route('invoices.store'), [
            'company_id' => $env['company']->id,
            'customer_id' => $env['customer']->id,
            'template_id' => $env['invoiceTemplate']->id,
            'quotation_id' => $env['quotation']->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'reference' => $env['quotation']->quotation_number,
            'subject' => 'Invoice for Quotation '.$env['quotation']->quotation_number,
            'notes' => 'Test notes',
            'terms_conditions' => 'Test terms',
            'additional_charges' => 0,
            'payment_method' => 'BANK_TRANSFER',
            'payment_date' => now()->toDateString(),
            'payment_amount' => 900,
            'items' => [
                [
                    'item_name' => 'Item 1',
                    'description' => 'Desc 1',
                    'quantity' => 2,
                    'unit' => 'pcs',
                    'unit_price' => 500,
                    'discount_type' => 'PERCENTAGE',
                    'discount_value' => 10,
                    'tax_percentage' => 0,
                ],
            ],
        ]);

    $invoice = Invoice::latest('id')->first();
    expect($invoice)->not->toBeNull();

    $response->assertRedirect(route('invoices.show', $invoice->id));
    $response->assertSessionHas('success');

    expect($invoice->quotation_id)->toBe($env['quotation']->id);

    $quotationAfter = Quotation::find($env['quotation']->id);
    expect($quotationAfter->status)->toBe('CONVERTED');
    expect($quotationAfter->converted_invoice_id)->toBe($invoice->id);
});

test('view invoice details page works', function () {
    $env = setupTestEnvironment();

    // Create an invoice directly via store()
    $this->actingAs($env['admin'])
        ->post(route('invoices.store'), [
            'company_id' => $env['company']->id,
            'customer_id' => $env['customer']->id,
            'template_id' => $env['invoiceTemplate']->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'payment_method' => 'CASH',
            'payment_date' => now()->toDateString(),
            'payment_amount' => 900,
            'items' => [
                [
                    'item_name' => 'Item 1',
                    'description' => 'Desc 1',
                    'quantity' => 2,
                    'unit' => 'pcs',
                    'unit_price' => 500,
                    'discount_type' => 'PERCENTAGE',
                    'discount_value' => 10,
                    'tax_percentage' => 0,
                ],
            ],
        ]);

    $invoice = Invoice::latest('id')->first();

    $response = $this->actingAs($env['admin'])
        ->get(route('invoices.show', $invoice->id));

    $response->assertStatus(200);
    $response->assertSee($invoice->invoice_number);
    $response->assertSee('John Business');
    $response->assertSee('Item 1');
    $response->assertSee('Export PDF');
});

test('quotation pdf download works', function () {
    $env = setupTestEnvironment();

    $response = $this->actingAs($env['admin'])
        ->get(route('quotations.pdf', $env['quotation']->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('invoice pdf download works', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'ACCEPTED']);

    $this->actingAs($env['admin'])
        ->post(route('quotations.convert', $env['quotation']->id));

    $invoice = Invoice::latest('id')->first();

    $response = $this->actingAs($env['admin'])
        ->get(route('invoices.pdf', $invoice->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('quotations index displays view and edit actions', function () {
    $env = setupTestEnvironment();

    $response = $this->actingAs($env['admin'])
        ->get(route('quotations.index', ['company_id' => $env['company']->id]));

    $response->assertStatus(200);
    $response->assertSee(route('quotations.show', $env['quotation']->id));
    $response->assertSee(route('quotations.edit', $env['quotation']->id));
});

test('invoices index displays view and pdf actions', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'ACCEPTED']);

    $this->actingAs($env['admin'])
        ->post(route('quotations.convert', $env['quotation']->id));

    $invoice = Invoice::latest('id')->first();

    $response = $this->actingAs($env['admin'])
        ->get(route('invoices.index', ['company_id' => $env['company']->id]));

    $response->assertStatus(200);
    $response->assertSee(route('invoices.show', $invoice->id));
    $response->assertSee(route('invoices.pdf', $invoice->id));
});

test('cannot edit a converted quotation', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'CONVERTED']);

    $response = $this->actingAs($env['admin'])
        ->get(route('quotations.edit', $env['quotation']->id));

    $response->assertRedirect(route('quotations.show', $env['quotation']->id));
    $response->assertSessionHas('error');
});

test('edit invoice page loads with prefilled data', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'ACCEPTED']);
    $this->actingAs($env['admin'])
        ->post(route('quotations.convert', $env['quotation']->id));
    $invoice = Invoice::latest('id')->first();

    $response = $this->actingAs($env['admin'])
        ->get(route('invoices.edit', $invoice->id));

    $response->assertStatus(200);
    $response->assertSee($invoice->invoice_number);
    $response->assertSee('John Business');
    $response->assertSee('Item 1');
});

test('update invoice updates details items and totals and logs activity', function () {
    $env = setupTestEnvironment();
    $env['quotation']->update(['status' => 'ACCEPTED']);
    $this->actingAs($env['admin'])
        ->post(route('quotations.convert', $env['quotation']->id));
    $invoice = Invoice::latest('id')->first();

    $updateData = [
        'customer_id' => $env['customer']->id,
        'template_id' => $env['invoiceTemplate']->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(20)->toDateString(),
        'subject' => 'Updated Subject',
        'reference' => 'PO-999',
        'additional_charges' => 50,
        'notes' => 'Updated invoice notes',
        'terms_conditions' => 'Updated invoice terms',
        'items' => [
            [
                'item_name' => 'Updated Service',
                'description' => 'Updated description',
                'quantity' => 2,
                'unit' => 'hours',
                'unit_price' => 300,
                'discount_type' => 'FIXED',
                'discount_value' => 50,
                'tax_percentage' => 0,
            ],
        ],
    ];

    $response = $this->actingAs($env['admin'])
        ->put(route('invoices.update', $invoice->id), $updateData);

    $response->assertRedirect(route('invoices.show', $invoice->id));
    $response->assertSessionHas('success');

    $updatedInvoice = Invoice::find($invoice->id);
    expect($updatedInvoice->subject)->toBe('Updated Subject');
    expect($updatedInvoice->reference)->toBe('PO-999');
    expect((float) $updatedInvoice->subtotal)->toBe(600.0);
    expect((float) $updatedInvoice->discount_amount)->toBe(50.0);
    expect((float) $updatedInvoice->additional_charges)->toBe(50.0);
    expect((float) $updatedInvoice->grand_total)->toBe(600.0);

    $log = DB::table('activity_logs')
        ->where('entity_type', 'Invoice')
        ->where('entity_id', $invoice->id)
        ->where('action', 'UPDATE')
        ->first();

    expect($log)->not->toBeNull();
});

test('activity logs are properly populated across operations and displayed on index', function () {
    $env = setupTestEnvironment();

    // 1. Quotation reject creates log
    $env['quotation']->update(['status' => 'SENT']);
    $this->actingAs($env['admin'])
        ->post(route('quotations.reject', $env['quotation']->id));

    $rejectLog = DB::table('activity_logs')
        ->where('entity_type', 'Quotation')
        ->where('entity_id', $env['quotation']->id)
        ->where('action', 'REJECT')
        ->first();
    expect($rejectLog)->not->toBeNull();

    // 2. Quotation accept creates log
    $this->actingAs($env['admin'])
        ->post(route('quotations.accept', $env['quotation']->id));

    $acceptLog = DB::table('activity_logs')
        ->where('entity_type', 'Quotation')
        ->where('entity_id', $env['quotation']->id)
        ->where('action', 'ACCEPT')
        ->first();
    expect($acceptLog)->not->toBeNull();

    // 3. Convert quotation creates log
    $this->actingAs($env['admin'])
        ->post(route('quotations.convert', $env['quotation']->id));

    $convertLog = DB::table('activity_logs')
        ->where('entity_type', 'Quotation')
        ->where('entity_id', $env['quotation']->id)
        ->where('action', 'CONVERT TO INVOICE')
        ->first();
    expect($convertLog)->not->toBeNull();

    $invoice = Invoice::latest('id')->first();

    // 4. Download invoice PDF creates log
    $this->actingAs($env['admin'])
        ->get(route('invoices.pdf', $invoice->id));

    $pdfLog = DB::table('activity_logs')
        ->where('entity_type', 'Invoice')
        ->where('entity_id', $invoice->id)
        ->where('action', 'EXPORT PDF')
        ->first();
    expect($pdfLog)->not->toBeNull();

    // 5. Activity logs index displays logs
    $response = $this->actingAs($env['admin'])
        ->get(route('activity-logs.index'));

    $response->assertStatus(200);
    $response->assertSee('Activity Logs');
    $response->assertSee('Quotation');
    $response->assertSee('Invoice');
});
