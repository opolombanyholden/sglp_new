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

class QrCodeService
{
    /**
     * ✅ SOLUTION FICHIER : Générer un QR Code avec sauvegarde sur disque
     */
    public function generateForDossier(Dossier $dossier)
    {
        try {
            if (!$dossier || !$dossier->organisation) {
                Log::error('QrCodeService: Dossier ou organisation manquant');
                return null;
            }

            $organisation = $dossier->organisation;
            $code = 'QR-' . strtoupper(Str::random(16));
            $organisationId = $organisation->id;
            $verificationUrl = "https://www.sglp.ga/annuaire/verify/{$organisationId}";
            
            $dateSubmission = $this->formatDateSafely($dossier->submitted_at);
            
            $donneesVerification = [
                'dossier_numero' => $dossier->numero_dossier,
                'organisation_nom' => $organisation->nom,
                'organisation_type' => $organisation->type,
                'numero_recepisse' => $organisation->numero_recepisse,
                'date_soumission' => $dateSubmission,
                'statut' => $dossier->statut,
                'province' => $organisation->province,
                'verification_url' => $verificationUrl,
                'hash_verification' => null
            ];

            $hashVerification = hash('sha256', json_encode($donneesVerification, JSON_UNESCAPED_UNICODE));
            $donneesVerification['hash_verification'] = $hashVerification;

            // ✅ SOLUTION : Générer QR code ET sauvegarder en fichier (SVG prioritaire)
            $svgContent = $this->generateRealQrCodeSvg($verificationUrl, $code);
            $pngBase64 = $this->generateRealQrCodePng($verificationUrl, $code);
            $qrFilePath = $this->saveQrCodeAsFile($verificationUrl, $code, 'svg'); // ⭐ SVG par défaut

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
                'file_path' => $qrFilePath, // ✅ NOUVEAU : Chemin du fichier
                'verification_url' => $verificationUrl,
                'nombre_verifications' => 0,
                'expire_at' => now()->addYears(5),
                'is_active' => true
            ]);

            Log::info('QR Code généré avec fichier', [
                'qr_code_id' => $qrCode->id,
                'code' => $code,
                'file_path' => $qrFilePath,
                'verification_url' => $verificationUrl
            ]);

            return $qrCode;

        } catch (\Exception $e) {
            Log::error('Erreur génération QR Code avec fichier', [
                'dossier_id' => $dossier->id ?? 'null',
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ MÉTHODE MISE À JOUR : Sauvegarder le QR code en tant que fichier (SVG prioritaire)
     */
    private function saveQrCodeAsFile($url, $code, $format = 'svg')
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                Log::error('Bibliothèque QR Code non disponible pour fichier');
                return null;
            }

            // ⭐ PRIORITÉ AU SVG : Pas d'ImageMagick requis
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
                    'format' => $format,
                    'size' => strlen($qrData ?? '')
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
     * ✅ MÉTHODE : Obtenir l'URL publique du fichier QR code
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
     * ✅ MÉTHODE : Vérifier si le fichier QR code existe
     */
    public function hasQrCodeFile(QrCode $qrCode)
    {
        return $qrCode->file_path && Storage::disk('public')->exists($qrCode->file_path);
    }

    /**
     * ✅ MÉTHODE MISE À JOUR : Générer un QR Code RÉEL SVG scannable
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
                ->errorCorrection('H') // ⭐ Correction maximale
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
     * ✅ MÉTHODE MISE À JOUR : Générer un QR Code PNG avec fallback SVG
     */
    private function generateRealQrCodePng($url, $code)
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                return null;
            }

            // Vérifier si GD est disponible avant de tenter PNG
            if (!extension_loaded('gd')) {
                Log::info('Extension GD non disponible, utilisation SVG comme PNG');
                $svgContent = $this->generateRealQrCodeSvg($url, $code);
                return $svgContent ? base64_encode($svgContent) : null;
            }

            $pngData = QrCodeGenerator::format('png')
                ->size(120)
                ->margin(2)
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($url);

            return base64_encode($pngData);

        } catch (\Exception $e) {
            Log::warning('Erreur génération PNG QR Code, fallback vers SVG', [
                'code' => $code,
                'error' => $e->getMessage()
            ]);
            
            // Fallback : Utiliser SVG encodé en base64
            $svgContent = $this->generateRealQrCodeSvg($url, $code);
            return $svgContent ? base64_encode($svgContent) : null;
        }
    }

    /**
     * ✅ MÉTHODE MISE À JOUR : Regénérer un QR Code existant sans ImageMagick
     */
    public function regenerateQrCodeSvg(QrCode $qrCode)
    {
        try {
            if (!$qrCode->verification_url) {
                if ($qrCode->verifiable_type === 'App\\Models\\Organisation') {
                    $qrCode->verification_url = "https://www.sglp.ga/annuaire/verify/{$qrCode->verifiable_id}";
                } else {
                    $dossier = $qrCode->verifiable;
                    if ($dossier && $dossier->organisation) {
                        $qrCode->verification_url = "https://www.sglp.ga/annuaire/verify/{$dossier->organisation->id}";
                    }
                }
            }

            $svgContent = $this->generateRealQrCodeSvg($qrCode->verification_url, $qrCode->code);
            $pngBase64 = $this->generateRealQrCodePng($qrCode->verification_url, $qrCode->code);
            $qrFilePath = $this->saveQrCodeAsFile($qrCode->verification_url, $qrCode->code, 'svg'); // ⭐ SVG prioritaire
            
            $qrCode->update([
                'svg_content' => $svgContent,
                'png_base64' => $pngBase64,
                'file_path' => $qrFilePath,
                'verification_url' => $qrCode->verification_url
            ]);

            Log::info('QR Code regénéré avec fichier SVG', [
                'qr_code_id' => $qrCode->id,
                'file_path' => $qrFilePath
            ]);

            return $qrCode;

        } catch (\Exception $e) {
            Log::error('Erreur regénération QR Code', [
                'qr_code_id' => $qrCode->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ NOUVELLE MÉTHODE : Obtenir QR code optimisé pour PDF (Base64)
     */
    public function getQrCodeForPdf(QrCode $qrCode): ?string
    {
        // Priorité 1: PNG base64 déjà stocké et valide
        if (!empty($qrCode->png_base64) && strlen($qrCode->png_base64 ?? '') > 500) {
            return 'data:image/png;base64,' . $qrCode->png_base64;
        }
        
        // Priorité 2: SVG converti en base64 (recommandé pour DomPDF)
        if (!empty($qrCode->svg_content)) {
            return 'data:image/svg+xml;base64,' . base64_encode($qrCode->svg_content);
        }
        
        // Priorité 3: Fichier existant vers base64
        if (!empty($qrCode->file_path)) {
            $filePath = storage_path('app/public/' . $qrCode->file_path);
            if (file_exists($filePath)) {
                try {
                    $imageData = file_get_contents($filePath);
                    $mimeType = $this->getMimeTypeFromPath($qrCode->file_path);
                    return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
                } catch (\Exception $e) {
                    Log::warning('Erreur lecture fichier QR pour PDF', [
                        'qr_code_id' => $qrCode->id,
                        'file_path' => $qrCode->file_path,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        // Priorité 4: Générer à la volée depuis l'URL
        if (!empty($qrCode->verification_url)) {
            return $this->generateQrCodeBase64($qrCode->verification_url);
        }
        
        Log::warning('Impossible de générer QR code pour PDF', [
            'qr_code_id' => $qrCode->id,
            'has_png_base64' => !empty($qrCode->png_base64),
            'has_svg_content' => !empty($qrCode->svg_content),
            'has_file_path' => !empty($qrCode->file_path),
            'has_verification_url' => !empty($qrCode->verification_url)
        ]);
        
        return null;
    }

    /**
     * ✅ MÉTHODE CORRIGÉE : Générer QR code en base64 sans ImageMagick
     */
    private function generateQrCodeBase64(string $url): ?string
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                Log::error('Bibliothèque QR Code non disponible pour génération base64');
                return null;
            }

            // ✅ UTILISER UNIQUEMENT SVG (pas de PNG = pas d'ImageMagick)
            $svgContent = QrCodeGenerator::format('svg')
                ->size(120)
                ->margin(2)
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($url);

            // Nettoyer le SVG
            $svgContent = trim(str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $svgContent));

            // Retourner le SVG en base64
            return 'data:image/svg+xml;base64,' . base64_encode($svgContent);

        } catch (\Exception $e) {
            Log::error('Erreur génération QR code SVG base64', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ MÉTHODE AMÉLIORÉE : Régénérer QR codes pour PDF sans ImageMagick
     */
    public function regenerateQrCodeForPdf(QrCode $qrCode): bool
    {
        try {
            if (empty($qrCode->verification_url)) {
                Log::warning('Impossible de régénérer QR code sans URL', ['qr_code_id' => $qrCode->id]);
                return false;
            }

            // ✅ STRATÉGIE 1 : Priorité au SVG (toujours fonctionnel)
            $svgContent = QrCodeGenerator::format('svg')
                ->size(150)
                ->margin(2)
                ->color(0, 0, 0)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($qrCode->verification_url);

            // Nettoyer le SVG
            $svgContent = trim(str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $svgContent));

            // ✅ STRATÉGIE 2 : Encoder SVG comme PNG base64 (compatible DomPDF)
            $pngBase64 = base64_encode($svgContent);

            // ✅ STRATÉGIE 3 : Sauvegarder fichier SVG
            $fileName = "qr-codes/{$qrCode->code}.svg";
            $saved = Storage::disk('public')->put($fileName, $svgContent);

            // Mettre à jour le QR code
            $updateData = [
                'svg_content' => $svgContent,
                'png_base64' => $pngBase64 // SVG encodé comme "PNG"
            ];

            if ($saved) {
                $updateData['file_path'] = $fileName;
            }

            $qrCode->update($updateData);

            Log::info('QR Code régénéré pour PDF sans ImageMagick', [
                'qr_code_id' => $qrCode->id,
                'svg_size' => strlen($svgContent ?? ''),
                'base64_size' => strlen($pngBase64 ?? ''),
                'file_saved' => $saved ? 'YES' : 'NO',
                'method' => 'SVG_ONLY'
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
     * ✅ MÉTHODE UTILITAIRE : Valider un QR code pour PDF
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

        // Vérifier PNG base64 (peut être SVG encodé)
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
     * ✅ MÉTHODE UTILITAIRE : Déterminer MIME type depuis le chemin
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
     * Autres méthodes utilitaires (conservées)
     */
    public function hasPng(QrCode $qrCode)
    {
        return !empty($qrCode->png_base64) && strlen($qrCode->png_base64 ?? '') > 100;
    }

    public function hasSvg(QrCode $qrCode)
    {
        return !empty($qrCode->svg_content) && strlen($qrCode->svg_content ?? '') > 100;
    }

    public function verifyQrCode($code)
    {
        try {
            $qrCode = QrCode::where('code', $code)
                ->where('is_active', true)
                ->where(function($query) {
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
}