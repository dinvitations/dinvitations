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
        Schema::create('invitation_template_views', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invitation_id')->index();
            $table->uuid('template_id')->nullable()->index();
            $table->uuid('file_id')->nullable();
            $table->string('type')->index();
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->foreign('template_id')
                ->references('id')
                ->on('templates')
                ->onDelete('cascade');

            $table->foreign('file_id')
                ->references('id')
                ->on('files')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_template_views');
    }
};
