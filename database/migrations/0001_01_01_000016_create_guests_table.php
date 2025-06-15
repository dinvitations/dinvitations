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
        Schema::create('guests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('guest_group_id')->nullable()->index();
            $table->string('name')->index();
            $table->string('phone_number')->nullable();
            $table->enum('type_default', ['reg', 'vip', 'vvip'])->default('reg');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('guest_group_id')
                ->references('id')->on('guest_groups')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
