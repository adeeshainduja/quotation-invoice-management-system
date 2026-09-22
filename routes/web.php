<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.submit');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/create-new', function () {
        return view('dashboard.create-new');
    })
        ->middleware('permission:dashboard.view')
        ->name('create-new');

    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */

    Route::get('/companies', [CompanyController::class, 'index'])
        ->middleware('permission:companies.view')
        ->name('companies.index');

    Route::middleware('admin')->group(function () {
        Route::get('/companies/create', [CompanyController::class, 'create'])
            ->name('companies.create');

        Route::post('/companies', [CompanyController::class, 'store'])
            ->name('companies.store');

        Route::get('/companies/{id}/edit', [CompanyController::class, 'edit'])
            ->whereNumber('id')
            ->name('companies.edit');

        Route::put('/companies/{id}', [CompanyController::class, 'update'])
            ->whereNumber('id')
            ->name('companies.update');

        Route::post('/companies/{id}/toggle-status', [CompanyController::class, 'toggleStatus'])
            ->whereNumber('id')
            ->name('companies.toggle-status');
    });

    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */

    Route::get('/customers', [CustomerController::class, 'index'])
        ->middleware('permission:customers.view')
        ->name('customers.index');

    Route::get('/customers/create', [CustomerController::class, 'create'])
        ->middleware('permission:customers.create')
        ->name('customers.create');

    Route::post('/customers', [CustomerController::class, 'store'])
        ->middleware('permission:customers.create')
        ->name('customers.store');

    Route::get('/customers/{id}', [CustomerController::class, 'show'])
        ->whereNumber('id')
        ->middleware('permission:customers.view')
        ->name('customers.show');

    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */

    Route::get('/quotations', [QuotationController::class, 'index'])
        ->middleware('permission:quotations.view')
        ->name('quotations.index');

    Route::get('/quotations/create', [QuotationController::class, 'create'])
        ->middleware('permission:quotations.create')
        ->name('quotations.create');

    Route::post('/quotations/preview', [QuotationController::class, 'preview'])
        ->middleware('permission:quotations.create')
        ->name('quotations.preview');

    Route::post('/quotations', [QuotationController::class, 'store'])
        ->middleware('permission:quotations.create')
        ->name('quotations.store');

    Route::get('/quotations/{id}/pdf', [QuotationController::class, 'downloadPdf'])
        ->whereNumber('id')
        ->middleware('permission:quotations.pdf')
        ->name('quotations.pdf');

    Route::get('/quotations/{id}/edit', [QuotationController::class, 'edit'])
        ->whereNumber('id')
        ->middleware('permission:quotations.update')
        ->name('quotations.edit');

    Route::put('/quotations/{id}', [QuotationController::class, 'update'])
        ->whereNumber('id')
        ->middleware('permission:quotations.update')
        ->name('quotations.update');

    Route::post('/quotations/{id}/clone', [QuotationController::class, 'clone'])
        ->whereNumber('id')
        ->middleware('permission:quotations.create')
        ->name('quotations.clone');

    Route::post('/quotations/{id}/send', [QuotationController::class, 'markAsSent'])
        ->whereNumber('id')
        ->middleware('permission:quotations.update')
        ->name('quotations.send');

    Route::post('/quotations/{id}/accept', [QuotationController::class, 'accept'])
        ->whereNumber('id')
        ->middleware('permission:quotations.update')
        ->name('quotations.accept');

    Route::post('/quotations/{id}/reject', [QuotationController::class, 'reject'])
        ->whereNumber('id')
        ->middleware('permission:quotations.update')
        ->name('quotations.reject');

    Route::post('/quotations/{id}/convert', [QuotationController::class, 'convertToInvoice'])
        ->whereNumber('id')
        ->middleware('permission:invoices.create')
        ->name('quotations.convert');

    Route::get('/quotations/{id}', [QuotationController::class, 'show'])
        ->whereNumber('id')
        ->middleware('permission:quotations.view')
        ->name('quotations.show');

    /*
    |--------------------------------------------------------------------------
    | Invoices
    |--------------------------------------------------------------------------
    */

    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->middleware('permission:invoices.view')
        ->name('invoices.index');

    Route::get('/invoices/create', [InvoiceController::class, 'create'])
        ->middleware('permission:invoices.create')
        ->name('invoices.create');

    Route::post('/invoices/preview', [InvoiceController::class, 'preview'])
        ->middleware('permission:invoices.create')
        ->name('invoices.preview');

    Route::post('/invoices', [InvoiceController::class, 'store'])
        ->middleware('permission:invoices.create')
        ->name('invoices.store');

    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])
        ->whereNumber('id')
        ->middleware('permission:invoices.view')
        ->name('invoices.show');

    Route::get('/invoices/{id}/edit', [InvoiceController::class, 'edit'])
        ->whereNumber('id')
        ->middleware('permission:invoices.update')
        ->name('invoices.edit');

    Route::put('/invoices/{id}', [InvoiceController::class, 'update'])
        ->whereNumber('id')
        ->middleware('permission:invoices.update')
        ->name('invoices.update');

    Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf'])
        ->whereNumber('id')
        ->middleware('permission:invoices.pdf')
        ->name('invoices.pdf');

    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

    Route::get('/payments', [PaymentController::class, 'index'])
        ->middleware('permission:payments.view')
        ->name('payments.index');

    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    */

    Route::get('/templates', [TemplateController::class, 'index'])
        ->middleware('permission:templates.view')
        ->name('templates.index');

    Route::get('/templates/create', [TemplateController::class, 'create'])
        ->middleware('permission:templates.create')
        ->name('templates.create');

    Route::post('/templates', [TemplateController::class, 'store'])
        ->middleware('permission:templates.create')
        ->name('templates.store');

    Route::get('/templates/{id}', [TemplateController::class, 'show'])
        ->whereNumber('id')
        ->middleware('permission:templates.view')
        ->name('templates.show');

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('permission:reports.view')
        ->name('reports.index');

    Route::get('/reports/invoices', [ReportController::class, 'invoiceReport'])
        ->middleware('permission:reports.invoice.view')
        ->name('reports.invoices');

    Route::get('/reports/payments', [ReportController::class, 'paymentReport'])
        ->middleware('permission:reports.payment.view')
        ->name('reports.payments');

    Route::get('/reports/quotations', [ReportController::class, 'quotationReport'])
        ->middleware('permission:reports.quotation.view')
        ->name('reports.quotations');

    Route::get('/reports/customers', [ReportController::class, 'customerReport'])
        ->middleware('permission:reports.customer.view')
        ->name('reports.customers');

    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])
        ->middleware('permission:activity_logs.view')
        ->name('activity-logs.index');

    Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])
        ->whereNumber('id')
        ->middleware('permission:activity_logs.view')
        ->name('activity-logs.show');

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', [SettingsController::class, 'index'])
        ->middleware('permission:settings.view')
        ->name('settings.index');

    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])
        ->middleware('permission:settings.view')
        ->name('settings.profile');

    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])
        ->middleware('permission:settings.view')
        ->name('settings.password');

    /*
    |--------------------------------------------------------------------------
    | Users & Permissions - ADMIN ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')->group(function () {

        Route::get('/users', [UserController::class, 'index'])
            ->name('users.index');

        Route::get('/users/create', [UserController::class, 'create'])
            ->name('users.create');

        Route::post('/users', [UserController::class, 'store'])
            ->name('users.store');

        Route::get('/users/{id}/edit', [UserController::class, 'edit'])
            ->whereNumber('id')
            ->name('users.edit');

        Route::put('/users/{id}', [UserController::class, 'update'])
            ->whereNumber('id')
            ->name('users.update');

        Route::post('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])
            ->whereNumber('id')
            ->name('users.toggle-status');
    });

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
