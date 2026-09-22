<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('company_user')) {
            Schema::create('company_user', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->foreignId('company_id')
                    ->constrained('companies')
                    ->cascadeOnDelete();

                $table->primary(['user_id', 'company_id']);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('company_template_user')) {
            Schema::create('company_template_user', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->foreignId('company_template_id')
                    ->constrained('company_templates')
                    ->cascadeOnDelete();

                $table->primary(['user_id', 'company_template_id']);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_template_user');
        Schema::dropIfExists('company_user');
    }
};
