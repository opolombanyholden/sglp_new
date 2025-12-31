<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration pour ajouter 'annule' à l'ENUM statut des dossiers
 * 
 * Cette migration permet l'annulation des dossiers avec soft delete
 */
class AddAnnuleToDossiersStatutEnum extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter 'annule' à l'ENUM statut
        DB::statement("ALTER TABLE dossiers MODIFY COLUMN statut ENUM('brouillon','soumis','en_cours','approuve','rejete','accepte','archive','annule') NOT NULL DEFAULT 'brouillon'");

        \Log::info('Migration ENUM statut appliquée - Ajout de "annule"', [
            'table' => 'dossiers',
            'column' => 'statut',
            'new_value' => 'annule'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Retour à l'ENUM sans 'annule'
        DB::statement("ALTER TABLE dossiers MODIFY COLUMN statut ENUM('brouillon','soumis','en_cours','approuve','rejete','accepte','archive') NOT NULL DEFAULT 'brouillon'");

        \Log::info('Rollback migration ENUM statut - Suppression de "annule"');
    }
}
