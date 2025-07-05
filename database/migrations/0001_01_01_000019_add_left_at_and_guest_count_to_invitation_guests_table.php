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
        Schema::table('invitation_guests', function (Blueprint $table) {
            $table->timestamp('left_at')->nullable()->after('selfie_at');
            $table->unsignedInteger('guest_count')->default(1)->after('left_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_guests', function (Blueprint $table) {
            $table->dropColumn(['left_at', 'guest_count']);
        });
    }
};
