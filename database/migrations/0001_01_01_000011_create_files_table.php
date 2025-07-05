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
        Schema::create('files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('fileable_type')->nullable()->index();
            $table->uuid('fileable_id')->nullable()->index();
            $table->string('name');
            $table->string('original_name')->nullable();
            $table->string('filename');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('extension')->nullable();
            $table->enum('type', ['image', 'document', 'video', 'audio', 'other']);
            $table->bigInteger('size')->nullable();
            $table->string('mime_type')->nullable();
            $table->enum('status', ['uploaded', 'processing', 'completed', 'failed'])->default('uploaded');
            $table->enum('visibility', ['public', 'private'])->default('public');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['fileable_type', 'fileable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
