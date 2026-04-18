<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Setting::firstOrCreate(
            ['key' => 'auto_submit_camera'],
            [
                'value' => '0',
                'type' => 'boolean',
                'group' => 'driver_attendance',
                'description' => 'Camera: automatically submit right after capture',
            ]
        );
    }

    public function down(): void
    {
        Setting::where('key', 'auto_submit_camera')->delete();
    }
};
