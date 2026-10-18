<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // OTP code store karne ke liye
            $table->string('otp')->nullable()->after('password');
            // OTP kab expire hoga uska time track karne ke liye
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_expires_at']);
        });
    }
};