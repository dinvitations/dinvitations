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
        Schema::create('invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('template_id')->nullable();
            $table->string('event_name')->index()->nullable();
            $table->string('organizer_name')->index()->nullable();
            $table->string('slug')->unique()->nullable();
            $table->timestamp('date_start')->nullable();
            $table->timestamp('date_end')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('message')->nullable();
            $table->text('location')->nullable();
            $table->string('location_latlng')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('order_id')
                  ->references('id')->on('orders')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->foreign('template_id')
                  ->references('id')->on('templates')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
