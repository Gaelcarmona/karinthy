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
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('title', 766)->nullable(false);
            $table->string('url', 766)->nullable(false)->unique();
            $table->foreignId('redirect_to_entry_id')
            ->references('id')
            ->on('entries')
            ->constrained()
            ->onUpdate('cascade')
            ->onDelete('cascade')
            ->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
