<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MIGRATION - TABLE ORGANISATION_TYPES
 * 
 * Cette table centralise la gestion des types d'organisations
 * avec leurs règles métier, documents requis et templates
 * 
 * Projet : SGLP - Système de Gestion des Libertés Publiques
 * Compatible : PHP 8.3, Laravel 10+, MySQL 5.7+
 */
return new class extends Migration
{
    /**
     * Exécuter la migration
     */
    public function up(): void
    {
        Schema::create('organisation_types', function (Blueprint $table) {
            // ========================================
            // IDENTIFICATION
            // ========================================
            $table->id(); // PHP 8.3 : Utilise id() au lieu de bigIncrements()
            
            $table->string('code', 50)->unique()
                ->comment('Code unique : association, ong, parti_politique, confession_religieuse');
            
            $table->string('nom', 150)
                ->comment('Nom complet affiché (ex: Association)');
            
            $table->text('description')->nullable()
                ->comment('Description détaillée du type d\'organisation');
            
            $table->string('couleur', 7)->default('#007bff')
                ->comment('Couleur hex pour l\'interface (#007bff, #28a745, etc.)');
            
            $table->string('icone', 50)->nullable()
                ->comment('Classe d\'icône Font Awesome (ex: fa-users, fa-building)');
            
            // ========================================
            // RÈGLES MÉTIER
            // ========================================
            $table->boolean('is_lucratif')->default(false)
                ->comment('But lucratif (true) ou non lucratif (false)');
            
            $table->unsignedInteger('nb_min_fondateurs_majeurs')->default(2)
                ->comment('Nombre minimum de fondateurs majeurs requis à la création');
            
            $table->unsignedInteger('nb_min_adherents_creation')->default(10)
                ->comment('Nombre minimum d\'adhérents requis à la création');
            
            // ========================================
            // GUIDES ET LÉGISLATION
            // ========================================
            $table->text('guide_creation')->nullable()
                ->comment('Guide explicatif de création (format Markdown ou HTML)');
            
            $table->text('texte_legislatif')->nullable()
                ->comment('Texte de loi ou référence législative applicable');
            
            $table->string('loi_reference', 100)->nullable()
                ->comment('Référence de la loi (ex: Loi 016/2025 du 15 octobre)');
            
            // ========================================
            // MÉTADONNÉES ET GESTION
            // ========================================
            $table->boolean('is_active')->default(true)
                ->comment('Type actif ou désactivé');
            
            $table->unsignedInteger('ordre')->default(0)
                ->comment('Ordre d\'affichage dans les listes (0 = premier)');
            
            $table->json('metadata')->nullable()
                ->comment('Données supplémentaires en JSON (config avancée)');
            
            // ========================================
            // TIMESTAMPS
            // ========================================
            $table->timestamps();
            $table->softDeletes()
                ->comment('Suppression douce (soft delete)');
            
            // ========================================
            // INDEX POUR PERFORMANCE
            // ========================================
            $table->index(['is_active', 'ordre'], 'idx_active_ordre');
            $table->index('code', 'idx_code');
        });
    }

    /**
     * Annuler la migration
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_types');
    }
};