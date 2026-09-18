<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->foreignId('quotation_id')
                ->after('id')
                ->constrained('quotations')
                ->cascadeOnDelete();

            $table->integer('sort_order')
                ->after('quotation_id');

            $table->string('item_name')
                ->after('sort_order');

            $table->text('description')
                ->nullable()
                ->after('item_name');

            $table->decimal('quantity', 12, 2)
                ->after('description');

            $table->string('unit', 50)
                ->nullable()
                ->after('quantity');

            $table->decimal('unit_price', 15, 2)
                ->after('unit');

            $table->enum('discount_type', [
                'NONE',
                'PERCENTAGE',
                'FIXED'
            ])->after('unit_price');

            $table->decimal('discount_value', 15, 2)
                ->default(0)
                ->after('discount_type');

            $table->decimal('discount_amount', 15, 2)
                ->default(0)
                ->after('discount_value');

            $table->decimal('tax_percentage', 5, 2)
                ->default(0)
                ->after('discount_amount');

            $table->decimal('tax_amount', 15, 2)
                ->default(0)
                ->after('tax_percentage');

            $table->decimal('line_total', 15, 2)
                ->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropColumn([
                'quotation_id',
                'sort_order',
                'item_name',
                'description',
                'quantity',
                'unit',
                'unit_price',
                'discount_type',
                'discount_value',
                'discount_amount',
                'tax_percentage',
                'tax_amount',
                'line_total',
            ]);
        });
    }
};