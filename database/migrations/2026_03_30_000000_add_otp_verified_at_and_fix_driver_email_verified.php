<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('otp_verified_at')->nullable()->after('email_verified_at');
        });

        // Anyone who already completed email/OTP has a timestamp in email_verified_at — preserve that as OTP completion.
        DB::statement('UPDATE users SET otp_verified_at = email_verified_at WHERE email_verified_at IS NOT NULL');

        // Drivers are only "account verified" after admin approves driver verification, not after typing the login code.
        DB::table('users')
            ->where('role', 'driver')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('driver_verifications')
                    ->whereColumn('driver_verifications.user_id', 'users.id')
                    ->where('driver_verifications.status', 'approved');
            })
            ->update(['email_verified_at' => null]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('otp_verified_at');
        });
    }
};
