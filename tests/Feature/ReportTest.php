<?php

use App\Models\Company;
use App\Models\CompanyTemplate;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('guests are redirected to login when visiting reports', function () {
    $response = $this->get(route('reports.index'));

    $response->assertRedirect(route('login'));
});

test('user without reports.view permission receives forbidden response', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($user)->get(route('reports.index'));

    $response->assertStatus(403);
});

test('user with reports.view permission can access reports page and sees sidebar link', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $permission = Permission::where('key', 'reports.view')->first();
    $user->permissions()->attach($permission);

    $response = $this->actingAs($user)->get(route('reports.index'));

    $response->assertOk();
    $response->assertSee('Reports');
    $response->assertSee('data-lucide="bar-chart"', false);
    $response->assertSee('Total Invoices');
    $response->assertSee('Total Invoice Amount');
    $response->assertSee('Payments Received');
    $response->assertSee('Outstanding Payments');
    $response->assertSee('Total Quotations');
    $response->assertSee('Total Customers');
});

test('sidebar does not show reports link if user lacks permission', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $dashboardPermission = Permission::where('key', 'dashboard.view')->first();
    $user->permissions()->attach($dashboardPermission);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertDontSee('data-lucide="bar-chart"', false);
});

test('admin can access reports page automatically and sees invoice reports links', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($admin)->get(route('reports.index'));

    $response->assertOk();
    $response->assertSee('Reports');
    $response->assertSee('data-lucide="bar-chart"', false);
    $response->assertSee('Total Invoices');
    $response->assertSee('Total Invoice Amount');
    $response->assertSee('Payments Received');
    $response->assertSee('Outstanding Payments');
    $response->assertSee('Total Quotations');
    $response->assertSee('Total Customers');
    $response->assertSee('Sales Reports');
    $response->assertSee('Invoice Reports');
    $response->assertSee('Payment Reports');
    $response->assertSee('Customer Reports');
    $response->assertSee('Quotation Reports');
    $response->assertSee('Invoice Aging Report');
    $response->assertSee('Paid vs Unpaid Breakdown');
    $response->assertSee(route('reports.invoices'));
});

test('guests are redirected to login when visiting invoice reports', function () {
    $response = $this->get(route('reports.invoices'));

    $response->assertRedirect(route('login'));
});

test('user without reports.invoice.view permission receives 403 on invoice reports', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($user)->get(route('reports.invoices'));

    $response->assertStatus(403);
});

test('user with reports.invoice.view can view invoice reports with cards and export buttons', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $permission = Permission::where('key', 'reports.invoice.view')->first();
    $user->permissions()->attach($permission);

    $response = $this->actingAs($user)->get(route('reports.invoices'));

    $response->assertOk();
    $response->assertSee('Invoice Reports');
    $response->assertSee('Detailed invoice performance and payment tracking');
    $response->assertSee('Export PDF');
    $response->assertSee('Export Excel');
    $response->assertSee('Print');
    $response->assertSee('Total Invoices');
    $response->assertSee('Total Invoice Amount');
    $response->assertSee('Paid Amount');
    $response->assertSee('Outstanding Amount');
    $response->assertSee('Paid Invoices');
    $response->assertSee('Pending Invoices');
    $response->assertSee('Overdue Invoices');
});

test('admin can access invoice reports page automatically and filter records', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $company = Company::create([
        'name' => 'Acme Corp',
        'registration_number' => 'REG-12345',
        'address_line_1' => '123 Main St',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112345678',
        'email' => 'acme@example.com',
        'website' => 'https://acme.example.com',
        'logo_path' => 'logos/acme.png',
        'quotation_prefix' => 'QT-',
        'invoice_prefix' => 'INV-',
        'status' => 'ACTIVE',
        'currency' => 'USD',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'John Doe',
        'business_name' => 'Doe Enterprises',
        'email' => 'john@example.com',
        'address_line_1' => '456 Commercial Rd',
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

    Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-001',
        'invoice_date' => '2026-09-01',
        'due_date' => '2026-09-15',
        'subtotal' => 1000,
        'grand_total' => 1000,
        'amount_paid' => 1000,
        'balance_amount' => 0,
        'status' => 'PAID',
        'template_id' => $template->id,
        'company_snapshot' => ['name' => 'Acme Corp'],
        'customer_snapshot' => ['name' => 'John Doe'],
        'template_snapshot' => ['name' => 'Standard Invoice'],
        'created_by' => $admin->id,
    ]);

    Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-002',
        'invoice_date' => '2026-09-05',
        'due_date' => '2026-09-10',
        'subtotal' => 2000,
        'grand_total' => 2000,
        'amount_paid' => 500,
        'balance_amount' => 1500,
        'status' => 'PARTIALLY_PAID',
        'template_id' => $template->id,
        'company_snapshot' => ['name' => 'Acme Corp'],
        'customer_snapshot' => ['name' => 'John Doe'],
        'template_snapshot' => ['name' => 'Standard Invoice'],
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('reports.invoices', [
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'status' => 'PARTIALLY_PAID',
    ]));

    $response->assertOk();
    $response->assertSee('INV-002');
    $response->assertDontSee('INV-001');
    $response->assertSee('Doe Enterprises');
    $response->assertSee('Acme Corp');
});

test('guests are redirected to login when visiting payment reports', function () {
    $response = $this->get(route('reports.payments'));

    $response->assertRedirect(route('login'));
});

test('user without reports.payment.view permission receives 403 on payment reports', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($user)->get(route('reports.payments'));

    $response->assertStatus(403);
});

test('user with reports.payment.view can view payment reports with cards, filters, and export buttons', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $permission = Permission::where('key', 'reports.payment.view')->first();
    $user->permissions()->attach($permission);

    $response = $this->actingAs($user)->get(route('reports.payments'));

    $response->assertOk();
    $response->assertSee('Payment Reports');
    $response->assertSee('Payment collection history and transaction analysis');
    $response->assertSee('Export PDF');
    $response->assertSee('Export Excel');
    $response->assertSee('Print');
    $response->assertSee('Total Received');
    $response->assertSee('Payment Count');
    $response->assertSee('This Month Collection');
    $response->assertSee('Outstanding Balance');
    $response->assertSee('Cash Collection');
    $response->assertSee('Bank Collection');
    $response->assertSee('Card Collection');
});

test('reports dashboard links to payment reports page', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($admin)->get(route('reports.index'));

    $response->assertOk();
    $response->assertSee('Collection History');
    $response->assertSee('Payment Methods Summary');
    $response->assertSee(route('reports.payments'));
});

test('admin can access payment reports page and filter payments by method', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $company = Company::create([
        'name' => 'Apex Solutions',
        'registration_number' => 'REG-999',
        'address_line_1' => '789 High Street',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94119999999',
        'email' => 'apex@example.com',
        'website' => 'https://apex.example.com',
        'logo_path' => 'logos/apex.png',
        'quotation_prefix' => 'QT-',
        'invoice_prefix' => 'INV-',
        'status' => 'ACTIVE',
        'currency' => 'LKR',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'Alice Smith',
        'business_name' => 'Smith Traders',
        'email' => 'alice@example.com',
        'address_line_1' => '101 Trade Plaza',
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

    $invoice = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-300',
        'invoice_date' => '2026-09-01',
        'due_date' => '2026-09-20',
        'subtotal' => 5000,
        'grand_total' => 5000,
        'amount_paid' => 3000,
        'balance_amount' => 2000,
        'status' => 'PARTIALLY_PAID',
        'template_id' => $template->id,
        'company_snapshot' => ['name' => 'Apex Solutions'],
        'customer_snapshot' => ['name' => 'Alice Smith'],
        'template_snapshot' => ['name' => 'Standard Invoice'],
        'created_by' => $admin->id,
    ]);

    Payment::create([
        'invoice_id' => $invoice->id,
        'payment_date' => '2026-09-02',
        'amount' => 2000,
        'payment_method' => 'BANK_TRANSFER',
        'reference' => 'TXN-BANK-01',
        'created_by' => $admin->id,
    ]);

    Payment::create([
        'invoice_id' => $invoice->id,
        'payment_date' => '2026-09-05',
        'amount' => 1000,
        'payment_method' => 'CASH',
        'reference' => 'TXN-CASH-01',
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('reports.payments', [
        'payment_method' => 'BANK_TRANSFER',
    ]));

    $response->assertOk();
    $response->assertSee('TXN-BANK-01');
    $response->assertDontSee('TXN-CASH-01');
    $response->assertSee('INV-300');
    $response->assertSee('Smith Traders');
    $response->assertSee('Apex Solutions');
});

test('guests are redirected to login when visiting quotation reports', function () {
    $response = $this->get(route('reports.quotations'));

    $response->assertRedirect(route('login'));
});

test('user without reports.quotation.view permission receives 403 on quotation reports', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($user)->get(route('reports.quotations'));

    $response->assertStatus(403);
});

test('user with reports.quotation.view can view quotation reports with cards and export buttons', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $permission = Permission::where('key', 'reports.quotation.view')->first();
    $user->permissions()->attach($permission);

    $response = $this->actingAs($user)->get(route('reports.quotations'));

    $response->assertOk();
    $response->assertSee('Quotation Reports');
    $response->assertSee('Quotation performance, conversion tracking and sales pipeline');
    $response->assertSee('Export PDF');
    $response->assertSee('Export Excel');
    $response->assertSee('Print');
    $response->assertSee('Total Quotations');
    $response->assertSee('Total Quotation Value');
    $response->assertSee('Accepted Quotations');
    $response->assertSee('Converted to Invoice');
    $response->assertSee('Pending Quotations');
    $response->assertSee('Rejected Quotations');
    $response->assertSee('Expired Quotations');
    $response->assertSee('Quotation Status Pipeline');
    $response->assertSee('Conversion Rate');
});

test('admin can access quotation reports automatically', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($admin)->get(route('reports.quotations'));

    $response->assertOk();
    $response->assertSee('Quotation Reports');
});

test('reports dashboard links to quotation reports page', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($admin)->get(route('reports.index'));

    $response->assertOk();
    $response->assertSee('Quotation Conversion Rate');
    $response->assertSee('Quotation Status Pipeline');
    $response->assertSee(route('reports.quotations'));
});

test('admin can filter quotation reports by status', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $company = Company::create([
        'name' => 'Beta Corp',
        'registration_number' => 'REG-BETA',
        'address_line_1' => '5 Innovation Drive',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94117777777',
        'email' => 'beta@example.com',
        'website' => 'https://beta.example.com',
        'logo_path' => 'logos/beta.png',
        'quotation_prefix' => 'QT-',
        'invoice_prefix' => 'INV-',
        'status' => 'ACTIVE',
        'currency' => 'LKR',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'Bob Johnson',
        'business_name' => 'Johnson Ltd',
        'email' => 'bob@example.com',
        'address_line_1' => '22 Commerce St',
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

    \App\Models\Quotation::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'quotation_number' => 'QT-001',
        'quotation_date' => '2026-09-01',
        'expiry_date' => '2026-09-30',
        'subtotal' => 5000,
        'grand_total' => 5500,
        'status' => 'ACCEPTED',
        'template_id' => $template->id,
        'company_snapshot' => ['name' => 'Beta Corp'],
        'customer_snapshot' => ['name' => 'Bob Johnson'],
        'template_snapshot' => ['name' => 'Standard Quotation'],
        'created_by' => $admin->id,
    ]);

    \App\Models\Quotation::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'quotation_number' => 'QT-002',
        'quotation_date' => '2026-09-05',
        'expiry_date' => '2026-09-25',
        'subtotal' => 3000,
        'grand_total' => 3300,
        'status' => 'REJECTED',
        'template_id' => $template->id,
        'company_snapshot' => ['name' => 'Beta Corp'],
        'customer_snapshot' => ['name' => 'Bob Johnson'],
        'template_snapshot' => ['name' => 'Standard Quotation'],
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('reports.quotations', [
        'status' => 'ACCEPTED',
    ]));

    $response->assertOk();
    $response->assertSee('QT-001');
    $response->assertDontSee('QT-002');
    $response->assertSee('Johnson Ltd');
    $response->assertSee('Beta Corp');
});

test('guests are redirected to login when visiting customer reports', function () {
    $response = $this->get(route('reports.customers'));

    $response->assertRedirect(route('login'));
});

test('user without reports.customer.view permission receives 403 on customer reports', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($user)->get(route('reports.customers'));

    $response->assertStatus(403);
});

test('user with reports.customer.view can view customer reports with cards and export buttons', function () {
    $this->seed(PermissionSeeder::class);

    $user = User::factory()->create([
        'role' => 'USER',
        'status' => 'ACTIVE',
    ]);

    $permission = Permission::where('key', 'reports.customer.view')->first();
    $user->permissions()->attach($permission);

    $response = $this->actingAs($user)->get(route('reports.customers'));

    $response->assertOk();
    $response->assertSee('Customer Reports');
    $response->assertSee('Customer performance, revenue analysis and outstanding balances');
    $response->assertSee('Export PDF');
    $response->assertSee('Export Excel');
    $response->assertSee('Print');
    $response->assertSee('Total Customers');
    $response->assertSee('Active Customers');
    $response->assertSee('Customer Revenue');
    $response->assertSee('Payments Received');
    $response->assertSee('Outstanding Balance');
    $response->assertSee('Total Quotations');
    $response->assertSee('Avg. Customer Value');
    $response->assertSee('Top Customers by Revenue');
    $response->assertSee('Outstanding Customers');
});

test('admin can access customer reports automatically', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($admin)->get(route('reports.customers'));

    $response->assertOk();
    $response->assertSee('Customer Reports');
});

test('reports dashboard links to customer reports page', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $response = $this->actingAs($admin)->get(route('reports.index'));

    $response->assertOk();
    $response->assertSee('Top Customers by Revenue');
    $response->assertSee('Client Outstanding Balances');
    $response->assertSee(route('reports.customers'));
});

test('admin can filter customer reports by company and sees customer table data', function () {
    $this->seed(PermissionSeeder::class);

    $admin = User::factory()->create([
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $company = Company::create([
        'name' => 'Delta Ltd',
        'registration_number' => 'REG-DELTA',
        'address_line_1' => '10 Delta Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94116666666',
        'email' => 'delta@example.com',
        'website' => 'https://delta.example.com',
        'logo_path' => 'logos/delta.png',
        'quotation_prefix' => 'QT-',
        'invoice_prefix' => 'INV-',
        'status' => 'ACTIVE',
        'currency' => 'LKR',
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'Carol White',
        'business_name' => 'White Holdings',
        'email' => 'carol@example.com',
        'address_line_1' => '7 Business Park',
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

    Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-D01',
        'invoice_date' => '2026-09-10',
        'due_date' => '2026-09-25',
        'subtotal' => 8000,
        'grand_total' => 8000,
        'amount_paid' => 5000,
        'balance_amount' => 3000,
        'status' => 'PARTIALLY_PAID',
        'template_id' => $template->id,
        'company_snapshot' => ['name' => 'Delta Ltd'],
        'customer_snapshot' => ['name' => 'Carol White'],
        'template_snapshot' => ['name' => 'Standard Invoice'],
        'created_by' => $admin->id,
    ]);

    $response = $this->actingAs($admin)->get(route('reports.customers', [
        'company_id' => $company->id,
    ]));

    $response->assertOk();
    $response->assertSee('White Holdings');
    $response->assertSee('Delta Ltd');
    $response->assertSee('LKR 8,000.00');
});
