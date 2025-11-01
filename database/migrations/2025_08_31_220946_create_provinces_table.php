<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique()->comment('Nom de la province');
            $table->string('code', 10)->unique()->comment('Code unique de la province (ex: EST, LIT, etc.)');
            $table->string('chef_lieu')->nullable()->comment('Chef-lieu de la province');
            $table->text('description')->nullable()->comment('Description de la province');
            
            // Données géographiques
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            
            // Métadonnées
            $table->json('metadata')->nullable()->comment('Données supplémentaires (frontières, limites, etc.)');
            $table->boolean('is_active')->default(true)->comment('Province active ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            
            $table->timestamps();
            
            // Index
            $table->index(['is_active', 'ordre_affichage']);
            $table->index('nom');
            $table->index('code');
        });
        
        // Commentaire de la table
        DB::statement("ALTER TABLE `provinces` COMMENT = 'Table des provinces du Gabon - Niveau 1 de la hiérarchie géographique'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provinces');
    }
};