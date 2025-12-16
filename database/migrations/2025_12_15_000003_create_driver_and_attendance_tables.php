<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('badge_number')->unique();
            $table->string('phone')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('api_token')->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('driver_faces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->text('face_template')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['check_in', 'check_out']);
            $table->timestamp('captured_at')->useCurrent();
            $table->decimal('face_confidence', 5, 2)->nullable();
            $table->decimal('liveness_score', 5, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->string('device_id')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('synced')->default(true);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('driver_faces');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('drivers');
    }
};

