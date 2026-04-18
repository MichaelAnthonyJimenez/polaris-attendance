<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_verifications')) {
            return;
        }

        Schema::create('driver_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();

            $table->string('verification_method')->nullable(); // facial | id_only | manual
            $table->string('type')->nullable(); // reserved for future use
            $table->string('status')->default('pending'); // pending | approved | rejected

            $table->json('manual_form_data')->nullable();

            $table->string('reason')->nullable();
            $table->text('admin_notes')->nullable();

            $table->string('face_image_path')->nullable();
            $table->string('selfie_with_id_path')->nullable();
            $table->string('id_image_path')->nullable();
            $table->string('id_image_back_path')->nullable();

            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['driver_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_verifications');
    }
};

