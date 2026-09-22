<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Permission;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionSeeder::class);
});

function setupCustomerTestEnvironment(): array
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
    ]);

    $customer = Customer::create([
        'company_id' => $company->id,
        'customer_name' => 'Alice Johnson',
        'business_name' => 'Johnson Enterprises',
        'registration_number' => 'REG-789',
        'vat_number' => 'VAT-12345',
        'email' => 'alice'.uniqid().'@example.com',
        'phone' => '+94771122334',
        'address_line_1' => '789 Commercial Rd',
        'address_line_2' => 'Suite 4',
        'city' => 'Kandy',
        'country' => 'Sri Lanka',
        'notes' => 'VIP Customer',
        'status' => 'ACTIVE',
    ]);

    return compact('admin', 'company', 'customer');
}

test('customer list displays view and edit action buttons linking to correct routes', function () {
    $env = setupCustomerTestEnvironment();

    $response = $this->actingAs($env['admin'])
        ->get(route('customers.index', ['company_id' => $env['company']->id]));

    $response->assertStatus(200);
    $response->assertSee(route('customers.show', $env['customer']->id));
    $response->assertSee(route('customers.edit', $env['customer']->id));
});

test('view button opens customer details', function () {
    $env = setupCustomerTestEnvironment();

    $response = $this->actingAs($env['admin'])
        ->get(route('customers.show', $env['customer']->id));

    $response->assertStatus(200);
    $response->assertSee('Alice Johnson');
    $response->assertSee('Johnson Enterprises');
    $response->assertSee('Kandy');
});

test('edit button opens edit page with prefilled data', function () {
    $env = setupCustomerTestEnvironment();

    $response = $this->actingAs($env['admin'])
        ->get(route('customers.edit', $env['customer']->id));

    $response->assertStatus(200);
    $response->assertSee('Edit Customer');
    $response->assertSee('Alice Johnson');
    $response->assertSee('Johnson Enterprises');
    $response->assertSee('REG-789');
    $response->assertSee('VAT-12345');
    $response->assertSee('Kandy');
});

test('update customer works and logs activity', function () {
    $env = setupCustomerTestEnvironment();

    $updateData = [
        'company_id' => $env['company']->id,
        'customer_name' => 'Alice J. Smith',
        'business_name' => 'Johnson & Smith Ltd',
        'registration_number' => 'REG-999',
        'vat_number' => 'VAT-99999',
        'email' => 'alice.smith@example.com',
        'phone' => '+94779998877',
        'address_line_1' => '999 New Road',
        'address_line_2' => 'Floor 2',
        'city' => 'Colombo',
        'country' => 'Sri Lanka',
        'notes' => 'Updated VIP Customer Notes',
        'status' => 'ACTIVE',
    ];

    $response = $this->actingAs($env['admin'])
        ->put(route('customers.update', $env['customer']->id), $updateData);

    $response->assertRedirect(route('customers.show', [
        'id' => $env['customer']->id,
        'company_id' => $env['company']->id,
    ]));
    $response->assertSessionHas('success');

    $updatedCustomer = Customer::find($env['customer']->id);
    expect($updatedCustomer->customer_name)->toBe('Alice J. Smith');
    expect($updatedCustomer->business_name)->toBe('Johnson & Smith Ltd');
    expect($updatedCustomer->city)->toBe('Colombo');
    expect($updatedCustomer->registration_number)->toBe('REG-999');

    $log = DB::table('activity_logs')
        ->where('entity_type', 'Customer')
        ->where('entity_id', $env['customer']->id)
        ->where('action', 'UPDATE')
        ->first();

    expect($log)->not->toBeNull();
});

test('user with customers.update permission can access edit and update', function () {
    $env = setupCustomerTestEnvironment();

    $user = User::factory()->create(['role' => 'USER', 'status' => 'ACTIVE']);
    $user->companies()->attach($env['company']->id);

    $perm = Permission::where('key', 'customers.edit')->first();
    if ($perm) {
        $user->permissions()->attach($perm->id);
    }

    $response = $this->actingAs($user)
        ->get(route('customers.edit', $env['customer']->id));
    $response->assertStatus(200);

    $updateData = [
        'company_id' => $env['company']->id,
        'customer_name' => 'Alice Permitted',
        'business_name' => 'Permitted Business',
        'address_line_1' => '123 Line',
        'city' => 'Kandy',
        'country' => 'Sri Lanka',
        'status' => 'ACTIVE',
    ];

    $putResponse = $this->actingAs($user)
        ->put(route('customers.update', $env['customer']->id), $updateData);
    $putResponse->assertRedirect();
});

test('user without customers.update permission is forbidden from edit and update', function () {
    $env = setupCustomerTestEnvironment();

    $user = User::factory()->create(['role' => 'USER', 'status' => 'ACTIVE']);
    $user->companies()->attach($env['company']->id);

    $response = $this->actingAs($user)
        ->get(route('customers.edit', $env['customer']->id));
    $response->assertStatus(403);

    $updateData = [
        'company_id' => $env['company']->id,
        'customer_name' => 'Alice Forbidden',
        'business_name' => 'Forbidden Business',
        'address_line_1' => '123 Line',
        'city' => 'Kandy',
        'country' => 'Sri Lanka',
        'status' => 'ACTIVE',
    ];

    $putResponse = $this->actingAs($user)
        ->put(route('customers.update', $env['customer']->id), $updateData);
    $putResponse->assertStatus(403);
});
