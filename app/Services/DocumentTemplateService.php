<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

/**
 * SERVICE - GESTION DES TEMPLATES DE DOCUMENTS
 * 
 * Service pour la génération et manipulation des templates de documents
 * 
 * Projet : SGLP
 */
class DocumentTemplateService
{
    /**
     * Générer des données de test pour un template
     * 
     * @param DocumentTemplate $template
     * @return array
     */
    public function generateTestData(DocumentTemplate $template): array
    {
        return [
            'organisation' => [
                'nom' => 'Organisation Test',
                'sigle' => 'ORG-TEST',
                'type' => 'Association',
                'adresse' => '123 Avenue Test, Libreville',
                'telephone' => '+241 01 23 45 67',
                'email' => 'contact@org-test.ga',
            ],
            'dossier' => [
                'numero' => 'DOSS-TEST-001',
                'date_soumission' => now()->format('d/m/Y'),
                'statut' => 'En cours',
            ],
            'document' => [
                'numero' => 'DOC-TEST-001',
                'date_generation' => now()->format('d/m/Y H:i'),
                'qr_code_token' => 'TEST-QR-CODE-TOKEN',
            ],
        ];
    }

    /**
     * Générer un PDF de prévisualisation
     * 
     * @param DocumentTemplate $template
     * @param array $data
     * @return string
     */
    public function generatePreviewPdf(DocumentTemplate $template, array $data): string
    {
        try {
            // Charger le template et générer le HTML
            $html = view($template->template_path, $data)->render();
            
            // TODO: Implémenter la génération PDF avec une librairie comme DomPDF ou mPDF
            // Pour l'instant, retourner le HTML
            return $html;
            
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF preview: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Valider un template
     * 
     * @param string $templatePath
     * @return bool
     */
    public function validateTemplate(string $templatePath): bool
    {
        try {
            // Vérifier si le template existe
            if (!View::exists($templatePath)) {
                return false;
            }
            
            // Essayer de compiler le template
            $html = view($templatePath, $this->generateTestData(new DocumentTemplate()))->render();
            
            return !empty($html);
            
        } catch (\Exception $e) {
            Log::error('Erreur validation template: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir les variables disponibles pour un template
     * 
     * @return array
     */
    public function getAvailableVariables(): array
    {
        return [
            'organisation' => [
                'nom' => 'Nom de l\'organisation',
                'sigle' => 'Sigle',
                'type' => 'Type d\'organisation',
                'adresse' => 'Adresse complète',
                'telephone' => 'Numéro de téléphone',
                'email' => 'Email de contact',
            ],
            'dossier' => [
                'numero' => 'Numéro du dossier',
                'date_soumission' => 'Date de soumission',
                'statut' => 'Statut du dossier',
            ],
            'document' => [
                'numero' => 'Numéro du document',
                'date_generation' => 'Date de génération',
                'qr_code_token' => 'Token QR Code',
            ],
        ];
    }
}