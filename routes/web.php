<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\QuotationController;

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


Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/companies', [CompanyController::class, 'index'])
        ->name('companies.index');


    Route::get('/customers', [CustomerController::class, 'index'])
        ->name('customers.index');

    Route::get('/customers/create', [CustomerController::class, 'create'])
        ->name('customers.create');

    Route::post('/customers', [CustomerController::class, 'store'])
        ->name('customers.store');

    Route::get('/customers/{id}', [CustomerController::class, 'show'])
        ->name('customers.show');


    Route::get('/quotations', [QuotationController::class, 'index'])
        ->name('quotations.index');

    Route::get('/quotations/create', [QuotationController::class, 'create'])
        ->name('quotations.create');

    Route::post('/quotations', [QuotationController::class, 'store'])
        ->name('quotations.store');


    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

});