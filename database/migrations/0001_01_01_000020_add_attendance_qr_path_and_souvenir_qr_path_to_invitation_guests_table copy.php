<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumns('invitation_guests', ['attendance_qr_path', 'souvenir_qr_path'])) {
            Schema::table('invitation_guests', function (Blueprint $table) {
                $table->string('attendance_qr_path')->nullable();
                $table->string('souvenir_qr_path')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_guests', function (Blueprint $table) {
            $table->dropColumn(['attendance_qr_path', 'souvenir_qr_path']);
        });
    }
};
