<?php

// ========================================
// MIGRATION 1 : Ajout colonnes FIFO + Priorité
// ========================================
// Fichier: database/migrations/2025_XX_XX_add_fifo_priority_to_dossiers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFifoPriorityToDossiersTable extends Migration
{
    public function up()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Colonne pour l'ordre de traitement FIFO
            $table->integer('ordre_traitement')->default(0)->after('statut');
            
            // Gestion de la priorité
            $table->boolean('priorite_urgente')->default(false)->after('ordre_traitement');
            $table->enum('priorite_niveau', ['normale', 'moyenne', 'haute', 'urgente'])
                  ->default('normale')->after('priorite_urgente');
            $table->text('priorite_justification')->nullable()->after('priorite_niveau');
            
            // Traçabilité de l'assignation de priorité
            $table->unsignedBigInteger('priorite_assignee_par')->nullable()->after('priorite_justification');
            $table->timestamp('priorite_assignee_at')->nullable()->after('priorite_assignee_par');
            
            // Colonne pour les instructions à l'agent
            $table->text('instructions_agent')->nullable()->after('priorite_assignee_at');
            
            // Index pour optimiser les requêtes FIFO
            $table->index(['statut', 'priorite_urgente', 'ordre_traitement'], 'idx_fifo_queue');
            $table->index(['priorite_niveau', 'created_at'], 'idx_priorite_niveau');
            
            // Contrainte de clé étrangère
            $table->foreign('priorite_assignee_par')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropForeign(['priorite_assignee_par']);
            $table->dropIndex('idx_fifo_queue');
            $table->dropIndex('idx_priorite_niveau');
            
            $table->dropColumn([
                'ordre_traitement',
                'priorite_urgente',
                'priorite_niveau',
                'priorite_justification',
                'priorite_assignee_par',
                'priorite_assignee_at',
                'instructions_agent'
            ]);
        });
    }
}