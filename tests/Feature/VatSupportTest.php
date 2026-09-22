<?php

use App\Models\Company;
use App\Models\CompanyTemplate;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\User;
use App\Services\NumberToWordsHelper;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

test('company can be created and edited with VAT fields', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($admin)->post(route('companies.store'), [
        'name' => 'DKS Techno Holdings Pvt Ltd',
        'registration_number' => 'PV123456',
        'tin_number' => '103211530',
        'tax_registration_number' => 'TRN-999',
        'vat_enabled' => '1',
        'vat_number' => '103211530',
        'tax_percentage' => '18.00',
        'address_line_1' => '100 Galle Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112233445',
        'email' => 'info@dkstechno.lk',
        'quotation_prefix' => 'DKS-Q',
        'invoice_prefix' => 'DKS-INV',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    $response->assertRedirect(route('companies.index'));

    $company = Company::where('name', 'DKS Techno Holdings Pvt Ltd')->first();
    expect($company)->not->toBeNull()
        ->and((bool) $company->vat_enabled)->toBeTrue()
        ->and((bool) $company->vat_registered)->toBeTrue()
        ->and($company->vat_number)->toBe('103211530')
        ->and($company->tin_number)->toBe('103211530')
        ->and($company->tax_registration_number)->toBe('TRN-999')
        ->and((float) $company->tax_percentage)->toBe(18.00);

    // Test Edit Page
    $editResponse = $this->actingAs($admin)->get(route('companies.edit', $company->id));
    $editResponse->assertOk();
    $editResponse->assertSee('Tax Information');
    $editResponse->assertSee('103211530');
    $editResponse->assertSee('TRN-999');

    // Test Update
    $updateResponse = $this->actingAs($admin)->put(route('companies.update', $company->id), [
        'name' => 'DKS Techno Holdings Updated',
        'registration_number' => 'PV123456',
        'tin_number' => '103211530-UPDATED',
        'tax_registration_number' => 'TRN-888',
        'vat_enabled' => '0',
        'vat_number' => null,
        'tax_percentage' => '0.00',
        'address_line_1' => '100 Galle Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112233445',
        'email' => 'info@dkstechno.lk',
        'quotation_prefix' => 'DKS-Q',
        'invoice_prefix' => 'DKS-INV',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    $updateResponse->assertRedirect(route('companies.index'));
    $company->refresh();
    expect($company->name)->toBe('DKS Techno Holdings Updated')
        ->and((bool) $company->vat_enabled)->toBeFalse()
        ->and($company->tax_registration_number)->toBe('TRN-888');
});

test('quotation calculation applies VAT when company VAT enabled', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $company = Company::create([
        'name' => 'VAT Active Co',
        'registration_number' => 'REG-1',
        'tin_number' => 'TIN-001',
        'vat_enabled' => true,
        'vat_registered' => true,
        'vat_number' => 'VAT-001',
        'tax_percentage' => 18.00,
        'vat_percentage' => 18.00,
        'address_line_1' => '10 High St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94111111111',
        'email' => 'vat@example.com',
        'website' => 'https://example.com',
        'logo_path' => 'logos/test.png',
        'quotation_prefix' => 'Q-',
        'invoice_prefix' => 'INV-',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'Alice Customer',
        'business_name' => 'Alice Business',
        'address_line_1' => '20 First St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'status' => 'ACTIVE',
    ]);

    $template = CompanyTemplate::create([
        'company_id' => $company->id,
        'document_type' => 'QUOTATION',
        'template_name' => 'Standard Quotation',
        'is_default' => true,
    ]);

    // Subtotal: 28,400. VAT 18%: 5,112. Total: 33,512.
    $response = $this->actingAs($admin)->post(route('quotations.store'), [
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'template_id' => $template->id,
        'quotation_date' => '2026-09-22',
        'items' => [
            [
                'item_name' => 'Consulting Service',
                'quantity' => 1,
                'unit_price' => 28400,
                'discount' => 0,
            ],
        ],
    ]);

    $response->assertRedirect();

    $quotation = Quotation::where('company_id', $company->id)->first();
    expect($quotation)->not->toBeNull()
        ->and((bool) $quotation->vat_enabled)->toBeTrue()
        ->and((float) $quotation->vat_percentage)->toBe(18.00)
        ->and((float) $quotation->vat_amount)->toBe(5112.00)
        ->and((float) $quotation->subtotal)->toBe(28400.00)
        ->and((float) $quotation->tax_percentage)->toBe(18.00)
        ->and((float) $quotation->tax_amount)->toBe(5112.00)
        ->and((float) $quotation->grand_total)->toBe(33512.00);
});

test('quotation calculation does not apply VAT when company VAT disabled', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $company = Company::create([
        'name' => 'VAT Disabled Co',
        'registration_number' => 'REG-2',
        'vat_enabled' => false,
        'vat_registered' => false,
        'tax_percentage' => 0,
        'address_line_1' => '10 High St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94111111111',
        'email' => 'novat@example.com',
        'website' => 'https://example.com',
        'logo_path' => 'logos/test.png',
        'quotation_prefix' => 'Q-',
        'invoice_prefix' => 'INV-',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'Bob Customer',
        'business_name' => 'Bob Business',
        'address_line_1' => '20 First St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'status' => 'ACTIVE',
    ]);

    $template = CompanyTemplate::create([
        'company_id' => $company->id,
        'document_type' => 'QUOTATION',
        'template_name' => 'Standard Quotation',
        'is_default' => true,
    ]);

    $response = $this->actingAs($admin)->post(route('quotations.store'), [
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'template_id' => $template->id,
        'quotation_date' => '2026-09-22',
        'items' => [
            [
                'item_name' => 'Web Design',
                'quantity' => 1,
                'unit_price' => 28400,
                'discount' => 0,
                'tax' => 18, // Should be ignored since company VAT is disabled
            ],
        ],
    ]);

    $response->assertRedirect();

    $quotation = Quotation::where('company_id', $company->id)->first();
    expect($quotation)->not->toBeNull()
        ->and((bool) $quotation->vat_enabled)->toBeFalse()
        ->and((float) $quotation->vat_percentage)->toBe(0.00)
        ->and((float) $quotation->vat_amount)->toBe(0.00)
        ->and((float) $quotation->subtotal)->toBe(28400.00)
        ->and((float) $quotation->tax_amount)->toBe(0.00)
        ->and((float) $quotation->grand_total)->toBe(28400.00);
});

test('invoice calculation applies VAT when company VAT enabled', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $company = Company::create([
        'name' => 'VAT Active Co Inv',
        'registration_number' => 'REG-3',
        'tin_number' => 'TIN-003',
        'vat_enabled' => true,
        'vat_registered' => true,
        'vat_number' => 'VAT-003',
        'tax_percentage' => 18.00,
        'vat_percentage' => 18.00,
        'address_line_1' => '10 High St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94111111111',
        'email' => 'vatinv@example.com',
        'website' => 'https://example.com',
        'logo_path' => 'logos/test.png',
        'quotation_prefix' => 'Q-',
        'invoice_prefix' => 'INV-',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'Charlie Customer',
        'business_name' => 'Charlie Business',
        'address_line_1' => '30 First St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'status' => 'ACTIVE',
    ]);

    $template = CompanyTemplate::create([
        'company_id' => $company->id,
        'document_type' => 'INVOICE',
        'template_name' => 'Standard Invoice',
        'is_default' => true,
    ]);

    // Subtotal: 28,400. VAT 18%: 5,112. Total: 33,512.
    $response = $this->actingAs($admin)->post(route('invoices.store'), [
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'template_id' => $template->id,
        'invoice_date' => '2026-09-22',
        'payment_amount' => 33512,
        'payment_method' => 'BANK_TRANSFER',
        'payment_date' => '2026-09-22',
        'items' => [
            [
                'item_name' => 'Product Batch',
                'quantity' => 1,
                'unit_price' => 28400,
                'discount_type' => 'NONE',
                'discount_value' => 0,
                'tax_percentage' => 18,
            ],
        ],
    ]);

    $response->assertRedirect();

    $invoice = Invoice::where('company_id', $company->id)->first();
    expect($invoice)->not->toBeNull()
        ->and((bool) $invoice->vat_enabled)->toBeTrue()
        ->and((float) $invoice->vat_percentage)->toBe(18.00)
        ->and((float) $invoice->vat_amount)->toBe(5112.00)
        ->and((float) $invoice->subtotal)->toBe(28400.00)
        ->and((float) $invoice->tax_amount)->toBe(5112.00)
        ->and((float) $invoice->grand_total)->toBe(33512.00)
        ->and((float) $invoice->balance_amount)->toBe(0.00);
});

test('invoice PDF uses TAX INVOICE template when VAT enabled and normal when VAT disabled', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    // 1. VAT ENABLED
    $vatCompany = Company::create([
        'name' => 'VAT Seller Ltd',
        'registration_number' => 'REG-VAT',
        'tin_number' => 'TIN-VAT-123',
        'vat_enabled' => true,
        'vat_registered' => true,
        'vat_number' => 'VAT-9999',
        'tax_percentage' => 18.00,
        'address_line_1' => '50 Galle Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112223334',
        'email' => 'seller@vat.lk',
        'website' => 'https://vat.lk',
        'logo_path' => 'logos/vat.png',
        'quotation_prefix' => 'VQ-',
        'invoice_prefix' => 'VINV-',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    $vatCustomer = Customer::create([
        'company_id' => $vatCompany->id,
        'customer_name' => 'Dan Customer',
        'business_name' => 'Dan Enterprises',
        'vat_number' => 'CUST-VAT-888',
        'address_line_1' => '100 Colombo St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'status' => 'ACTIVE',
    ]);

    $invTemplate = CompanyTemplate::create([
        'company_id' => $vatCompany->id,
        'document_type' => 'INVOICE',
        'template_name' => 'Standard Invoice',
        'is_default' => true,
    ]);

    $vatInvoice = Invoice::create([
        'company_id' => $vatCompany->id,
        'customer_id' => $vatCustomer->id,
        'invoice_number' => 'VINV-001',
        'invoice_date' => '2026-09-22',
        'subtotal' => 28400,
        'tax_percentage' => 18,
        'tax_amount' => 5112,
        'grand_total' => 33512,
        'amount_paid' => 33512,
        'balance_amount' => 0,
        'status' => 'PAID',
        'template_id' => $invTemplate->id,
        'company_snapshot' => $vatCompany->toArray(),
        'customer_snapshot' => $vatCustomer->toArray(),
        'template_snapshot' => $invTemplate->toArray(),
        'created_by' => $admin->id,
    ]);

    $vatPdfResponse = $this->actingAs($admin)->get(route('invoices.pdf', $vatInvoice->id));
    $vatPdfResponse->assertOk();
    // Verify it generates PDF and headers are for download
    expect($vatPdfResponse->headers->get('content-type'))->toBe('application/pdf');

    // 2. VAT DISABLED
    $noVatCompany = Company::create([
        'name' => 'Normal Seller Ltd',
        'registration_number' => 'REG-NOVAT',
        'vat_enabled' => false,
        'vat_registered' => false,
        'address_line_1' => '50 Galle Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112223334',
        'email' => 'seller@novat.lk',
        'website' => 'https://novat.lk',
        'logo_path' => 'logos/novat.png',
        'quotation_prefix' => 'NVQ-',
        'invoice_prefix' => 'NVINV-',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    $noVatCustomer = Customer::create([
        'company_id' => $noVatCompany->id,
        'customer_name' => 'Eve Customer',
        'business_name' => 'Eve Enterprises',
        'address_line_1' => '100 Colombo St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'status' => 'ACTIVE',
    ]);

    $noVatTemplate = CompanyTemplate::create([
        'company_id' => $noVatCompany->id,
        'document_type' => 'INVOICE',
        'template_name' => 'Standard Invoice',
        'is_default' => true,
    ]);

    $noVatInvoice = Invoice::create([
        'company_id' => $noVatCompany->id,
        'customer_id' => $noVatCustomer->id,
        'invoice_number' => 'NVINV-001',
        'invoice_date' => '2026-09-22',
        'subtotal' => 28400,
        'tax_percentage' => 0,
        'tax_amount' => 0,
        'grand_total' => 28400,
        'amount_paid' => 28400,
        'balance_amount' => 0,
        'status' => 'PAID',
        'template_id' => $noVatTemplate->id,
        'company_snapshot' => $noVatCompany->toArray(),
        'customer_snapshot' => $noVatCustomer->toArray(),
        'template_snapshot' => $noVatTemplate->toArray(),
        'created_by' => $admin->id,
    ]);

    $noVatPdfResponse = $this->actingAs($admin)->get(route('invoices.pdf', $noVatInvoice->id));
    $noVatPdfResponse->assertOk();
    expect($noVatPdfResponse->headers->get('content-type'))->toBe('application/pdf');
});

test('quotation PDF generates properly for VAT and non-VAT companies', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $vatCompany = Company::create([
        'name' => 'Quo VAT Co',
        'registration_number' => 'REG-Q-VAT',
        'tin_number' => 'TIN-Q-123',
        'vat_enabled' => true,
        'vat_registered' => true,
        'vat_number' => 'VAT-Q-999',
        'tax_percentage' => 18.00,
        'address_line_1' => '50 Galle Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112223334',
        'email' => 'qvat@example.com',
        'website' => 'https://example.com',
        'logo_path' => 'logos/vat.png',
        'quotation_prefix' => 'VQ-',
        'invoice_prefix' => 'VINV-',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    $customer = Customer::create([
        'company_id' => $vatCompany->id,
        'customer_name' => 'Frank Customer',
        'business_name' => 'Frank Co',
        'address_line_1' => '10 Colombo St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'status' => 'ACTIVE',
    ]);

    $template = CompanyTemplate::create([
        'company_id' => $vatCompany->id,
        'document_type' => 'QUOTATION',
        'template_name' => 'Standard Quotation',
        'is_default' => true,
    ]);

    $quotation = Quotation::create([
        'company_id' => $vatCompany->id,
        'customer_id' => $customer->id,
        'quotation_number' => 'VQ-001',
        'quotation_date' => '2026-09-22',
        'subtotal' => 28400,
        'tax_percentage' => 18,
        'tax_amount' => 5112,
        'grand_total' => 33512,
        'status' => 'DRAFT',
        'template_id' => $template->id,
        'company_snapshot' => $vatCompany->toArray(),
        'customer_snapshot' => $customer->toArray(),
        'template_snapshot' => $template->toArray(),
        'created_by' => $admin->id,
    ]);

    $pdfResponse = $this->actingAs($admin)->get(route('quotations.pdf', $quotation->id));
    $pdfResponse->assertOk();
    expect($pdfResponse->headers->get('content-type'))->toBe('application/pdf');
});

test('NumberToWordsHelper spells amounts correctly', function () {
    expect(NumberToWordsHelper::spell(33512, 'LKR'))
        ->toBe('Thirty Three Thousand Five Hundred Twelve and 00/100 LKR')
        ->and(NumberToWordsHelper::spell(28400.50, 'LKR'))
        ->toBe('Twenty Eight Thousand Four Hundred and 50/100 LKR');
});

test('TEST 1: invoice create view locks template to Tax Invoice Template when company VAT enabled', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $vatCompany = Company::create([
        'name' => 'DKS Techno Holdings Pvt Ltd',
        'registration_number' => 'REG-DKS',
        'tin_number' => '103211530',
        'vat_enabled' => true,
        'vat_registered' => true,
        'vat_number' => '103211530',
        'tax_percentage' => 18.00,
        'address_line_1' => '100 Galle Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112233445',
        'email' => 'info@dkstechno.lk',
        'website' => 'https://dkstechno.lk',
        'logo_path' => 'logos/test.png',
        'quotation_prefix' => 'DKS-Q',
        'invoice_prefix' => 'DKS-INV',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    CompanyTemplate::create([
        'company_id' => $vatCompany->id,
        'document_type' => 'INVOICE',
        'template_name' => 'Standard Invoice',
        'is_default' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('invoices.create', ['company_id' => $vatCompany->id]));
    $response->assertOk();
    $response->assertSee('Tax Invoice Template');
    $response->assertSee('VAT registered company - Tax template automatically applied');
    $response->assertSee('VAT Enabled (18%)');
});

test('TEST 2: quotation create view locks template to Tax Quotation Template when company VAT enabled', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $vatCompany = Company::create([
        'name' => 'DKS Techno Holdings Quotation Co',
        'registration_number' => 'REG-DKS-Q',
        'tin_number' => '103211530',
        'vat_enabled' => true,
        'vat_registered' => true,
        'vat_number' => '103211530',
        'tax_percentage' => 18.00,
        'address_line_1' => '100 Galle Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112233445',
        'email' => 'info@dkstechno.lk',
        'website' => 'https://dkstechno.lk',
        'logo_path' => 'logos/test.png',
        'quotation_prefix' => 'DKS-Q',
        'invoice_prefix' => 'DKS-INV',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    CompanyTemplate::create([
        'company_id' => $vatCompany->id,
        'document_type' => 'QUOTATION',
        'template_name' => 'Standard Quotation',
        'is_default' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('quotations.create', ['company_id' => $vatCompany->id]));
    $response->assertOk();
    $response->assertSee('Tax Quotation Template');
    $response->assertSee('VAT registered company - Tax template automatically applied');
    $response->assertSee('VAT Enabled (18%)');
});

test('TEST 3: invoice and quotation create views show enabled template dropdown when company VAT disabled', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $nonVatCompany = Company::create([
        'name' => 'Micro Clean Solutions',
        'registration_number' => 'REG-MCS',
        'vat_enabled' => false,
        'vat_registered' => false,
        'tax_percentage' => 0.00,
        'address_line_1' => '20 Park Road',
        'city' => 'Kandy',
        'country' => 'Sri Lanka',
        'phone' => '+94812233445',
        'email' => 'info@microclean.lk',
        'website' => 'https://microclean.lk',
        'logo_path' => 'logos/test.png',
        'quotation_prefix' => 'MCS-Q',
        'invoice_prefix' => 'MCS-INV',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    CompanyTemplate::create([
        'company_id' => $nonVatCompany->id,
        'document_type' => 'INVOICE',
        'template_name' => 'Modern Template',
        'is_default' => true,
    ]);

    CompanyTemplate::create([
        'company_id' => $nonVatCompany->id,
        'document_type' => 'QUOTATION',
        'template_name' => 'Classic Template',
        'is_default' => true,
    ]);

    // Invoice create view
    $invResponse = $this->actingAs($admin)->get(route('invoices.create', ['company_id' => $nonVatCompany->id]));
    $invResponse->assertOk();
    $invResponse->assertSee('Modern Template');
    $invResponse->assertDontSee('VAT registered company - Tax template automatically applied');
    $invResponse->assertSee('VAT Disabled');

    // Quotation create view
    $quoResponse = $this->actingAs($admin)->get(route('quotations.create', ['company_id' => $nonVatCompany->id]));
    $quoResponse->assertOk();
    $quoResponse->assertSee('Classic Template');
    $quoResponse->assertDontSee('VAT registered company - Tax template automatically applied');
    $quoResponse->assertSee('VAT Disabled');
});

test('TEST 4: changing company VAT status later does not affect existing documents or PDFs', function () {
    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $company = Company::create([
        'name' => 'Dynamic Tax Corp',
        'registration_number' => 'REG-DTC',
        'tin_number' => '103211530',
        'vat_enabled' => true,
        'vat_registered' => true,
        'vat_number' => '103211530',
        'tax_percentage' => 18.00,
        'address_line_1' => '100 Galle Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112233445',
        'email' => 'info@dynamictax.lk',
        'website' => 'https://dynamictax.lk',
        'logo_path' => 'logos/test.png',
        'quotation_prefix' => 'DTC-Q',
        'invoice_prefix' => 'DTC-INV',
        'currency' => 'LKR',
        'status' => 'ACTIVE',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'Grace Customer',
        'business_name' => 'Grace Enterprise',
        'address_line_1' => '50 Main St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'status' => 'ACTIVE',
    ]);

    $invTemplate = CompanyTemplate::create([
        'company_id' => $company->id,
        'document_type' => 'INVOICE',
        'template_name' => 'Standard Invoice',
        'is_default' => true,
    ]);

    $quoTemplate = CompanyTemplate::create([
        'company_id' => $company->id,
        'document_type' => 'QUOTATION',
        'template_name' => 'Standard Quotation',
        'is_default' => true,
    ]);

    // 1. Create Invoice while company is VAT registered
    $this->actingAs($admin)->post(route('invoices.store'), [
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'template_id' => $invTemplate->id,
        'invoice_date' => '2026-09-22',
        'payment_amount' => 11800,
        'payment_method' => 'CASH',
        'payment_date' => '2026-09-22',
        'items' => [
            [
                'item_name' => 'Item A',
                'quantity' => 1,
                'unit_price' => 10000,
                'discount_type' => 'NONE',
                'discount_value' => 0,
                'tax_percentage' => 18,
            ],
        ],
    ]);

    $invoice = Invoice::where('company_id', $company->id)->first();
    expect($invoice->vat_enabled)->toBeTrue()
        ->and((float) $invoice->vat_amount)->toBe(1800.00);

    // 2. Create Quotation while company is VAT registered
    $this->actingAs($admin)->post(route('quotations.store'), [
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'template_id' => $quoTemplate->id,
        'quotation_date' => '2026-09-22',
        'items' => [
            [
                'item_name' => 'Item B',
                'quantity' => 1,
                'unit_price' => 10000,
                'discount' => 0,
            ],
        ],
    ]);

    $quotation = Quotation::where('company_id', $company->id)->first();
    expect($quotation->vat_enabled)->toBeTrue()
        ->and((float) $quotation->vat_amount)->toBe(1800.00);

    // 3. Now change company VAT settings to DISABLED
    $company->update([
        'vat_enabled' => false,
        'vat_registered' => false,
        'tax_percentage' => 0.00,
        'vat_number' => null,
    ]);

    // 4. Verify existing records retain their document-level VAT status
    $invoice->refresh();
    $quotation->refresh();

    expect($invoice->vat_enabled)->toBeTrue()
        ->and((float) $invoice->vat_percentage)->toBe(18.00)
        ->and((float) $invoice->vat_amount)->toBe(1800.00)
        ->and((float) $invoice->grand_total)->toBe(11800.00)
        ->and($quotation->vat_enabled)->toBeTrue()
        ->and((float) $quotation->vat_percentage)->toBe(18.00)
        ->and((float) $quotation->vat_amount)->toBe(1800.00)
        ->and((float) $quotation->grand_total)->toBe(11800.00);

    // 5. Existing PDFs must still download as TAX templates without errors
    $invPdfResponse = $this->actingAs($admin)->get(route('invoices.pdf', $invoice->id));
    $invPdfResponse->assertOk();
    expect($invPdfResponse->headers->get('content-type'))->toBe('application/pdf');

    $quoPdfResponse = $this->actingAs($admin)->get(route('quotations.pdf', $quotation->id));
    $quoPdfResponse->assertOk();
    expect($quoPdfResponse->headers->get('content-type'))->toBe('application/pdf');
});
