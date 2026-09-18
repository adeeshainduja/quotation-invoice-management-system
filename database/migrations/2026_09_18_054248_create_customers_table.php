<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Company relationship
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Customer information
            $table->string('customer_name');
            $table->string('business_name');

            $table->string('registration_number', 100)->nullable();
            $table->string('vat_number', 100)->nullable();

            // Contact information
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();

            // Address
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city', 100);
            $table->string('country', 100);

            // Additional information
            $table->text('notes')->nullable();

            // Status
            $table->enum('status', ['ACTIVE', 'INACTIVE'])
                ->default('ACTIVE');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};