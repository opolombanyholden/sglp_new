<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSvgFieldsToQrCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            // Ajouter les champs pour le SVG et l'URL de vérification
            $table->longText('svg_content')->nullable()->after('donnees_verification')
                  ->comment('Contenu SVG du QR Code généré');
            $table->string('verification_url')->nullable()->after('svg_content')
                  ->comment('URL complète de vérification du QR Code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn(['svg_content', 'verification_url']);
        });
    }
}