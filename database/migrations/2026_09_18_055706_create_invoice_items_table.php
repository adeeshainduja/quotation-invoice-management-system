<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            // Invoice relationship
            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            // Optional source quotation item
            $table->foreignId('source_quotation_item_id')
                ->nullable()
                ->constrained('quotation_items')
                ->restrictOnDelete();

            // Item ordering
            $table->integer('sort_order')->default(0);

            // Item information
            $table->string('item_name');
            $table->text('description')->nullable();

            // Quantity and pricing
            $table->decimal('quantity', 12, 2);
            $table->string('unit', 50)->nullable();
            $table->decimal('unit_price', 15, 2);

            // Discount
            $table->enum('discount_type', [
                'NONE',
                'PERCENTAGE',
                'FIXED'
            ])->default('NONE');

            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);

            // Tax
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);

            // Calculated line total
            $table->decimal('line_total', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};