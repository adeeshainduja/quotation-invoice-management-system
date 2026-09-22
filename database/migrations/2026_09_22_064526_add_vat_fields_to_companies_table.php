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
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'vat_enabled')) {
                $table->boolean('vat_enabled')->default(false)->after('vat_registered');
            }

            if (! Schema::hasColumn('companies', 'tin_number')) {
                $table->string('tin_number', 100)->nullable()->after('registration_number');
            }

            if (! Schema::hasColumn('companies', 'tax_registration_number')) {
                $table->string('tax_registration_number', 100)->nullable()->after('tin_number');
            }

            if (! Schema::hasColumn('companies', 'vat_number')) {
                $table->string('vat_number', 100)->nullable()->after('vat_registered');
            }

            if (! Schema::hasColumn('companies', 'tax_percentage')) {
                $table->decimal('tax_percentage', 5, 2)->nullable()->after('vat_percentage');
            }
        });

        // Sync existing data from vat_registered and vat_percentage if present
        if (Schema::hasColumn('companies', 'vat_registered') && Schema::hasColumn('companies', 'vat_enabled')) {
            DB::table('companies')
                ->where('vat_registered', true)
                ->update(['vat_enabled' => true]);
        }

        if (Schema::hasColumn('companies', 'vat_percentage') && Schema::hasColumn('companies', 'tax_percentage')) {
            DB::table('companies')
                ->whereNotNull('vat_percentage')
                ->update(['tax_percentage' => DB::raw('vat_percentage')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $columnsToDrop = [];

            if (Schema::hasColumn('companies', 'vat_enabled')) {
                $columnsToDrop[] = 'vat_enabled';
            }
            if (Schema::hasColumn('companies', 'tax_registration_number')) {
                $columnsToDrop[] = 'tax_registration_number';
            }
            if (Schema::hasColumn('companies', 'tax_percentage')) {
                $columnsToDrop[] = 'tax_percentage';
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
