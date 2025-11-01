<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\Dossier;
use App\Models\WorkflowStep;
use App\Models\OperationType;
use Illuminate\Support\Collection;

/**
 * SERVICE DE RÉSOLUTION DE TEMPLATES
 * 
 * Trouve le template approprié selon le contexte :
 * - Type d'organisation
 * - Type d'opération
 * - Étape du workflow
 * 
 * Projet : SGLP
 */
class DocumentTemplateResolver
{
    /**
     * Trouver le template approprié selon le contexte
     * 
     * @param Dossier $dossier Dossier concerné
     * @param WorkflowStep $step Étape courante
     * @return DocumentTemplate|null
     */
    public function resolve(Dossier $dossier, WorkflowStep $step): ?DocumentTemplate
    {
        $organisationTypeId = $dossier->organisation->organisation_type_id;
        $operationTypeId = $this->getOperationTypeId($dossier->type_operation);
        
        return DocumentTemplate::forContext(
            $organisationTypeId,
            $operationTypeId,
            $step->id
        )->first();
    }

    /**
     * Trouver tous les templates pour un contexte donné
     * 
     * @param int $orgTypeId Type d'organisation
     * @param int|null $opTypeId Type d'opération
     * @param int|null $stepId Étape workflow
     * @return Collection
     */
    public function findAll(int $orgTypeId, ?int $opTypeId = null, ?int $stepId = null): Collection
    {
        return DocumentTemplate::forContext($orgTypeId, $opTypeId, $stepId)
            ->orderBy('type_document')
            ->get();
    }

    /**
     * Trouver tous les templates auto-générés pour une étape
     * 
     * @param WorkflowStep $step Étape du workflow
     * @return Collection
     */
    public function getAutoGenerateTemplates(WorkflowStep $step): Collection
    {
        return DocumentTemplate::autoGenerate($step->id)->get();
    }

    /**
     * Vérifier si un document doit être généré automatiquement
     * 
     * @param Dossier $dossier Dossier concerné
     * @param WorkflowStep $step Étape courante
     * @return bool
     */
    public function shouldAutoGenerate(Dossier $dossier, WorkflowStep $step): bool
    {
        $template = $this->resolve($dossier, $step);
        
        return $template && $template->shouldAutoGenerate();
    }

    /**
     * Obtenir l'ID du type d'opération depuis le code
     * 
     * @param string $typeOperation Code du type d'opération
     * @return int|null
     */
    protected function getOperationTypeId(string $typeOperation): ?int
    {
        return OperationType::where('code', $typeOperation)->value('id');
    }

    /**
     * Trouver un template par code
     * 
     * @param string $code Code du template
     * @return DocumentTemplate|null
     */
    public function findByCode(string $code): ?DocumentTemplate
    {
        return DocumentTemplate::where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Obtenir les templates disponibles pour une organisation
     * 
     * @param int $organisationId ID de l'organisation
     * @return Collection
     */
    public function getAvailableTemplates(int $organisationId): Collection
    {
        $organisation = \App\Models\Organisation::findOrFail($organisationId);
        
        return DocumentTemplate::where('organisation_type_id', $organisation->organisation_type_id)
            ->where('is_active', true)
            ->orderBy('type_document')
            ->orderBy('nom')
            ->get();
    }
}