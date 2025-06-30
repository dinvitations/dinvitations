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
        Schema::create('feature_package', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('feature_id');
            $table->uuid('package_id');
            $table->timestamps();

            $table->foreign('feature_id')
                ->references('id')->on('features')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('package_id')
                ->references('id')->on('packages')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_package');
    }
};
