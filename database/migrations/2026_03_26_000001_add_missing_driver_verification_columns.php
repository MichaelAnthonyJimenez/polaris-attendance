<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = 'driver_verifications';

        if (! Schema::hasTable($table)) {
            return;
        }

        $schema = function (Blueprint $t) use ($table) {
            // SQLite migrations can’t always add foreign keys reliably; we only add
            // the columns required by the code/views.

            if (! Schema::hasColumn($table, 'driver_id')) {
                $t->unsignedBigInteger('driver_id')->nullable();
            }

            if (! Schema::hasColumn($table, 'verification_method')) {
                $t->string('verification_method')->nullable();
            }

            if (! Schema::hasColumn($table, 'type')) {
                $t->string('type')->nullable();
            }

            if (! Schema::hasColumn($table, 'status')) {
                $t->string('status')->default('pending');
            }

            if (! Schema::hasColumn($table, 'manual_form_data')) {
                $t->json('manual_form_data')->nullable();
            }

            if (! Schema::hasColumn($table, 'reason')) {
                $t->string('reason')->nullable();
            }

            if (! Schema::hasColumn($table, 'admin_notes')) {
                $t->text('admin_notes')->nullable();
            }

            if (! Schema::hasColumn($table, 'face_image_path')) {
                $t->string('face_image_path')->nullable();
            }

            if (! Schema::hasColumn($table, 'selfie_with_id_path')) {
                $t->string('selfie_with_id_path')->nullable();
            }

            if (! Schema::hasColumn($table, 'id_image_path')) {
                $t->string('id_image_path')->nullable();
            }

            if (! Schema::hasColumn($table, 'id_image_back_path')) {
                $t->string('id_image_back_path')->nullable();
            }

            if (! Schema::hasColumn($table, 'reviewer_id')) {
                $t->unsignedBigInteger('reviewer_id')->nullable();
            }

            if (! Schema::hasColumn($table, 'reviewed_at')) {
                $t->timestamp('reviewed_at')->nullable();
            }
        };

        Schema::table($table, $schema);
    }

    public function down(): void
    {
        // Intentionally left blank (dropping columns is destructive).
    }
};

