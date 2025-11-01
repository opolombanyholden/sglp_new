<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrganisationType;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * CONTRÔLEUR - GESTION DES TYPES D'ORGANISATIONS
 * 
 * Gère le CRUD complet des types d'organisations avec :
 * - Règles métier (fondateurs, adhérents, but)
 * - Documents requis (obligatoires/facultatifs)
 * - Templates de documents à délivrer
 * - Guides et législation
 * 
 * Projet : SGLP
 * Compatible : PHP 8.3, Laravel 10+
 */
class OrganisationTypeController extends Controller
{
    /**
     * Constructor - Middleware admin requis
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * ========================================
     * INDEX - Liste des types d'organisations
     * ========================================
     * 
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // ✅ CORRECTION : Retrait de documentTemplates (colonne deleted_at manquante)
            $query = OrganisationType::query()
                ->withCount(['organisations', 'documentTypes']);

            // Filtre par statut
            if ($request->filled('statut')) {
                if ($request->statut === 'actif') {
                    $query->actif();
                } elseif ($request->statut === 'inactif') {
                    $query->inactif();
                }
            }

            // Filtre par but
            if ($request->filled('but')) {
                if ($request->but === 'lucratif') {
                    $query->lucratif();
                } elseif ($request->but === 'non_lucratif') {
                    $query->nonLucratif();
                }
            }

            // Recherche
            if ($request->filled('search')) {
                $query->search($request->search);
            }

            // Tri
            $sortField = $request->get('sort', 'ordre');
            $sortDirection = $request->get('direction', 'asc');
            
            if (in_array($sortField, ['ordre', 'nom', 'created_at', 'nb_min_fondateurs_majeurs'])) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->ordered();
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $organisationTypes = $query->paginate($perPage);

            // Statistiques globales
            $stats = [
                'total' => OrganisationType::count(),
                'actifs' => OrganisationType::actif()->count(),
                'inactifs' => OrganisationType::inactif()->count(),
                'lucratifs' => OrganisationType::lucratif()->count(),
                'non_lucratifs' => OrganisationType::nonLucratif()->count(),
            ];

            return view('admin.referentiels.organisation-types.index', compact(
                'organisationTypes',
                'stats'
            ));

        } catch (\Exception $e) {
            \Log::error('Erreur OrganisationTypeController@index: ' . $e->getMessage());
            
            return back()->with('error', 'Erreur lors du chargement des types d\'organisations.');
        }
    }

    /**
     * ========================================
     * CREATE - Formulaire de création
     * ========================================
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            // Récupérer tous les types de documents disponibles
            $documentTypes = DocumentType::actifs()
                ->ordered()
                ->get();

            // Couleurs prédéfinies
            $couleurs = [
                '#007bff' => 'Bleu',
                '#28a745' => 'Vert',
                '#dc3545' => 'Rouge',
                '#ffc107' => 'Jaune',
                '#17a2b8' => 'Cyan',
                '#6c757d' => 'Gris',
                '#6f42c1' => 'Violet',
                '#e83e8c' => 'Rose',
            ];

            // Icônes prédéfinies
            $icones = [
                'fa-users' => 'Groupe',
                'fa-building' => 'Bâtiment',
                'fa-landmark' => 'Institution',
                'fa-church' => 'Église',
                'fa-hands-helping' => 'Entraide',
                'fa-globe' => 'Global',
                'fa-flag' => 'Drapeau',
                'fa-balance-scale' => 'Justice',
            ];

            return view('admin.referentiels.organisation-types.create', compact(
                'documentTypes',
                'couleurs',
                'icones'
            ));

        } catch (\Exception $e) {
            \Log::error('Erreur OrganisationTypeController@create: ' . $e->getMessage());
            
            return back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * ========================================
     * STORE - Enregistrement d'un nouveau type
     * ========================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z_]+$/',
                Rule::unique('organisation_types', 'code')
            ],
            'nom' => 'required|string|max:150',
            'description' => 'nullable|string',
            'couleur' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone' => 'nullable|string|max:50',
            
            // Règles métier
            'is_lucratif' => 'required|boolean',
            'nb_min_fondateurs_majeurs' => 'required|integer|min:1|max:100',
            'nb_min_adherents_creation' => 'required|integer|min:1|max:10000',
            
            // Guides et législation
            'guide_creation' => 'nullable|string',
            'texte_legislatif' => 'nullable|string',
            'loi_reference' => 'nullable|string|max:100',
            
            // Métadonnées
            'is_active' => 'required|boolean',
            'ordre' => 'nullable|integer|min:0',
            
            // Documents requis
            'documents' => 'nullable|array',
            'documents.*.document_type_id' => 'required|exists:document_types,id',
            'documents.*.is_obligatoire' => 'required|boolean',
            'documents.*.ordre' => 'required|integer|min:0',
            'documents.*.aide_texte' => 'nullable|string',
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
            'code.regex' => 'Le code ne peut contenir que des lettres minuscules et underscores.',
            'nom.required' => 'Le nom est obligatoire.',
            'couleur.required' => 'La couleur est obligatoire.',
            'couleur.regex' => 'Format de couleur invalide (ex: #007bff).',
            'nb_min_fondateurs_majeurs.required' => 'Le nombre minimum de fondateurs est obligatoire.',
            'nb_min_fondateurs_majeurs.min' => 'Le nombre minimum de fondateurs doit être au moins 1.',
            'nb_min_adherents_creation.required' => 'Le nombre minimum d\'adhérents est obligatoire.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Créer le type d'organisation
            $organisationType = OrganisationType::create([
                'code' => $request->code,
                'nom' => $request->nom,
                'description' => $request->description,
                'couleur' => $request->couleur,
                'icone' => $request->icone,
                'is_lucratif' => $request->is_lucratif,
                'nb_min_fondateurs_majeurs' => $request->nb_min_fondateurs_majeurs,
                'nb_min_adherents_creation' => $request->nb_min_adherents_creation,
                'guide_creation' => $request->guide_creation,
                'texte_legislatif' => $request->texte_legislatif,
                'loi_reference' => $request->loi_reference,
                'is_active' => $request->is_active,
                'ordre' => $request->ordre,
            ]);

            // Attacher les documents requis
            if ($request->has('documents') && is_array($request->documents)) {
                foreach ($request->documents as $document) {
                    $organisationType->documentTypes()->attach(
                        $document['document_type_id'],
                        [
                            'is_obligatoire' => $document['is_obligatoire'] ?? true,
                            'ordre' => $document['ordre'] ?? 0,
                            'aide_texte' => $document['aide_texte'] ?? null,
                        ]
                    );
                }
            }

            DB::commit();

            \Log::info('Type d\'organisation créé', [
                'user_id' => auth()->id(),
                'organisation_type_id' => $organisationType->id,
                'code' => $organisationType->code,
            ]);

            return redirect()
                ->route('admin.referentiels.organisation-types.show', $organisationType->id)
                ->with('success', "Le type d'organisation « {$organisationType->nom} » a été créé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur OrganisationTypeController@store: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création du type d\'organisation.');
        }
    }

    /**
     * ========================================
     * SHOW - Affichage détaillé d'un type
     * ========================================
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show(int $id)
    {
        try {
            // ✅ CORRECTION : Retrait de documentTemplates du with() et withCount()
            $organisationType = OrganisationType::with([
                'documentTypes',
                'organisations' => function ($query) {
                    $query->latest()->take(10);
                }
            ])
            ->withCount(['organisations', 'documentTypes'])
            ->findOrFail($id);

            // Charger manuellement les templates (sans SoftDeletes)
            $organisationType->load('documentTemplates');

            // Statistiques détaillées (✅ CORRECTION : Utilisation de DB::table)
            $statistics = [
                'nb_organisations' => $organisationType->organisations()->count(),
                'nb_organisations_actives' => $organisationType->organisations()->where('is_active', true)->count(),
                'nb_documents_requis' => $organisationType->documentTypes()->count(),
                'nb_documents_obligatoires' => $organisationType->documentTypes()->wherePivot('is_obligatoire', true)->count(),
                'nb_templates' => DB::table('document_templates')
                    ->where('organisation_type_id', $organisationType->id)
                    ->where('is_active', true)
                    ->count(),
            ];

            return view('admin.referentiels.organisation-types.show', compact(
                'organisationType',
                'statistics'
            ));

        } catch (\Exception $e) {
            \Log::error('Erreur OrganisationTypeController@show: ' . $e->getMessage());
            
            return back()->with('error', 'Type d\'organisation introuvable.');
        }
    }

    /**
     * ========================================
     * EDIT - Formulaire d'édition
     * ========================================
     * 
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        try {
            $organisationType = OrganisationType::with('documentTypes')
                ->findOrFail($id);

            // Récupérer tous les types de documents disponibles
            $documentTypes = DocumentType::actifs()
                ->ordered()
                ->get();

            // Documents actuellement attachés
            $attachedDocuments = $organisationType->documentTypes->keyBy('id');

            // Couleurs prédéfinies
            $couleurs = [
                '#007bff' => 'Bleu',
                '#28a745' => 'Vert',
                '#dc3545' => 'Rouge',
                '#ffc107' => 'Jaune',
                '#17a2b8' => 'Cyan',
                '#6c757d' => 'Gris',
                '#6f42c1' => 'Violet',
                '#e83e8c' => 'Rose',
            ];

            // Icônes prédéfinies
            $icones = [
                'fa-users' => 'Groupe',
                'fa-building' => 'Bâtiment',
                'fa-landmark' => 'Institution',
                'fa-church' => 'Église',
                'fa-hands-helping' => 'Entraide',
                'fa-globe' => 'Global',
                'fa-flag' => 'Drapeau',
                'fa-balance-scale' => 'Justice',
            ];

            return view('admin.referentiels.organisation-types.edit', compact(
                'organisationType',
                'documentTypes',
                'attachedDocuments',
                'couleurs',
                'icones'
            ));

        } catch (\Exception $e) {
            \Log::error('Erreur OrganisationTypeController@edit: ' . $e->getMessage());
            
            return back()->with('error', 'Type d\'organisation introuvable.');
        }
    }

    /**
     * ========================================
     * UPDATE - Mise à jour d'un type
     * ========================================
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $organisationType = OrganisationType::findOrFail($id);

        // Validation des données
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z_]+$/',
                Rule::unique('organisation_types', 'code')->ignore($id)
            ],
            'nom' => 'required|string|max:150',
            'description' => 'nullable|string',
            'couleur' => 'required|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'icone' => 'nullable|string|max:50',
            
            // Règles métier
            'is_lucratif' => 'required|boolean',
            'nb_min_fondateurs_majeurs' => 'required|integer|min:1|max:100',
            'nb_min_adherents_creation' => 'required|integer|min:1|max:10000',
            
            // Guides et législation
            'guide_creation' => 'nullable|string',
            'texte_legislatif' => 'nullable|string',
            'loi_reference' => 'nullable|string|max:100',
            
            // Métadonnées
            'is_active' => 'required|boolean',
            'ordre' => 'nullable|integer|min:0',
            
            // Documents requis
            'documents' => 'nullable|array',
            'documents.*.document_type_id' => 'required|exists:document_types,id',
            'documents.*.is_obligatoire' => 'required|boolean',
            'documents.*.ordre' => 'required|integer|min:0',
            'documents.*.aide_texte' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            // Mettre à jour le type d'organisation
            $organisationType->update([
                'code' => $request->code,
                'nom' => $request->nom,
                'description' => $request->description,
                'couleur' => $request->couleur,
                'icone' => $request->icone,
                'is_lucratif' => $request->is_lucratif,
                'nb_min_fondateurs_majeurs' => $request->nb_min_fondateurs_majeurs,
                'nb_min_adherents_creation' => $request->nb_min_adherents_creation,
                'guide_creation' => $request->guide_creation,
                'texte_legislatif' => $request->texte_legislatif,
                'loi_reference' => $request->loi_reference,
                'is_active' => $request->is_active,
                'ordre' => $request->ordre,
            ]);

            // Synchroniser les documents requis
            $syncData = [];
            if ($request->has('documents') && is_array($request->documents)) {
                foreach ($request->documents as $document) {
                    $syncData[$document['document_type_id']] = [
                        'is_obligatoire' => $document['is_obligatoire'] ?? true,
                        'ordre' => $document['ordre'] ?? 0,
                        'aide_texte' => $document['aide_texte'] ?? null,
                    ];
                }
            }
            $organisationType->documentTypes()->sync($syncData);

            DB::commit();

            \Log::info('Type d\'organisation mis à jour', [
                'user_id' => auth()->id(),
                'organisation_type_id' => $organisationType->id,
                'code' => $organisationType->code,
            ]);

            return redirect()
                ->route('admin.referentiels.organisation-types.show', $organisationType->id)
                ->with('success', "Le type d'organisation « {$organisationType->nom} » a été mis à jour avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur OrganisationTypeController@update: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour du type d\'organisation.');
        }
    }

    /**
     * ========================================
     * DESTROY - Suppression d'un type
     * ========================================
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        try {
            $organisationType = OrganisationType::findOrFail($id);

            // Vérifier s'il y a des organisations liées
            $nbOrganisations = $organisationType->organisations()->count();
            
            if ($nbOrganisations > 0) {
                return back()->with('error', 
                    "Impossible de supprimer ce type : {$nbOrganisations} organisation(s) y sont liées."
                );
            }

            $nom = $organisationType->nom;

            // Soft delete
            $organisationType->delete();

            \Log::warning('Type d\'organisation supprimé', [
                'user_id' => auth()->id(),
                'organisation_type_id' => $id,
                'nom' => $nom,
            ]);

            return redirect()
                ->route('admin.referentiels.organisation-types.index')
                ->with('success', "Le type d'organisation « {$nom} » a été supprimé avec succès.");

        } catch (\Exception $e) {
            \Log::error('Erreur OrganisationTypeController@destroy: ' . $e->getMessage());
            
            return back()->with('error', 'Erreur lors de la suppression du type d\'organisation.');
        }
    }

    /**
     * ========================================
     * ATTACH DOCUMENTS - Attacher des documents
     * ========================================
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachDocuments(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'document_type_ids' => 'required|array',
            'document_type_ids.*' => 'required|exists:document_types,id',
            'is_obligatoire' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $organisationType = OrganisationType::findOrFail($id);

            foreach ($request->document_type_ids as $documentTypeId) {
                // Éviter les doublons
                if (!$organisationType->documentTypes()->where('document_type_id', $documentTypeId)->exists()) {
                    $organisationType->attachDocumentType(
                        $documentTypeId,
                        $request->is_obligatoire ?? true
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Document(s) attaché(s) avec succès.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur OrganisationTypeController@attachDocuments: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'attachement des documents.'
            ], 500);
        }
    }

    /**
     * ========================================
     * DETACH DOCUMENT - Détacher un document
     * ========================================
     * 
     * @param int $id
     * @param int $docId
     * @return \Illuminate\Http\JsonResponse
     */
    public function detachDocument(int $id, int $docId)
    {
        try {
            $organisationType = OrganisationType::findOrFail($id);
            
            $organisationType->detachDocumentType($docId);

            return response()->json([
                'success' => true,
                'message' => 'Document détaché avec succès.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur OrganisationTypeController@detachDocument: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du détachement du document.'
            ], 500);
        }
    }

    /**
     * ========================================
     * REORDER - Réorganiser l'ordre
     * ========================================
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reorder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orders' => 'required|array',
            'orders.*' => 'required|integer|exists:organisation_types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.'
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->orders as $ordre => $id) {
                OrganisationType::where('id', $id)
                    ->update(['ordre' => $ordre + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ordre mis à jour avec succès.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur OrganisationTypeController@reorder: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'ordre.'
            ], 500);
        }
    }
}