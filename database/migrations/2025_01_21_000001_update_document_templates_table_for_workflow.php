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
        Schema::table('document_templates', function (Blueprint $table) {
            // Supprimer les colonnes obsolètes si elles existent
            if (Schema::hasColumn('document_templates', 'template_content')) {
                $table->dropColumn('template_content');
            }
            if (Schema::hasColumn('document_templates', 'type_organisation')) {
                $table->dropColumn('type_organisation');
            }
            
            // ⭐ DIMENSION 2 : Type d'opération
            $table->foreignId('operation_type_id')
                ->nullable()
                ->after('organisation_type_id')
                ->constrained('operation_types')
                ->onDelete('cascade')
                ->comment('Type d\'opération concernée (création, modification, etc.)');
            
            // ⭐ DIMENSION 3 : Étape du workflow
            $table->foreignId('workflow_step_id')
                ->nullable()
                ->after('operation_type_id')
                ->constrained('workflow_steps')
                ->onDelete('cascade')
                ->comment('Étape du workflow qui déclenche la génération');
            
            // Chemin vers le template Blade
            $table->string('template_path')
                ->after('description')
                ->comment('Chemin Blade (ex: documents.templates.association.recepisse)');
            
            // Layout parent (optionnel)
            $table->string('layout_path')
                ->nullable()
                ->default('documents.layouts.official')
                ->after('template_path')
                ->comment('Layout parent Blade');
            
            // Variables requises (pour validation)
            $table->json('required_variables')
                ->nullable()
                ->after('variables')
                ->comment('Variables obligatoires pour générer le document');
            
            // Configuration PDF
            $table->json('pdf_config')
                ->nullable()
                ->after('required_variables')
                ->comment('Config PDF : format, orientation, marges');
            
            // Génération automatique
            $table->boolean('auto_generate')
                ->default(false)
                ->after('is_active')
                ->comment('Générer automatiquement à l\'étape du workflow');
            
            // Délai de génération (en heures après l'étape)
            $table->integer('generation_delay_hours')
                ->nullable()
                ->default(0)
                ->after('auto_generate')
                ->comment('Délai en heures après l\'étape avant génération auto');
            
            // Index composites pour recherche efficace
            $table->index(['organisation_type_id', 'operation_type_id', 'workflow_step_id'], 'idx_template_triplet');
            $table->index(['type_document', 'is_active'], 'idx_type_active');
            $table->index(['auto_generate', 'workflow_step_id'], 'idx_auto_workflow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropForeign(['operation_type_id']);
            $table->dropForeign(['workflow_step_id']);
            $table->dropIndex('idx_template_triplet');
            $table->dropIndex('idx_type_active');
            $table->dropIndex('idx_auto_workflow');
            
            $table->dropColumn([
                'operation_type_id',
                'workflow_step_id',
                'template_path',
                'layout_path',
                'required_variables',
                'pdf_config',
                'auto_generate',
                'generation_delay_hours'
            ]);
            
            // Remettre les anciennes colonnes
            $table->text('template_content')->after('description');
            $table->enum('type_organisation', ['association', 'ong', 'parti_politique', 'confession_religieuse'])->nullable();
        });
    }
};