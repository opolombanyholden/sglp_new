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
        // Supprimer l'ancienne table si elle existe
        Schema::dropIfExists('document_verifications');
        
        Schema::create('document_verifications', function (Blueprint $table) {
            $table->id();
            
            // Relation vers document_generations
            $table->foreignId('document_generation_id')
                ->constrained('document_generations')
                ->onDelete('cascade');
            
            // Informations de vérification
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->json('geolocation')->nullable()->comment('Données de géolocalisation si disponibles');
            
            // Résultat de la vérification
            $table->boolean('verification_reussie')->default(true);
            $table->text('motif_echec')->nullable();
            
            $table->timestamp('verified_at')->useCurrent();
            $table->timestamps();
            
            // Index
            $table->index(['document_generation_id', 'verified_at'], 'idx_doc_date');
            $table->index(['ip_address'], 'idx_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_verifications');
    }
};