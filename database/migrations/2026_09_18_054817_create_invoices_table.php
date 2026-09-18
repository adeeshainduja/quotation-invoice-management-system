<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            // Invoice can optionally come from a quotation
            $table->foreignId('quotation_id')
                ->nullable()
                ->constrained('quotations')
                ->restrictOnDelete();

            // Invoice information
            $table->string('invoice_number', 100);
            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            $table->string('subject')->nullable();
            $table->string('reference')->nullable();

            // Financial information
            $table->decimal('subtotal', 15, 2);

            $table->enum('discount_type', [
                'NONE',
                'PERCENTAGE',
                'FIXED'
            ])->default('NONE');

            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);

            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);

            $table->decimal('additional_charges', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2);

            // Payment information
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('balance_amount', 15, 2);

            // Invoice status
            $table->enum('status', [
                'DRAFT',
                'ISSUED',
                'PARTIALLY_PAID',
                'PAID',
                'OVERDUE',
                'CANCELLED'
            ])->default('DRAFT');

            // Additional information
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();

            // Template
            $table->foreignId('template_id')
                ->constrained('company_templates')
                ->restrictOnDelete();

            // Historical snapshots
            $table->json('company_snapshot');
            $table->json('customer_snapshot');
            $table->json('template_snapshot');

            // Creator
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();

            // Invoice number must be unique within a company
            $table->unique(
                ['company_id', 'invoice_number'],
                'invoices_company_number_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};