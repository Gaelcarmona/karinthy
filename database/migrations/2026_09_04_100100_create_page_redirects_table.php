<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les redirections de l'espace principal (~1,5 M) : « Tour Eiffel (Paris) »
 * doit trouver l'article « Tour Eiffel ».
 *
 * Table séparée de `pages` pour que celle-ci ne contienne que de vrais
 * articles — c'est elle que parcourt la recherche de chemins.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_redirects', function (Blueprint $table) {
            // page_id de la page de redirection elle-même.
            $table->unsignedInteger('id')->primary();

            $table->string('title', 255);

            // Article visé, dans `pages`. Volontairement sans clé étrangère :
            // une redirection peut viser une page inexistante ou d'un autre
            // espace, et l'import écrit des millions de lignes d'un coup.
            $table->unsignedInteger('page_id');

            $table->unique('title');
            $table->index('page_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_redirects');
    }
};
