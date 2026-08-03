<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_provider_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('provider');
            $table->string('model');
            $table->string('base_url')->nullable();
            $table->text('encrypted_api_key')->nullable();
            $table->string('api_key_hint', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_tested_at')->nullable();
            $table->string('last_test_status')->nullable();
            $table->text('last_test_error')->nullable();
            $table->timestamps();

            $table->unique('provider');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_provider_settings');
    }
};
