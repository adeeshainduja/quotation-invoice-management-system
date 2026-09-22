<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // Dashboard
            ['key' => 'dashboard.view', 'name' => 'View Dashboard', 'module' => 'Dashboard'],

            // Companies
            ['key' => 'companies.view', 'name' => 'View Companies', 'module' => 'Companies'],
            ['key' => 'companies.create', 'name' => 'Create Companies', 'module' => 'Companies'],
            ['key' => 'companies.edit', 'name' => 'Edit Companies', 'module' => 'Companies'],

            // Customers
            ['key' => 'customers.view', 'name' => 'View Customers', 'module' => 'Customers'],
            ['key' => 'customers.create', 'name' => 'Create Customers', 'module' => 'Customers'],
            ['key' => 'customers.edit', 'name' => 'Edit Customers', 'module' => 'Customers'],

            // Quotations
            ['key' => 'quotations.view', 'name' => 'View Quotations', 'module' => 'Quotations'],
            ['key' => 'quotations.create', 'name' => 'Create Quotations', 'module' => 'Quotations'],
            ['key' => 'quotations.edit', 'name' => 'Edit Quotations', 'module' => 'Quotations'],
            ['key' => 'quotations.send', 'name' => 'Send Quotations', 'module' => 'Quotations'],
            ['key' => 'quotations.accept', 'name' => 'Accept Quotations', 'module' => 'Quotations'],
            ['key' => 'quotations.reject', 'name' => 'Reject Quotations', 'module' => 'Quotations'],
            ['key' => 'quotations.convert', 'name' => 'Convert to Invoice', 'module' => 'Quotations'],
            ['key' => 'quotations.pdf', 'name' => 'Download Quotation PDF', 'module' => 'Quotations'],

            // Invoices
            ['key' => 'invoices.view', 'name' => 'View Invoices', 'module' => 'Invoices'],
            ['key' => 'invoices.create', 'name' => 'Create Invoices', 'module' => 'Invoices'],
            ['key' => 'invoices.edit', 'name' => 'Edit Invoices', 'module' => 'Invoices'],
            ['key' => 'invoices.pdf', 'name' => 'Download Invoice PDF', 'module' => 'Invoices'],

            // Payments
            ['key' => 'payments.view', 'name' => 'View Payments', 'module' => 'Payments'],
            ['key' => 'payments.edit', 'name' => 'Edit Payments', 'module' => 'Payments'],

            // Templates
            ['key' => 'templates.view', 'name' => 'View Templates', 'module' => 'Templates'],
            ['key' => 'templates.create', 'name' => 'Create Templates', 'module' => 'Templates'],
            ['key' => 'templates.edit', 'name' => 'Edit Templates', 'module' => 'Templates'],

            // Activity Logs
            ['key' => 'activity_logs.view', 'name' => 'View Activity Logs', 'module' => 'Activity Logs'],

            // Settings
            ['key' => 'settings.view', 'name' => 'View Settings', 'module' => 'Settings'],

            // Reports
            ['key' => 'reports.view', 'name' => 'View Reports', 'module' => 'Reports'],
            ['key' => 'reports.invoice.view', 'name' => 'View Invoice Reports', 'module' => 'Reports'],
        ];

        foreach ($permissions as $permission) {

            DB::table('permissions')->updateOrInsert(
                ['key' => $permission['key']],
                [
                    'name' => $permission['name'],
                    'module' => $permission['module'],
                    'updated_at' => now(),
                ]
            );
        }
    }
}
