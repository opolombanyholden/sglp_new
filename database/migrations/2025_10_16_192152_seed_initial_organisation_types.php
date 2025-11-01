<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * MIGRATION SEEDER - TYPES D'ORGANISATIONS INITIAUX
 * 
 * Création des 4 types d'organisations standards du Gabon :
 * 1. Association
 * 2. ONG (Organisation Non Gouvernementale)
 * 3. Parti politique
 * 4. Confession religieuse
 * 
 * Projet : SGLP
 * Compatible : PHP 8.3, Laravel 10+
 */
return new class extends Migration
{
    /**
     * Exécuter la migration
     */
    public function up(): void
    {
        $now = Carbon::now();
        
        $types = [
            // ========================================
            // 1. ASSOCIATION
            // ========================================
            [
                'code' => 'association',
                'nom' => 'Association',
                'description' => 'Organisation à but non lucratif regroupant des personnes autour d\'objectifs communs dans les domaines social, culturel, éducatif, sportif ou professionnel.',
                'couleur' => '#007bff',
                'icone' => 'fa-users',
                'is_lucratif' => false,
                'nb_min_fondateurs_majeurs' => 2,
                'nb_min_adherents_creation' => 10,
                'guide_creation' => "## Étapes de création d'une association\n\n1. **Rédiger les statuts** : Document définissant les règles de fonctionnement\n2. **Constituer le bureau** : Nommer président, secrétaire général, trésorier\n3. **Tenir l'assemblée générale constitutive** : Réunion des membres fondateurs\n4. **Préparer le dossier** : Rassembler tous les documents requis\n5. **Déposer la demande** : Soumettre le dossier complet au SGLP\n\n## Documents à fournir\n- Statuts signés par tous les fondateurs\n- PV de l'assemblée générale constitutive\n- Liste des membres fondateurs et adhérents\n- Pièces d'identité des dirigeants\n- Casier judiciaire des dirigeants",
                'texte_legislatif' => "Conformément à la Constitution de la République Gabonaise et aux lois en vigueur, toute personne a le droit de créer une association à but non lucratif. Les associations doivent se déclarer auprès des autorités compétentes et respecter les principes de transparence et de légalité dans leurs activités.",
                'loi_reference' => 'Loi 35/62 du 10 décembre 1962',
                'is_active' => true,
                'ordre' => 1,
                'metadata' => json_encode([
                    'popularite' => 'tres_elevee',
                    'delai_traitement_moyen_jours' => 30,
                    'taux_approbation' => 85
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ========================================
            // 2. ONG
            // ========================================
            [
                'code' => 'ong',
                'nom' => 'ONG (Organisation Non Gouvernementale)',
                'description' => 'Organisation à but non lucratif, indépendante de tout gouvernement, œuvrant dans des domaines humanitaires, de développement, de défense des droits ou d\'environnement.',
                'couleur' => '#28a745',
                'icone' => 'fa-hands-helping',
                'is_lucratif' => false,
                'nb_min_fondateurs_majeurs' => 3,
                'nb_min_adherents_creation' => 15,
                'guide_creation' => "## Création d'une ONG au Gabon\n\n### Conditions spécifiques\n- Objectif humanitaire, social ou de développement clairement défini\n- Indépendance vis-à-vis des gouvernements\n- Transparence financière obligatoire\n- Capacité à justifier des sources de financement\n\n### Procédure\n1. Élaboration du projet associatif\n2. Rédaction des statuts conformes aux normes internationales\n3. Constitution d'un conseil d'administration\n4. Assemblée générale constitutive\n5. Dépôt du dossier complet\n6. Obtention de l'agrément ONG\n\n### Engagements\n- Rapport d'activités annuel obligatoire\n- Audit financier régulier\n- Respect des conventions internationales",
                'texte_legislatif' => "Les ONG sont régies par les dispositions spécifiques relatives aux associations à but non lucratif, avec des exigences supplémentaires en matière de transparence, de gouvernance et de reporting. Elles doivent démontrer leur indépendance et leur engagement dans des causes d'intérêt général.",
                'loi_reference' => 'Loi 35/62 + Décret d\'application ONG',
                'is_active' => true,
                'ordre' => 2,
                'metadata' => json_encode([
                    'popularite' => 'elevee',
                    'delai_traitement_moyen_jours' => 45,
                    'taux_approbation' => 75,
                    'exigences_supplementaires' => true
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ========================================
            // 3. PARTI POLITIQUE
            // ========================================
            [
                'code' => 'parti_politique',
                'nom' => 'Parti politique',
                'description' => 'Organisation politique permanente qui participe à l\'expression du suffrage, concourt à l\'animation de la vie politique et à la formation de l\'opinion publique.',
                'couleur' => '#dc3545',
                'icone' => 'fa-landmark',
                'is_lucratif' => false,
                'nb_min_fondateurs_majeurs' => 50,
                'nb_min_adherents_creation' => 500,
                'guide_creation' => "## Création d'un parti politique\n\n### Conditions strictes\n- Respect des principes démocratiques\n- Caractère national (présence dans plusieurs provinces)\n- Programme politique clairement défini\n- Engagement à respecter la Constitution\n\n### Membres fondateurs\n- Minimum 50 fondateurs majeurs\n- Nationalité gabonaise obligatoire\n- Jouissance des droits civiques\n- Absence de condamnations incompatibles\n\n### Procédure\n1. Rédaction des statuts et du programme politique\n2. Constitution des organes dirigeants nationaux\n3. Établissement de la représentation territoriale\n4. Assemblée générale constitutive\n5. Dépôt de la déclaration\n6. Publication au Journal Officiel\n\n### Obligations\n- Tenue de congrès réguliers\n- Transparence financière renforcée\n- Déclaration des sources de financement\n- Respect du pluralisme politique",
                'texte_legislatif' => "Les partis politiques concourent à l'expression du suffrage. Ils se forment et exercent leur activité librement dans le respect des principes de la souveraineté nationale, de la démocratie, de l'intégrité territoriale et de l'ordre public. Ils doivent respecter les principes énoncés dans la Constitution.",
                'loi_reference' => 'Loi 5/2015 sur les partis politiques',
                'is_active' => true,
                'ordre' => 3,
                'metadata' => json_encode([
                    'popularite' => 'moyenne',
                    'delai_traitement_moyen_jours' => 60,
                    'taux_approbation' => 60,
                    'verification_renforcee' => true,
                    'exigences_territoriales' => true
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ========================================
            // 4. CONFESSION RELIGIEUSE
            // ========================================
            [
                'code' => 'confession_religieuse',
                'nom' => 'Confession religieuse',
                'description' => 'Organisation religieuse ou cultuelle réunissant des fidèles autour d\'une foi commune, dans le respect de la liberté de conscience et de culte.',
                'couleur' => '#6f42c1',
                'icone' => 'fa-church',
                'is_lucratif' => false,
                'nb_min_fondateurs_majeurs' => 5,
                'nb_min_adherents_creation' => 20,
                'guide_creation' => "## Déclaration d'une confession religieuse\n\n### Principes fondamentaux\n- Liberté de culte garantie par la Constitution\n- Respect de l'ordre public et de la laïcité de l'État\n- Tolérance et respect des autres confessions\n- Transparence dans l'organisation et les activités\n\n### Catégories reconnues\n- Églises chrétiennes\n- Communautés islamiques\n- Autres confessions monothéistes\n- Mouvements spirituels reconnus\n\n### Procédure de déclaration\n1. Définition des principes doctrinaux\n2. Rédaction des statuts religieux\n3. Constitution du clergé ou des responsables religieux\n4. Désignation du représentant légal\n5. Assemblée constitutive des fidèles\n6. Dépôt de la déclaration\n\n### Documents spécifiques\n- Profession de foi ou doctrine religieuse\n- Références théologiques\n- Organisation hiérarchique (si applicable)\n- Règlement intérieur du culte\n\n### Engagements\n- Respect de la dignité humaine\n- Non-discrimination\n- Transparence financière\n- Rapport d'activités annuel",
                'texte_legislatif' => "La liberté de conscience et de culte est garantie par la Constitution. L'État respecte toutes les croyances. Les confessions religieuses sont libres de s'organiser et de pratiquer leur culte dans le respect des lois de la République, de l'ordre public et des bonnes mœurs.",
                'loi_reference' => 'Loi 15/2005 sur la liberté religieuse',
                'is_active' => true,
                'ordre' => 4,
                'metadata' => json_encode([
                    'popularite' => 'moyenne',
                    'delai_traitement_moyen_jours' => 35,
                    'taux_approbation' => 70,
                    'verification_doctrinale' => true
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insertion dans la base de données
        DB::table('organisation_types')->insert($types);

        // Message de confirmation
        $count = count($types);
        echo "\n✅ {$count} types d'organisations créés avec succès :\n";
        foreach ($types as $type) {
            echo "   - {$type['nom']} (code: {$type['code']})\n";
        }
        echo "\n";
    }

    /**
     * Annuler la migration
     */
    public function down(): void
    {
        // Supprimer les types créés par ce seeder
        DB::table('organisation_types')->whereIn('code', [
            'association',
            'ong',
            'parti_politique',
            'confession_religieuse',
        ])->delete();

        echo "\n❌ Types d'organisations initiaux supprimés\n\n";
    }
};