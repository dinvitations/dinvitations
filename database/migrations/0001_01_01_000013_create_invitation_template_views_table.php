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
            $table->uuid('template_view_id')->nullable()->index();
            $table->uuid('file_id')->nullable();
            $table->enum('type', ['html', 'css', 'js', 'json'])->index();
            $table->longText('content')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('template_view_id')
                ->references('id')->on('template_views')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreign('file_id')
                ->references('id')->on('files')
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
