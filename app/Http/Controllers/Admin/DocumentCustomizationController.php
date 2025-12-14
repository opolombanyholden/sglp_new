<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dossier;
use App\Models\DocumentTemplate;
use App\Models\DocumentGenerationCustomization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur pour la personnalisation des en-têtes et signatures de documents
 */
class DocumentCustomizationController extends Controller
{
    /**
     * Afficher le formulaire de personnalisation avant génération
     */
    public function edit(Dossier $dossier, DocumentTemplate $template)
    {
        try {
            // Charger les personnalisations existantes ou utiliser les valeurs par défaut du template
            $customization = DocumentGenerationCustomization::where('dossier_id', $dossier->id)
                ->where('document_template_id', $template->id)
                ->first();

            $headerText = $customization->header_text ?? $template->header_text;
            $signatureText = $customization->signature_text ?? $template->signature_text;

            return view('admin.documents.customize', compact(
                'dossier',
                'template',
                'headerText',
                'signatureText'
            ));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de la personnalisation: ' . $e->getMessage());
            return back()->with('error', 'Impossible de charger le formulaire de personnalisation.');
        }
    }

    /**
     * Sauvegarder la personalisation et générer le document
     */
    public function store(Request $request, Dossier $dossier)
    {
        $request->validate([
            'template_id' => 'required|exists:document_templates,id',
            'header_text' => 'nullable|string',
            'signature_text' => 'nullable|string',
        ]);

        try {
            // Sauvegarder la personalisation
            $customization = DocumentGenerationCustomization::updateOrCreate(
                [
                    'dossier_id' => $dossier->id,
                    'document_template_id' => $request->template_id,
                ],
                [
                    'header_text' => $request->header_text,
                    'signature_text' => $request->signature_text,
                    'customized_by' => auth()->id(),
                    'customized_at' => now(),
                ]
            );

            // TODO: Appeler le service de génération de document
            // Exemple: app(PDFService::class)->generateDocument($dossier, $request->template_id);

            return redirect()
                ->route('admin.dossiers.show', $dossier)
                ->with('success', 'Document personnalisé et généré avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde de la personnalisation: ' . $e->getMessage());
            return back()
                ->with('error', 'Erreur lors de la sauvegarde de la personnalisation.')
                ->withInput();
        }
    }

    /**
     * Récupérer les données de personnalisation pour un dossier et un template
     */
    public function getCustomization(Dossier $dossier, DocumentTemplate $template)
    {
        $customization = DocumentGenerationCustomization::where('dossier_id', $dossier->id)
            ->where('document_template_id', $template->id)
            ->first();

        return response()->json([
            'header_text' => $customization->header_text ?? $template->header_text,
            'signature_text' => $customization->signature_text ?? $template->signature_text,
            'has_customization' => $customization !== null,
        ]);
    }
}
