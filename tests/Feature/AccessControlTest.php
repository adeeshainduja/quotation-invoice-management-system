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

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

function createTestCompany(array $attributes = []): Company
{
    return Company::create(array_merge([
        'name' => 'Test Company '.uniqid(),
        'registration_number' => 'REG-'.rand(1000, 9999),
        'currency' => 'LKR',
        'address_line_1' => 'Road 1',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112345678',
        'email' => 'test'.uniqid().'@example.com',
        'website' => 'https://example.com',
        'logo_path' => 'logos/test.png',
        'status' => 'ACTIVE',
        'quotation_prefix' => 'QT-',
        'invoice_prefix' => 'INV-',
    ], $attributes));
}

function createTestCustomer(int $companyId, array $attributes = []): Customer
{
    return Customer::create(array_merge([
        'company_id' => $companyId,
        'customer_name' => 'Customer '.uniqid(),
        'business_name' => 'Business '.uniqid(),
        'address_line_1' => 'Road 1',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112345678',
        'email' => 'cust'.uniqid().'@example.com',
        'status' => 'ACTIVE',
    ], $attributes));
}

function createTestTemplate(int $companyId, array $attributes = []): CompanyTemplate
{
    return CompanyTemplate::create(array_merge([
        'company_id' => $companyId,
        'document_type' => 'INVOICE',
        'template_name' => 'Template '.uniqid(),
        'is_default' => true,
    ], $attributes));
}

function createTestInvoice(Company $company, Customer $customer, CompanyTemplate $template, array $attributes = []): Invoice
{
    return Invoice::create(array_merge([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'template_id' => $template->id,
        'created_by' => 1,
        'invoice_number' => 'INV-'.uniqid(),
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(7)->toDateString(),
        'subtotal' => 100,
        'grand_total' => 100,
        'amount_paid' => 100,
        'balance_amount' => 0,
        'status' => 'PAID',
        'company_snapshot' => [
            'name' => $company->name,
            'currency' => $company->currency ?? 'LKR',
            'address_line_1' => $company->address_line_1,
            'city' => $company->city,
            'country' => $company->country,
            'phone' => $company->phone,
            'email' => $company->email,
        ],
        'customer_snapshot' => [
            'name' => $customer->customer_name,
            'address_line_1' => $customer->address_line_1,
            'city' => $customer->city,
            'country' => $customer->country,
        ],
        'template_snapshot' => [
            'name' => $template->template_name,
        ],
    ], $attributes));
}

test('1. admin only company creation and management', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);
    $user = User::factory()->create(['role' => 'USER', 'status' => 'ACTIVE']);
    $user->permissions()->attach(Permission::pluck('id'));

    // Admin can access create
    $this->actingAs($admin)->get(route('companies.create'))->assertOk();

    // Normal user gets 403 on create, store, edit, update, toggle-status
    $this->actingAs($user)->get(route('companies.create'))->assertForbidden();
    $this->actingAs($user)->post(route('companies.store'), [
        'name' => 'Forbidden Co',
        'registration_number' => 'REG-999',
        'currency' => 'LKR',
        'address_line_1' => 'Road 1',
        'city' => 'City',
        'country' => 'Country',
        'phone' => '+94112345678',
        'email' => 'forbidden@example.com',
        'website' => 'https://example.com',
        'status' => 'ACTIVE',
    ])->assertForbidden();

    $company = createTestCompany(['name' => 'Test Company', 'status' => 'ACTIVE']);

    $this->actingAs($user)->get(route('companies.edit', $company->id))->assertForbidden();
    $this->actingAs($user)->put(route('companies.update', $company->id), ['name' => 'Hacked'])->assertForbidden();
    $this->actingAs($user)->post(route('companies.toggle-status', $company->id))->assertForbidden();

    // Normal user does not see Add Company button on companies index
    $indexResponse = $this->actingAs($user)->get(route('companies.index'));
    $indexResponse->assertOk();
    $indexResponse->assertDontSee(route('companies.create'));
});

test('2. admin assigns company access to users and normal user can only see assigned companies', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);
    $user = User::factory()->create(['role' => 'USER', 'status' => 'ACTIVE']);
    $user->permissions()->attach(Permission::pluck('id'));

    $companyA = createTestCompany(['name' => 'Alpha Corp', 'status' => 'ACTIVE']);
    $companyB = createTestCompany(['name' => 'Beta Corp', 'status' => 'ACTIVE']);

    // Admin assigns Company A to User
    $user->companies()->attach($companyA->id);

    // User only sees Alpha Corp on companies index, not Beta Corp
    $response = $this->actingAs($user)->get(route('companies.index'));
    $response->assertOk();
    $response->assertSee('Alpha Corp');
    $response->assertDontSee('Beta Corp');
});

test('3. user cannot select company or pass unauthorized company_id', function () {
    $user = User::factory()->create(['role' => 'USER', 'status' => 'ACTIVE']);
    $user->permissions()->attach(Permission::pluck('id'));

    $companyA = createTestCompany(['name' => 'Allowed Corp', 'status' => 'ACTIVE']);
    $companyB = createTestCompany(['name' => 'Forbidden Corp', 'status' => 'ACTIVE']);

    $user->companies()->attach($companyA->id);

    // Trying to access dashboard with companyB gives 403
    $this->actingAs($user)->get(route('dashboard', ['company_id' => $companyB->id]))->assertForbidden();

    // Trying to access invoices.index with companyB gives 403
    $this->actingAs($user)->get(route('invoices.index', ['company_id' => $companyB->id]))->assertForbidden();

    // Trying to access quotations.index with companyB gives 403
    $this->actingAs($user)->get(route('quotations.index', ['company_id' => $companyB->id]))->assertForbidden();

    // Trying to access customer creation with companyB gives 403
    $this->actingAs($user)->get(route('customers.create', ['company_id' => $companyB->id]))->assertForbidden();
});

test('4 & 5. admin assigns template and user cannot select unauthorized template', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);
    $user = User::factory()->create(['role' => 'USER', 'status' => 'ACTIVE']);
    $user->permissions()->attach(Permission::pluck('id'));

    $company = createTestCompany(['name' => 'Acme Corp', 'status' => 'ACTIVE', 'vat_enabled' => 0]);
    $user->companies()->attach($company->id);

    $customer = createTestCustomer($company->id, ['status' => 'ACTIVE']);

    $allowedTemplate = createTestTemplate($company->id, [
        'document_type' => 'INVOICE',
        'template_name' => 'Assigned Modern Invoice',
    ]);

    $disallowedTemplate = createTestTemplate($company->id, [
        'document_type' => 'INVOICE',
        'template_name' => 'Disallowed Classic Invoice',
    ]);

    // Admin assigns allowed template to user
    $user->templates()->attach($allowedTemplate->id);

    // Normal user accessing create page sees assigned template
    $createView = $this->actingAs($user)->get(route('invoices.create', ['company_id' => $company->id]));
    $createView->assertOk();
    $createView->assertSee('Assigned Modern Invoice (Assigned)');

    // Attempting to post an invoice with disallowed template gives 403
    $storeAttempt = $this->actingAs($user)->post(route('invoices.store'), [
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'template_id' => $disallowedTemplate->id,
        'invoice_date' => now()->toDateString(),
        'payment_method' => 'CASH',
        'payment_date' => now()->toDateString(),
        'payment_amount' => 100,
        'items' => [
            [
                'item_name' => 'Item 1',
                'quantity' => 1,
                'unit_price' => 100,
                'discount_type' => 'NONE',
                'discount_value' => 0,
                'tax_percentage' => 0,
            ],
        ],
    ]);
    $storeAttempt->assertForbidden();

    // Storing with allowed template succeeds
    $validStore = $this->actingAs($user)->post(route('invoices.store'), [
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'template_id' => $allowedTemplate->id,
        'invoice_date' => now()->toDateString(),
        'payment_method' => 'CASH',
        'payment_date' => now()->toDateString(),
        'payment_amount' => 100,
        'items' => [
            [
                'item_name' => 'Item 1',
                'quantity' => 1,
                'unit_price' => 100,
                'discount_type' => 'NONE',
                'discount_value' => 0,
                'tax_percentage' => 0,
            ],
        ],
    ]);
    $validStore->assertRedirect(route('invoices.index', ['company_id' => $company->id]));
});

test('6. common topbar is rendered across pages with correct admin dropdown vs user read-only', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);
    $user = User::factory()->create(['role' => 'USER', 'status' => 'ACTIVE']);
    $user->permissions()->attach(Permission::pluck('id'));

    $company = createTestCompany(['name' => 'TopBar Company', 'status' => 'ACTIVE']);
    $user->companies()->attach($company->id);

    // Admin sees company-select form
    $adminDashboard = $this->actingAs($admin)->get(route('dashboard'));
    $adminDashboard->assertOk();
    $adminDashboard->assertSee('topbarCompanyForm');

    // Normal user sees read-only Company: TopBar Company
    $userDashboard = $this->actingAs($user)->get(route('dashboard'));
    $userDashboard->assertOk();
    $userDashboard->assertSee('company-readonly');
    $userDashboard->assertSee('TopBar Company');
    $userDashboard->assertDontSee('topbarCompanyForm');
});

test('7 & 8. replace delete with deactivate on users and companies', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);
    $targetUser = User::factory()->create(['role' => 'USER', 'status' => 'ACTIVE']);
    $company = createTestCompany(['name' => 'Toggle Co', 'status' => 'ACTIVE']);

    // Admin view of users page has Deactivate button, no delete button
    $usersIndex = $this->actingAs($admin)->get(route('users.index'));
    $usersIndex->assertOk();
    $usersIndex->assertSee('Deactivate');
    $usersIndex->assertDontSee('users.destroy');

    // Toggle user status to INACTIVE
    $toggleUser = $this->actingAs($admin)->from(route('users.index'))->post(route('users.toggle-status', $targetUser->id));
    $toggleUser->assertRedirect(route('users.index'));
    expect($targetUser->fresh()->status)->toBe('INACTIVE');

    // Users view now shows Activate
    $usersIndexAfter = $this->actingAs($admin)->get(route('users.index'));
    $usersIndexAfter->assertSee('Activate');

    // Toggle company status to INACTIVE
    $toggleCompany = $this->actingAs($admin)->from(route('companies.index'))->post(route('companies.toggle-status', $company->id));
    $toggleCompany->assertRedirect(route('companies.index'));
    expect($company->fresh()->status)->toBe('INACTIVE');

    // Companies view now shows Activate
    $companiesIndex = $this->actingAs($admin)->get(route('companies.index'));
    $companiesIndex->assertSee('Activate');
});

test('9. user deactivation terminates session and blocks login with deactivated message', function () {
    $deactivatedUser = User::factory()->create([
        'email' => 'deactivated@example.com',
        'password' => bcrypt('password123'),
        'status' => 'INACTIVE',
    ]);

    // Login fails with exact deactivated message
    $response = $this->post(route('login.submit'), [
        'email' => 'deactivated@example.com',
        'password' => 'password123',
    ]);
    $response->assertSessionHasErrors(['email' => 'Your account is deactivated. Please contact admin.']);

    // Active session for deactivated user gets 403
    $sessionResponse = $this->actingAs($deactivatedUser)->get(route('dashboard'));
    $sessionResponse->assertForbidden();
});

test('10. company deactivation blocks new transactions while keeping historical data safe', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);
    $inactiveCompany = createTestCompany(['name' => 'Inactive Corp', 'status' => 'INACTIVE']);
    $customer = createTestCustomer($inactiveCompany->id, ['status' => 'ACTIVE']);
    $template = createTestTemplate($inactiveCompany->id, ['document_type' => 'INVOICE']);

    // Existing invoice remains visible
    $invoice = createTestInvoice($inactiveCompany, $customer, $template, [
        'invoice_number' => 'INV-HISTORICAL-001',
        'created_by' => $admin->id,
    ]);

    $pdfResponse = $this->actingAs($admin)->get(route('invoices.pdf', $invoice->id));
    $pdfResponse->assertOk();

    // Creating a new invoice for the inactive company is blocked with 403
    $createInvoice = $this->actingAs($admin)->post(route('invoices.store'), [
        'company_id' => $inactiveCompany->id,
        'customer_id' => $customer->id,
        'template_id' => $template->id,
        'invoice_date' => now()->toDateString(),
        'payment_method' => 'CASH',
        'payment_date' => now()->toDateString(),
        'payment_amount' => 50,
        'items' => [
            [
                'item_name' => 'Item 1',
                'quantity' => 1,
                'unit_price' => 50,
                'discount_type' => 'NONE',
                'discount_value' => 0,
                'tax_percentage' => 0,
            ],
        ],
    ]);
    $createInvoice->assertForbidden();

    // Creating a new customer for inactive company is blocked with 403
    $createCustomer = $this->actingAs($admin)->post(route('customers.store'), [
        'company_id' => $inactiveCompany->id,
        'customer_name' => 'New Customer',
        'business_name' => 'New Biz',
        'address_line_1' => 'Road',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'phone' => '+94112345678',
        'email' => 'customer@example.com',
        'status' => 'ACTIVE',
    ]);
    $createCustomer->assertForbidden();
});

test('11. admin user assignment UI renders scrollable containers and saves company/template selections', function () {
    $admin = User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);
    $company1 = createTestCompany(['name' => 'Alpha Solutions']);
    $company2 = createTestCompany(['name' => 'Beta Systems']);
    $invTemplate = createTestTemplate($company1->id, ['document_type' => 'INVOICE', 'template_name' => 'Standard Invoice']);
    $quoTemplate = createTestTemplate($company1->id, ['document_type' => 'QUOTATION', 'template_name' => 'Standard Quotation']);

    // Admin visits Create User page
    $createResponse = $this->actingAs($admin)->get(route('users.create'));
    $createResponse->assertOk();
    $createResponse->assertSee('scroll-selection-box');
    $createResponse->assertSee('max-height: 250px; overflow-y: auto;', false);
    $createResponse->assertSee('Alpha Solutions');
    $createResponse->assertSee('Beta Systems');
    $createResponse->assertSee('Standard Invoice');
    $createResponse->assertSee('Standard Quotation');

    // Admin stores a new user with selected companies and templates
    $storeResponse = $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Test Assigned User',
        'email' => 'assigned@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'status' => 'ACTIVE',
        'companies' => [$company1->id],
        'templates' => [$invTemplate->id, $quoTemplate->id],
        'permissions' => [Permission::first()->id],
    ]);
    $storeResponse->assertRedirect(route('users.index'));

    $createdUser = User::where('email', 'assigned@example.com')->first();
    expect($createdUser)->not->toBeNull()
        ->and($createdUser->companies->pluck('id')->all())->toBe([$company1->id])
        ->and($createdUser->templates->pluck('id')->sort()->values()->all())->toBe([$invTemplate->id, $quoTemplate->id]);

    // Admin visits Edit User page
    $editResponse = $this->actingAs($admin)->get(route('users.edit', $createdUser->id));
    $editResponse->assertOk();
    $editResponse->assertSee('scroll-selection-box');
    $editResponse->assertSee('max-height: 250px; overflow-y: auto;', false);
    $editResponse->assertSee('value="'.$company1->id.'"', false);
    $editResponse->assertSee('value="'.$company2->id.'"', false);

    // Admin updates user assignments
    $updateResponse = $this->actingAs($admin)->put(route('users.update', $createdUser->id), [
        'name' => 'Test Assigned User Updated',
        'email' => 'assigned@example.com',
        'status' => 'ACTIVE',
        'companies' => [$company2->id],
        'templates' => [$invTemplate->id],
        'permissions' => [Permission::first()->id],
    ]);
    $updateResponse->assertRedirect(route('users.index'));

    $createdUser->refresh();
    expect($createdUser->companies->pluck('id')->all())->toBe([$company2->id])
        ->and($createdUser->templates->pluck('id')->all())->toBe([$invTemplate->id]);
});
