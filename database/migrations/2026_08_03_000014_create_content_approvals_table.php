<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_approvals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('generated_content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('generated_content_version_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approved_by')->constrained('users')->cascadeOnDelete();
            $table->text('checklist');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at');
            $table->timestamps();

            $table->unique('generated_content_version_id');
            $table->index(['generated_content_id', 'approved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_approvals');
    }
};
