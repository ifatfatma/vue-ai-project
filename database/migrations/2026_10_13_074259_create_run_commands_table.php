<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('run_commands', function (Blueprint $table) {
            $table->id();
            $table->string('heading'); 
            $table->text('command_sql'); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('run_commands');
    }
};