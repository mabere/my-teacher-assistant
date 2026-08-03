<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_documents', function (Blueprint $table): void {
            $table->longText('extracted_text')->nullable()->after('checksum');
            $table->json('extraction_metadata')->nullable()->after('extracted_text');
            $table->text('processing_error')->nullable()->after('processing_status');
            $table->timestamp('processed_at')->nullable()->after('processing_error');
        });
    }

    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table): void {
            $table->dropColumn(['extracted_text', 'extraction_metadata', 'processing_error', 'processed_at']);
        });
    }
};
