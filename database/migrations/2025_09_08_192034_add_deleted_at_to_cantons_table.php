<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration pour ajouter la colonne deleted_at à la table cantons
 * Commande de création : php artisan make:migration add_deleted_at_to_cantons_table
 * Commande d'exécution : php artisan migrate
 */
class AddDeletedAtToCantonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cantons', function (Blueprint $table) {
            $table->softDeletes(); // Ajoute la colonne deleted_at TIMESTAMP NULL
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cantons', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Supprime la colonne deleted_at
        });
    }
}