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
        if (!Schema::hasColumns('templates', ['display_background_landscape_url', 'display_background_portrait_url'])) {
            Schema::table('templates', function (Blueprint $table) {
                $table->string('display_background_landscape_url')->nullable();
                $table->string('display_background_portrait_url')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitation_guests', function (Blueprint $table) {
            $table->dropColumn(['display_background_landscape_url', 'display_background_portrait_url']);
        });
    }
};
