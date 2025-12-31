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
        Schema::create('domaines_activite', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        // Seed initial data
        $domaines = [
            ['nom' => 'Social', 'code' => 'SOCIAL', 'description' => 'Actions sociales et solidarité', 'ordre' => 1],
            ['nom' => 'Culturel', 'code' => 'CULTUREL', 'description' => 'Promotion de la culture et des arts', 'ordre' => 2],
            ['nom' => 'Éducatif', 'code' => 'EDUCATIF', 'description' => 'Éducation et formation', 'ordre' => 3],
            ['nom' => 'Sportif', 'code' => 'SPORTIF', 'description' => 'Activités sportives et loisirs', 'ordre' => 4],
            ['nom' => 'Professionnel', 'code' => 'PROFESSIONNEL', 'description' => 'Défense des intérêts professionnels', 'ordre' => 5],
            ['nom' => 'Humanitaire', 'code' => 'HUMANITAIRE', 'description' => 'Actions humanitaires et aide d\'urgence', 'ordre' => 6],
            ['nom' => 'Environnement', 'code' => 'ENVIRONNEMENT', 'description' => 'Protection de l\'environnement', 'ordre' => 7],
            ['nom' => 'Santé', 'code' => 'SANTE', 'description' => 'Promotion de la santé et bien-être', 'ordre' => 8],
            ['nom' => 'Développement', 'code' => 'DEVELOPPEMENT', 'description' => 'Développement communautaire et économique', 'ordre' => 9],
            ['nom' => 'Droits de l\'Homme', 'code' => 'DROITS_HOMME', 'description' => 'Défense des droits humains', 'ordre' => 10],
            ['nom' => 'Religieux', 'code' => 'RELIGIEUX', 'description' => 'Activités religieuses et spirituelles', 'ordre' => 11],
            ['nom' => 'Civique', 'code' => 'CIVIQUE', 'description' => 'Engagement civique et citoyenneté', 'ordre' => 12],
            ['nom' => 'Autre', 'code' => 'AUTRE', 'description' => 'Autres domaines d\'activité', 'ordre' => 99],
        ];

        foreach ($domaines as $domaine) {
            \DB::table('domaines_activite')->insert(array_merge($domaine, [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domaines_activite');
    }
};
