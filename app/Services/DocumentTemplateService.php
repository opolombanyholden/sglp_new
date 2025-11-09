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
        $now = now();
        
        return [
            'organisation' => [
                'nom' => 'Association Test SGLP',
                'sigle' => 'AT-SGLP',
                'denomination' => 'Association Test SGLP',
                'objet' => 'Promouvoir le développement social et culturel au Gabon',
                'siege_social' => '123 Avenue Bouet, Quartier Plaine Orety, Libreville',
                'adresse' => '123 Avenue Bouet, Quartier Plaine Orety, Libreville',
                'province' => 'Estuaire',
                'departement' => 'Libreville',
                'email' => 'contact@association-test.ga',
                'telephone' => '066119001',
                'type' => 'Association',
                'fondateurs_count' => 15,
                'date_creation' => $now->format('d/m/Y'), // ✅ AJOUTÉ
            ],
            'dossier' => [
                'numero_dossier' => 'SGLP-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'numero' => 'DOSS-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'date_depot' => $now->format('d/m/Y'),
                'date_soumission' => $now->format('d/m/Y'),
                'date_creation' => $now->format('d/m/Y'), // ✅ AJOUTÉ
                'statut' => 'En cours d\'instruction',
            ],
            'document' => [
                'numero_document' => 'DOC-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'numero' => 'DOC-' . date('Ymd') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'date_generation' => $now->format('d/m/Y H:i'),
                'date_creation' => $now->format('d/m/Y'), // ✅ AJOUTÉ
                'qr_code_token' => 'QR-' . md5(uniqid()),
                'qr_code_url' => 'https://sglp.ga/verify/QR-' . md5(uniqid()),
            ],
            'agent' => [
                'nom' => 'AGENT TEST',
                'prenom' => 'Prénom',
                'fonction' => 'Chargé de dossier',
            ],
            'qrCode' => null,
            'signature' => null,
            'signataire' => 'LE DIRECTEUR GÉNÉRAL DES LIBERTÉS PUBLIQUES',
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