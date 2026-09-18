<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'bank_name',
                'bank_account_name',
                'bank_account_number',
                'bank_branch',
                'swift_code',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account_name', 255)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_branch', 255)->nullable();
            $table->string('swift_code', 100)->nullable();
        });
    }
};