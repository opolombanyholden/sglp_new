<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dossier_validations', function (Blueprint $table) {
            // Ajouter référence vers document_generations
            $table->foreignId('document_generation_id')
                ->nullable()
                ->after('document_genere')
                ->constrained('document_generations')
                ->onDelete('set null')
                ->comment('Référence au document généré (métadonnées)');
            
            // Note : On garde document_genere pour compatibilité ascendante
            // mais il ne sera plus utilisé pour les nouveaux documents
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossier_validations', function (Blueprint $table) {
            $table->dropForeign(['document_generation_id']);
            $table->dropColumn('document_generation_id');
        });
    }
};