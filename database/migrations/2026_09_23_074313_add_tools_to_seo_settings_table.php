<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('seo_settings', function (Blueprint $table) {
        if (!Schema::hasColumn('seo_settings', 'tools')) {
            $table->json('tools')->nullable()->after('meta_keywords');
        }
    });
}

    public function down(): void
    {
        Schema::table('seo_settings', function (Blueprint $table) {
            $table->dropColumn('tools');
        });
    }
};