
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Frontend prompts ko frontend users se link karo
        Schema::table('prompts', function (Blueprint $table) {
            $table->foreignId('frontend_user_id')
                ->nullable()
                ->after('user_id')
                ->constrained('frontend_users')
                ->nullOnDelete();
        });

        // Saved prompts mein frontend users ka support
        Schema::table('saved_prompts', function (Blueprint $table) {
            // Existing Admin user_id ko nullable karo
            $table->foreignId('user_id')
                ->nullable()
                ->change();

            // Frontend user relation
            $table->foreignId('frontend_user_id')
                ->nullable()
                ->after('user_id')
                ->constrained('frontend_users')
                ->cascadeOnDelete();

            // Same frontend user ek prompt ko duplicate save na kar sake
            $table->unique(
                ['frontend_user_id', 'prompt_id'],
                'frontend_user_prompt_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('saved_prompts', function (Blueprint $table) {
            $table->dropUnique('frontend_user_prompt_unique');
            $table->dropForeign(['frontend_user_id']);
            $table->dropColumn('frontend_user_id');

            // user_id ko nullable hi rehne do,
            // taaki frontend saved records ke rollback par error na aaye.
        });

        Schema::table('prompts', function (Blueprint $table) {
            $table->dropForeign(['frontend_user_id']);
            $table->dropColumn('frontend_user_id');
        });
    }
};