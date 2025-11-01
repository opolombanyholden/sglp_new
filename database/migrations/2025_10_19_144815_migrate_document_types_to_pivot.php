<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ========================================
        // ÉTAPE 1 : Migrer les données existantes vers les tables pivot
        // ========================================
        
        // Récupérer tous les document_types existants
        $documentTypes = DB::table('document_types')->get();

        foreach ($documentTypes as $docType) {
            // Migrer vers la table pivot organisation
            if (!empty($docType->type_organisation)) {
                // Trouver l'ID du type d'organisation
                $orgType = DB::table('organisation_types')
                    ->where('code', $docType->type_organisation)
                    ->first();

                if ($orgType) {
                    DB::table('document_type_organisation_type')->insert([
                        'document_type_id' => $docType->id,
                        'organisation_type_id' => $orgType->id,
                        'is_obligatoire' => $docType->is_required ?? false,
                        'ordre' => $docType->ordre ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Migrer vers la table pivot opération
            if (!empty($docType->type_operation)) {
                // Trouver l'ID du type d'opération
                $opType = DB::table('operation_types')
                    ->where('code', $docType->type_operation)
                    ->first();

                if ($opType) {
                    DB::table('document_type_operation_type')->insert([
                        'document_type_id' => $docType->id,
                        'operation_type_id' => $opType->id,
                        'is_obligatoire' => $docType->is_required ?? false,
                        'ordre' => $docType->ordre ?? 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // ========================================
        // ÉTAPE 2 : Supprimer les anciennes colonnes de document_types
        // ========================================
        Schema::table('document_types', function (Blueprint $table) {
            // Supprimer les colonnes devenues inutiles
            $table->dropColumn([
                'type_organisation',
                'type_operation',
                'is_required'
            ]);
        });

        // ========================================
        // ÉTAPE 3 : Modifier format_accepte pour stocker plusieurs formats
        // ========================================
        // La colonne format_accepte existe déjà et stocke déjà du CSV (ex: 'pdf,jpg,png')
        // Donc pas de modification nécessaire
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer les colonnes
        Schema::table('document_types', function (Blueprint $table) {
            $table->enum('type_organisation', [
                'association',
                'ong',
                'parti_politique',
                'confession_religieuse'
            ])->nullable()->after('description');
            
            $table->enum('type_operation', [
                'creation',
                'modification',
                'cessation',
                'ajout_adherent',
                'retrait_adherent',
                'declaration_activite',
                'changement_statutaire'
            ])->default('creation')->after('type_organisation');
            
            $table->boolean('is_required')->default(false)->after('is_active');
        });

        // Re-migrer les données depuis les pivots (optionnel, complexe)
        // On pourrait reconstruire les données mais c'est risqué
        // Mieux vaut une sauvegarde avant migration
    }
};