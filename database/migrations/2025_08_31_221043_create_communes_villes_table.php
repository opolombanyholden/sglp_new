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
        Schema::create('communes_villes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departement_id')->constrained('departements')->onDelete('cascade')->comment('Référence vers le département');
            $table->string('nom')->comment('Nom de la commune ou ville');
            $table->string('code', 20)->comment('Code unique de la commune/ville');
            $table->enum('type', ['commune', 'ville'])->default('commune')->comment('Type: commune ou ville');
            $table->string('statut')->nullable()->comment('Statut administratif (commune urbaine, ville, etc.)');
            $table->text('description')->nullable()->comment('Description de la commune/ville');
            
            // Données géographiques
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            
            // Informations municipales
            $table->string('maire')->nullable()->comment('Nom du maire en exercice');
            $table->date('date_creation')->nullable()->comment('Date de création de la commune/ville');
            $table->string('telephone')->nullable()->comment('Téléphone de la mairie');
            $table->string('email')->nullable()->comment('Email de la mairie');
            $table->string('site_web')->nullable()->comment('Site web officiel');
            
            // Métadonnées
            $table->json('metadata')->nullable()->comment('Données supplémentaires (services, équipements, etc.)');
            $table->boolean('is_active')->default(true)->comment('Commune/ville active ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            
            $table->timestamps();
            
            // Index composé pour éviter les doublons nom + département
            $table->unique(['departement_id', 'nom'], 'unique_commune_par_dept');
            $table->unique(['departement_id', 'code'], 'unique_code_commune_par_dept');
            
            // Index pour les requêtes
            $table->index(['departement_id', 'is_active', 'ordre_affichage']);
            $table->index(['type', 'is_active']);
            $table->index('nom');
            $table->index('code');
        });
        
        // Commentaire de la table
        DB::statement("ALTER TABLE `communes_villes` COMMENT = 'Table des communes et villes du Gabon - Niveau 3 URBAIN, rattachées aux départements'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('communes_villes');
    }
};