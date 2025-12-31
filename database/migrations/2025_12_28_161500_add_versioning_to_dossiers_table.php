<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Ajoute les champs nécessaires pour le versioning des dossiers:
     * - parent_dossier_id: Référence au dossier parent pour traçabilité
     * - version: Numéro de version (1, 2, 3...)
     * - is_current_version: Indique si c'est la version active
     * - champs_modifies: Champs sélectionnés pour modification
     * - donnees_avant_modification: Snapshot des données avant opération
     */
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Référence au dossier parent pour le versioning
            $table->unsignedBigInteger('parent_dossier_id')->nullable()->after('organisation_id');
            $table->foreign('parent_dossier_id')
                ->references('id')
                ->on('dossiers')
                ->nullOnDelete();

            // Numéro de version (commence à 1)
            $table->unsignedInteger('version')->default(1)->after('parent_dossier_id');

            // Indicateur de version courante
            $table->boolean('is_current_version')->default(true)->after('version');

            // Champs sélectionnés pour modification (JSON)
            $table->json('champs_modifies')->nullable()->after('donnees_supplementaires')
                ->comment('Liste des champs sélectionnés pour modification');

            // Snapshot complet des données avant l'opération (JSON)
            $table->json('donnees_avant_modification')->nullable()->after('champs_modifies')
                ->comment('Snapshot des données avant opération pour historique');

            // Index pour optimiser les requêtes
            $table->index(['organisation_id', 'is_current_version'], 'idx_org_current_version');
            $table->index('parent_dossier_id', 'idx_parent_dossier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Supprimer les index
            $table->dropIndex('idx_org_current_version');
            $table->dropIndex('idx_parent_dossier');

            // Supprimer la foreign key
            $table->dropForeign(['parent_dossier_id']);

            // Supprimer les colonnes
            $table->dropColumn([
                'parent_dossier_id',
                'version',
                'is_current_version',
                'champs_modifies',
                'donnees_avant_modification'
            ]);
        });
    }
};
