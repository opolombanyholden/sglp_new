<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentGeneration;
use App\Models\DocumentVerification;
use App\Services\DocumentVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * CONTROLLER DE VÉRIFICATION PUBLIQUE DES DOCUMENTS (VERSION FUSIONNÉE)
 * 
 * Permet à n'importe qui de vérifier l'authenticité d'un document officiel via :
 * - Formulaire web avec saisie du token
 * - Scan de QR Code
 * - Recherche par numéro de document
 * - API JSON pour intégrations tierces
 * 
 * Toutes les vérifications sont enregistrées avec :
 * - Date et heure
 * - Adresse IP
 * - User agent (navigateur)
 * 
 * Projet : SGLP
 * IMPORTANT : Ce controller est PUBLIC (pas d'authentification requise)
 */
class DocumentVerificationController extends Controller
{
    protected $verificationService;

    /**
     * Constructeur avec injection de dépendances
     */
    public function __construct(DocumentVerificationService $verificationService = null)
    {
        // Pas d'authentification requise - accès public
        $this->verificationService = $verificationService;
    }

    /**
     * Page d'accueil - Formulaire de vérification
     * 
     * GET /verify
     */
    public function index()
    {
        return view('public.document-verification.index');
    }

    /**
     * Page d'aide et FAQ
     * 
     * GET /verify/help
     */
    public function help()
    {
        return view('public.document-verification.help');
    }

    /**
     * Vérifier un document par token (via formulaire POST)
     * 
     * POST /verify/check
     */
    public function check(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|min:10|max:255',
        ], [
            'token.required' => 'Veuillez saisir le code de vérification.',
            'token.min' => 'Le code de vérification est trop court.',
        ]);

        // Nettoyer le token (supprimer espaces, etc.)
        $token = trim($validated['token']);

        // Rediriger vers la page de résultat
        return redirect()->route('public.document.verify', $token);
    }

    /**
     * Vérifier un document par token (QR code scanné ou URL directe)
     * 
     * GET /verify/{token}
     */
    public function verify(string $token)
    {
        try {
            // Nettoyer le token
            $token = trim($token);

            // Rechercher le document par qr_code_token
            $document = DocumentGeneration::where('qr_code_token', $token)
                ->with([
                    'template',
                    'organisation.organisationType',
                    'dossier',
                    'dossierValidation.workflowStep',
                    'generatedBy'
                ])
                ->first();

            // Enregistrer la vérification
            $this->recordVerification($token, $document, $request);

            // Afficher le résultat avec la nouvelle vue
            return view('public.document-verification.verify', [
                'document' => $document,
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur vérification document', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('public.document-verification.verify', [
                'document' => null,
                'token' => $token,
                'error' => 'Une erreur système s\'est produite.',
            ]);
        }
    }

    /**
     * Vérifier un document par numéro (recherche manuelle)
     * 
     * POST /verify/search
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'numero_document' => 'required|string|max:255',
        ], [
            'numero_document.required' => 'Veuillez saisir le numéro de document.',
        ]);

        try {
            $numeroDocument = strtoupper(trim($validated['numero_document']));

            $generation = DocumentGeneration::where('numero_document', $numeroDocument)
                ->first();

            if (!$generation) {
                return back()
                    ->withInput()
                    ->with('error', 'Aucun document trouvé avec ce numéro : ' . $numeroDocument);
            }

            // Rediriger vers la vérification par token
            return redirect()->route('public.document.verify', $generation->qr_code_token);

        } catch (\Exception $e) {
            Log::error('Erreur recherche document', [
                'numero_document' => $validated['numero_document'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Une erreur s\'est produite lors de la recherche.');
        }
    }

    /**
     * Vérifier par scan QR Code (redirection vers verify)
     * 
     * GET /qr/{token}
     */
    public function verifyQr(Request $request, string $token)
    {
        // Rediriger vers la méthode verify standard
        return $this->verify($token);
    }

    /**
     * API JSON - Vérifier un document (GET)
     * 
     * GET /api/verify-document/{token}
     */
    public function verifyApi(Request $request, string $token)
    {
        try {
            // Nettoyer le token
            $token = trim($token);

            // Rechercher le document
            $document = DocumentGeneration::where('qr_code_token', $token)
                ->with(['template', 'organisation.organisationType'])
                ->first();

            // Enregistrer la vérification
            $this->recordVerification($token, $document, $request);

            // Document introuvable
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'verified' => false,
                    'error' => 'not_found',
                    'message' => 'Document introuvable',
                    'data' => null,
                ], 404);
            }

            // Préparer les données
            $data = [
                'numero_document' => $document->numero_document,
                'type_document' => $document->type_document_label ?? $document->type_document,
                'organisation' => [
                    'nom' => $document->organisation->nom,
                    'sigle' => $document->organisation->sigle,
                    'type' => $document->organisation->organisationType->nom,
                ],
                'generated_at' => $document->generated_at->format('Y-m-d H:i:s'),
                'generated_at_human' => $document->generated_at->diffForHumans(),
                'is_valid' => $document->is_valid,
                'download_count' => $document->download_count,
                'verifications_count' => $document->verifications->count(),
            ];

            // Ajouter les infos d'invalidation si nécessaire
            if (!$document->is_valid) {
                $data['invalidation'] = [
                    'reason' => $document->invalidation_reason,
                    'invalidated_at' => $document->invalidated_at ? $document->invalidated_at->format('Y-m-d H:i:s') : null,
                ];
            }

            return response()->json([
                'success' => true,
                'verified' => $document->is_valid,
                'message' => $document->is_valid ? 'Document authentique et valide' : 'Document invalidé',
                'data' => $data,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur API vérification', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'verified' => false,
                'error' => 'system_error',
                'message' => 'Erreur système',
            ], 500);
        }
    }

    /**
     * API JSON - Vérifier un document (POST avec token dans le body)
     * 
     * POST /api/verify-document
     */
    public function verifyApiPost(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|min:10|max:255',
        ]);

        return $this->verifyApi($request, $validated['token']);
    }

    /**
     * Télécharger le PDF d'un document vérifié (si autorisé)
     * 
     * GET /verify/{token}/download
     */
    public function download(Request $request, string $token)
    {
        try {
            // Rechercher le document
            $document = DocumentGeneration::where('qr_code_token', $token)->first();

            if (!$document) {
                return back()->with('error', 'Document introuvable.');
            }

            if (!$document->is_valid) {
                return back()->with('error', 'Ce document a été invalidé et ne peut pas être téléchargé.');
            }

            // Vérifier que le fichier existe
            if (!$document->pdf_path || !Storage::exists($document->pdf_path)) {
                return back()->with('error', 'Le fichier PDF n\'est pas disponible.');
            }

            // Incrémenter le compteur
            $document->increment('download_count');
            $document->update(['last_downloaded_at' => now()]);

            // Télécharger
            $filename = $document->numero_document . '.pdf';
            
            return Storage::download(
                $document->pdf_path,
                $filename,
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            Log::error('Erreur téléchargement public : ' . $e->getMessage(), [
                'token' => $token,
            ]);

            return back()->with('error', 'Erreur lors du téléchargement.');
        }
    }

    /**
     * Enregistrer une vérification dans l'historique
     * 
     * @param string $token
     * @param DocumentGeneration|null $document
     * @param Request $request
     * @return DocumentVerification|null
     */
    protected function recordVerification(
        string $token, 
        ?DocumentGeneration $document, 
        Request $request
    ): ?DocumentVerification {
        try {
            // Préparer les données de vérification
            $data = [
                'document_generation_id' => $document?->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'geolocation' => null, // TODO: Implémenter la géolocalisation si nécessaire
                'verification_reussie' => $document ? $document->is_valid : false,
                'motif_echec' => $document 
                    ? ($document->is_valid ? null : 'Document invalidé')
                    : 'Token invalide ou document introuvable',
                'verified_at' => now(),
            ];

            // Créer l'enregistrement
            $verification = DocumentVerification::create($data);

            // Log pour audit
            Log::info('Vérification document enregistrée', [
                'token' => $token,
                'document_id' => $document?->id,
                'document_found' => $document ? true : false,
                'is_valid' => $data['verification_reussie'],
                'ip' => $data['ip_address'],
            ]);

            return $verification;

        } catch (\Exception $e) {
            // En cas d'erreur, logger mais ne pas bloquer l'utilisateur
            Log::error('Erreur enregistrement vérification : ' . $e->getMessage(), [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Statistiques publiques des vérifications (optionnel)
     * 
     * GET /verify/stats
     */
    public function stats()
    {
        try {
            $stats = [
                'total_documents' => DocumentGeneration::where('is_valid', true)->count(),
                'total_verifications' => DocumentVerification::count(),
                'verifications_today' => DocumentVerification::whereDate('created_at', today())->count(),
                'verifications_this_week' => DocumentVerification::where('created_at', '>=', now()->startOfWeek())->count(),
                'verifications_this_month' => DocumentVerification::where('created_at', '>=', now()->startOfMonth())->count(),
            ];

            return view('public.document-verification.stats', compact('stats'));

        } catch (\Exception $e) {
            Log::error('Erreur chargement stats : ' . $e->getMessage());
            
            return view('public.document-verification.stats', [
                'stats' => [
                    'total_documents' => 0,
                    'total_verifications' => 0,
                    'verifications_today' => 0,
                    'verifications_this_week' => 0,
                    'verifications_this_month' => 0,
                ],
            ]);
        }
    }

    /**
     * Widget de vérification embarquable (iframe)
     * 
     * GET /verify/widget
     */
    public function widget()
    {
        // Vue minimaliste pour intégration en iframe
        return view('public.document-verification.widget');
    }

    /**
     * API - Obtenir les détails d'un document sans enregistrer la vérification
     * 
     * GET /api/document-info/{token}
     */
    public function documentInfo(string $token)
    {
        try {
            $document = DocumentGeneration::where('qr_code_token', $token)->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document introuvable',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'numero_document' => $document->numero_document,
                    'type_document' => $document->type_document_label ?? $document->type_document,
                    'organisation_nom' => $document->organisation->nom,
                    'generated_at' => $document->generated_at->format('Y-m-d'),
                    'is_valid' => $document->is_valid,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur système',
            ], 500);
        }
    }

    /**
     * Rapport de vérifications pour un document spécifique (Admin uniquement)
     * 
     * GET /admin/documents/{id}/verifications
     */
    public function documentVerifications(DocumentGeneration $document)
    {
        // Vérifier les permissions
        if (!auth()->check()) {
            abort(403, 'Accès non autorisé');
        }

        $verifications = $document->verifications()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.documents.verifications', compact('document', 'verifications'));
    }

    /**
     * Export des vérifications en CSV (Admin uniquement)
     * 
     * GET /admin/verifications/export
     */
    public function exportVerifications(Request $request)
    {
        // Vérifier les permissions
        if (!auth()->check()) {
            abort(403, 'Accès non autorisé');
        }

        $query = DocumentVerification::with(['documentGeneration.organisation']);

        // Filtres
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $verifications = $query->orderBy('created_at', 'desc')->get();

        // Générer le CSV
        $filename = 'verifications_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($verifications) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // En-têtes
            fputcsv($file, [
                'Date/Heure',
                'N° Document',
                'Organisation',
                'Résultat',
                'Motif échec',
                'Adresse IP',
                'Navigateur',
            ], ';');

            // Données
            foreach ($verifications as $verif) {
                fputcsv($file, [
                    $verif->created_at->format('d/m/Y H:i:s'),
                    $verif->documentGeneration?->numero_document ?? 'N/A',
                    $verif->documentGeneration?->organisation?->nom ?? 'N/A',
                    $verif->verification_reussie ? 'Succès' : 'Échec',
                    $verif->motif_echec ?? '',
                    $verif->ip_address,
                    substr($verif->user_agent, 0, 100),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}