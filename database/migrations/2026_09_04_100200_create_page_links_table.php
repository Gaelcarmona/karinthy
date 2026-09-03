<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Listes d'adjacence du graphe, dans les deux sens.
 *
 * Les ids visés sont stockés packés en binaire (uint32 little-endian), pas en
 * JSON : 4 octets par lien au lieu d'environ 7 caractères, et la relecture par
 * unpack() se fait en C là où json_decode() pesait ~40 % du temps de parcours.
 *
 * La clé primaire commence par la direction : un parcours en largeur ne lit
 * qu'un sens à la fois, et les lignes de ce sens sont alors contiguës sur le
 * disque. Avoir aussi les liens entrants permet un BFS bidirectionnel, qui se
 * rejoint au milieu au lieu de déplier tout le graphe dans un seul sens.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_links', function (Blueprint $table) {
            $table->unsignedTinyInteger('direction'); // 0 = sortants, 1 = entrants
            $table->unsignedInteger('page_id');
            $table->unsignedMediumInteger('count');
            $table->binary('targets');

            $table->primary(['direction', 'page_id']);
        });

        // binary() donne un BLOB, plafonné à 16 383 liens. Les liens entrants
        // d'un article très cité en comptent bien davantage.
        DB::statement('ALTER TABLE `page_links` MODIFY `targets` MEDIUMBLOB NOT NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('page_links');
    }
};
