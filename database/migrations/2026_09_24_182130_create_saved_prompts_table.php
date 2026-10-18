
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_prompts', function (Blueprint $table) {
            $table->id();

            // Admin / existing users
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            // Frontend users
            $table->foreignId('frontend_user_id')
                ->nullable()
                ->constrained('frontend_users')
                ->cascadeOnDelete();

            $table->foreignId('prompt_id')
                ->constrained('prompts')
                ->cascadeOnDelete();

            $table->timestamps();

            // Prevent duplicate saves for each user type
            $table->unique(['user_id', 'prompt_id']);
            $table->unique(['frontend_user_id', 'prompt_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_prompts');
    }
};