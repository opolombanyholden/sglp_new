<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membres_bureau', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->string('nip', 50);
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('fonction', 150)->comment('Fonction dans l\'organisation');
            $table->string('contact', 100)->nullable()->comment('Téléphone ou email');
            $table->string('domicile', 255)->nullable()->comment('Adresse de résidence');
            $table->boolean('afficher_recepisse')->default(false)->comment('Afficher sur le récépissé définitif');
            $table->unsignedSmallInteger('ordre')->default(0)->comment('Ordre d\'affichage');
            $table->timestamps();

            $table->index(['organisation_id', 'afficher_recepisse']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('membres_bureau');
    }
};
