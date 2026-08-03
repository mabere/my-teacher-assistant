<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_documents', function (Blueprint $table): void {
            $table->timestamp('processing_started_at')->nullable()->after('processing_error');
        });
    }

    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table): void {
            $table->dropColumn('processing_started_at');
        });
    }
};
