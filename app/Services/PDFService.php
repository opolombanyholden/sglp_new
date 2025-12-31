<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\DocumentTemplate;
use App\Services\DocumentGenerationService;
use Illuminate\Support\Facades\Log;

/**
 * SERVICE PDF - VERSION SIMPLIFIÉE
 * 
 * Ce service délègue toute la génération PDF à DocumentGenerationService
 * pour avoir un seul système de gestion via admin/document-templates
 */
class PDFService
{
    protected DocumentGenerationService $documentService;

    public function __construct(DocumentGenerationService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Générer l'accusé de réception
     */
    public function generateAccuseReception(Dossier $dossier)
    {
        return $this->generateDocument($dossier, 'accuse_reception');
    }

    /**
     * Générer le récépissé provisoire
     */
    public function generateRecepisseProvisoire(Dossier $dossier)
    {
        return $this->generateDocument($dossier, 'recepisse_provisoire');
    }

    /**
     * Générer le récépissé définitif
     */
    public function generateRecepisseDefinitif(Dossier $dossier)
    {
        return $this->generateDocument($dossier, 'recepisse_definitif');
    }

    /**
     * Méthode générique pour générer un document via DocumentGenerationService
     * 
     * @param Dossier $dossier
     * @param string $typeDocument Type de document (accuse_reception, recepisse_provisoire, etc.)
     * @return \Mpdf\Mpdf
     */
    protected function generateDocument(Dossier $dossier, string $typeDocument)
    {
        try {
            // Charger l'organisation avec son type
            $dossier->loadMissing('organisation.organisationType');

            // DIMENSION 1: Type d'organisation
            $organisationTypeId = $dossier->organisation->organisation_type_id ?? null;

            // DIMENSION 2: Type d'opération (conversion code -> id)
            $typeOperation = $dossier->type_operation ?? 'creation';
            $operationType = \App\Models\OperationType::where('code', $typeOperation)->first();
            $operationTypeId = $operationType?->id;

            Log::info("PDFService: Recherche template avec 3 dimensions", [
                'dossier_id' => $dossier->id,
                'type_document' => $typeDocument,
                'organisation_type_id' => $organisationTypeId,
                'operation_type_id' => $operationTypeId,
                'type_operation' => $typeOperation,
            ]);

            $template = null;

            // 1. Recherche exacte avec les 3 dimensions
            if ($organisationTypeId && $operationTypeId) {
                $template = DocumentTemplate::where('type_document', $typeDocument)
                    ->where('organisation_type_id', $organisationTypeId)
                    ->where('operation_type_id', $operationTypeId)
                    ->where('is_active', true)
                    ->first();

                if ($template) {
                    Log::info("Template exact trouvé (3 dimensions)", [
                        'template_id' => $template->id,
                        'template_path' => $template->template_path,
                    ]);
                }
            }

            // 2. Template avec organisation + opération générique (null)
            if (!$template && $organisationTypeId) {
                $template = DocumentTemplate::where('type_document', $typeDocument)
                    ->where('organisation_type_id', $organisationTypeId)
                    ->where(function ($q) {
                        $q->whereNull('operation_type_id')
                            ->orWhere('operation_type_id', 0);
                    })
                    ->where('is_active', true)
                    ->first();

                if ($template) {
                    Log::info("Template générique organisation trouvé", [
                        'template_id' => $template->id,
                        'template_path' => $template->template_path,
                    ]);
                }
            }

            // 3. Dernier recours: n'importe quel template actif
            if (!$template) {
                $template = DocumentTemplate::where('type_document', $typeDocument)
                    ->where('is_active', true)
                    ->first();

                if ($template) {
                    Log::info("Template fallback trouvé", [
                        'template_id' => $template->id,
                        'template_path' => $template->template_path,
                    ]);
                }
            }

            if (!$template) {
                throw new \Exception("Template '{$typeDocument}' introuvable ou inactif pour organisation_type={$organisationTypeId}, operation={$typeOperation}");
            }

            // Préparer les données
            $data = [
                'organisation_id' => $dossier->organisation_id,
                'dossier_id' => $dossier->id,
            ];

            // Générer via DocumentGenerationService
            $result = $this->documentService->generate($template, $data);

            // Retourner l'objet Mpdf
            return $result['pdf'];

        } catch (\Exception $e) {
            Log::error("Erreur génération {$typeDocument}: " . $e->getMessage(), [
                'dossier_id' => $dossier->id ?? null,
                'organisation_id' => $dossier->organisation_id ?? null,
                'type_operation' => $dossier->type_operation ?? 'unknown',
            ]);
            throw new \Exception("Erreur lors de la génération du document: " . $e->getMessage());
        }
    }
}