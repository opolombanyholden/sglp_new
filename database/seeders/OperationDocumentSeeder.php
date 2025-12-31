<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentType;
use App\Models\OperationType;
use Illuminate\Support\Facades\DB;

/**
 * SEEDER - ASSOCIATION DOCUMENTS / TYPES D'OPÉRATIONS
 * 
 * Configure les documents requis pour chaque type d'opération :
 * - Modification
 * - Cessation
 * - Ajout adhérent
 * - Retrait adhérent
 * - Déclaration d'activité
 * - Changement statutaire
 * 
 * Projet : SGLP
 * Date : 28/12/2025
 */
class OperationDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Configuration des documents requis par type d\'opération...');

        // Récupérer les types d'opérations
        $operationTypes = OperationType::all()->keyBy('code');

        if ($operationTypes->isEmpty()) {
            $this->command->error('❌ Aucun type d\'opération trouvé. Lancez d\'abord les migrations.');
            return;
        }

        // Créer les types de documents si nécessaires
        $this->createDocumentTypes();

        // Récupérer les types de documents
        $documentTypes = DocumentType::all()->keyBy('code');

        // Configuration des documents par opération
        $this->configureModification($operationTypes, $documentTypes);
        $this->configureCessation($operationTypes, $documentTypes);
        $this->configureAjoutAdherent($operationTypes, $documentTypes);
        $this->configureRetraitAdherent($operationTypes, $documentTypes);
        $this->configureDeclarationActivite($operationTypes, $documentTypes);
        $this->configureChangementStatutaire($operationTypes, $documentTypes);

        $this->command->info('✅ Configuration des documents terminée !');
    }

    /**
     * Créer les types de documents nécessaires
     */
    protected function createDocumentTypes(): void
    {
        $documents = [
            // Documents généraux
            ['code' => 'pv_ag', 'libelle' => 'Procès-verbal d\'Assemblée Générale', 'description' => 'PV de l\'AG ayant décidé des modifications'],
            ['code' => 'liste_presence_ag', 'libelle' => 'Liste de présence AG', 'description' => 'Liste des membres présents à l\'AG'],
            ['code' => 'statuts_modifies', 'libelle' => 'Statuts modifiés', 'description' => 'Copie des statuts après modification'],
            ['code' => 'ri_modifie', 'libelle' => 'Règlement intérieur modifié', 'description' => 'Copie du règlement intérieur après modification'],

            // Documents pour cessation
            ['code' => 'pv_dissolution', 'libelle' => 'PV de dissolution', 'description' => 'Procès-verbal de l\'AG décidant la dissolution'],
            ['code' => 'bilan_cloture', 'libelle' => 'Bilan de clôture', 'description' => 'Bilan financier à la date de clôture'],
            ['code' => 'attestation_dettes', 'libelle' => 'Attestation d\'absence de dettes', 'description' => 'Attestation certifiant l\'absence de dettes'],

            // Documents pour adhérents
            ['code' => 'cni_adherent', 'libelle' => 'CNI/Passeport de l\'adhérent', 'description' => 'Copie de la pièce d\'identité'],
            ['code' => 'photo_adherent', 'libelle' => 'Photo d\'identité adhérent', 'description' => 'Photo d\'identité format passeport'],
            ['code' => 'demande_adhesion', 'libelle' => 'Demande d\'adhésion', 'description' => 'Formulaire de demande d\'adhésion signé'],
            ['code' => 'demission_adherent', 'libelle' => 'Lettre de démission', 'description' => 'Lettre de démission de l\'adhérent'],
            ['code' => 'decision_exclusion', 'libelle' => 'Décision d\'exclusion', 'description' => 'Décision motivée d\'exclusion'],

            // Documents pour déclaration d'activité
            ['code' => 'rapport_activite', 'libelle' => 'Rapport d\'activité', 'description' => 'Rapport détaillé des activités de la période'],
            ['code' => 'bilan_financier', 'libelle' => 'Bilan financier', 'description' => 'État des recettes et dépenses'],
            ['code' => 'rapport_commissaire', 'libelle' => 'Rapport du commissaire aux comptes', 'description' => 'Rapport de vérification des comptes'],

            // Documents pour changement statutaire
            ['code' => 'tableau_comparatif', 'libelle' => 'Tableau comparatif', 'description' => 'Tableau comparatif avant/après modifications'],
            ['code' => 'note_explicative', 'libelle' => 'Note explicative', 'description' => 'Note explicative des changements'],
        ];

        foreach ($documents as $doc) {
            DocumentType::firstOrCreate(
                ['code' => $doc['code']],
                [
                    'libelle' => $doc['libelle'],
                    'description' => $doc['description'],
                    'is_active' => true,
                    'format_accepte' => 'pdf,jpg,png',
                    'taille_max' => 5,
                ]
            );
        }

        $this->command->info('  📄 Types de documents créés/vérifiés');
    }

    /**
     * Configurer documents pour MODIFICATION
     */
    protected function configureModification($operationTypes, $documentTypes): void
    {
        $opType = $operationTypes->get('modification');
        if (!$opType)
            return;

        // Synchroniser les documents requis
        $docsRequired = [
            'pv_ag' => ['is_obligatoire' => true, 'ordre' => 1],
            'liste_presence_ag' => ['is_obligatoire' => true, 'ordre' => 2],
            'statuts_modifies' => ['is_obligatoire' => false, 'ordre' => 3],
            'ri_modifie' => ['is_obligatoire' => false, 'ordre' => 4],
        ];

        $this->syncDocuments($opType, $documentTypes, $docsRequired);
        $this->command->info('  ✅ Modification : ' . count($docsRequired) . ' documents configurés');
    }

    /**
     * Configurer documents pour CESSATION
     */
    protected function configureCessation($operationTypes, $documentTypes): void
    {
        $opType = $operationTypes->get('cessation');
        if (!$opType)
            return;

        $docsRequired = [
            'pv_dissolution' => ['is_obligatoire' => true, 'ordre' => 1],
            'liste_presence_ag' => ['is_obligatoire' => true, 'ordre' => 2],
            'bilan_cloture' => ['is_obligatoire' => true, 'ordre' => 3],
            'attestation_dettes' => ['is_obligatoire' => false, 'ordre' => 4],
        ];

        $this->syncDocuments($opType, $documentTypes, $docsRequired);
        $this->command->info('  ✅ Cessation : ' . count($docsRequired) . ' documents configurés');
    }

    /**
     * Configurer documents pour AJOUT ADHÉRENT
     */
    protected function configureAjoutAdherent($operationTypes, $documentTypes): void
    {
        $opType = $operationTypes->get('ajout_adherent');
        if (!$opType)
            return;

        $docsRequired = [
            'demande_adhesion' => ['is_obligatoire' => true, 'ordre' => 1],
            'cni_adherent' => ['is_obligatoire' => true, 'ordre' => 2],
            'photo_adherent' => ['is_obligatoire' => false, 'ordre' => 3],
        ];

        $this->syncDocuments($opType, $documentTypes, $docsRequired);
        $this->command->info('  ✅ Ajout adhérent : ' . count($docsRequired) . ' documents configurés');
    }

    /**
     * Configurer documents pour RETRAIT ADHÉRENT
     */
    protected function configureRetraitAdherent($operationTypes, $documentTypes): void
    {
        $opType = $operationTypes->get('retrait_adherent');
        if (!$opType)
            return;

        $docsRequired = [
            'demission_adherent' => ['is_obligatoire' => false, 'ordre' => 1],
            'decision_exclusion' => ['is_obligatoire' => false, 'ordre' => 2],
            'pv_ag' => ['is_obligatoire' => false, 'ordre' => 3],
        ];

        $this->syncDocuments($opType, $documentTypes, $docsRequired);
        $this->command->info('  ✅ Retrait adhérent : ' . count($docsRequired) . ' documents configurés');
    }

    /**
     * Configurer documents pour DÉCLARATION D'ACTIVITÉ
     */
    protected function configureDeclarationActivite($operationTypes, $documentTypes): void
    {
        $opType = $operationTypes->get('declaration_activite');
        if (!$opType)
            return;

        $docsRequired = [
            'rapport_activite' => ['is_obligatoire' => true, 'ordre' => 1],
            'bilan_financier' => ['is_obligatoire' => true, 'ordre' => 2],
            'rapport_commissaire' => ['is_obligatoire' => false, 'ordre' => 3],
            'pv_ag' => ['is_obligatoire' => false, 'ordre' => 4],
        ];

        $this->syncDocuments($opType, $documentTypes, $docsRequired);
        $this->command->info('  ✅ Déclaration activité : ' . count($docsRequired) . ' documents configurés');
    }

    /**
     * Configurer documents pour CHANGEMENT STATUTAIRE
     */
    protected function configureChangementStatutaire($operationTypes, $documentTypes): void
    {
        $opType = $operationTypes->get('changement_statutaire');
        if (!$opType)
            return;

        $docsRequired = [
            'pv_ag' => ['is_obligatoire' => true, 'ordre' => 1],
            'liste_presence_ag' => ['is_obligatoire' => true, 'ordre' => 2],
            'statuts_modifies' => ['is_obligatoire' => true, 'ordre' => 3],
            'tableau_comparatif' => ['is_obligatoire' => false, 'ordre' => 4],
            'note_explicative' => ['is_obligatoire' => false, 'ordre' => 5],
        ];

        $this->syncDocuments($opType, $documentTypes, $docsRequired);
        $this->command->info('  ✅ Changement statutaire : ' . count($docsRequired) . ' documents configurés');
    }

    /**
     * Synchroniser les documents avec un type d'opération
     */
    protected function syncDocuments(OperationType $opType, $documentTypes, array $docsRequired): void
    {
        $syncData = [];

        foreach ($docsRequired as $docCode => $pivotData) {
            $docType = $documentTypes->get($docCode);
            if ($docType) {
                $syncData[$docType->id] = $pivotData;
            }
        }

        $opType->documentTypes()->sync($syncData);
    }
}
