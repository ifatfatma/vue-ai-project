<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Overall copies count column in prompts table
        Schema::table('prompts', function (Blueprint $table) {
            if (!Schema::hasColumn('prompts', 'copies_count')) {
                $table->unsignedBigInteger('copies_count')->default(0)->after('prompt_text');
            }
        });

        // 2. Date-wise Analytics tracking table
        if (!Schema::hasTable('prompt_copies')) {
            Schema::create('prompt_copies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('prompt_id')->constrained()->onDelete('cascade');
                $table->string('email');
                $table->date('copied_date');
                $table->timestamps();

                // 🔴 CRITICAL FOR 1 DAY = 1 COUNT LIMIT
                // Ye line ek same email + prompt + date combination ko duplicate hone se rokegi
                $table->unique(['prompt_id', 'email', 'copied_date'], 'unique_user_daily_copy');
            });
        }
    }

    public function down(): void
    {
        Schema::table('prompts', function (Blueprint $table) {
            if (Schema::hasColumn('prompts', 'copies_count')) {
                $table->dropColumn('copies_count');
            }
        });

        Schema::dropIfExists('prompt_copies');
    }
};