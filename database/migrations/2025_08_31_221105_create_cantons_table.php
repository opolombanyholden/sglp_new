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
        Schema::create('cantons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departement_id')->constrained('departements')->onDelete('cascade')->comment('Référence vers le département');
            $table->string('nom')->comment('Nom du canton');
            $table->string('code', 25)->comment('Code unique du canton');
            $table->string('chef_lieu')->nullable()->comment('Chef-lieu du canton');
            $table->text('description')->nullable()->comment('Description du canton');
            
            // Données géographiques
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            
            // Informations administratives rurales
            $table->string('chef_canton')->nullable()->comment('Nom du chef de canton');
            $table->string('telephone_chef')->nullable()->comment('Téléphone du chef de canton');
            $table->string('telephone_administration')->nullable()->comment('Téléphone de l\'administration cantonale');
            $table->text('limites_geographiques')->nullable()->comment('Description des limites géographiques');
            
            // Caractéristiques rurales
            $table->json('ethnies_principales')->nullable()->comment('Ethnies principales présentes');
            $table->json('langues_parlees')->nullable()->comment('Langues parlées dans le canton');
            $table->json('activites_economiques')->nullable()->comment('Principales activités économiques');
            $table->json('ressources_naturelles')->nullable()->comment('Ressources naturelles disponibles');
            
            // Infrastructures et services
            $table->json('infrastructures')->nullable()->comment('Infrastructures disponibles (routes, ponts, etc.)');
            $table->json('services_publics')->nullable()->comment('Services publics disponibles');
            $table->boolean('acces_electricite')->default(false)->comment('Accès à l\'électricité');
            $table->boolean('acces_eau_potable')->default(false)->comment('Accès à l\'eau potable');
            $table->boolean('reseau_telephonique')->default(false)->comment('Couverture réseau téléphonique');
            
            // Métadonnées
            $table->json('metadata')->nullable()->comment('Données supplémentaires (climat, géologie, etc.)');
            $table->boolean('is_active')->default(true)->comment('Canton actif ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            
            $table->timestamps();
            
            // Index composé pour éviter les doublons nom + département
            $table->unique(['departement_id', 'nom'], 'unique_canton_par_dept');
            $table->unique(['departement_id', 'code'], 'unique_code_canton_par_dept');
            
            // Index pour les requêtes
            $table->index(['departement_id', 'is_active', 'ordre_affichage']);
            $table->index(['acces_electricite', 'acces_eau_potable']);
            $table->index('nom');
            $table->index('code');
        });
        
        // Commentaire de la table
        DB::statement("ALTER TABLE `cantons` COMMENT = 'Table des cantons du Gabon - Niveau 3 RURAL, rattachés aux départements'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cantons');
    }
};