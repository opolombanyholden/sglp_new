<?php

namespace App\Http\Controllers\PublicControllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentGeneration;
use App\Models\DocumentVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * CONTROLLER DE VÉRIFICATION PUBLIQUE DES DOCUMENTS
 * 
 * Permet à n'importe qui de vérifier l'authenticité d'un document
 * en scannant le QR code ou en entrant le numéro manuellement
 * 
 * Projet : SGLP
 * IMPORTANT : Ce controller est PUBLIC (pas d'authentification)
 */
class DocumentVerificationController extends Controller
{
    /**
     * Page d'accueil de vérification
     * 
     * GET /verify
     */
    public function index()
    {
        return view('public.documents.verify-home');
    }

    /**
     * Vérifier un document par token (QR code scanné)
     * 
     * GET /verify/{token}
     */
    public function verify(string $token)
    {
        try {
            // Rechercher le document par token
            $generation = DocumentGeneration::where('qr_code_token', $token)
                ->with([
                    'template',
                    'organisation.organisationType',
                    'dossier',
                    'generatedBy'
                ])
                ->first();

            // Document introuvable
            if (!$generation) {
                $this->logVerification(null, $token, false, 'Token invalide ou document introuvable');
                
                return view('public.documents.verify-error', [
                    'error_type' => 'not_found',
                    'message' => 'Document introuvable',
                    'details' => 'Le code scanné ne correspond à aucun document dans notre système.',
                    'token' => $token,
                ]);
            }

            // Document invalidé
            if (!$generation->is_valid) {
                $this->logVerification($generation, $token, false, 'Document invalidé');
                
                return view('public.documents.verify-error', [
                    'error_type' => 'invalidated',
                    'message' => 'Document invalidé',
                    'details' => 'Ce document a été invalidé par l\'administration.',
                    'generation' => $generation,
                    'invalidation_reason' => $generation->invalidation_reason,
                    'invalidated_at' => $generation->invalidated_at,
                ]);
            }

            // Document valide - Logger la vérification
            $this->logVerification($generation, $token, true, null);

            // Afficher les informations du document
            return view('public.documents.verify-success', [
                'generation' => $generation,
                'organisation' => $generation->organisation,
                'template' => $generation->template,
                'dossier' => $generation->dossier,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur vérification document', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return view('public.documents.verify-error', [
                'error_type' => 'system_error',
                'message' => 'Erreur système',
                'details' => 'Une erreur s\'est produite lors de la vérification.',
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
        ]);

        try {
            $numeroDocument = strtoupper(trim($validated['numero_document']));

            $generation = DocumentGeneration::where('numero_document', $numeroDocument)
                ->with(['template', 'organisation', 'dossier'])
                ->first();

            if (!$generation) {
                return back()
                    ->withInput()
                    ->with('error', 'Aucun document trouvé avec ce numéro : ' . $numeroDocument);
            }

            return redirect()->route('public.document.verify', $generation->qr_code_token);

        } catch (\Exception $e) {
            Log::error('Erreur recherche document', [
                'numero_document' => $validated['numero_document'],
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Une erreur s\'est produite lors de la recherche.');
        }
    }

    /**
     * API : Vérifier un document (format JSON)
     * 
     * GET /api/verify/{token}
     */
    public function apiVerify(string $token)
    {
        try {
            $generation = DocumentGeneration::where('qr_code_token', $token)
                ->with(['template', 'organisation'])
                ->first();

            if (!$generation) {
                return response()->json([
                    'valid' => false,
                    'error' => 'not_found',
                    'message' => 'Document introuvable',
                ], 404);
            }

            if (!$generation->is_valid) {
                return response()->json([
                    'valid' => false,
                    'error' => 'invalidated',
                    'message' => 'Document invalidé',
                    'reason' => $generation->invalidation_reason,
                ], 200);
            }

            $this->logVerification($generation, $token, true, null);

            return response()->json([
                'valid' => true,
                'document' => [
                    'numero' => $generation->numero_document,
                    'type' => $generation->type_document_label,
                    'organisation' => [
                        'nom' => $generation->organisation->nom,
                    ],
                    'generated_at' => $generation->generated_at->format('d/m/Y H:i'),
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'error' => 'system_error',
                'message' => 'Erreur système',
            ], 500);
        }
    }

    /**
     * Logger une vérification
     */
    protected function logVerification(
        ?DocumentGeneration $generation, 
        string $token, 
        bool $success, 
        ?string $failureReason
    ): void {
        try {
            DocumentVerification::create([
                'document_generation_id' => $generation?->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'geolocation' => null,
                'verification_reussie' => $success,
                'motif_echec' => $failureReason,
                'verified_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur logging vérification', ['error' => $e->getMessage()]);
        }
    }
}