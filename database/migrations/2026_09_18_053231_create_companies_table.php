<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Company identity
            $table->string('name');
            $table->string('registration_number', 100);

            // Contact details
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city', 100);
            $table->string('country', 100);
            $table->string('phone', 50);
            $table->string('email', 255);
            $table->string('website', 255);

            // Branding
            $table->string('logo_path', 500);
            $table->string('signature_path', 500)->nullable();
            $table->string('stamp_path', 500)->nullable();

            // Tax / VAT
            $table->boolean('vat_registered')->default(false);
            $table->string('vat_number', 100)->nullable();
            $table->decimal('vat_percentage', 5, 2)->nullable();

            // Banking
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account_name', 255)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('bank_branch', 255)->nullable();
            $table->string('swift_code', 100)->nullable();

            // Document numbering
            $table->string('quotation_prefix', 50);
            $table->unsignedBigInteger('quotation_next_number')->default(1);

            $table->string('invoice_prefix', 50);
            $table->unsignedBigInteger('invoice_next_number')->default(1);

            // Defaults
            $table->string('currency', 10)->default('LKR');
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};