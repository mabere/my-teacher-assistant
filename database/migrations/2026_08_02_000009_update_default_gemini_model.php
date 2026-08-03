<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ai_provider_settings')
            ->where('provider', 'gemini')
            ->where('model', 'gemini-2.5-flash')
            ->update(['model' => 'gemini-3.6-flash', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('ai_provider_settings')
            ->where('provider', 'gemini')
            ->where('model', 'gemini-3.6-flash')
            ->update(['model' => 'gemini-2.5-flash', 'updated_at' => now()]);
    }
};
