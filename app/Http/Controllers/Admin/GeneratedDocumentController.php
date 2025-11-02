<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentGeneration;
use App\Models\DocumentTemplate;
use App\Models\Organisation;
use App\Models\DocumentVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * CONTROLLER ADMIN - GESTION DES DOCUMENTS GÉNÉRÉS
 * 
 * Gère le cycle de vie des documents officiels depuis l'interface admin :
 * - Liste et consultation des documents générés
 * - Génération manuelle de documents
 * - Téléchargement et régénération
 * - Invalidation et réactivation
 * - Historique des vérifications
 * 
 * Projet : SGLP
 */
class GeneratedDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Liste des documents générés (Admin)
     * GET /admin/generated-documents
     */
    public function index(Request $request)
    {
        $query = DocumentGeneration::with([
            'template',
            'organisation.organisationType',
            'dossier',
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
            $query->where('is_valid', $request->is_valid === 'true');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('numero_document', 'like', "%{$search}%")
                  ->orWhere('qr_code_token', 'like', "%{$search}%");
            });
        }

        // Tri
        $query->orderBy('generated_at', 'desc');

        $documents = $query->paginate(20);

        return view('admin.generated-documents.index', compact('documents'));
    }

    /**
     * Afficher un document généré (Admin)
     * GET /admin/generated-documents/{generation}
     */
    public function show(DocumentGeneration $generation)
    {
        $generation->load([
            'template',
            'organisation',
            'dossier',
            'generatedBy',
            'invalidatedBy',
            'verifications' => function($query) {
                $query->orderBy('verified_at', 'desc')->limit(50);
            }
        ]);

        return view('admin.generated-documents.show', compact('generation'));
    }

    /**
     * Formulaire de génération manuelle (Admin)
     * GET /admin/generated-documents/create
     */
    public function create()
    {
        $templates = DocumentTemplate::where('is_active', true)->get();
        $organisations = Organisation::where('statut', 'approuve')->get();

        return view('admin.generated-documents.create', compact('templates', 'organisations'));
    }

    /**
     * Générer un document (Admin)
     * POST /admin/generated-documents/generate
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'document_template_id' => 'required|exists:document_templates,id',
            'organisation_id' => 'required|exists:organisations,id',
            'dossier_id' => 'nullable|exists:dossiers,id',
        ]);

        try {
            DB::beginTransaction();

            // TODO: Implémenter la logique de génération via un service
            // Pour l'instant, créer un enregistrement basique
            $generation = DocumentGeneration::create([
                'document_template_id' => $validated['document_template_id'],
                'organisation_id' => $validated['organisation_id'],
                'dossier_id' => $validated['dossier_id'] ?? null,
                'numero_document' => $this->generateDocumentNumber(),
                'qr_code_token' => $this->generateQrToken(),
                'generated_by' => Auth::id(),
                'generated_at' => now(),
                'is_valid' => true,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.generated-documents.show', $generation)
                ->with('success', 'Document généré avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur génération document admin: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la génération du document.');
        }
    }

    /**
     * Télécharger un document (Admin)
     * GET /admin/generated-documents/{generation}/download
     */
    public function download(DocumentGeneration $generation)
    {
        try {
            if (!$generation->pdf_path || !Storage::exists($generation->pdf_path)) {
                return back()->with('error', 'Fichier PDF introuvable.');
            }

            $filename = $generation->numero_document . '.pdf';

            return Storage::download($generation->pdf_path, $filename);

        } catch (\Exception $e) {
            Log::error('Erreur téléchargement document admin: ' . $e->getMessage());

            return back()->with('error', 'Erreur lors du téléchargement.');
        }
    }

    /**
     * Régénérer un document (Admin)
     * POST /admin/generated-documents/{generation}/regenerate
     */
    public function regenerate(DocumentGeneration $generation)
    {
        try {
            // TODO: Implémenter la régénération
            $generation->update([
                'generated_at' => now(),
                'generated_by' => Auth::id(),
            ]);

            return back()->with('success', 'Document régénéré avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur régénération document: ' . $e->getMessage());

            return back()->with('error', 'Erreur lors de la régénération.');
        }
    }

    /**
     * Invalider un document (Admin)
     * PUT /admin/generated-documents/{generation}/invalidate
     */
    public function invalidate(Request $request, DocumentGeneration $generation)
    {
        $validated = $request->validate([
            'invalidation_reason' => 'required|string|max:500',
        ]);

        try {
            $generation->update([
                'is_valid' => false,
                'invalidation_reason' => $validated['invalidation_reason'],
                'invalidated_at' => now(),
                'invalidated_by' => Auth::id(),
            ]);

            return back()->with('success', 'Document invalidé avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur invalidation document: ' . $e->getMessage());

            return back()->with('error', 'Erreur lors de l\'invalidation.');
        }
    }

    /**
     * Réactiver un document invalidé (Admin)
     * PUT /admin/generated-documents/{generation}/reactivate
     */
    public function reactivate(DocumentGeneration $generation)
    {
        try {
            $generation->update([
                'is_valid' => true,
                'invalidation_reason' => null,
                'invalidated_at' => null,
                'invalidated_by' => null,
            ]);

            return back()->with('success', 'Document réactivé avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur réactivation document: ' . $e->getMessage());

            return back()->with('error', 'Erreur lors de la réactivation.');
        }
    }

    /**
     * Supprimer un document (Admin)
     * DELETE /admin/generated-documents/{generation}
     */
    public function destroy(DocumentGeneration $generation)
    {
        try {
            // Supprimer le fichier PDF s'il existe
            if ($generation->pdf_path && Storage::exists($generation->pdf_path)) {
                Storage::delete($generation->pdf_path);
            }

            $generation->delete();

            return redirect()
                ->route('admin.generated-documents.index')
                ->with('success', 'Document supprimé avec succès !');

        } catch (\Exception $e) {
            Log::error('Erreur suppression document: ' . $e->getMessage());

            return back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    /**
     * Historique des vérifications d'un document (Admin)
     * GET /admin/document-verifications/{generation}/verifications
     */
    public function documentVerifications(DocumentGeneration $generation)
    {
        $verifications = $generation->verifications()
            ->orderBy('verified_at', 'desc')
            ->paginate(50);

        return view('admin.document-verifications.history', compact('generation', 'verifications'));
    }

    /**
     * Export des vérifications (Admin)
     * GET /admin/document-verifications/export/verifications
     */
    public function exportVerifications(Request $request)
    {
        // TODO: Implémenter l'export
        return back()->with('info', 'Export en cours de développement.');
    }

    /**
     * AJAX : Obtenir les templates pour une organisation
     */
    public function getTemplatesForOrganisation(Request $request)
    {
        $organisationId = $request->input('organisation_id');

        if (!$organisationId) {
            return response()->json([]);
        }

        $organisation = Organisation::find($organisationId);

        if (!$organisation) {
            return response()->json([]);
        }

        $templates = DocumentTemplate::where('organisation_type_id', $organisation->organisation_type_id)
            ->where('is_active', true)
            ->select('id', 'code', 'nom')
            ->get();

        return response()->json($templates);
    }

    /**
     * Méthodes privées - Génération de numéros
     */
    private function generateDocumentNumber(): string
    {
        return 'DOC-' . now()->format('Y') . '-' . strtoupper(uniqid());
    }

    private function generateQrToken(): string
    {
        return strtoupper(bin2hex(random_bytes(16)));
    }
}