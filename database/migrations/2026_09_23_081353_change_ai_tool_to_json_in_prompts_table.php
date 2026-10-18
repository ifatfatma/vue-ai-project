<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        if (!Schema::hasColumn('prompts', 'ai_tool')) {
            
            Schema::table('prompts', function (Blueprint $table) {
                $table->json('ai_tool')->nullable();
            });
        } else {
            
            DB::statement("
                UPDATE prompts
                SET ai_tool = JSON_ARRAY(ai_tool)
                WHERE ai_tool IS NOT NULL
                AND ai_tool != ''
            ");

            Schema::table('prompts', function (Blueprint $table) {
                $table->json('ai_tool')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('prompts', 'ai_tool')) {
            DB::statement("
                UPDATE prompts
                SET ai_tool = JSON_UNQUOTE(JSON_EXTRACT(ai_tool, '$[0]'))
                WHERE ai_tool IS NOT NULL
            ");

            Schema::table('prompts', function (Blueprint $table) {
                $table->string('ai_tool', 100)->nullable()->change();
            });
        }
    }
};