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
        Schema::create('paths', function (Blueprint $table) {
            $table->id();

            $table->foreignId('start_entry_id')
            ->references('id')
            ->on('entries')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade')
            ->index('fk_paths_start');

            $table->json('data')->nullable();

            $table->foreignId('end_entry_id')
            ->references('id')
            ->on('entries')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade')
            ->index('fk_paths_end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paths');
    }
};
