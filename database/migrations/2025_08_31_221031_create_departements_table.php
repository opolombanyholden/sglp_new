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
        Schema::create('departements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->onDelete('cascade')->comment('Référence vers la province');
            $table->string('nom')->comment('Nom du département');
            $table->string('code', 15)->comment('Code unique du département');
            $table->string('chef_lieu')->nullable()->comment('Chef-lieu du département');
            $table->text('description')->nullable()->comment('Description du département');
            
            // Données géographiques
            $table->decimal('superficie_km2', 10, 2)->nullable()->comment('Superficie en km²');
            $table->integer('population_estimee')->nullable()->comment('Population estimée');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude du centre géographique');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude du centre géographique');
            
            // Métadonnées
            $table->json('metadata')->nullable()->comment('Données supplémentaires (frontières, limites, etc.)');
            $table->boolean('is_active')->default(true)->comment('Département actif ou non');
            $table->integer('ordre_affichage')->default(0)->comment('Ordre d\'affichage dans les listes');
            
            $table->timestamps();
            
            // Index composé pour éviter les doublons nom + province
            $table->unique(['province_id', 'nom'], 'unique_dept_par_province');
            $table->unique(['province_id', 'code'], 'unique_code_dept_par_province');
            
            // Index pour les requêtes
            $table->index(['province_id', 'is_active', 'ordre_affichage']);
            $table->index('nom');
            $table->index('code');
        });
        
        // Commentaire de la table
        DB::statement("ALTER TABLE `departements` COMMENT = 'Table des départements du Gabon - Niveau 2 de la hiérarchie géographique, rattachés aux provinces'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('departements');
    }
};