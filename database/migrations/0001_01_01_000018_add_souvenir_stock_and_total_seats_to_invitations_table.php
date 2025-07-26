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
        if (!Schema::hasColumns('invitations', ['souvenir_stock', 'total_seats'])) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->unsignedInteger('souvenir_stock')->nullable()->default(0)->after('date_end');
                $table->unsignedInteger('total_seats')->nullable()->default(0)->after('souvenir_stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn(['souvenir_stock', 'total_seats']);
        });
    }
};
