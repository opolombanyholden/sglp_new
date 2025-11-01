<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ========================================
// MIGRATION 2 : Table historique priorité
// ========================================
// Fichier: database/migrations/2025_XX_XX_create_dossier_priority_history_table.php

class CreateDossierPriorityHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('dossier_priority_history', function (Blueprint $table) {
            $table->id();
            
            // Référence au dossier
            $table->unsignedBigInteger('dossier_id');
            
            // Changement de priorité
            $table->enum('ancien_niveau', ['normale', 'moyenne', 'haute', 'urgente']);
            $table->enum('nouveau_niveau', ['normale', 'moyenne', 'haute', 'urgente']);
            $table->text('justification')->nullable();
            
            // Traçabilité
            $table->unsignedBigInteger('changed_by');
            $table->timestamp('changed_at')->useCurrent();
            
            // Changement d'ordre
            $table->integer('ordre_avant')->nullable();
            $table->integer('ordre_apres')->nullable();
            
            // Index
            $table->index(['dossier_id', 'changed_at']);
            $table->index('changed_by');
            
            // Contraintes
            $table->foreign('dossier_id')
                  ->references('id')->on('dossiers')
                  ->onDelete('cascade');
            $table->foreign('changed_by')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dossier_priority_history');
    }
}
