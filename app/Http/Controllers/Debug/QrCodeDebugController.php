<?php

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\Dossier;
use App\Services\QRCodeService;
use App\Services\PDFService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * CONTRÔLEUR DE DÉBOGAGE QR CODE PDF
 * 
 * Ce contrôleur permet de diagnostiquer les problèmes d'affichage 
 * des QR codes dans les PDF générés par DomPDF
 */
class QrCodeDebugController extends Controller
{
    protected $qrCodeService;
    protected $pdfService;

    public function __construct(QRCodeService $qrCodeService, PDFService $pdfService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->pdfService = $pdfService;
    }

    /**
     * PAGE PRINCIPALE DE DÉBOGAGE
     */
    public function index()
    {
        return view('debug.qr-code-debug');
    }

    /**
     * DIAGNOSTIC COMPLET DES QR CODES
     */
    public function diagnosticComplet(Request $request)
    {
        $results = [];
        $dossierId = $request->get('dossier_id');

        try {
            // Récupérer un dossier pour test
            $dossier = $dossierId ? Dossier::find($dossierId) : Dossier::with('organisation')->first();
            
            if (!$dossier) {
                return response()->json(['error' => 'Aucun dossier trouvé pour le test']);
            }

            $results['dossier_info'] = [
                'id' => $dossier->id,
                'organisation' => $dossier->organisation->nom ?? 'N/A',
                'statut' => $dossier->statut
            ];

            // 1. VÉRIFIER LES QR CODES EXISTANTS
            $qrCodes = QrCode::where('verifiable_type', 'App\\Models\\Dossier')
                ->where('verifiable_id', $dossier->id)
                ->get();

            $results['qr_codes_existants'] = [];
            foreach ($qrCodes as $qr) {
                $results['qr_codes_existants'][] = [
                    'id' => $qr->id,
                    'code' => $qr->code,
                    'is_active' => $qr->is_active,
                    'has_svg' => !empty($qr->svg_content),
                    'svg_length' => strlen($qr->svg_content ?? ''),
                    'has_png' => !empty($qr->png_base64),
                    'png_length' => strlen($qr->png_base64 ?? ''),
                    'verification_url' => $qr->verification_url ?? 'N/A',
                    'created_at' => $qr->created_at
                ];
            }

            // 2. TESTER LA GÉNÉRATION D'UN NOUVEAU QR CODE
            $testQr = $this->qrCodeService->generateForDossier($dossier);
            
            $results['nouveau_qr_genere'] = [
                'success' => $testQr ? true : false,
                'qr_id' => $testQr ? $testQr->id : null,
                'has_svg' => $testQr ? !empty($testQr->svg_content) : false,
                'has_png' => $testQr ? !empty($testQr->png_base64) : false,
                'verification_url' => $testQr ? $testQr->verification_url : null
            ];

            // 3. TESTER LA VALIDITÉ DES DONNÉES PNG BASE64
            if ($testQr && !empty($testQr->png_base64)) {
                $results['png_validation'] = $this->validatePngBase64($testQr->png_base64);
            }

            // 4. TESTER LA VALIDITÉ DU SVG
            if ($testQr && !empty($testQr->svg_content)) {
                $results['svg_validation'] = $this->validateSvgContent($testQr->svg_content);
            }

            // 5. TESTER LES URLS DE VÉRIFICATION
            if ($testQr) {
                $results['url_verification'] = $this->testVerificationUrl($testQr);
            }

            // 6. TESTER LE RENDU PDF
            $results['pdf_test'] = $this->testPdfRendering($dossier, $testQr);

        } catch (\Exception $e) {
            $results['error'] = [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ];
        }

        return response()->json($results, 200, [], JSON_PRETTY_PRINT);
    }

    /**
     * VALIDER LES DONNÉES PNG BASE64
     */
    private function validatePngBase64($pngBase64)
    {
        try {
            // Vérifier que c'est bien du base64 valide
            $decodedData = base64_decode($pngBase64, true);
            
            if ($decodedData === false) {
                return ['valid' => false, 'error' => 'Base64 invalide'];
            }

            // Vérifier la signature PNG
            $pngSignature = "\x89PNG\r\n\x1a\n";
            $hasValidSignature = substr($decodedData, 0, 8) === $pngSignature;

            // Obtenir les informations sur l'image
            try {
                $tempFile = tempnam(sys_get_temp_dir(), 'qr_debug_');
                file_put_contents($tempFile, $decodedData);
                $imageInfo = getimagesize($tempFile);
                unlink($tempFile);
            } catch (\Exception $e) {
                $imageInfo = null;
            }

            return [
                'valid' => $hasValidSignature,
                'size_bytes' => strlen($decodedData),
                'base64_length' => strlen($pngBase64),
                'has_png_signature' => $hasValidSignature,
                'image_info' => $imageInfo ? [
                    'width' => $imageInfo[0],
                    'height' => $imageInfo[1],
                    'mime' => $imageInfo['mime']
                ] : null
            ];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * VALIDER LE CONTENU SVG
     */
    private function validateSvgContent($svgContent)
    {
        try {
            // Vérifier que c'est du XML valide
            $doc = new \DOMDocument();
            $xmlValid = $doc->loadXML($svgContent);

            // Vérifier les éléments SVG de base
            $hasSvgTag = strpos($svgContent, '<svg') !== false;
            $hasCloseTag = strpos($svgContent, '</svg>') !== false;
            $hasViewBox = strpos($svgContent, 'viewBox') !== false;

            return [
                'valid_xml' => $xmlValid,
                'has_svg_tag' => $hasSvgTag,
                'has_close_tag' => $hasCloseTag,
                'has_viewbox' => $hasViewBox,
                'length' => strlen($svgContent),
                'preview' => substr($svgContent, 0, 200) . '...'
            ];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * TESTER L'URL DE VÉRIFICATION
     */
    private function testVerificationUrl($qrCode)
    {
        try {
            $url = $qrCode->verification_url ?? $this->qrCodeService->getVerificationUrl($qrCode);
            
            // Vérifier que l'URL est bien formée
            $urlValid = filter_var($url, FILTER_VALIDATE_URL) !== false;
            
            // Tester l'accès à l'URL (en local, cela peut ne pas marcher)
            $urlAccessible = false;
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout' => 5,
                        'method' => 'HEAD'
                    ]
                ]);
                $headers = @get_headers($url, false, $context);
                $urlAccessible = $headers && strpos($headers[0], '200') !== false;
            } catch (\Exception $e) {
                // Ignorer les erreurs d'accès réseau en développement
            }

            return [
                'url' => $url,
                'valid_format' => $urlValid,
                'accessible' => $urlAccessible,
                'qr_code_id' => $qrCode->code
            ];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * TESTER LE RENDU PDF AVEC DIFFÉRENTES MÉTHODES
     */
    private function testPdfRendering($dossier, $qrCode)
    {
        try {
            $results = [];

            // Tester le rendu avec PNG base64
            if ($qrCode && !empty($qrCode->png_base64)) {
                $results['png_test'] = $this->testPdfWithPng($dossier, $qrCode);
            }

            // Tester le rendu avec SVG
            if ($qrCode && !empty($qrCode->svg_content)) {
                $results['svg_test'] = $this->testPdfWithSvg($dossier, $qrCode);
            }

            // Tester le rendu avec placeholder
            $results['placeholder_test'] = $this->testPdfWithPlaceholder($dossier);

            return $results;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * TESTER PDF AVEC PNG
     */
    private function testPdfWithPng($dossier, $qrCode)
    {
        try {
            $testData = [
                'nom_organisation' => $dossier->organisation->nom,
                'qr_code' => $qrCode,
                'test_mode' => 'PNG_ONLY'
            ];

            $html = view('debug.pdf-test-png', $testData)->render();
            
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'serif']);

            // Générer et sauvegarder temporairement
            $pdfContent = $pdf->output();
            $filename = 'debug_qr_png_' . time() . '.pdf';
            $path = 'debug/' . $filename;
            
            Storage::disk('public')->put($path, $pdfContent);

            return [
                'success' => true,
                'file_path' => $path,
                'file_size' => strlen($pdfContent),
                'download_url' => Storage::disk('public')->url($path)
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * TESTER PDF AVEC SVG
     */
    private function testPdfWithSvg($dossier, $qrCode)
    {
        try {
            $testData = [
                'nom_organisation' => $dossier->organisation->nom,
                'qr_code' => $qrCode,
                'test_mode' => 'SVG_ONLY'
            ];

            $html = view('debug.pdf-test-svg', $testData)->render();
            
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'serif']);

            $pdfContent = $pdf->output();
            $filename = 'debug_qr_svg_' . time() . '.pdf';
            $path = 'debug/' . $filename;
            
            Storage::disk('public')->put($path, $pdfContent);

            return [
                'success' => true,
                'file_path' => $path,
                'file_size' => strlen($pdfContent),
                'download_url' => Storage::disk('public')->url($path)
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * TESTER PDF AVEC PLACEHOLDER
     */
    private function testPdfWithPlaceholder($dossier)
    {
        try {
            $testData = [
                'nom_organisation' => $dossier->organisation->nom,
                'test_mode' => 'PLACEHOLDER_ONLY'
            ];

            $html = view('debug.pdf-test-placeholder', $testData)->render();
            
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions(['dpi' => 150, 'defaultFont' => 'serif']);

            $pdfContent = $pdf->output();
            $filename = 'debug_qr_placeholder_' . time() . '.pdf';
            $path = 'debug/' . $filename;
            
            Storage::disk('public')->put($path, $pdfContent);

            return [
                'success' => true,
                'file_path' => $path,
                'file_size' => strlen($pdfContent),
                'download_url' => Storage::disk('public')->url($path)
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * REGÉNÉRER TOUS LES QR CODES D'UN DOSSIER
     */
    public function regenererQrCodes(Request $request)
    {
        try {
            $dossierId = $request->get('dossier_id');
            $dossier = Dossier::findOrFail($dossierId);

            // Désactiver les anciens QR codes
            QrCode::where('verifiable_type', 'App\\Models\\Dossier')
                ->where('verifiable_id', $dossier->id)
                ->update(['is_active' => false]);

            // Générer un nouveau QR code
            $newQrCode = $this->qrCodeService->generateForDossier($dossier);

            return response()->json([
                'success' => true,
                'message' => 'QR Code regénéré avec succès',
                'qr_code' => [
                    'id' => $newQrCode->id,
                    'code' => $newQrCode->code,
                    'has_svg' => !empty($newQrCode->svg_content),
                    'has_png' => !empty($newQrCode->png_base64),
                    'verification_url' => $newQrCode->verification_url
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * NETTOYER LES FICHIERS DE DEBUG
     */
    public function cleanupDebugFiles()
    {
        try {
            $files = Storage::disk('public')->files('debug');
            $deletedCount = 0;

            foreach ($files as $file) {
                if (strpos($file, 'debug_qr_') === 0) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Supprimé {$deletedCount} fichiers de debug"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}