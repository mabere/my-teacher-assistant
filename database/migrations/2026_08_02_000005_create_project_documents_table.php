<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teaching_project_id')->constrained()->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('storage_path');
            $table->string('mime_type', 120);
            $table->string('extension', 12);
            $table->unsignedBigInteger('size_bytes');
            $table->string('checksum', 64);
            $table->string('processing_status')->default('pending');
            $table->timestamps();

            $table->index(['teaching_project_id', 'processing_status']);
            $table->index(['user_id', 'checksum']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};
