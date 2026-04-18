<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'location_sharing_enabled')) {
                $table->boolean('location_sharing_enabled')->default(false)->after('longitude');
            }

            if (! Schema::hasColumn('users', 'location_updated_at')) {
                $table->timestamp('location_updated_at')->nullable()->after('location_sharing_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'location_updated_at')) {
                $table->dropColumn('location_updated_at');
            }

            if (Schema::hasColumn('users', 'location_sharing_enabled')) {
                $table->dropColumn('location_sharing_enabled');
            }
        });
    }
};
