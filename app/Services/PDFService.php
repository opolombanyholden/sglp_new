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
            // Récupérer le template actif
            $template = DocumentTemplate::where('type_document', $typeDocument)
                ->where('is_active', true)
                ->first();

            if (!$template) {
                throw new \Exception("Template '{$typeDocument}' introuvable ou inactif");
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
                'organisation_id' => $dossier->organisation_id ?? null
            ]);
            throw new \Exception("Erreur lors de la génération du document: " . $e->getMessage());
        }
    }
}