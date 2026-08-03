<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_content_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('generated_content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('payload');
            $table->string('change_summary')->nullable();
            $table->timestamps();

            $table->unique(['generated_content_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_content_versions');
    }
};
