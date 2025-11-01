<?php

namespace App\Http\Controllers;

use App\Models\DocumentGeneration;
use App\Models\DocumentTemplate;
use App\Models\Organisation;
use App\Services\DocumentGenerationService;
use App\Services\DocumentVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * CONTROLLER - GESTION DES DOCUMENTS GÉNÉRÉS
 * 
 * Gère le cycle de vie des documents officiels :
 * - Génération manuelle et automatique
 * - Consultation et téléchargement
 * - Invalidation et réactivation
 * - Régénération
 * 
 * Projet : SGLP
 */
class DocumentGenerationController extends Controller
{
    protected $generationService;
    protected $verificationService;

    public function __construct(
        DocumentGenerationService $generationService,
        DocumentVerificationService $verificationService
    ) {
        $this->generationService = $generationService;
        $this->verificationService = $verificationService;
        
        // Middleware d'autorisation
        $this->middleware('auth');
        $this->middleware('can:manage-documents');
    }

    /**
     * Liste des documents générés
     */
    public function index(Request $request)
    {
        $query = DocumentGeneration::with([
            'documentTemplate',
            'organisation.organisationType',
            'generatedBy'
        ]);

        // Filtres
        if ($request->filled('template_id')) {
            $query->where('document_template_id', $request->template_id);
        }

        if ($request->filled('organisation_id')) {
            $query->where('organisation_id', $request->organisation_id);
        }

        if ($request->filled('is_valid')) {
            $query->where('is_valid', $request->is_valid);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('generated_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('generated_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_document', 'like', "%{$search}%")
                  ->orWhere('verification_token', 'like', "%{$search}%")
                  ->orWhereHas('organisation', function($orgQuery) use ($search) {
                      $orgQuery->where('nom', 'like', "%{$search}%");
                  });
            });
        }

        // Tri par défaut : plus récents en premier
        $query->orderBy('generated_at', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 25);
        $documents = $query->paginate($perPage);

        // Données pour les filtres
        $templates = DocumentTemplate::orderBy('nom')->get(['id', 'nom']);
        $organisations = Organisation::orderBy('nom')->get(['id', 'nom', 'sigle']);

        // Statistiques
        $stats = [
            'total' => DocumentGeneration::count(),
            'valid' => DocumentGeneration::where('is_valid', true)->count(),
            'downloads' => DocumentGeneration::sum('download_count'),
            'today' => DocumentGeneration::whereDate('generated_at', today())->count(),
        ];

        return view('documents.generated.index', compact(
            'documents',
            'templates',
            'organisations',
            'stats'
        ));
    }

    /**
     * Formulaire de génération manuelle
     */
    public function create()
    {
        $organisations = Organisation::with('organisationType')
            ->orderBy('nom')
            ->get();

        $templates = DocumentTemplate::where('is_active', true)
            ->orderBy('nom')
            ->get();

        return view('documents.generated.create', compact(
            'organisations',
            'templates'
        ));
    }

    /**
     * Générer un nouveau document
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organisation_id' => 'required|exists:organisations,id',
            'document_template_id' => 'required|exists:document_templates,id',
            'use_default_variables' => 'boolean',
            'custom_variables' => 'nullable|array',
            'generate_qr_code' => 'boolean',
            'send_notification' => 'boolean',
            'auto_download' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            $organisation = Organisation::findOrFail($validated['organisation_id']);
            $template = DocumentTemplate::findOrFail($validated['document_template_id']);

            // Vérifier la cohérence organisation <-> template
            if ($template->organisation_type_id && 
                $template->organisation_type_id != $organisation->organisation_type_id) {
                return back()
                    ->withInput()
                    ->with('error', 'Ce template n\'est pas compatible avec le type d\'organisation sélectionné.');
            }

            // Préparer les options
            $options = [
                'user_id' => Auth::id(),
                'generate_qr_code' => $request->boolean('generate_qr_code', true),
                'metadata' => $request->input('custom_variables', []),
            ];

            // Générer le document
            $result = $this->generationService->generate($template, [
                'organisation_id' => $organisation->id,
            ], $options);

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Erreur lors de la génération');
            }

            $document = $result['metadata'];

            // Envoyer une notification si demandé
            if ($request->boolean('send_notification') && $organisation->email) {
                // TODO: Implémenter l'envoi d'email
                // Mail::to($organisation->email)->send(new DocumentGeneratedMail($document));
            }

            DB::commit();

            // Si auto-download demandé, rediriger vers le téléchargement
            if ($request->boolean('auto_download')) {
                return redirect()
                    ->route('admin.documents.download', $document)
                    ->with('success', 'Document généré avec succès !');
            }

            return redirect()
                ->route('admin.documents.show', $document)
                ->with('success', 'Document généré avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur génération document : ' . $e->getMessage(), [
                'organisation_id' => $validated['organisation_id'] ?? null,
                'template_id' => $validated['document_template_id'] ?? null,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la génération : ' . $e->getMessage());
        }
    }

    /**
     * Afficher les détails d'un document
     */
    public function show(DocumentGeneration $documentGeneration)
    {
        $documentGeneration->load([
            'documentTemplate',
            'organisation.organisationType',
            'generatedBy',
            'invalidatedBy',
            'verifications' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return view('documents.generated.show', compact('documentGeneration'));
    }

    /**
     * Télécharger le PDF d'un document
     */
    public function download(DocumentGeneration $documentGeneration)
    {
        try {
            // Vérifier que le fichier existe
            if (!$documentGeneration->pdf_path || !Storage::exists($documentGeneration->pdf_path)) {
                return back()->with('error', 'Le fichier PDF n\'existe pas.');
            }

            // Incrémenter le compteur de téléchargements
            $documentGeneration->increment('download_count');
            $documentGeneration->update(['last_downloaded_at' => now()]);

            // Télécharger le fichier
            $filename = $documentGeneration->numero_document . '.pdf';
            
            return Storage::download(
                $documentGeneration->pdf_path,
                $filename,
                ['Content-Type' => 'application/pdf']
            );

        } catch (\Exception $e) {
            \Log::error('Erreur téléchargement document : ' . $e->getMessage(), [
                'document_id' => $documentGeneration->id,
            ]);

            return back()->with('error', 'Erreur lors du téléchargement : ' . $e->getMessage());
        }
    }

    /**
     * Invalider un document
     */
    public function invalidate(Request $request, DocumentGeneration $documentGeneration)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if (!$documentGeneration->is_valid) {
            return back()->with('error', 'Ce document est déjà invalide.');
        }

        DB::beginTransaction();
        try {
            $documentGeneration->update([
                'is_valid' => false,
                'invalidated_at' => now(),
                'invalidated_by' => Auth::id(),
                'invalidation_reason' => $request->input('reason'),
            ]);

            DB::commit();

            return back()->with('success', 'Document invalidé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de l\'invalidation : ' . $e->getMessage());
        }
    }

    /**
     * Réactiver un document invalide
     */
    public function revalidate(DocumentGeneration $documentGeneration)
    {
        if ($documentGeneration->is_valid) {
            return back()->with('error', 'Ce document est déjà valide.');
        }

        DB::beginTransaction();
        try {
            $documentGeneration->update([
                'is_valid' => true,
                'invalidated_at' => null,
                'invalidated_by' => null,
                'invalidation_reason' => null,
            ]);

            DB::commit();

            return back()->with('success', 'Document réactivé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de la réactivation : ' . $e->getMessage());
        }
    }

    /**
     * Régénérer un document
     */
    public function regenerate(DocumentGeneration $documentGeneration)
    {
        DB::beginTransaction();
        try {
            $template = $documentGeneration->documentTemplate;
            $organisation = $documentGeneration->organisation;

            // Utiliser les mêmes métadonnées
            $options = [
                'user_id' => Auth::id(),
                'generate_qr_code' => $documentGeneration->qr_code_path ? true : false,
                'metadata' => $documentGeneration->metadata,
            ];

            // Régénérer
            $result = $this->generationService->generate($template, [
                'organisation_id' => $organisation->id,
            ], $options);

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Erreur lors de la régénération');
            }

            // Supprimer l'ancien fichier PDF si existe
            if ($documentGeneration->pdf_path && Storage::exists($documentGeneration->pdf_path)) {
                Storage::delete($documentGeneration->pdf_path);
            }

            // Supprimer l'ancien QR code si existe
            if ($documentGeneration->qr_code_path && file_exists(public_path($documentGeneration->qr_code_path))) {
                unlink(public_path($documentGeneration->qr_code_path));
            }

            // Mettre à jour avec les nouveaux chemins
            $documentGeneration->update([
                'pdf_path' => $result['pdf_path'],
                'qr_code_path' => $result['qr_code_path'] ?? null,
                'qr_code_url' => $result['qr_code_url'] ?? null,
                'generated_at' => now(),
                'generated_by' => Auth::id(),
            ]);

            DB::commit();

            return back()->with('success', 'Document régénéré avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur régénération document : ' . $e->getMessage(), [
                'document_id' => $documentGeneration->id,
            ]);

            return back()->with('error', 'Erreur lors de la régénération : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer un document
     */
    public function destroy(DocumentGeneration $documentGeneration)
    {
        DB::beginTransaction();
        try {
            // Supprimer le fichier PDF
            if ($documentGeneration->pdf_path && Storage::exists($documentGeneration->pdf_path)) {
                Storage::delete($documentGeneration->pdf_path);
            }

            // Supprimer le QR code
            if ($documentGeneration->qr_code_path && file_exists(public_path($documentGeneration->qr_code_path))) {
                unlink(public_path($documentGeneration->qr_code_path));
            }

            // Supprimer les vérifications associées
            $documentGeneration->verifications()->delete();

            // Supprimer le document
            $documentGeneration->delete();

            DB::commit();

            return redirect()
                ->route('admin.documents.index')
                ->with('success', 'Document supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Actions groupées : Téléchargement multiple
     */
    public function bulkDownload(Request $request)
    {
        $ids = explode(',', $request->get('ids', ''));
        
        if (empty($ids)) {
            return back()->with('error', 'Aucun document sélectionné.');
        }

        try {
            $documents = DocumentGeneration::whereIn('id', $ids)->get();

            if ($documents->isEmpty()) {
                return back()->with('error', 'Aucun document trouvé.');
            }

            // Si un seul document, téléchargement direct
            if ($documents->count() === 1) {
                return $this->download($documents->first());
            }

            // Plusieurs documents : créer un ZIP
            $zipName = 'documents_' . now()->format('Y-m-d_His') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipName);

            // Créer le dossier temp si n'existe pas
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
                throw new \Exception('Impossible de créer l\'archive ZIP');
            }

            foreach ($documents as $document) {
                if ($document->pdf_path && Storage::exists($document->pdf_path)) {
                    $zip->addFile(
                        Storage::path($document->pdf_path),
                        $document->numero_document . '.pdf'
                    );
                }
            }

            $zip->close();

            // Télécharger et supprimer le ZIP
            return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Erreur téléchargement groupé : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du téléchargement groupé.');
        }
    }

    /**
     * Actions groupées : Invalidation multiple
     */
    public function bulkInvalidate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:document_generations,id',
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $count = DocumentGeneration::whereIn('id', $request->ids)
                ->where('is_valid', true)
                ->update([
                    'is_valid' => false,
                    'invalidated_at' => now(),
                    'invalidated_by' => Auth::id(),
                    'invalidation_reason' => $request->input('reason', 'Invalidation groupée'),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$count} document(s) invalidé(s) avec succès.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'invalidation : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actions groupées : Suppression multiple
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:document_generations,id',
        ]);

        DB::beginTransaction();
        try {
            $documents = DocumentGeneration::whereIn('id', $request->ids)->get();

            foreach ($documents as $document) {
                // Supprimer les fichiers
                if ($document->pdf_path && Storage::exists($document->pdf_path)) {
                    Storage::delete($document->pdf_path);
                }
                if ($document->qr_code_path && file_exists(public_path($document->qr_code_path))) {
                    unlink(public_path($document->qr_code_path));
                }
                
                // Supprimer les vérifications
                $document->verifications()->delete();
                
                // Supprimer le document
                $document->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . " document(s) supprimé(s) avec succès.",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export Excel
     */
    public function export(Request $request)
    {
        // TODO: Implémenter l'export Excel avec league/csv ou maatwebsite/excel
        
        return back()->with('info', 'Fonctionnalité d\'export en cours de développement.');
    }
}