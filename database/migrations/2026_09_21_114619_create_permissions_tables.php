<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permissions')) {

            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('name');
                $table->string('module');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permission_user')) {

            Schema::create('permission_user', function (Blueprint $table) {
                $table->foreignId('user_id')
                    ->constrained('users');

                $table->foreignId('permission_id')
                    ->constrained('permissions');

                $table->primary([
                    'user_id',
                    'permission_id'
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('permissions');
    }
};