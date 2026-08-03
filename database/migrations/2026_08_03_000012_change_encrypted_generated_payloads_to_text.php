<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generated_contents', function (Blueprint $table): void {
            $table->text('input_snapshot')->nullable()->change();
        });

        Schema::table('generated_content_versions', function (Blueprint $table): void {
            $table->text('payload')->change();
        });
    }

    public function down(): void
    {
        Schema::table('generated_contents', function (Blueprint $table): void {
            $table->json('input_snapshot')->nullable()->change();
        });

        Schema::table('generated_content_versions', function (Blueprint $table): void {
            $table->json('payload')->change();
        });
    }
};
