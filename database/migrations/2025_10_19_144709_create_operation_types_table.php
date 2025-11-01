<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Créer la table operation_types
        Schema::create('operation_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique()->comment('Code unique de l\'opération');
            $table->string('libelle', 100)->comment('Libellé de l\'opération');
            $table->text('description')->nullable()->comment('Description détaillée');
            $table->boolean('is_active')->default(true)->comment('Opération active');
            $table->integer('ordre')->default(0)->comment('Ordre d\'affichage');
            $table->timestamps();

            // Index
            $table->index('is_active');
            $table->index('ordre');
        });

        // Insérer les types d'opération standards
        DB::table('operation_types')->insert([
            [
                'code' => 'creation',
                'libelle' => 'Création',
                'description' => 'Création d\'une nouvelle organisation',
                'is_active' => true,
                'ordre' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'modification',
                'libelle' => 'Modification',
                'description' => 'Modification des informations de l\'organisation',
                'is_active' => true,
                'ordre' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'cessation',
                'libelle' => 'Cessation',
                'description' => 'Cessation d\'activité de l\'organisation',
                'is_active' => true,
                'ordre' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ajout_adherent',
                'libelle' => 'Ajout adhérent',
                'description' => 'Ajout d\'un nouvel adhérent',
                'is_active' => true,
                'ordre' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'retrait_adherent',
                'libelle' => 'Retrait adhérent',
                'description' => 'Retrait d\'un adhérent',
                'is_active' => true,
                'ordre' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'declaration_activite',
                'libelle' => 'Déclaration d\'activité',
                'description' => 'Déclaration annuelle des activités',
                'is_active' => true,
                'ordre' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'changement_statutaire',
                'libelle' => 'Changement statutaire',
                'description' => 'Modification des statuts de l\'organisation',
                'is_active' => true,
                'ordre' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_types');
    }
};