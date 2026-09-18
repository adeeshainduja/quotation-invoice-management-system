<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('company_id')
                ->constrained('companies')
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            // Quotation information
            $table->string('quotation_number', 100);
            $table->date('quotation_date');
            $table->date('expiry_date')->nullable();

            $table->string('subject')->nullable();
            $table->string('project_name')->nullable();
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

            // Status
            $table->enum('status', [
                'DRAFT',
                'SENT',
                'ACCEPTED',
                'REJECTED',
                'EXPIRED',
                'CONVERTED'
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

            // Quotation number must be unique within a company
            $table->unique(
                ['company_id', 'quotation_number'],
                'quotations_company_number_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};