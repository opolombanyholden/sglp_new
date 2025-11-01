<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentTemplate;
use App\Models\OrganisationType;
use App\Models\OperationType;
use App\Models\WorkflowStep;
use Illuminate\Support\Facades\DB;

/**
 * SEEDER - TEMPLATES DE DOCUMENTS
 * 
 * Insère les templates de documents officiels pour :
 * - Associations
 * - ONG
 * - Partis politiques
 * - Confessions religieuses
 * 
 * Projet : SGLP
 */
class DocumentTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Insertion des templates de documents...');

        // Désactiver temporairement les contraintes de clés étrangères
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Vider les tables (dans le bon ordre)
        DB::table('document_verifications')->truncate();
        DB::table('document_generations')->truncate();
        DB::table('document_templates')->truncate();
        
        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Récupérer les IDs nécessaires
        $orgTypes = $this->getOrganisationTypes();
        $opTypes = $this->getOperationTypes();
        $steps = $this->getWorkflowSteps();

        // Créer les templates par type d'organisation
        $this->createAssociationTemplates($orgTypes, $opTypes, $steps);
        $this->createOngTemplates($orgTypes, $opTypes, $steps);
        $this->createPartiPolitiqueTemplates($orgTypes, $opTypes, $steps);
        $this->createConfessionReligieuseTemplates($orgTypes, $opTypes, $steps);

        $this->command->info('✅ Templates de documents insérés avec succès !');
        $this->command->info('📊 Total : ' . DocumentTemplate::count() . ' templates');
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
            'creation' => OperationType::where('code', 'creation')->first()?->id,
            'modification' => OperationType::where('code', 'modification')->first()?->id,
            'cessation' => OperationType::where('code', 'cessation')->first()?->id,
        ];
    }

    /**
     * Récupérer les étapes du workflow
     * Note : Table workflow_steps vide pour l'instant, on utilise NULL
     */
    protected function getWorkflowSteps(): array
    {
        return [
            'depot' => null,
            'validation_technique' => null,
            'validation_juridique' => null,
            'approbation' => null,
            'rejet' => null,
        ];
    }

    /**
     * Créer les templates pour ASSOCIATIONS
     */
    protected function createAssociationTemplates(array $orgTypes, array $opTypes, array $steps): void
    {
        if (!$orgTypes['association']) {
            $this->command->warn('⚠️ Type "association" introuvable, templates ignorés');
            return;
        }

        $this->command->info('📝 Création templates Association...');

        // 1. RÉCÉPISSÉ DE DÉPÔT - CRÉATION
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['association'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'ASSOC_CREATION_DEPOT',
            'nom' => 'Récépissé de dépôt - Association (Création)',
            'description' => 'Récépissé provisoire délivré lors du dépôt initial du dossier de création',
            'type_document' => 'recepisse_provisoire',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom', 'sigle', 'siege_social', 'province', 'telephone', 'email', 'date_creation'],
                'dossier' => ['numero_dossier', 'date_soumission', 'statut'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom', 'email'],
            ],
            'required_variables' => [
                'organisation.nom',
                'organisation.siege_social',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => false,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        // 2. ACCUSÉ VALIDATION TECHNIQUE - CRÉATION
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['association'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'ASSOC_CREATION_VALID_TECH',
            'nom' => 'Accusé validation technique - Association',
            'description' => 'Accusé de réception après validation technique du dossier',
            'type_document' => 'attestation',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom', 'sigle'],
                'dossier' => ['numero_dossier'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom'],
            ],
            'required_variables' => [
                'organisation.nom',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => false,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        // 3. RÉCÉPISSÉ DÉFINITIF - CRÉATION
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['association'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'ASSOC_CREATION_RECEP_DEF',
            'nom' => 'Récépissé définitif - Association',
            'description' => 'Récépissé définitif après approbation finale',
            'type_document' => 'recepisse_definitif',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom', 'sigle', 'numero_recepisse', 'date_creation', 'siege_social'],
                'dossier' => ['numero_dossier'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom'],
            ],
            'required_variables' => [
                'organisation.nom',
                'organisation.numero_recepisse',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => true,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        // 4. NOTIFICATION DE REJET - CRÉATION
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['association'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'ASSOC_CREATION_REJET',
            'nom' => 'Notification de rejet - Association',
            'description' => 'Notification de rejet du dossier avec motifs',
            'type_document' => 'notification_rejet',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom'],
                'dossier' => ['numero_dossier', 'motif_rejet'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom'],
            ],
            'required_variables' => [
                'organisation.nom',
                'dossier.motif_rejet',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => false,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        $this->command->info('  ✅ 4 templates Association créés');
    }

    /**
     * Créer les templates pour ONG
     */
    protected function createOngTemplates(array $orgTypes, array $opTypes, array $steps): void
    {
        if (!$orgTypes['ong']) {
            $this->command->warn('⚠️ Type "ong" introuvable, templates ignorés');
            return;
        }

        $this->command->info('📝 Création templates ONG...');

        // 1. RÉCÉPISSÉ DE DÉPÔT - CRÉATION
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['ong'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'ONG_CREATION_DEPOT',
            'nom' => 'Récépissé de dépôt - ONG (Création)',
            'description' => 'Récépissé provisoire délivré lors du dépôt du dossier',
            'type_document' => 'recepisse_provisoire',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom', 'sigle', 'siege_social', 'province', 'telephone', 'email'],
                'dossier' => ['numero_dossier', 'date_soumission'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom'],
            ],
            'required_variables' => [
                'organisation.nom',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => false,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        // 2. CERTIFICAT D'ENREGISTREMENT - CRÉATION
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['ong'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'ONG_CREATION_CERTIFICAT',
            'nom' => 'Certificat d\'enregistrement - ONG',
            'description' => 'Certificat définitif après approbation',
            'type_document' => 'certificat_enregistrement',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom', 'sigle', 'numero_recepisse', 'siege_social'],
                'dossier' => ['numero_dossier'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom'],
            ],
            'required_variables' => [
                'organisation.nom',
                'organisation.numero_recepisse',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => true,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        $this->command->info('  ✅ 2 templates ONG créés');
    }

    /**
     * Créer les templates pour PARTIS POLITIQUES
     */
    protected function createPartiPolitiqueTemplates(array $orgTypes, array $opTypes, array $steps): void
    {
        if (!$orgTypes['parti_politique']) {
            $this->command->warn('⚠️ Type "parti_politique" introuvable, templates ignorés');
            return;
        }

        $this->command->info('📝 Création templates Parti Politique...');

        // 1. RÉCÉPISSÉ DE DÉPÔT - CRÉATION
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['parti_politique'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'PARTI_CREATION_DEPOT',
            'nom' => 'Récépissé de dépôt - Parti Politique',
            'description' => 'Récépissé provisoire délivré lors du dépôt',
            'type_document' => 'recepisse_provisoire',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom', 'sigle', 'siege_social', 'province'],
                'dossier' => ['numero_dossier', 'date_soumission'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom'],
            ],
            'required_variables' => [
                'organisation.nom',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => false,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        // 2. RÉCÉPISSÉ DÉFINITIF
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['parti_politique'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'PARTI_CREATION_RECEP_DEF',
            'nom' => 'Récépissé définitif - Parti Politique',
            'description' => 'Récépissé définitif après approbation',
            'type_document' => 'recepisse_definitif',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom', 'sigle', 'numero_recepisse', 'siege_social'],
                'dossier' => ['numero_dossier'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom'],
            ],
            'required_variables' => [
                'organisation.nom',
                'organisation.numero_recepisse',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => true,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        $this->command->info('  ✅ 2 templates Parti Politique créés');
    }

    /**
     * Créer les templates pour CONFESSIONS RELIGIEUSES
     */
    protected function createConfessionReligieuseTemplates(array $orgTypes, array $opTypes, array $steps): void
    {
        if (!$orgTypes['confession_religieuse']) {
            $this->command->warn('⚠️ Type "confession_religieuse" introuvable, templates ignorés');
            return;
        }

        $this->command->info('📝 Création templates Confession Religieuse...');

        // 1. RÉCÉPISSÉ DE DÉPÔT - CRÉATION
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['confession_religieuse'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'CONFESS_CREATION_DEPOT',
            'nom' => 'Récépissé de dépôt - Confession Religieuse',
            'description' => 'Récépissé provisoire délivré lors du dépôt',
            'type_document' => 'recepisse_provisoire',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom', 'sigle', 'siege_social', 'province'],
                'dossier' => ['numero_dossier', 'date_soumission'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom'],
            ],
            'required_variables' => [
                'organisation.nom',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => false,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        // 2. CERTIFICAT D'ENREGISTREMENT
        DocumentTemplate::create([
            'organisation_type_id' => $orgTypes['confession_religieuse'],
            'operation_type_id' => $opTypes['creation'],
            'workflow_step_id' => null,
            'code' => 'CONFESS_CREATION_CERTIFICAT',
            'nom' => 'Certificat d\'enregistrement - Confession Religieuse',
            'description' => 'Certificat définitif après approbation',
            'type_document' => 'certificat_enregistrement',
            'template_path' => 'documents.templates.association.creation.step-1-recepisse-depot',
            'layout_path' => 'documents.layouts.official',
            'variables' => [
                'organisation' => ['nom', 'sigle', 'numero_recepisse', 'siege_social'],
                'dossier' => ['numero_dossier'],
                'document' => ['numero_document', 'date_generation', 'qr_code_url'],
                'agent' => ['nom'],
            ],
            'required_variables' => [
                'organisation.nom',
                'organisation.numero_recepisse',
                'document.numero_document',
            ],
            'pdf_config' => [
                'format' => 'a4',
                'orientation' => 'portrait',
                'margins' => ['top' => 20, 'bottom' => 20, 'left' => 15, 'right' => 15],
            ],
            'has_qr_code' => true,
            'has_watermark' => true,
            'has_signature' => true,
            'signature_image' => null,
            'auto_generate' => true,
            'generation_delay_hours' => 0,
            'is_active' => true,
        ]);

        $this->command->info('  ✅ 2 templates Confession Religieuse créés');
    }
}