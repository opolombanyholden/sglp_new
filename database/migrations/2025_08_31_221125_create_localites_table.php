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
        Schema::create('localites', function (Blueprint $table) {
            $table->id();
            
            // Relations polymorphes selon le type
            $table->foreignId('arrondissement_id')->nullable()->constrained('arrondissements')->onDelete('cascade')->comment('Référence vers arrondissement (zone urbaine)');
            $table->foreignId('regroupement_id')->nullable()->constrained('regroupements')->onDelete('cascade')->comment('Référence vers regroupement (zone rurale)');
            
            $table->enum('type', ['quartier', 'village'])->comment('Type: quartier (urbain) ou village (rural)');
            $table->string('nom')->comment('Nom du quartier ou village');
            $table->string('code', 35)->comment('Code unique de la localité');
            $table->text('description')->nullable()->comment('Description de la localité');
            
            // Données géographiques
            $table->decimal('superficie_km2', 8, 4)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude');
            $table->decimal('altitude_m', 8, 2)->nullable()->comment('Altitude en mètres');
            
            // Informations selon le type (quartier ou village)
            $table->string('responsable_nom')->nullable()->comment('Nom du chef de quartier/village');
            $table->string('responsable_telephone')->nullable()->comment('Téléphone du responsable');
            $table->string('responsable_email')->nullable()->comment('Email du responsable');
            
            // Caractéristiques socio-culturelles
            $table->json('ethnies_presentes')->nullable()->comment('Ethnies présentes dans la localité');
            $table->json('langues_parlees')->nullable()->comment('Langues parlées');
            $table->json('religions_pratiquees')->nullable()->comment('Religions pratiquées');
            
            // Activités économiques
            $table->json('activites_economiques')->nullable()->comment('Activités économiques principales');
            $table->json('commerces_presents')->nullable()->comment('Types de commerces présents');
            $table->json('services_artisanaux')->nullable()->comment('Services artisanaux disponibles');
            
            // Infrastructures et équipements
            $table->boolean('ecole_maternelle')->default(false)->comment('École maternelle');
            $table->boolean('ecole_primaire')->default(false)->comment('École primaire');
            $table->boolean('college')->default(false)->comment('Collège');
            $table->boolean('lycee')->default(false)->comment('Lycée');
            $table->boolean('centre_sante')->default(false)->comment('Centre de santé');
            $table->boolean('pharmacie')->default(false)->comment('Pharmacie');
            $table->boolean('marche')->default(false)->comment('Marché');
            $table->boolean('eglise')->default(false)->comment('Église');
            $table->boolean('mosquee')->default(false)->comment('Mosquée');
            $table->boolean('terrain_sport')->default(false)->comment('Terrain de sport');
            
            // Accès et services
            $table->enum('type_route', ['bitumee', 'laterite', 'piste', 'sentier', 'aucune'])->nullable()->comment('Type de route d\'accès');
            $table->boolean('transport_public')->default(false)->comment('Transport public disponible');
            $table->enum('acces_eau', ['robinet', 'borne_fontaine', 'puits', 'forage', 'riviere', 'source', 'aucun'])->nullable()->comment('Type d\'accès à l\'eau');
            $table->boolean('electricite')->default(false)->comment('Électricité disponible');
            $table->enum('assainissement', ['tout_a_legout', 'fosse_septique', 'latrine', 'aucun'])->nullable()->comment('Type d\'assainissement');
            $table->enum('gestion_dechets', ['collecte_municipale', 'enfouissement', 'incineration', 'aucune'])->nullable()->comment('Gestion des déchets');
            
            // Communication
            $table->enum('couverture_mobile', ['aucune', 'faible', 'moyenne', 'bonne', 'excellente'])->default('aucune')->comment('Couverture réseau mobile');
            $table->boolean('internet_disponible')->default(false)->comment('Internet disponible');
            $table->boolean('radio_communautaire')->default(false)->comment('Radio communautaire');
            
            // Sécurité
            $table->boolean('poste_police')->default(false)->comment('Poste de police/gendarmerie');
            $table->boolean('eclairage_public')->default(false)->comment('Éclairage public');
            $table->enum('niveau_securite', ['tres_faible', 'faible', 'moyen', 'bon', 'excellent'])->default('moyen')->comment('Niveau de sécurité perçu');
            
            // Métadonnées
            $table->json('evenements_culturels')->nullable()->comment('Événements culturels annuels');
            $table->json('problemes_recurrents')->nullable()->comment('Problèmes récurrents identifiés');
            $table->json('projets_developpement')->nullable()->comment('Projets de développement en cours/prévus');
            $table->json('metadata')->nullable()->comment('Autres données supplémentaires');
            
            $table->boolean('is_active')->default(true)->comment('Localité active ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            
            $table->timestamps();
            
            // Index composés selon le type
            $table->unique(['arrondissement_id', 'nom'], 'unique_quartier_par_arrond');
            $table->unique(['regroupement_id', 'nom'], 'unique_village_par_regroupement');
            $table->unique(['arrondissement_id', 'code'], 'unique_code_quartier_par_arrond');
            $table->unique(['regroupement_id', 'code'], 'unique_code_village_par_regroupement');
            
            // Index pour les requêtes
            $table->index(['type', 'is_active', 'ordre_affichage']);
            $table->index(['arrondissement_id', 'type', 'is_active']);
            $table->index(['regroupement_id', 'type', 'is_active']);
            $table->index(['electricite', 'transport_public']);
            $table->index(['ecole_primaire', 'centre_sante']);
            $table->index('nom');
            $table->index('code');
        });
        
        // Contrainte CHECK pour assurer la cohérence type/relation
        DB::statement("ALTER TABLE `localites` ADD CONSTRAINT `chk_localite_coherence` CHECK (
            (arrondissement_id IS NOT NULL AND regroupement_id IS NULL AND type = 'quartier') OR 
            (arrondissement_id IS NULL AND regroupement_id IS NOT NULL AND type = 'village')
        )");
        
        // Commentaire de la table
        DB::statement("ALTER TABLE `localites` COMMENT = 'Table unifiée des quartiers (urbain) et villages (rural) du Gabon - Niveau 5 FINAL de la hiérarchie géographique'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('localites');
    }
};