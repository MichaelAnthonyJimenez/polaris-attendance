<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@polaris.test'],
            [
                'name' => 'Polaris Admin',
                'password' => bcrypt('Admin!234'),
                'role' => 'admin',
            ],
        );
        
        // Update existing admin if role is not set
        if ($admin->role !== 'admin') {
            $admin->update(['role' => 'admin']);
        }

        $device = Device::firstOrCreate(
            ['api_token' => 'offline-demo-token'],
            ['name' => 'Offline Tablet']
        );

        $this->call(SettingsSeeder::class);

        $this->command?->info("Admin user ready: {$admin->email} / Admin!234");
        $this->command?->info("Offline device token: {$device->api_token}");
    }
}
