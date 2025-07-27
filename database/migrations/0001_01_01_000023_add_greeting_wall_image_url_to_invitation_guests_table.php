<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn($table = 'invitation_guests', $column = 'greeting_wall_image_url')) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->string($column)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn($table = 'invitation_guests', $column = 'greeting_wall_image_url')) {
            Schema::table($table, function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }
};
