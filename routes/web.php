<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SettingsController;


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

    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.submit');
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
        ->name('dashboard');

    Route::get('/create-new', function () {
        return view('dashboard.create-new');
    })->name('create-new');


    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */

    Route::get('/companies', [CompanyController::class, 'index'])
        ->name('companies.index');

    Route::get('/companies/create', [CompanyController::class, 'create'])
        ->name('companies.create');

    Route::post('/companies', [CompanyController::class, 'store'])
        ->name('companies.store');


    /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */

    Route::get('/customers', [CustomerController::class, 'index'])
        ->name('customers.index');

    Route::get('/customers/create', [CustomerController::class, 'create'])
        ->name('customers.create');

    Route::post('/customers', [CustomerController::class, 'store'])
        ->name('customers.store');

    Route::get('/customers/{id}', [CustomerController::class, 'show'])
        ->name('customers.show');


    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */

    Route::get('/quotations', [QuotationController::class, 'index'])
        ->name('quotations.index');

    Route::get('/quotations/create', [QuotationController::class, 'create'])
        ->name('quotations.create');

    Route::post('/quotations/preview', [QuotationController::class, 'preview'])
        ->name('quotations.preview');

    Route::post('/quotations', [QuotationController::class, 'store'])
        ->name('quotations.store');

    Route::get('/quotations/{id}/pdf', [QuotationController::class, 'downloadPdf'])
        ->name('quotations.pdf');

    Route::get('/quotations/{id}', [QuotationController::class, 'show'])
        ->name('quotations.show');


    /*
    |--------------------------------------------------------------------------
    | Invoices
    |--------------------------------------------------------------------------
    */

    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->name('invoices.index');

    Route::get('/invoices/create', [InvoiceController::class, 'create'])
        ->name('invoices.create');

    Route::post('/invoices/preview', [InvoiceController::class, 'preview'])
        ->name('invoices.preview');

    Route::post('/invoices', [InvoiceController::class, 'store'])
        ->name('invoices.store');

    Route::get('/invoices/{id}/pdf', [InvoiceController::class, 'downloadPdf'])
        ->name('invoices.pdf');


    /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    |
    | Payments are created from an invoice.
    | This page is only the payment history/register.
    |
    */

    Route::get('/payments', [PaymentController::class, 'index'])
        ->name('payments.index');


    /*
    |--------------------------------------------------------------------------
    | Templates
    |--------------------------------------------------------------------------
    */

    Route::get('/templates', [TemplateController::class, 'index'])
        ->name('templates.index');

    Route::get('/templates/create', [TemplateController::class, 'create'])
        ->name('templates.create');

    Route::post('/templates', [TemplateController::class, 'store'])
        ->name('templates.store');

    Route::get('/templates/{id}', [TemplateController::class, 'show'])
        ->name('templates.show');


    /*
    |--------------------------------------------------------------------------
    | Activity Logs
    |--------------------------------------------------------------------------
    */

    Route::middleware('can:viewActivityLogs')->group(function () {

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');

        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])
            ->name('activity-logs.show');
    });


    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('settings.index');

    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])
        ->name('settings.profile');

    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])
        ->name('settings.password');


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});