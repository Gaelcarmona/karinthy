<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Les articles de l'espace principal de fr.wikipedia (~2,78 M), hors
 * redirections. Une ligne par article, identifiée par son page_id Wikipédia.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            // page_id de Wikipédia : identifiant stable, imposé et non auto-incrémenté.
            // Les titres, eux, changent — c'est ce qui obligeait à dédoublonner.
            $table->unsignedInteger('id')->primary();

            $table->string('title', 255);

            // page_random de MediaWiki : permet de tirer un article au hasard en
            // O(log n) (where random >= x order by random limit 1) au lieu de
            // tenter des ids au hasard jusqu'à en trouver un qui existe.
            $table->double('random');

            // Degrés du nœud, pour ne pas avoir à décoder les blobs de liens.
            $table->unsignedMediumInteger('outbound_count')->default(0);
            $table->unsignedMediumInteger('inbound_count')->default(0);

            // Sert aussi la recherche par préfixe de l'autocomplétion.
            $table->unique('title');
            $table->index('random');
        });

        // Pas de timestamps : sur 2,78 M de lignes ils coûteraient ~50 Mo pour
        // une information que le dump ne fournit pas et dont on ne fait rien.
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
