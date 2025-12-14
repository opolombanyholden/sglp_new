<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_generation_customizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dossier_id')->constrained('dossiers')->onDelete('cascade')
                ->comment('Dossier concerné');
            $table->foreignId('document_template_id')->constrained('document_templates')->onDelete('cascade')
                ->comment('Template de document');
            $table->longText('header_text')->nullable()
                ->comment('Texte d\'en-tête personnalisé (HTML)');
            $table->longText('signature_text')->nullable()
                ->comment('Texte de signature personnalisé (HTML)');
            $table->foreignId('customized_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Utilisateur ayant personnalisé');
            $table->timestamp('customized_at')->nullable()
                ->comment('Date de personnalisation');
            $table->timestamps();

            // Index unique pour éviter doublons
            $table->unique(['dossier_id', 'document_template_id'], 'unique_dossier_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_generation_customizations');
    }
};
