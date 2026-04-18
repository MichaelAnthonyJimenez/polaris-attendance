<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['attendances', 'driver_faces', 'driver_verifications'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'driver_id')) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) {
                try {
                    $table->dropForeign(['driver_id']);
                } catch (\Throwable) {
                    // Ignore when foreign key does not exist.
                }
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'badge_number')) {
                $table->string('badge_number')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('badge_number');
            }
            if (! Schema::hasColumn('users', 'vehicle_number')) {
                $table->string('vehicle_number')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'active')) {
                $table->boolean('active')->default(true)->after('vehicle_number');
            }
            if (! Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('active');
            }
            if (! Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });

        if (! Schema::hasTable('drivers')) {
            return;
        }

        $drivers = DB::table('drivers')->get();

        foreach ($drivers as $driver) {
            $userId = $driver->user_id;

            if (! $userId && ! empty($driver->email)) {
                $userId = DB::table('users')->where('email', $driver->email)->value('id');
            }

            if (! $userId) {
                $existingByBadge = DB::table('users')
                    ->where('role', 'driver')
                    ->where('badge_number', $driver->badge_number)
                    ->value('id');

                if ($existingByBadge) {
                    $userId = $existingByBadge;
                }
            }

            if (! $userId) {
                $insert = [
                    'name' => $driver->name ?: ('Driver '.$driver->id),
                    'email' => $driver->email ?: 'driver-'.$driver->id.'@local.invalid',
                    'password' => DB::table('users')->where('id', 1)->value('password') ?: bcrypt(\Illuminate\Support\Str::random(32)),
                    'role' => 'driver',
                    'badge_number' => $driver->badge_number,
                    'phone' => $driver->phone,
                    'vehicle_number' => $driver->vehicle_number,
                    'profile_photo_path' => $driver->profile_photo_path ?? null,
                    'active' => $driver->active ?? true,
                    'latitude' => $driver->latitude ?? null,
                    'longitude' => $driver->longitude ?? null,
                    'created_at' => $driver->created_at ?? now(),
                    'updated_at' => now(),
                ];

                $userId = DB::table('users')->insertGetId($insert);
            } else {
                DB::table('users')->where('id', $userId)->update([
                    'name' => $driver->name ?: DB::raw('name'),
                    'role' => 'driver',
                    'badge_number' => $driver->badge_number ?: DB::raw('badge_number'),
                    'phone' => $driver->phone ?: DB::raw('phone'),
                    'vehicle_number' => $driver->vehicle_number ?: DB::raw('vehicle_number'),
                    'profile_photo_path' => $driver->profile_photo_path ?: DB::raw('profile_photo_path'),
                    'active' => $driver->active ?? true,
                    'latitude' => $driver->latitude ?? null,
                    'longitude' => $driver->longitude ?? null,
                    'updated_at' => now(),
                ]);
            }

            // Repoint references to users.id
            DB::table('attendances')->where('driver_id', $driver->id)->update(['driver_id' => $userId]);
            DB::table('driver_faces')->where('driver_id', $driver->id)->update(['driver_id' => $userId]);
            DB::table('driver_verifications')
                ->where('driver_id', $driver->id)
                ->update(['driver_id' => $userId, 'user_id' => DB::raw('COALESCE(user_id, '.$userId.')')]);
        }

        foreach (['attendances', 'driver_faces', 'driver_verifications'] as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'driver_id')) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreign('driver_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive; this migration consolidates legacy driver data.
    }
};
