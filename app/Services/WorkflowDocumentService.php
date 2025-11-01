<?php

namespace App\Services;

use App\Models\Dossier;
use App\Models\WorkflowStep;
use App\Models\DossierValidation;
use Illuminate\Support\Facades\Log;

/**
 * SERVICE D'INTÉGRATION WORKFLOW-DOCUMENTS
 * 
 * Gère la génération automatique de documents lors des étapes du workflow
 * 
 * Projet : SGLP
 */
class WorkflowDocumentService
{
    protected DocumentTemplateResolver $templateResolver;
    protected DocumentGenerationService $documentGenerator;

    public function __construct(
        DocumentTemplateResolver $templateResolver,
        DocumentGenerationService $documentGenerator
    ) {
        $this->templateResolver = $templateResolver;
        $this->documentGenerator = $documentGenerator;
    }

    /**
     * Générer automatiquement les documents requis pour une étape
     * 
     * @param Dossier $dossier Dossier concerné
     * @param WorkflowStep $step Étape courante
     * @param DossierValidation|null $validation Validation associée
     * @return array Documents générés
     */
    public function generateStepDocuments(
        Dossier $dossier, 
        WorkflowStep $step, 
        ?DossierValidation $validation = null
    ): array {
        $generatedDocuments = [];

        // Trouver tous les templates auto-générés pour cette étape
        $templates = $this->templateResolver->getAutoGenerateTemplates($step);

        foreach ($templates as $template) {
            // Vérifier que le template correspond au type d'organisation
            if ($template->organisation_type_id !== $dossier->organisation->organisation_type_id) {
                continue;
            }

            // Vérifier le type d'opération si spécifié
            if ($template->operation_type_id) {
                $operationTypeId = $this->getOperationTypeId($dossier->type_operation);
                if ($template->operation_type_id !== $operationTypeId) {
                    continue;
                }
            }

            try {
                // Générer le document
                $result = $this->documentGenerator->generate($template, [
                    'organisation_id' => $dossier->organisation_id,
                    'dossier_id' => $dossier->id,
                    'dossier_validation_id' => $validation?->id,
                ]);

                $generatedDocuments[] = [
                    'template' => $template,
                    'generation' => $result['metadata'],
                    'filename' => $result['filename'],
                ];

                // Lier le document à la validation si elle existe
                if ($validation) {
                    $validation->update([
                        'document_generation_id' => $result['metadata']->id,
                    ]);
                }

                Log::info('Document auto-généré', [
                    'dossier_id' => $dossier->id,
                    'template_id' => $template->id,
                    'numero_document' => $result['metadata']->numero_document,
                ]);

            } catch (\Exception $e) {
                Log::error('Erreur génération automatique', [
                    'dossier_id' => $dossier->id,
                    'template_id' => $template->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $generatedDocuments;
    }

    /**
     * Hook appelé après validation d'une étape
     * 
     * @param DossierValidation $validation Validation effectuée
     */
    public function onStepValidated(DossierValidation $validation): void
    {
        if ($validation->decision !== 'approuve') {
            return; // Ne générer que si approuvé
        }

        $dossier = $validation->dossier;
        $step = $validation->workflowStep;

        // Générer automatiquement les documents
        $this->generateStepDocuments($dossier, $step, $validation);
    }

    /**
     * Obtenir l'ID du type d'opération
     * 
     * @param string $typeOperation Code du type
     * @return int|null
     */
    protected function getOperationTypeId(string $typeOperation): ?int
    {
        return \DB::table('operation_types')
            ->where('code', $typeOperation)
            ->value('id');
    }
}