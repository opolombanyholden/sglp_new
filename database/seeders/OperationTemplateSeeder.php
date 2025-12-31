<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentTemplate;
use App\Models\OrganisationType;
use App\Models\OperationType;
use Illuminate\Support\Facades\DB;

/**
 * SEEDER - TEMPLATES POUR LES NOUVELLES OPÉRATIONS
 * 
 * Insère les templates de documents pour :
 * - Modification
 * - Cessation
 * - Ajout/Retrait adhérent
 * - Déclaration d'activité
 * - Changement statutaire
 * 
 * Projet : SGLP
 * Date : 28/12/2025
 */
class OperationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Insertion des templates pour les nouvelles opérations...');

        // Récupérer les IDs nécessaires
        $orgTypes = $this->getOrganisationTypes();
        $opTypes = $this->getOperationTypes();

        if (empty($opTypes['modification'])) {
            $this->command->error('❌ Types d\'opérations non trouvés. Lancez les migrations.');
            return;
        }

        // Créer les templates pour chaque type d'organisation
        foreach ($orgTypes as $orgCode => $orgId) {
            if (!$orgId)
                continue;

            $this->command->info("📝 Création templates pour {$orgCode}...");

            $this->createModificationTemplates($orgId, $opTypes, $orgCode);
            $this->createCessationTemplates($orgId, $opTypes, $orgCode);
            $this->createAjoutAdherentTemplates($orgId, $opTypes, $orgCode);
            $this->createRetraitAdherentTemplates($orgId, $opTypes, $orgCode);
            $this->createDeclarationActiviteTemplates($orgId, $opTypes, $orgCode);
            $this->createChangementStatutaireTemplates($orgId, $opTypes, $orgCode);
        }

        $this->command->info('✅ Templates opérations insérés avec succès !');
        $this->command->info('📊 Total templates : ' . DocumentTemplate::count());
    }

    /**
     * Récupérer les types d'organisations
     */
    protected function getOrganisationTypes(): array
    {
        return [
            'association' => OrganisationType::where('code', 'association')->first()?->id,
            'ong' => OrganisationType::where('code', 'ong')->first()?->id,
            'parti_politique' => OrganisationType::where('code', 'parti_politique')->first()?->id,
            'confession_religieuse' => OrganisationType::where('code', 'confession_religieuse')->first()?->id,
        ];
    }

    /**
     * Récupérer les types d'opérations
     */
    protected function getOperationTypes(): array
    {
        return [
            'modification' => OperationType::where('code', 'modification')->first()?->id,
            'cessation' => OperationType::where('code', 'cessation')->first()?->id,
            'ajout_adherent' => OperationType::where('code', 'ajout_adherent')->first()?->id,
            'retrait_adherent' => OperationType::where('code', 'retrait_adherent')->first()?->id,
            'declaration_activite' => OperationType::where('code', 'declaration_activite')->first()?->id,
            'changement_statutaire' => OperationType::where('code', 'changement_statutaire')->first()?->id,
        ];
    }

    /**
     * Préfixe pour les codes de template
     */
    protected function getPrefix(string $orgCode): string
    {
        return match ($orgCode) {
            'association' => 'ASSOC',
            'ong' => 'ONG',
            'parti_politique' => 'PARTI',
            'confession_religieuse' => 'CONFESS',
            default => 'ORG',
        };
    }

    /**
     * Templates MODIFICATION
     */
    protected function createModificationTemplates($orgId, $opTypes, $orgCode): void
    {
        $prefix = $this->getPrefix($orgCode);
        $opId = $opTypes['modification'];
        if (!$opId)
            return;

        // Récépissé de modification
        DocumentTemplate::updateOrCreate(
            ['code' => "{$prefix}_MOD_RECEPISSE"],
            [
                'organisation_type_id' => $orgId,
                'operation_type_id' => $opId,
                'nom' => "Récépissé de modification - {$orgCode}",
                'description' => 'Récépissé délivré après validation de la modification',
                'type_document' => 'attestation',
                'template_path' => 'documents.templates.operations.modification.recepisse',
                'layout_path' => 'documents.layouts.official',
                'variables' => [
                    'organisation' => ['nom', 'sigle', 'numero_recepisse'],
                    'dossier' => ['numero_dossier', 'modifications'],
                    'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                ],
                'required_variables' => ['organisation.nom', 'document.numero_document'],
                'pdf_config' => [
                    'format' => 'a4',
                    'orientation' => 'portrait',
                    'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
                ],
                'has_qr_code' => true,
                'has_signature' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Templates CESSATION
     */
    protected function createCessationTemplates($orgId, $opTypes, $orgCode): void
    {
        $prefix = $this->getPrefix($orgCode);
        $opId = $opTypes['cessation'];
        if (!$opId)
            return;

        // Attestation de cessation
        DocumentTemplate::updateOrCreate(
            ['code' => "{$prefix}_CESS_ATTESTATION"],
            [
                'organisation_type_id' => $orgId,
                'operation_type_id' => $opId,
                'nom' => "Attestation de cessation - {$orgCode}",
                'description' => 'Attestation officielle de cessation d\'activité',
                'type_document' => 'attestation',
                'template_path' => 'documents.templates.operations.cessation.attestation',
                'layout_path' => 'documents.layouts.official',
                'variables' => [
                    'organisation' => ['nom', 'sigle', 'numero_recepisse', 'date_creation'],
                    'dossier' => ['numero_dossier', 'date_effet', 'motif_cessation'],
                    'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                ],
                'required_variables' => ['organisation.nom', 'dossier.date_effet', 'document.numero_document'],
                'pdf_config' => [
                    'format' => 'a4',
                    'orientation' => 'portrait',
                    'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
                ],
                'has_qr_code' => true,
                'has_watermark' => true,
                'has_signature' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Templates AJOUT ADHÉRENT
     */
    protected function createAjoutAdherentTemplates($orgId, $opTypes, $orgCode): void
    {
        $prefix = $this->getPrefix($orgCode);
        $opId = $opTypes['ajout_adherent'];
        if (!$opId)
            return;

        // Attestation d'enregistrement des adhérents
        DocumentTemplate::updateOrCreate(
            ['code' => "{$prefix}_AJOUT_ADH_ATTESTATION"],
            [
                'organisation_type_id' => $orgId,
                'operation_type_id' => $opId,
                'nom' => "Attestation enregistrement adhérents - {$orgCode}",
                'description' => 'Attestation d\'enregistrement des nouveaux adhérents',
                'type_document' => 'attestation',
                'template_path' => 'documents.templates.operations.adherent.ajout',
                'layout_path' => 'documents.layouts.official',
                'variables' => [
                    'organisation' => ['nom', 'sigle'],
                    'dossier' => ['numero_dossier', 'nombre_adherents'],
                    'adherents' => ['liste'],
                    'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                ],
                'required_variables' => ['organisation.nom', 'document.numero_document'],
                'pdf_config' => [
                    'format' => 'a4',
                    'orientation' => 'portrait',
                    'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
                ],
                'has_qr_code' => true,
                'has_signature' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Templates RETRAIT ADHÉRENT
     */
    protected function createRetraitAdherentTemplates($orgId, $opTypes, $orgCode): void
    {
        $prefix = $this->getPrefix($orgCode);
        $opId = $opTypes['retrait_adherent'];
        if (!$opId)
            return;

        // Notification de retrait
        DocumentTemplate::updateOrCreate(
            ['code' => "{$prefix}_RETRAIT_ADH_NOTIF"],
            [
                'organisation_type_id' => $orgId,
                'operation_type_id' => $opId,
                'nom' => "Notification retrait adhérents - {$orgCode}",
                'description' => 'Notification officielle du retrait d\'adhérents',
                'type_document' => 'courrier_officiel',
                'template_path' => 'documents.templates.operations.adherent.retrait',
                'layout_path' => 'documents.layouts.official',
                'variables' => [
                    'organisation' => ['nom', 'sigle'],
                    'dossier' => ['numero_dossier', 'motif_retrait'],
                    'adherents' => ['liste_retires'],
                    'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                ],
                'required_variables' => ['organisation.nom', 'document.numero_document'],
                'pdf_config' => [
                    'format' => 'a4',
                    'orientation' => 'portrait',
                    'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
                ],
                'has_qr_code' => true,
                'has_signature' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Templates DÉCLARATION D'ACTIVITÉ
     */
    protected function createDeclarationActiviteTemplates($orgId, $opTypes, $orgCode): void
    {
        $prefix = $this->getPrefix($orgCode);
        $opId = $opTypes['declaration_activite'];
        if (!$opId)
            return;

        // Accusé de réception déclaration
        DocumentTemplate::updateOrCreate(
            ['code' => "{$prefix}_DECL_ACCUSE"],
            [
                'organisation_type_id' => $orgId,
                'operation_type_id' => $opId,
                'nom' => "Accusé déclaration d'activité - {$orgCode}",
                'description' => 'Accusé de réception de la déclaration d\'activité',
                'type_document' => 'accuse_reception',
                'template_path' => 'documents.templates.operations.declaration.accuse',
                'layout_path' => 'documents.layouts.official',
                'variables' => [
                    'organisation' => ['nom', 'sigle'],
                    'dossier' => ['numero_dossier', 'periode', 'annee'],
                    'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                ],
                'required_variables' => ['organisation.nom', 'dossier.periode', 'document.numero_document'],
                'pdf_config' => [
                    'format' => 'a4',
                    'orientation' => 'portrait',
                    'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
                ],
                'has_qr_code' => true,
                'has_signature' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Templates CHANGEMENT STATUTAIRE
     */
    protected function createChangementStatutaireTemplates($orgId, $opTypes, $orgCode): void
    {
        $prefix = $this->getPrefix($orgCode);
        $opId = $opTypes['changement_statutaire'];
        if (!$opId)
            return;

        // Récépissé de changement statutaire
        DocumentTemplate::updateOrCreate(
            ['code' => "{$prefix}_CHGT_STAT_RECEPISSE"],
            [
                'organisation_type_id' => $orgId,
                'operation_type_id' => $opId,
                'nom' => "Récépissé changement statutaire - {$orgCode}",
                'description' => 'Récépissé de validation du changement statutaire',
                'type_document' => 'attestation',
                'template_path' => 'documents.templates.operations.statutaire.recepisse',
                'layout_path' => 'documents.layouts.official',
                'variables' => [
                    'organisation' => ['nom', 'sigle', 'numero_recepisse'],
                    'dossier' => ['numero_dossier', 'description_changements'],
                    'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                ],
                'required_variables' => ['organisation.nom', 'document.numero_document'],
                'pdf_config' => [
                    'format' => 'a4',
                    'orientation' => 'portrait',
                    'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
                ],
                'has_qr_code' => true,
                'has_watermark' => true,
                'has_signature' => true,
                'is_active' => true,
            ]
        );
    }
}
