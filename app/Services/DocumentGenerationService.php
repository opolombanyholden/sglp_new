<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\DocumentGeneration;
use App\Models\Organisation;
use App\Models\Dossier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * SERVICE DE GÉNÉRATION DE DOCUMENTS
 * 
 * Service principal pour générer les documents PDF à la volée
 * à partir des templates Blade configurés
 * 
 * Projet : SGLP
 */
class DocumentGenerationService
{
    protected QRCodeService $qrCodeService;
    protected DocumentNumberingService $numberingService;

    public function __construct(
        QRCodeService $qrCodeService,
        DocumentNumberingService $numberingService
    ) {
        $this->qrCodeService = $qrCodeService;
        $this->numberingService = $numberingService;
    }

    /**
     * Générer un document à la volée
     * 
     * @param DocumentTemplate $template Template à utiliser
     * @param array $data Données du document
     * @return array ['pdf' => stream, 'metadata' => DocumentGeneration, 'filename' => string]
     * @throws \Exception
     */
    public function generate(DocumentTemplate $template, array $data): array
    {
        try {
            // 1. Vérifier que le template existe
            if (!$template->templateExists()) {
                throw new \Exception("Le template Blade '{$template->template_path}' n'existe pas.");
            }

            // 2. Générer numéro unique
            $numeroDocument = $this->numberingService->generate(
                $template->type_document,
                $data['organisation_id']
            );

            // 3. Générer token QR code
            $qrToken = $this->qrCodeService->generateToken();
            $qrUrl = $this->qrCodeService->getVerificationUrl($qrToken);

            // 4. Préparer les variables
            $variables = $this->prepareVariables($data, $numeroDocument, $qrUrl);

            // 5. Valider les variables requises
            $this->validateRequiredVariables($template, $variables);

            // 6. Générer hash de vérification
            $hash = $this->generateHash($numeroDocument, $variables);

            // 7. Enregistrer les métadonnées (LOG uniquement)
            $generation = DocumentGeneration::create([
                'document_template_id' => $template->id,
                'dossier_id' => $data['dossier_id'] ?? null,
                'dossier_validation_id' => $data['dossier_validation_id'] ?? null,
                'organisation_id' => $data['organisation_id'],
                'numero_document' => $numeroDocument,
                'type_document' => $template->type_document,
                'qr_code_token' => $qrToken,
                'qr_code_url' => $qrUrl,
                'hash_verification' => $hash,
                'variables_data' => $variables,
                'generated_by' => Auth::id(),
                'generated_at' => now(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // 8. Générer le HTML avec variables
            $html = $this->renderTemplate($template, $variables);

            // 9. Générer le PDF en mémoire
            $pdf = $this->generatePDF($html, $template);

            Log::info('Document généré avec succès', [
                'template_id' => $template->id,
                'numero_document' => $numeroDocument,
                'organisation_id' => $data['organisation_id'],
            ]);

            return [
                'pdf' => $pdf,
                'metadata' => $generation,
                'filename' => $this->generateFilename($template, $numeroDocument),
            ];

        } catch (\Exception $e) {
            Log::error('Erreur génération document', [
                'template_id' => $template->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Régénérer un document existant
     * 
     * @param DocumentGeneration $generation Document à régénérer
     * @return array
     * @throws \Exception
     */
    public function regenerate(DocumentGeneration $generation): array
    {
        if (!$generation->is_valid) {
            throw new \Exception('Ce document a été invalidé et ne peut être régénéré.');
        }

        // Incrémenter le compteur de téléchargement
        $generation->incrementDownloadCount();

        $template = $generation->template;

        // Vérifier que le template existe toujours
        if (!$template->templateExists()) {
            throw new \Exception("Le template Blade '{$template->template_path}' n'existe plus.");
        }

        // Générer le HTML avec les variables sauvegardées
        $html = $this->renderTemplate($template, $generation->variables_data);

        // Générer le PDF
        $pdf = $this->generatePDF($html, $template);

        Log::info('Document régénéré', [
            'generation_id' => $generation->id,
            'numero_document' => $generation->numero_document,
        ]);

        return [
            'pdf' => $pdf,
            'metadata' => $generation,
            'filename' => $this->generateFilename($template, $generation->numero_document),
        ];
    }

    /**
     * Préparer les variables pour le template
     * 
     * @param array $data Données brutes
     * @param string $numeroDocument Numéro du document
     * @param string $qrUrl URL du QR code
     * @return array Variables préparées
     */
    protected function prepareVariables(array $data, string $numeroDocument, string $qrUrl): array
    {
        $organisation = Organisation::with(['organisationType'])->findOrFail($data['organisation_id']);
        $dossier = isset($data['dossier_id']) ? Dossier::find($data['dossier_id']) : null;

        $variables = [
            'organisation' => [
                'nom' => $organisation->nom,
                'sigle' => $organisation->sigle,
                'type' => $organisation->organisationType->nom,
                'numero_recepisse' => $organisation->numero_recepisse,
                'date_creation' => $organisation->date_creation ? $organisation->date_creation->format('d/m/Y') : 'N/A',
                'siege_social' => $organisation->siege_social,
                'province' => $organisation->province,
                'telephone' => $organisation->telephone,
                'email' => $organisation->email,
            ],
            'document' => [
                'numero_document' => $numeroDocument,
                'date_generation' => now()->format('d/m/Y à H:i'),
                'qr_code_url' => $qrUrl,
                'url_verification' => $qrUrl,
            ],
            'agent' => [
                'nom' => Auth::user()->name ?? 'Système',
                'email' => Auth::user()->email ?? '',
            ],
        ];

        // Ajouter les données du dossier si disponibles
        if ($dossier) {
            $variables['dossier'] = [
                'numero_dossier' => $dossier->numero_dossier,
                'type_operation' => $dossier->type_operation,
                'date_soumission' => $dossier->submitted_at ? $dossier->submitted_at->format('d/m/Y à H:i') : 'N/A',
                'statut' => $dossier->statut,
            ];
        }

        // Fusionner avec les variables personnalisées
        if (isset($data['custom_variables'])) {
            $variables = array_merge($variables, $data['custom_variables']);
        }

        return $variables;
    }

    /**
     * Valider les variables requises
     * 
     * @param DocumentTemplate $template Template
     * @param array $variables Variables à valider
     * @throws \Exception
     */
    protected function validateRequiredVariables(DocumentTemplate $template, array $variables): void
    {
        if (empty($template->required_variables)) {
            return;
        }

        $missingVars = [];
        $flatVars = $this->flattenArray($variables);

        foreach ($template->required_variables as $requiredVar) {
            if (!isset($flatVars[$requiredVar])) {
                $missingVars[] = $requiredVar;
            }
        }

        if (!empty($missingVars)) {
            throw new \Exception('Variables requises manquantes : ' . implode(', ', $missingVars));
        }
    }

    /**
     * Rendre le template HTML avec variables
     * 
     * @param DocumentTemplate $template Template
     * @param array $variables Variables
     * @return string HTML généré
     */
    protected function renderTemplate(DocumentTemplate $template, array $variables): string
    {
        // Générer le QR code SVG
        $qrCodeSvg = $template->has_qr_code 
            ? $this->qrCodeService->generateSVG($variables['document']['qr_code_url'])
            : '';

        // Rendre le template Blade
        $html = View::make($template->template_path, [
            ...$variables,
            'qr_code_svg' => $qrCodeSvg,
            'has_qr_code' => $template->has_qr_code,
            'has_signature' => $template->has_signature,
            'has_watermark' => $template->has_watermark,
            'signature_path' => $template->getSignatureFullPath(),
        ])->render();

        return $html;
    }

    /**
     * Générer le PDF en mémoire
     * 
     * @param string $html HTML du document
     * @param DocumentTemplate $template Template
     * @return \Barryvdh\DomPDF\PDF
     */
    protected function generatePDF(string $html, DocumentTemplate $template)
    {
        $margins = $template->getPdfMargins();

        $pdf = Pdf::loadHTML($html)
            ->setPaper($template->getPdfFormat(), $template->getPdfOrientation())
            ->setOption('margin_top', $margins['top'])
            ->setOption('margin_bottom', $margins['bottom'])
            ->setOption('margin_left', $margins['left'])
            ->setOption('margin_right', $margins['right'])
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf;
    }

    /**
     * Générer le nom du fichier PDF
     * 
     * @param DocumentTemplate $template Template
     * @param string $numeroDocument Numéro du document
     * @return string Nom du fichier
     */
    protected function generateFilename(DocumentTemplate $template, string $numeroDocument): string
    {
        $slug = \Str::slug($template->nom);
        $cleanNumber = str_replace(['/', '\\', '-'], '_', $numeroDocument);
        
        return "{$slug}_{$cleanNumber}.pdf";
    }

    /**
     * Générer hash de vérification
     * 
     * @param string $numeroDocument Numéro du document
     * @param array $variables Variables utilisées
     * @return string Hash SHA-256
     */
    protected function generateHash(string $numeroDocument, array $variables): string
    {
        return hash('sha256', $numeroDocument . json_encode($variables) . config('app.key'));
    }

    /**
     * Aplatir un tableau multidimensionnel
     * 
     * @param array $array Tableau à aplatir
     * @param string $prefix Préfixe pour les clés
     * @return array Tableau aplati
     */
    protected function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;
            
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }
}