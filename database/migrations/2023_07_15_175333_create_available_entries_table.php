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
        Schema::create('available_entries', function (Blueprint $table) {
            $table->foreignId('parent_entry_id')
                ->references('id')
                ->on('entries')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade')
                ->index('fk_available_entries_parent');
    
            $table->foreignId('child_entry_id')
                ->references('id')
                ->on('entries')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade')
                ->index('fk_available_entries_child');
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('available_entries');
    }
};
