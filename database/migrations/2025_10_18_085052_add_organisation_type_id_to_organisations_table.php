<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * MIGRATION : Ajout de organisation_type_id à la table organisations
 * 
 * Convertit le champ ENUM 'type' vers une FK 'organisation_type_id'
 * 
 * Projet : SGLP
 * Compatible : PHP 8.3, Laravel 10+
 */
return new class extends Migration
{
    /**
     * Exécuter la migration
     */
    public function up(): void
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════\n";
        echo "  MIGRATION : type (ENUM) → organisation_type_id (FK)\n";
        echo "═══════════════════════════════════════════════════════\n\n";

        // ========================================
        // ÉTAPE 1 : Ajouter la nouvelle colonne
        // ========================================
        
        echo "📝 ÉTAPE 1 : Création de la colonne organisation_type_id...\n";
        
        Schema::table('organisations', function (Blueprint $table) {
            $table->foreignId('organisation_type_id')
                ->nullable()
                ->after('user_id')
                ->comment('FK vers organisation_types (remplace ENUM type)');
            
            $table->index('organisation_type_id', 'idx_organisations_type_id');
        });

        echo "   ✅ Colonne créée\n\n";

        // ========================================
        // ÉTAPE 2 : Mapping ENUM → ID
        // ========================================
        
        echo "📊 ÉTAPE 2 : Récupération des mappings depuis organisation_types...\n\n";
        
        $mappings = DB::table('organisation_types')
            ->select('id', 'code')
            ->get()
            ->pluck('id', 'code')
            ->toArray();

        if (empty($mappings)) {
            echo "   ⚠️  ATTENTION : Aucun type trouvé dans organisation_types !\n";
            echo "   Assurez-vous d'avoir exécuté le seeder des types d'organisations.\n\n";
            return;
        }

        echo "   Mappings disponibles :\n";
        foreach ($mappings as $code => $id) {
            echo "      • {$code} → ID {$id}\n";
        }
        echo "\n";

        // ========================================
        // ÉTAPE 3 : Migrer les données
        // ========================================
        
        echo "🔄 ÉTAPE 3 : Migration des données existantes...\n\n";
        
        $totalUpdated = 0;
        $totalOrgs = DB::table('organisations')->count();
        
        echo "   Total d'organisations à migrer : {$totalOrgs}\n\n";

        foreach ($mappings as $enumValue => $typeId) {
            $count = DB::table('organisations')
                ->where('type', $enumValue)
                ->count();
            
            if ($count > 0) {
                $updated = DB::table('organisations')
                    ->where('type', $enumValue)
                    ->update(['organisation_type_id' => $typeId]);
                
                echo "   ✓ {$updated} organisation(s) '{$enumValue}' → type_id {$typeId}\n";
                $totalUpdated += $updated;
            }
        }

        echo "\n   ✅ {$totalUpdated} organisation(s) migrée(s) avec succès\n\n";

        // Vérifier les organisations non migrées
        $unmigrated = DB::table('organisations')
            ->whereNull('organisation_type_id')
            ->count();

        if ($unmigrated > 0) {
            echo "   ⚠️  ATTENTION : {$unmigrated} organisation(s) NON migrée(s)\n\n";
            
            $problematic = DB::table('organisations')
                ->whereNull('organisation_type_id')
                ->select('id', 'nom', 'type')
                ->get();
            
            echo "   Organisations problématiques :\n";
            foreach ($problematic as $org) {
                echo "      - ID {$org->id} : {$org->nom} (type: {$org->type})\n";
            }
            echo "\n";
            echo "   ❌ Migration interrompue : corrigez ces organisations avant de continuer.\n\n";
            return;
        }

        // ========================================
        // ÉTAPE 4 : Rendre la colonne obligatoire
        // ========================================
        
        echo "🔒 ÉTAPE 4 : Finalisation de la colonne...\n\n";
        
        Schema::table('organisations', function (Blueprint $table) {
            // Rendre NOT NULL
            $table->foreignId('organisation_type_id')
                ->nullable(false)
                ->change();
        });

        echo "   ✓ Colonne rendue obligatoire (NOT NULL)\n";

        // Ajouter la contrainte de clé étrangère
        Schema::table('organisations', function (Blueprint $table) {
            $table->foreign('organisation_type_id', 'fk_organisations_type')
                ->references('id')
                ->on('organisation_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        echo "   ✓ Clé étrangère créée\n\n";

        // ========================================
        // ÉTAPE 5 : Résumé et conseils
        // ========================================
        
        echo "═══════════════════════════════════════════════════════\n";
        echo "  ✅ MIGRATION TERMINÉE AVEC SUCCÈS\n";
        echo "═══════════════════════════════════════════════════════\n\n";
        
        echo "📊 Résumé :\n";
        echo "   • Colonne créée : organisation_type_id (bigint, FK)\n";
        echo "   • Organisations migrées : {$totalUpdated}/{$totalOrgs}\n";
        echo "   • Contrainte ajoutée : fk_organisations_type\n\n";
        
        echo "ℹ️  Prochaines étapes :\n";
        echo "   1. Testez l'application\n";
        echo "   2. Si tout fonctionne bien, supprimez la colonne 'type'\n";
        echo "   3. Utilisez : php artisan make:migration remove_type_enum_from_organisations\n\n";
        
        echo "⚠️  L'ancienne colonne 'type' (ENUM) est conservée pour sécurité.\n\n";
    }

    /**
     * Annuler la migration
     */
    public function down(): void
    {
        echo "\n";
        echo "═══════════════════════════════════════════════════════\n";
        echo "  ROLLBACK : Suppression de organisation_type_id\n";
        echo "═══════════════════════════════════════════════════════\n\n";
        
        Schema::table('organisations', function (Blueprint $table) {
            // Supprimer la clé étrangère
            $table->dropForeign('fk_organisations_type');
            echo "   ✓ Clé étrangère supprimée\n";
            
            // Supprimer l'index
            $table->dropIndex('idx_organisations_type_id');
            echo "   ✓ Index supprimé\n";
            
            // Supprimer la colonne
            $table->dropColumn('organisation_type_id');
            echo "   ✓ Colonne supprimée\n";
        });

        echo "\n⚠️  Les données devront être restaurées manuellement dans 'type'\n\n";
    }
};