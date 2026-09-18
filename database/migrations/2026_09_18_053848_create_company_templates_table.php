<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->enum('document_type', ['QUOTATION', 'INVOICE']);

            $table->string('template_name');

            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->text('terms_conditions')->nullable();

            $table->boolean('show_logo')->default(true);
            $table->boolean('show_bank_details')->default(true);
            $table->boolean('show_vat')->default(true);
            $table->boolean('show_signature')->default(true);

            $table->json('template_config')->nullable();

            $table->boolean('is_default')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_templates');
    }
};