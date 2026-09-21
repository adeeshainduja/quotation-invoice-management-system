<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_invoice_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('template_id')->constrained('company_templates');

            $table->enum('frequency', [
                'WEEKLY',
                'MONTHLY',
                'QUARTERLY',
                'YEARLY'
            ]);

            $table->date('next_run_date');
            $table->date('end_date')->nullable();

            $table->unsignedInteger('due_days')->default(30);

            $table->json('invoice_data');

            $table->enum('status', [
                'ACTIVE',
                'INACTIVE'
            ])->default('ACTIVE');

            $table->date('last_run_date')->nullable();
            $table->unsignedBigInteger('last_invoice_id')->nullable();

            $table->foreignId('created_by')->constrained('users');

            $table->timestamps();

            $table->index(['status', 'next_run_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_invoice_schedules');
    }
};