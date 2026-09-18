<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });

        Schema::table('company_templates', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
        });

        Schema::table('company_bank_details', function (Blueprint $table) {
            $table->dateTime('created_at')->change();
            $table->dateTime('updated_at')->change();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });

        Schema::table('company_templates', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });

        Schema::table('quotations', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });

        Schema::table('quotation_items', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
        });

        Schema::table('company_bank_details', function (Blueprint $table) {
            $table->timestamp('created_at')->change();
            $table->timestamp('updated_at')->change();
        });
    }
};