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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->string('title', 766)->nullable(false)->index();
            $table->string('url', 766)->nullable(false);
            $table->boolean('not_a_page')->nullable();
            $table->integer('redirect_to')->nullable();
            $table->json('paths')->nullable();
            $table->dateTime('treated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
