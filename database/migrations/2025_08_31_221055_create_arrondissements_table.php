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
        Schema::create('arrondissements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commune_ville_id')->constrained('communes_villes')->onDelete('cascade')->comment('Référence vers la commune/ville');
            $table->string('nom')->comment('Nom de l\'arrondissement');
            $table->string('code', 25)->comment('Code unique de l\'arrondissement');
            $table->integer('numero_arrondissement')->nullable()->comment('Numéro d\'ordre de l\'arrondissement (1er, 2ème, etc.)');
            $table->text('description')->nullable()->comment('Description de l\'arrondissement');
            
            // Données géographiques
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            
            // Informations administratives urbaines
            $table->string('delegue')->nullable()->comment('Nom du délégué d\'arrondissement');
            $table->string('telephone')->nullable()->comment('Téléphone de la délégation');
            $table->string('email')->nullable()->comment('Email de la délégation');
            $table->text('limites_geographiques')->nullable()->comment('Description des limites géographiques');
            
            // Services et équipements
            $table->json('services_publics')->nullable()->comment('Liste des services publics disponibles');
            $table->json('equipements')->nullable()->comment('Équipements disponibles (écoles, hôpitaux, etc.)');
            
            // Métadonnées
            $table->json('metadata')->nullable()->comment('Données supplémentaires (transport, commerce, etc.)');
            $table->boolean('is_active')->default(true)->comment('Arrondissement actif ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            
            $table->timestamps();
            
            // Index composé pour éviter les doublons nom + commune/ville
            $table->unique(['commune_ville_id', 'nom'], 'unique_arrond_par_commune');
            $table->unique(['commune_ville_id', 'code'], 'unique_code_arrond_par_commune');
            $table->unique(['commune_ville_id', 'numero_arrondissement'], 'unique_numero_arrond_par_commune');
            
            // Index pour les requêtes
            $table->index(['commune_ville_id', 'is_active', 'ordre_affichage']);
            $table->index(['numero_arrondissement', 'is_active']);
            $table->index('nom');
            $table->index('code');
        });
        
        // Commentaire de la table
        DB::statement("ALTER TABLE `arrondissements` COMMENT = 'Table des arrondissements urbains du Gabon - Niveau 4 URBAIN, rattachés aux communes/villes'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arrondissements');
    }
};