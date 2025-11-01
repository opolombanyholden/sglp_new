<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION - TABLE PIVOT ORGANISATION_TYPE_DOCUMENT_TYPE
 * 
 * Cette table pivot gère la relation Many-to-Many entre :
 * - organisation_types (types d'organisations)
 * - document_types (types de documents à fournir)
 * 
 * Elle permet de définir quels documents sont requis pour chaque type d'organisation
 * 
 * Projet : SGLP
 * Compatible : PHP 8.3, Laravel 10+, MySQL 5.7+
 */
return new class extends Migration
{
    /**
     * Exécuter la migration
     */
    public function up(): void
    {
        Schema::create('organisation_type_document_type', function (Blueprint $table) {
            // ========================================
            // CLÉS PRIMAIRES ET ÉTRANGÈRES
            // ========================================
            $table->id();
            
            $table->foreignId('organisation_type_id')
                ->constrained('organisation_types')
                ->onDelete('cascade')
                ->comment('ID du type d\'organisation');
            
            $table->foreignId('document_type_id')
                ->constrained('document_types')
                ->onDelete('cascade')
                ->comment('ID du type de document requis');
            
            // ========================================
            // PARAMÈTRES DE LA RELATION
            // ========================================
            $table->boolean('is_obligatoire')->default(true)
                ->comment('Document obligatoire (true) ou facultatif (false)');
            
            $table->unsignedInteger('ordre')->default(0)
                ->comment('Ordre d\'affichage du document dans la liste (0 = premier)');
            
            $table->text('aide_texte')->nullable()
                ->comment('Instructions ou aide spécifique pour ce document et ce type d\'organisation');
            
            $table->text('modele_texte')->nullable()
                ->comment('Texte modèle ou exemple pour aider à remplir le document');
            
            $table->string('exemple_fichier', 255)->nullable()
                ->comment('Chemin vers un fichier exemple (PDF, image)');
            
            // ========================================
            // MÉTADONNÉES
            // ========================================
            $table->json('regles_validation')->nullable()
                ->comment('Règles de validation spécifiques en JSON (taille max, formats, etc.)');
            
            $table->json('metadata')->nullable()
                ->comment('Données supplémentaires en JSON');
            
            // ========================================
            // TIMESTAMPS
            // ========================================
            $table->timestamps();
            
            // ========================================
            // INDEX ET CONTRAINTES
            // ========================================
            
            // Index unique : un document ne peut être lié qu'une seule fois à un type d'organisation
            $table->unique(
                ['organisation_type_id', 'document_type_id'], 
                'unique_org_type_doc_type'
            );
            
            // Index pour performance sur les requêtes fréquentes
            $table->index(['organisation_type_id', 'is_obligatoire'], 'idx_org_type_obligatoire');
            $table->index(['document_type_id', 'is_obligatoire'], 'idx_doc_type_obligatoire');
            $table->index('ordre', 'idx_ordre');
        });
    }

    /**
     * Annuler la migration
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_type_document_type');
    }
};