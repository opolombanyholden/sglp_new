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
        Schema::create('regroupements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canton_id')->constrained('cantons')->onDelete('cascade')->comment('Référence vers le canton');
            $table->string('nom')->comment('Nom du regroupement');
            $table->string('code', 30)->comment('Code unique du regroupement');
            $table->string('village_centre')->nullable()->comment('Village centre du regroupement');
            $table->text('description')->nullable()->comment('Description du regroupement');
            
            // Données géographiques
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            
            // Informations administratives
            $table->string('chef_regroupement')->nullable()->comment('Nom du chef de regroupement');
            $table->string('telephone_chef')->nullable()->comment('Téléphone du chef de regroupement');
            $table->integer('nombre_villages')->default(0)->comment('Nombre de villages dans le regroupement');
            $table->text('villages_composants')->nullable()->comment('Liste des villages composant le regroupement');
            
            // Caractéristiques socio-économiques
            $table->json('ethnies_dominantes')->nullable()->comment('Ethnies dominantes dans le regroupement');
            $table->json('langues_courantes')->nullable()->comment('Langues couramment parlées');
            $table->json('activites_principales')->nullable()->comment('Activités économiques principales');
            $table->json('cultures_pratiquees')->nullable()->comment('Cultures agricoles pratiquées');
            $table->json('elevage_pratique')->nullable()->comment('Types d\'élevage pratiqués');
            
            // Infrastructures et services
            $table->boolean('ecole_primaire')->default(false)->comment('Présence d\'école primaire');
            $table->boolean('ecole_secondaire')->default(false)->comment('Présence d\'école secondaire');
            $table->boolean('centre_sante')->default(false)->comment('Présence de centre de santé');
            $table->boolean('marche')->default(false)->comment('Présence de marché');
            $table->boolean('route_praticable')->default(false)->comment('Route praticable toute l\'année');
            $table->boolean('transport_commun')->default(false)->comment('Transport en commun disponible');
            
            // Accès aux services
            $table->enum('acces_eau', ['puits', 'forage', 'riviere', 'source', 'adduction', 'aucun'])->nullable()->comment('Type d\'accès à l\'eau');
            $table->boolean('electricite_disponible')->default(false)->comment('Électricité disponible');
            $table->enum('couverture_reseau', ['aucune', 'faible', 'moyenne', 'bonne'])->default('aucune')->comment('Couverture réseau téléphonique');
            
            // Métadonnées
            $table->json('metadata')->nullable()->comment('Données supplémentaires (traditions, événements, etc.)');
            $table->boolean('is_active')->default(true)->comment('Regroupement actif ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            
            $table->timestamps();
            
            // Index composé pour éviter les doublons nom + canton
            $table->unique(['canton_id', 'nom'], 'unique_regroupement_par_canton');
            $table->unique(['canton_id', 'code'], 'unique_code_regroupement_par_canton');
            
            // Index pour les requêtes
            $table->index(['canton_id', 'is_active', 'ordre_affichage']);
            $table->index(['ecole_primaire', 'centre_sante']);
            $table->index(['route_praticable', 'transport_commun']);
            $table->index('nom');
            $table->index('code');
        });
        
        // Commentaire de la table
        DB::statement("ALTER TABLE `regroupements` COMMENT = 'Table des regroupements ruraux du Gabon - Niveau 4 RURAL, rattachés aux cantons'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regroupements');
    }
};