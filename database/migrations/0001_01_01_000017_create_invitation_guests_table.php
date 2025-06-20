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
        Schema::create('invitation_guests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('guest_id')->index();
            $table->uuid('invitation_id')->index();
            $table->enum('type', ['reg', 'vip', 'vvip']);
            $table->boolean('rsvp')->nullable();
            $table->timestamp('attended_at')->nullable();
            $table->timestamp('souvenir_at')->nullable();
            $table->timestamp('selfie_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('invitation_id')
                  ->references('id')
                  ->on('invitations')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('guest_id')
                  ->references('id')
                  ->on('guests')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_guests');
    }
};
