<?php

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

test('admin can access reports page automatically', function () {
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
});
