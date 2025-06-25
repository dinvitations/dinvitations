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
        Schema::create('templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('package_id')->nullable();
            $table->uuid('event_id')->nullable();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->string('preview_url')->nullable();
            $table->timestamps();

            $table->foreign('package_id')
                  ->references('id')->on('packages')
                  ->onUpdate('cascade')
                  ->onDelete('set null');

            $table->foreign('event_id')
                  ->references('id')->on('events')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
