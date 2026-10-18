<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prompts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained()
                ->cascadeOnDelete();

            // Frontend user who created the prompt
            $table->foreignId('frontend_user_id')
                ->nullable()
                ->constrained('frontend_users')
                ->nullOnDelete();

            $table->string('title');
            $table->string('label')->nullable();
            $table->text('prompt_text');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prompts');
    }
};