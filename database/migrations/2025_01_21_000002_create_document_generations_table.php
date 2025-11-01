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
        Schema::create('document_generations', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('document_template_id')->constrained('document_templates')->onDelete('cascade');
            $table->foreignId('dossier_id')->nullable()->constrained('dossiers')->onDelete('cascade');
            $table->foreignId('dossier_validation_id')->nullable()->constrained('dossier_validations')->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');
            
            // Identifiant unique du document
            $table->string('numero_document', 100)->unique()->comment('Numéro unique du document généré');
            $table->string('type_document', 100)->comment('Type de document (recepisse_provisoire, etc.)');
            
            // QR Code (juste le token, pas l'image)
            $table->string('qr_code_token')->unique()->comment('Token pour vérification publique');
            $table->string('qr_code_url')->comment('URL de vérification complète');
            
            // Hash de vérification d'intégrité
            $table->string('hash_verification')->comment('Hash SHA-256 des données du document');
            
            // Variables utilisées (pour régénération)
            $table->json('variables_data')->comment('Snapshot des données utilisées pour la génération');
            
            // Métadonnées de génération
            $table->foreignId('generated_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('generated_at')->useCurrent();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            // Téléchargements
            $table->integer('download_count')->default(0)->comment('Nombre de téléchargements');
            $table->timestamp('last_downloaded_at')->nullable();
            $table->foreignId('last_downloaded_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Statut du document (pour invalidation)
            $table->boolean('is_valid')->default(true)->comment('Document valide ou invalidé');
            $table->timestamp('invalidated_at')->nullable();
            $table->foreignId('invalidated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('invalidation_reason')->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['organisation_id', 'type_document'], 'idx_org_type');
            $table->index(['numero_document'], 'idx_numero');
            $table->index(['qr_code_token'], 'idx_qr_token');
            $table->index(['is_valid', 'type_document'], 'idx_valid_type');
            $table->index(['generated_at'], 'idx_generated_date');
            $table->index(['dossier_id'], 'idx_dossier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_generations');
    }
};