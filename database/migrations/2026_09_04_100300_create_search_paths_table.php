<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cache des recherches déjà effectuées.
 *
 * `degree` à null mémorise aussi les recherches sans résultat : ce sont les
 * plus coûteuses, il serait absurde de les relancer à chaque fois.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_paths', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('start_page_id');
            $table->unsignedInteger('end_page_id');

            // Nombre de liens entre les deux pages ; null si aucun chemin.
            $table->unsignedTinyInteger('degree')->nullable();

            // Les chaînes d'ids intermédiaires trouvées, une par chemin.
            $table->json('chains')->nullable();

            $table->timestamps();

            $table->unique(['start_page_id', 'end_page_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_paths');
    }
};
