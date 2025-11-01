<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSvgContentToQrCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            // Vérifier si les colonnes n'existent pas déjà
            if (!Schema::hasColumn('qr_codes', 'svg_content')) {
                $table->longText('svg_content')->nullable()->after('donnees_verification')
                      ->comment('Contenu SVG du QR Code généré');
            }
            
            if (!Schema::hasColumn('qr_codes', 'verification_url')) {
                $table->string('verification_url')->nullable()->after('svg_content')
                      ->comment('URL complète de vérification du QR Code');
            }
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
            if (Schema::hasColumn('qr_codes', 'svg_content')) {
                $table->dropColumn('svg_content');
            }
            
            if (Schema::hasColumn('qr_codes', 'verification_url')) {
                $table->dropColumn('verification_url');
            }
        });
    }
}