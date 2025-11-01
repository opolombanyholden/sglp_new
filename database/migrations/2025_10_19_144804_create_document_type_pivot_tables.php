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
        // ========================================
        // TABLE PIVOT : document_type_organisation_type
        // ========================================
        Schema::create('document_type_organisation_type', function (Blueprint $table) {
            $table->id();
            
            // Clés étrangères
            $table->foreignId('document_type_id')
                ->constrained('document_types')
                ->onDelete('cascade')
                ->comment('Référence au type de document');
            
            $table->foreignId('organisation_type_id')
                ->constrained('organisation_types')
                ->onDelete('cascade')
                ->comment('Référence au type d\'organisation');
            
            // Colonnes pivot
            $table->boolean('is_obligatoire')->default(false)->comment('Document obligatoire pour ce type d\'organisation');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage pour ce type d\'organisation');
            
            $table->timestamps();

            // Index composés pour éviter les doublons
            $table->unique(['document_type_id', 'organisation_type_id'], 'doc_org_unique');
            
            // Index pour les recherches
            $table->index('is_obligatoire');
            $table->index('ordre');
        });

        // ========================================
        // TABLE PIVOT : document_type_operation_type
        // ========================================
        Schema::create('document_type_operation_type', function (Blueprint $table) {
            $table->id();
            
            // Clés étrangères
            $table->foreignId('document_type_id')
                ->constrained('document_types')
                ->onDelete('cascade')
                ->comment('Référence au type de document');
            
            $table->foreignId('operation_type_id')
                ->constrained('operation_types')
                ->onDelete('cascade')
                ->comment('Référence au type d\'opération');
            
            // Colonnes pivot
            $table->boolean('is_obligatoire')->default(false)->comment('Document obligatoire pour ce type d\'opération');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage pour ce type d\'opération');
            
            $table->timestamps();

            // Index composés pour éviter les doublons
            $table->unique(['document_type_id', 'operation_type_id'], 'doc_op_unique');
            
            // Index pour les recherches
            $table->index('is_obligatoire');
            $table->index('ordre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_type_operation_type');
        Schema::dropIfExists('document_type_organisation_type');
    }
};