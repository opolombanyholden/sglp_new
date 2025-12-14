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
        Schema::table('document_templates', function (Blueprint $table) {
            $table->longText('header_text')->nullable()->after('signature_image')
                ->comment('Texte d\'en-tête par défaut (HTML WYSIWYG)');
            $table->longText('signature_text')->nullable()->after('header_text')
                ->comment('Texte de signature par défaut (HTML WYSIWYG)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_templates', function (Blueprint $table) {
            $table->dropColumn(['header_text', 'signature_text']);
        });
    }
};
