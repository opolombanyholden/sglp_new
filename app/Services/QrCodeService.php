<?php
namespace App\Services;

use App\Models\QrCode;
use App\Models\Dossier;
use App\Models\Organisation;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeGenerator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * ========================================
 * SERVICE DE GÉNÉRATION DE QR CODES
 * ========================================
 * 
 * Service amélioré pour générer et gérer les QR codes
 * Compatible avec DocumentGenerationService et ImageHelperService
 * 
 * CORRECTIONS PRINCIPALES :
 * - ✅ Ajout de generateToken() et getVerificationUrl()
 * - ✅ URL de vérification : domaine/annuaire/verify/{qr_code}
 * - ✅ Méthode getQrCodeBase64ForPdf() pour intégration PDF
 * - ✅ Méthode generateForDocument() pour documents
 * - ✅ Correction de regenerateForPdf()
 * - ✅ Uniformisation des URLs
 * 
 * Projet : SGLP
 * Version : 2.0 - Mise à jour globale
 */
class QRCodeService
{
    /**
     * ========================================
     * NOUVELLES MÉTHODES PUBLIQUES REQUISES
     * ========================================
     */

    /**
     * ✅ NOUVEAU : Générer un token unique pour QR code
     * 
     * Utilisé par DocumentGenerationService
     * Format : QR-XXXXXXXXXXXX (16 caractères alphanumériques)
     * 
     * @return string Token unique
     */
    public function generateToken(): string
    {
        do {
            $token = 'QR-' . strtoupper(Str::random(16));
        } while (QrCode::where('code', $token)->exists());

        Log::info('Token QR Code généré', ['token' => $token]);

        return $token;
    }

    /**
     * ✅ NOUVEAU : Obtenir l'URL de vérification pour un token
     * 
     * Format : domaine/annuaire/verify/{qr_code}
     * Exemple : https://sglp.ga/annuaire/verify/QR-ABC123XYZ456
     * 
     * @param string $token Token du QR code
     * @return string URL complète de vérification
     */
    public function getVerificationUrl(string $token): string
    {
        $url = url("/annuaire/verify/{$token}");

        Log::debug('URL de vérification générée', [
            'token' => $token,
            'url' => $url
        ]);

        return $url;
    }

    /**
     * Générer un QR Code SVG brut (non encodé)
     * 
     * @param string $url URL à encoder dans le QR code
     * @return string SVG brut
     */
    public function generateSVG(string $url): string
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                Log::warning('Bibliothèque QR Code non disponible');
                return $this->getPlaceholderSvg('TEMP-CODE');
            }

            $svg = QrCodeGenerator::format('svg')
                ->size(150)
                ->margin(2)
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($url);

            // Nettoyer le XML header
            $svg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $svg);

            return trim($svg);

        } catch (\Exception $e) {
            Log::error('Erreur génération SVG QR Code', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return $this->getPlaceholderSvg('ERROR');
        }
    }


    /**
     * ✅ MODIFIÉ : Obtenir le QR code en base64 pour PDF (PNG UNIQUEMENT)
     * 
     * HTML2PDF ne supporte PAS les SVG !
     * Cette méthode retourne UNIQUEMENT du PNG en base64
     * 
     * @param QrCode|null $qrCode QR code existant (optionnel)
     * @param string|null $url URL pour générer un nouveau QR (optionnel)
     * @return string Base64 data URI (data:image/png;base64,...)
     */
    public function getQrCodeBase64ForPdf(?QrCode $qrCode = null, ?string $url = null): string
    {
        try {
            // CAS 1 : Utiliser un QrCode existant
            if ($qrCode) {
                // ✅ PRIORITÉ 1 : PNG base64 (HTML2PDF compatible)
                if (!empty($qrCode->png_base64)) {
                    // Vérifier si déjà au format data URI
                    if (strpos($qrCode->png_base64, 'data:image/') === 0) {
                        return $qrCode->png_base64;
                    }

                    // DÉTECTION DU TYPE DE CONTENU (PNG vs SVG)
                    // Si ImageMagick manque, le fallback génère du SVG encodé en base64
                    $decoded = base64_decode(substr($qrCode->png_base64, 0, 100)); // Lire le début

                    if (strpos($decoded, '<svg') !== false || strpos($decoded, '<?xml') !== false) {
                        // C'est du SVG !
                        Log::info('QR Code détecté comme SVG (fallback activé)', ['code' => $qrCode->code]);
                        return "data:image/svg+xml;base64,{$qrCode->png_base64}";
                    }

                    // C'est du PNG standard
                    return "data:image/png;base64,{$qrCode->png_base64}";
                }

                // ❌ NE PAS utiliser SVG - HTML2PDF ne le supporte pas !
                // if (!empty($qrCode->svg_content)) { ... }

                // Fallback : Générer PNG depuis l'URL de vérification
                if (!empty($qrCode->verification_url)) {
                    $url = $qrCode->verification_url;
                }
            }

            // CAS 2 : Générer un nouveau QR PNG depuis une URL
            if ($url) {
                // generateQrBase64FromUrl génère du PNG
                return $this->generateQrBase64FromUrl($url);
            }

            // CAS 3 : Aucune source valide - retourner placeholder PNG
            Log::warning('QR Code base64 : aucune source valide, utilisation placeholder');
            return $this->getPlaceholderBase64();

        } catch (\Exception $e) {
            Log::error('Erreur getQrCodeBase64ForPdf', [
                'qr_code_id' => $qrCode->id ?? null,
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return $this->getPlaceholderBase64();
        }
    }

    /**
     * ✅ NOUVEAU : Générer un QR code pour un document
     * 
     * Méthode simplifiée pour DocumentGenerationService
     * 
     * @param string $documentNumero Numéro unique du document
     * @param array $data Données du document
     * @return QrCode QR code créé
     */
    public function generateForDocument(string $documentNumero, array $data): QrCode
    {
        try {
            // Générer token (pour référence interne uniquement)
            $code = $this->generateToken();

            // ✅ CORRECTION : URL utilise le numéro de document
            // Format : /annuaire/verify/{numero_document}
            $verificationUrl = url("/annuaire/verify/{$documentNumero}");

            Log::info('URL de vérification générée', [
                'document_numero' => $documentNumero,
                'url' => $verificationUrl,
                'token' => $code
            ]);

            // Préparer les données de vérification
            $donneesVerification = [
                'document_numero' => $documentNumero,
                'organisation_nom' => $data['organisation_nom'] ?? 'Organisation',
                'organisation_id' => $data['organisation_id'] ?? null,
                'type_document' => $data['type_document'] ?? 'Document',
                'date_generation' => now()->toISOString(),
                'verification_url' => $verificationUrl,
                'hash_verification' => null
            ];

            // Générer hash de vérification
            $hashVerification = hash('sha256', json_encode($donneesVerification, JSON_UNESCAPED_UNICODE));
            $donneesVerification['hash_verification'] = $hashVerification;

            // Générer les QR codes (SVG + PNG)
            $svgContent = $this->generateRealQrCodeSvg($verificationUrl, $code);
            $pngBase64 = $this->generateRealQrCodePng($verificationUrl, $code);
            $qrFilePath = $this->saveQrCodeAsFile($verificationUrl, $code, 'svg');

            // Créer l'enregistrement
            $qrCode = QrCode::create([
                'code' => $code,
                'type' => 'document_verification',
                'verifiable_type' => null,
                'verifiable_id' => null,
                'document_numero' => $documentNumero,
                'donnees_verification' => $donneesVerification,
                'hash_verification' => $hashVerification,
                'svg_content' => $svgContent,
                'png_base64' => $pngBase64,
                'file_path' => $qrFilePath,
                'verification_url' => $verificationUrl, // ✅ URL avec numero_document
                'nombre_verifications' => 0,
                'expire_at' => now()->addYears(5),
                'is_active' => true
            ]);

            Log::info('QR Code généré pour document', [
                'qr_code_id' => $qrCode->id,
                'code' => $code,
                'document_numero' => $documentNumero,
                'verification_url' => $verificationUrl
            ]);

            return $qrCode;

        } catch (\Exception $e) {
            Log::error('Erreur génération QR Code pour document', [
                'document_numero' => $documentNumero,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * ========================================
     * MÉTHODES EXISTANTES CORRIGÉES
     * ========================================
     */

    /**
     * ✅ CORRIGÉ : Générer un QR Code pour un dossier avec URL correcte
     */
    public function generateForDossier(Dossier $dossier)
    {
        try {
            if (!$dossier || !$dossier->organisation) {
                Log::error('QrCodeService: Dossier ou organisation manquant');
                return null;
            }

            $organisation = $dossier->organisation;

            // ✅ CORRECTION : Générer token et URL correcte
            $code = $this->generateToken();
            $verificationUrl = $this->getVerificationUrl($code); // URL : domaine/annuaire/verify/{code}

            $dateSubmission = $this->formatDateSafely($dossier->submitted_at);

            $donneesVerification = [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation_nom' => $organisation->nom,
                'organisation_id' => $organisation->id,
                'organisation_type' => $organisation->type ?? 'Type non défini',
                'numero_recepisse' => $organisation->numero_recepisse,
                'date_soumission' => $dateSubmission,
                'statut' => $dossier->statut,
                'province' => $organisation->province,
                'verification_url' => $verificationUrl,
                'hash_verification' => null
            ];

            $hashVerification = hash('sha256', json_encode($donneesVerification, JSON_UNESCAPED_UNICODE));
            $donneesVerification['hash_verification'] = $hashVerification;

            // Générer QR code et sauvegarder en fichier (SVG prioritaire)
            $svgContent = $this->generateRealQrCodeSvg($verificationUrl, $code);
            $pngBase64 = $this->generateRealQrCodePng($verificationUrl, $code);
            $qrFilePath = $this->saveQrCodeAsFile($verificationUrl, $code, 'svg');

            $qrCode = QrCode::create([
                'code' => $code,
                'type' => 'dossier_verification',
                'verifiable_type' => Dossier::class,
                'verifiable_id' => $dossier->id,
                'document_numero' => $dossier->numero_dossier,
                'donnees_verification' => $donneesVerification,
                'hash_verification' => $hashVerification,
                'svg_content' => $svgContent,
                'png_base64' => $pngBase64,
                'file_path' => $qrFilePath,
                'verification_url' => $verificationUrl,
                'nombre_verifications' => 0,
                'expire_at' => now()->addYears(5),
                'is_active' => true
            ]);

            Log::info('QR Code généré pour dossier', [
                'qr_code_id' => $qrCode->id,
                'code' => $code,
                'dossier_id' => $dossier->id,
                'verification_url' => $verificationUrl
            ]);

            return $qrCode;

        } catch (\Exception $e) {
            Log::error('Erreur génération QR Code pour dossier', [
                'dossier_id' => $dossier->id ?? 'null',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ CORRIGÉ : Régénérer un QR code pour PDF (sans ImageMagick)
     */
    public function regenerateForPdf(QrCode $qrCode): bool
    {
        try {
            if (!$qrCode->verification_url) {
                Log::error('Impossible de régénérer QR : URL manquante', [
                    'qr_code_id' => $qrCode->id
                ]);
                return false;
            }

            // Régénérer SVG et PNG (méthodes corrigées)
            $svgContent = $this->generateRealQrCodeSvg($qrCode->verification_url, $qrCode->code);
            $pngBase64 = $this->generateRealQrCodePng($qrCode->verification_url, $qrCode->code);

            // Tenter de sauvegarder le fichier
            $fileName = $this->saveQrCodeAsFile($qrCode->verification_url, $qrCode->code, 'svg');
            $saved = !empty($fileName);

            // Préparer les données de mise à jour
            $updateData = [
                'svg_content' => $svgContent,
                'png_base64' => $pngBase64
            ];

            if ($saved) {
                $updateData['file_path'] = $fileName;
            }

            $qrCode->update($updateData);

            Log::info('QR Code régénéré pour PDF', [
                'qr_code_id' => $qrCode->id,
                'svg_size' => strlen($svgContent ?? ''),
                'base64_size' => strlen($pngBase64 ?? ''),
                'file_saved' => $saved ? 'YES' : 'NO'
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erreur régénération QR code pour PDF', [
                'qr_code_id' => $qrCode->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * ========================================
     * MÉTHODES PRIVÉES DE GÉNÉRATION
     * ========================================
     */

    /**
     * ✅ MODIFIÉ : Générer un QR Code PNG base64 depuis une URL (HTML2PDF compatible)
     */
    private function generateQrBase64FromUrl(string $url): string
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                return $this->getPlaceholderBase64();
            }

            // Générer PNG au lieu de SVG (HTML2PDF ne supporte pas SVG)
            $pngData = QrCodeGenerator::format('png')
                ->size(150)
                ->margin(2)
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($url);

            // Encoder en base64
            $base64 = base64_encode($pngData);
            return "data:image/png;base64,{$base64}";

        } catch (\Exception $e) {
            Log::error('Erreur génération QR base64', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return $this->getPlaceholderBase64();
        }
    }

    /**
     * Sauvegarder le QR code en tant que fichier (SVG prioritaire)
     */
    private function saveQrCodeAsFile($url, $code, $format = 'svg')
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                Log::error('Bibliothèque QR Code non disponible pour fichier');
                return null;
            }

            // Priorité au SVG (pas d'ImageMagick requis)
            if ($format === 'svg') {
                $qrData = QrCodeGenerator::format('svg')
                    ->size(120)
                    ->margin(2)
                    ->color(0, 0, 0)
                    ->backgroundColor(255, 255, 255)
                    ->errorCorrection('H')
                    ->generate($url);
            } else {
                // PNG uniquement si GD est disponible
                if (!extension_loaded('gd')) {
                    Log::warning('Extension GD non disponible, utilisation SVG');
                    return $this->saveQrCodeAsFile($url, $code, 'svg');
                }

                $qrData = QrCodeGenerator::format('png')
                    ->size(120)
                    ->margin(2)
                    ->color(0, 0, 0)
                    ->backgroundColor(255, 255, 255)
                    ->errorCorrection('H')
                    ->generate($url);
            }

            // Créer le nom de fichier
            $fileName = "qr-codes/{$code}.{$format}";

            // Sauvegarder dans storage/app/public
            $saved = Storage::disk('public')->put($fileName, $qrData);

            if ($saved) {
                Log::info('QR Code fichier sauvegardé', [
                    'code' => $code,
                    'file_name' => $fileName,
                    'format' => $format
                ]);

                return $fileName;
            } else {
                Log::error('Échec sauvegarde QR Code fichier', ['code' => $code]);
                return null;
            }

        } catch (\Exception $e) {
            Log::error('Erreur sauvegarde QR Code fichier', [
                'code' => $code,
                'format' => $format,
                'error' => $e->getMessage()
            ]);

            // Fallback vers SVG si PNG échoue
            if ($format === 'png') {
                Log::info('Fallback vers SVG après échec PNG');
                return $this->saveQrCodeAsFile($url, $code, 'svg');
            }

            return null;
        }
    }

    /**
     * Générer un QR Code RÉEL SVG scannable
     */
    private function generateRealQrCodeSvg($url, $code)
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                return $this->getPlaceholderSvg($code);
            }

            $svg = QrCodeGenerator::format('svg')
                ->size(150)
                ->margin(2)
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($url);

            $svg = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $svg);

            return trim($svg);

        } catch (\Exception $e) {
            Log::error('Erreur génération SVG QR Code', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);
            return $this->getPlaceholderSvg($code);
        }
    }

    /**
     * Générer un QR Code PNG avec fallback SVG
     */
    private function generateRealQrCodePng($url, $code)
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                Log::warning('Bibliothèque QR Code non disponible');
                $svgContent = $this->getPlaceholderSvg($code);
                return base64_encode($svgContent);
            }

            if (!extension_loaded('gd')) {
                Log::warning('Extension GD non disponible, encodage SVG en base64');
                $svgContent = $this->generateRealQrCodeSvg($url, $code);
                return base64_encode($svgContent);
            }

            $png = QrCodeGenerator::format('png')
                ->size(150)
                ->margin(2)
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($url);

            return base64_encode($png);

        } catch (\Exception $e) {
            Log::error('Erreur génération PNG QR Code', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);

            $svgContent = $this->generateRealQrCodeSvg($url, $code);
            return base64_encode($svgContent);
        }
    }

    /**
     * ========================================
     * MÉTHODES UTILITAIRES
     * ========================================
     */

    /**
     * Obtenir l'URL publique du fichier QR code
     */
    public function getQrCodeFileUrl(QrCode $qrCode)
    {
        if (!$qrCode->file_path || !Storage::disk('public')->exists($qrCode->file_path)) {
            // Regénérer le fichier si manquant (SVG prioritaire)
            if ($qrCode->verification_url) {
                $filePath = $this->saveQrCodeAsFile($qrCode->verification_url, $qrCode->code, 'svg');
                if ($filePath) {
                    $qrCode->update(['file_path' => $filePath]);
                    return Storage::disk('public')->url($filePath);
                }
            }
            return null;
        }

        return Storage::disk('public')->url($qrCode->file_path);
    }

    /**
     * Vérifier si le fichier QR code existe
     */
    public function hasQrCodeFile(QrCode $qrCode)
    {
        return $qrCode->file_path && Storage::disk('public')->exists($qrCode->file_path);
    }

    /**
     * Valider un QR code pour PDF
     */
    public function validateQrCodeForPdf(QrCode $qrCode): array
    {
        $validation = [
            'valid' => false,
            'methods_available' => [],
            'recommended_method' => null,
            'issues' => []
        ];

        // Vérifier SVG content
        if (!empty($qrCode->svg_content) && strlen($qrCode->svg_content ?? '') > 500) {
            $validation['methods_available'][] = 'svg_content';
        } else {
            $validation['issues'][] = 'SVG content manquant ou invalide';
        }

        // Vérifier PNG base64
        if (!empty($qrCode->png_base64) && strlen($qrCode->png_base64 ?? '') > 500) {
            $validation['methods_available'][] = 'png_base64';
        } else {
            $validation['issues'][] = 'PNG base64 manquant ou invalide';
        }

        // Vérifier fichier
        if (!empty($qrCode->file_path)) {
            $filePath = storage_path('app/public/' . $qrCode->file_path);
            if (file_exists($filePath)) {
                $validation['methods_available'][] = 'file_to_base64';
            } else {
                $validation['issues'][] = 'Fichier référencé mais non trouvé: ' . $qrCode->file_path;
            }
        }

        // Vérifier URL pour génération
        if (!empty($qrCode->verification_url)) {
            $validation['methods_available'][] = 'generate_from_url';
        } else {
            $validation['issues'][] = 'URL de vérification manquante';
        }

        // Déterminer la méthode recommandée (SVG prioritaire)
        if (in_array('svg_content', $validation['methods_available'])) {
            $validation['recommended_method'] = 'svg_content';
            $validation['valid'] = true;
        } elseif (in_array('png_base64', $validation['methods_available'])) {
            $validation['recommended_method'] = 'png_base64';
            $validation['valid'] = true;
        } elseif (in_array('file_to_base64', $validation['methods_available'])) {
            $validation['recommended_method'] = 'file_to_base64';
            $validation['valid'] = true;
        } elseif (in_array('generate_from_url', $validation['methods_available'])) {
            $validation['recommended_method'] = 'generate_from_url';
            $validation['valid'] = true;
        }

        return $validation;
    }

    /**
     * Vérifier un QR code
     */
    public function verifyQrCode($code)
    {
        try {
            $qrCode = QrCode::where('code', $code)
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('expire_at')
                        ->orWhere('expire_at', '>', now());
                })
                ->first();

            if (!$qrCode) {
                return [
                    'success' => false,
                    'message' => 'QR Code non trouvé ou expiré'
                ];
            }

            $qrCode->increment('nombre_verifications');
            $qrCode->update(['derniere_verification' => now()]);

            $donneesVerification = [];
            if ($qrCode->donnees_verification) {
                if (is_string($qrCode->donnees_verification)) {
                    $donneesVerification = json_decode($qrCode->donnees_verification, true) ?? [];
                } elseif (is_array($qrCode->donnees_verification)) {
                    $donneesVerification = $qrCode->donnees_verification;
                }
            }

            return [
                'success' => true,
                'qr_code' => $qrCode,
                'donnees' => $donneesVerification,
                'verifications_count' => $qrCode->nombre_verifications
            ];

        } catch (\Exception $e) {
            Log::error('Erreur vérification QR Code', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification'
            ];
        }
    }

    /**
     * Vérifier si le QR a un PNG
     */
    public function hasPng(QrCode $qrCode)
    {
        return !empty($qrCode->png_base64) && strlen($qrCode->png_base64 ?? '') > 100;
    }

    /**
     * Vérifier si le QR a un SVG
     */
    public function hasSvg(QrCode $qrCode)
    {
        return !empty($qrCode->svg_content) && strlen($qrCode->svg_content ?? '') > 100;
    }

    /**
     * ========================================
     * MÉTHODES PRIVÉES UTILITAIRES
     * ========================================
     */

    /**
     * Gestion sécurisée des dates
     */
    private function formatDateSafely($date)
    {
        try {
            if (is_null($date)) {
                return now()->toISOString();
            }

            if (is_string($date)) {
                if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $date)) {
                    return $date;
                }

                try {
                    $carbonDate = Carbon::parse($date);
                    return $carbonDate->toISOString();
                } catch (\Exception $e) {
                    return now()->toISOString();
                }
            }

            if ($date instanceof Carbon) {
                return $date->toISOString();
            }

            if ($date instanceof \DateTime) {
                return Carbon::parse($date)->toISOString();
            }

            if (is_numeric($date)) {
                return Carbon::createFromTimestamp($date)->toISOString();
            }

            return now()->toISOString();

        } catch (\Exception $e) {
            return now()->toISOString();
        }
    }

    /**
     * SVG placeholder amélioré
     */
    private function getPlaceholderSvg($code = 'QR-ERROR')
    {
        return '<svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
            <rect width="150" height="150" fill="#f8f9fa" stroke="#000000" stroke-width="2" stroke-dasharray="8,8"/>
            <text x="75" y="60" font-family="Arial" font-size="12" text-anchor="middle" fill="#000000">QR Code</text>
            <text x="75" y="80" font-family="Arial" font-size="10" text-anchor="middle" fill="#6c757d">' . substr($code, 0, 12) . '</text>
            <text x="75" y="100" font-family="Arial" font-size="8" text-anchor="middle" fill="#6c757d">SVG Placeholder</text>
        </svg>';
    }

    /**
     * MODIFIÉ : Placeholder PNG base64 pour PDF (HTML2PDF compatible)
     */
    private function getPlaceholderBase64(): string
    {
        // Générer un QR code placeholder en PNG au lieu de SVG
        try {
            if (class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                $pngData = QrCodeGenerator::format('png')
                    ->size(100)
                    ->margin(1)
                    ->color(100, 100, 100)
                    ->backgroundColor(245, 245, 245)
                    ->generate('QR-PLACEHOLDER');

                $base64 = base64_encode($pngData);
                return "data:image/png;base64,{$base64}";
            }
        } catch (\Exception $e) {
            Log::warning('Erreur génération placeholder QR', ['error' => $e->getMessage()]);
        }

        // Fallback ultime: pixel transparent 1x1 PNG
        $transparentPng = base64_encode(file_get_contents('data://text/plain;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='));
        return "data:image/png;base64,{$transparentPng}";
    }

    /**
     * Déterminer MIME type depuis le chemin
     */
    private function getMimeTypeFromPath(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $mimeTypes = [
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif'
        ];

        return $mimeTypes[$extension] ?? 'image/svg+xml';
    }
}