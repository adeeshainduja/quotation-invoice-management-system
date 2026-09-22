<?php

use App\Models\Company;
use App\Models\CompanyTemplate;
use App\Models\Customer;
use App\Models\Invoice;
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
