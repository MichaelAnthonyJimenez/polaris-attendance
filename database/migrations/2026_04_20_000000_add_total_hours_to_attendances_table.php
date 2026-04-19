<?php

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'total_hours')) {
                $table->decimal('total_hours', 8, 2)->nullable()->after('status');
            }
        });

        Attendance::query()
            ->where('type', 'check_out')
            ->whereNull('total_hours')
            ->orderBy('captured_at')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $checkout) {
                    $checkOutAt = $checkout->captured_at instanceof Carbon
                        ? $checkout->captured_at
                        : Carbon::parse((string) $checkout->captured_at);
                    $paired = Attendance::findPairedCheckInForCheckout((int) $checkout->driver_id, $checkOutAt);
                    if ($paired === null) {
                        continue;
                    }
                    $hours = Attendance::hoursBetween($paired->captured_at, $checkOutAt);
                    if ($hours !== null) {
                        $checkout->update(['total_hours' => $hours]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'total_hours')) {
                $table->dropColumn('total_hours');
            }
        });
    }
};
