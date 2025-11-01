<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPngBase64ToQrCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            // Ajouter le champ pour stocker le PNG en base64 (pour PDF)
            if (!Schema::hasColumn('qr_codes', 'png_base64')) {
                $table->longText('png_base64')->nullable()->after('svg_content')
                      ->comment('Image PNG du QR Code en base64 pour PDF');
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
            if (Schema::hasColumn('qr_codes', 'png_base64')) {
                $table->dropColumn('png_base64');
            }
        });
    }
}