<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'vat_enabled')) {
                $table->boolean('vat_enabled')->default(false)->after('tax_amount');
            }
            if (! Schema::hasColumn('invoices', 'vat_percentage')) {
                $table->decimal('vat_percentage', 5, 2)->nullable()->after('vat_enabled');
            }
            if (! Schema::hasColumn('invoices', 'vat_amount')) {
                $table->decimal('vat_amount', 12, 2)->default(0)->after('vat_percentage');
            }
            if (! Schema::hasColumn('invoices', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('terms_conditions');
            }
        });

        Schema::table('quotations', function (Blueprint $table) {
            if (! Schema::hasColumn('quotations', 'vat_enabled')) {
                $table->boolean('vat_enabled')->default(false)->after('tax_amount');
            }
            if (! Schema::hasColumn('quotations', 'vat_percentage')) {
                $table->decimal('vat_percentage', 5, 2)->nullable()->after('vat_enabled');
            }
            if (! Schema::hasColumn('quotations', 'vat_amount')) {
                $table->decimal('vat_amount', 12, 2)->default(0)->after('vat_percentage');
            }
            if (! Schema::hasColumn('quotations', 'template_id')) {
                $table->unsignedBigInteger('template_id')->nullable()->after('terms_conditions');
            }
        });

        // Sync existing data for invoices
        DB::table('invoices')->where('tax_amount', '>', 0)->update([
            'vat_enabled' => true,
            'vat_percentage' => DB::raw('tax_percentage'),
            'vat_amount' => DB::raw('tax_amount'),
        ]);

        // Sync existing data for quotations
        DB::table('quotations')->where('tax_amount', '>', 0)->update([
            'vat_enabled' => true,
            'vat_percentage' => DB::raw('tax_percentage'),
            'vat_amount' => DB::raw('tax_amount'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('invoices', 'vat_enabled')) {
                $cols[] = 'vat_enabled';
            }
            if (Schema::hasColumn('invoices', 'vat_percentage')) {
                $cols[] = 'vat_percentage';
            }
            if (Schema::hasColumn('invoices', 'vat_amount')) {
                $cols[] = 'vat_amount';
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('quotations', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('quotations', 'vat_enabled')) {
                $cols[] = 'vat_enabled';
            }
            if (Schema::hasColumn('quotations', 'vat_percentage')) {
                $cols[] = 'vat_percentage';
            }
            if (Schema::hasColumn('quotations', 'vat_amount')) {
                $cols[] = 'vat_amount';
            }
            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
