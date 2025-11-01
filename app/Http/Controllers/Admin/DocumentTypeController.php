<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\OrganisationType;
use App\Models\OperationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * CONTRÔLEUR - GESTION DES TYPES DE DOCUMENTS
 * 
 * Gère le CRUD complet des types de documents avec relations Many-to-Many
 * Structure : libelle, format_accepte, taille_max + pivots
 * 
 * Projet : SGLP
 * Compatible : PHP 7.3.29, Laravel 10+
 */
class DocumentTypeController extends Controller
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
     * INDEX - Liste des types de documents
     * ========================================
     */
    public function index(Request $request)
    {
        try {
            // Construction de la requête de base
            $query = DocumentType::query()->with(['organisationTypes', 'operationTypes']);

            // FILTRES
            if ($request->filled('search')) {
                $search = $request->search;
                $query->recherche($search);
            }

            if ($request->filled('statut')) {
                if ($request->statut === 'actif') {
                    $query->actifs();
                } elseif ($request->statut === 'inactif') {
                    $query->inactifs();
                }
            }

            if ($request->filled('organisation_type')) {
                $orgType = OrganisationType::where('code', $request->organisation_type)->first();
                if ($orgType) {
                    $query->forOrganisationType($orgType->id);
                }
            }

            if ($request->filled('operation_type')) {
                $opType = OperationType::where('code', $request->operation_type)->first();
                if ($opType) {
                    $query->forOperationType($opType->id);
                }
            }

            // TRI
            $sortField = $request->get('sort', 'ordre');
            $sortDirection = $request->get('direction', 'asc');
            $allowedSortFields = ['code', 'libelle', 'ordre', 'is_active', 'created_at', 'updated_at'];
            if (!in_array($sortField, $allowedSortFields)) {
                $sortField = 'ordre';
            }
            $query->orderBy($sortField, $sortDirection);

            // PAGINATION
            $perPage = $request->get('per_page', 15);
            $documentTypes = $query->paginate($perPage)->withQueryString();

            // STATISTIQUES
            $stats = [
                'total' => DocumentType::count(),
                'actifs' => DocumentType::actifs()->count(),
                'inactifs' => DocumentType::inactifs()->count(),
            ];

            // DONNÉES POUR LES FILTRES
            $typesOrganisation = OrganisationType::actif()
                ->ordered()
                ->get()
                ->mapWithKeys(function ($type) {
                    return [$type->code => $type->nom];
                })
                ->toArray();

            $typesOperation = OperationType::actif()
                ->ordered()
                ->get()
                ->mapWithKeys(function ($type) {
                    return [$type->code => $type->libelle];
                })
                ->toArray();

            return view('admin.referentiels.document-types.index', compact(
                'documentTypes',
                'stats',
                'typesOrganisation',
                'typesOperation'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur DocumentTypeController@index: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement de la liste.');
        }
    }

    /**
     * ========================================
     * CREATE - Formulaire de création
     * ========================================
     */
    public function create()
    {
        try {
            $organisationTypes = OrganisationType::actif()->ordered()->get();

            $typesOrganisation = OrganisationType::actif()
                ->ordered()
                ->get()
                ->mapWithKeys(function ($type) {
                    return [$type->code => $type->nom];
                })
                ->toArray();

            $typesOperation = OperationType::actif()
                ->ordered()
                ->get()
                ->mapWithKeys(function ($type) {
                    return [$type->code => $type->libelle];
                })
                ->toArray();

            $formatsPredefinis = [
                'pdf' => 'PDF uniquement',
                'pdf,jpg,jpeg,png' => 'PDF et Images (JPG, PNG)',
                'jpg,jpeg,png' => 'Images uniquement (JPG, PNG)',
                'pdf,doc,docx' => 'PDF et Word',
                'pdf,xls,xlsx' => 'PDF et Excel',
                'pdf,jpg,jpeg,png,doc,docx' => 'Documents et Images',
            ];

            return view('admin.referentiels.document-types.create', compact(
                'organisationTypes',
                'typesOrganisation',
                'typesOperation',
                'formatsPredefinis'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur DocumentTypeController@create: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * ========================================
     * STORE - Enregistrement
     * ========================================
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('document_types', 'code')
            ],
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organisation_types' => 'required|array|min:1',
            'organisation_types.*' => 'required|string|exists:organisation_types,code',
            'org_obligatoire' => 'nullable|array',
            'org_ordre' => 'nullable|array',
            'operation_types' => 'required|array|min:1',
            'operation_types.*' => 'required|string|exists:operation_types,code',
            'op_obligatoire' => 'nullable|array',
            'op_ordre' => 'nullable|array',
            'formats' => 'required|array|min:1',
            'formats.*' => 'required|string|in:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'taille_max' => 'required|integer|min:1|max:50',
            'ordre' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
            'libelle.required' => 'Le libellé est obligatoire.',
            'organisation_types.required' => 'Sélectionnez au moins un type d\'organisation.',
            'operation_types.required' => 'Sélectionnez au moins un type d\'opération.',
            'formats.required' => 'Sélectionnez au moins un format de fichier.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $documentType = DocumentType::create([
                'code' => $request->code,
                'libelle' => $request->libelle,
                'description' => $request->description,
                'format_accepte' => implode(',', $request->formats),
                'taille_max' => $request->taille_max,
                'is_active' => $request->boolean('is_active', true),
                'ordre' => $request->ordre ?? 0,
            ]);

            // Attacher types d'organisation
            foreach ($request->organisation_types as $orgCode) {
                $orgType = OrganisationType::where('code', $orgCode)->first();
                if ($orgType) {
                    $documentType->organisationTypes()->attach($orgType->id, [
                        'is_obligatoire' => $request->input("org_obligatoire.{$orgCode}", false) ? true : false,
                        'ordre' => $request->input("org_ordre.{$orgCode}", 0),
                    ]);
                }
            }

            // Attacher types d'opération
            foreach ($request->operation_types as $opCode) {
                $opType = OperationType::where('code', $opCode)->first();
                if ($opType) {
                    $documentType->operationTypes()->attach($opType->id, [
                        'is_obligatoire' => $request->input("op_obligatoire.{$opCode}", false) ? true : false,
                        'ordre' => $request->input("op_ordre.{$opCode}", 0),
                    ]);
                }
            }

            DB::commit();

            Log::info('Type de document créé', [
                'user_id' => auth()->id(),
                'document_type_id' => $documentType->id,
            ]);

            return redirect()
                ->route('admin.referentiels.document-types.index')
                ->with('success', "Le type de document « {$documentType->libelle} » a été créé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur DocumentTypeController@store: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * SHOW - Affichage détaillé
     * ========================================
     */
    public function show(int $id)
    {
        try {
            $documentType = DocumentType::with(['organisationTypes', 'operationTypes'])
                ->findOrFail($id);

            $statistics = [
                'nb_types_orga' => $documentType->organisationTypes()->count(),
                'nb_types_operation' => $documentType->operationTypes()->count(),
            ];

            return view('admin.referentiels.document-types.show', compact(
                'documentType',
                'statistics'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur DocumentTypeController@show: ' . $e->getMessage());
            return back()->with('error', 'Type de document introuvable.');
        }
    }

    /**
     * ========================================
     * EDIT - Formulaire d'édition
     * ========================================
     */
    public function edit(int $id)
    {
        try {
            $documentType = DocumentType::with(['organisationTypes', 'operationTypes'])->findOrFail($id);

            $organisationTypes = OrganisationType::actif()->ordered()->get();

            $typesOrganisation = OrganisationType::actif()
                ->ordered()
                ->get()
                ->mapWithKeys(function ($type) {
                    return [$type->code => $type->nom];
                })
                ->toArray();

            $typesOperation = OperationType::actif()
                ->ordered()
                ->get()
                ->mapWithKeys(function ($type) {
                    return [$type->code => $type->libelle];
                })
                ->toArray();

            $formatsPredefinis = [
                'pdf' => 'PDF uniquement',
                'pdf,jpg,jpeg,png' => 'PDF et Images (JPG, PNG)',
                'jpg,jpeg,png' => 'Images uniquement (JPG, PNG)',
                'pdf,doc,docx' => 'PDF et Word',
                'pdf,xls,xlsx' => 'PDF et Excel',
                'pdf,jpg,jpeg,png,doc,docx' => 'Documents et Images',
            ];

            return view('admin.referentiels.document-types.edit', compact(
                'documentType',
                'organisationTypes',
                'typesOrganisation',
                'typesOperation',
                'formatsPredefinis'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur DocumentTypeController@edit: ' . $e->getMessage());
            return back()->with('error', 'Type de document introuvable.');
        }
    }

    /**
     * ========================================
     * UPDATE - Mise à jour
     * ========================================
     */
    public function update(Request $request, int $id)
    {
        $documentType = DocumentType::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('document_types', 'code')->ignore($id)
            ],
            'libelle' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organisation_types' => 'required|array|min:1',
            'organisation_types.*' => 'required|string|exists:organisation_types,code',
            'org_obligatoire' => 'nullable|array',
            'org_ordre' => 'nullable|array',
            'operation_types' => 'required|array|min:1',
            'operation_types.*' => 'required|string|exists:operation_types,code',
            'op_obligatoire' => 'nullable|array',
            'op_ordre' => 'nullable|array',
            'formats' => 'required|array|min:1',
            'formats.*' => 'required|string|in:pdf,jpg,jpeg,png,doc,docx,xls,xlsx',
            'taille_max' => 'required|integer|min:1|max:50',
            'ordre' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            // Mise à jour des champs de base
            $documentType->update([
                'code' => $request->code,
                'libelle' => $request->libelle,
                'description' => $request->description,
                'format_accepte' => implode(',', $request->formats),
                'taille_max' => $request->taille_max,
                'is_active' => $request->boolean('is_active', true),
                'ordre' => $request->ordre ?? 0,
            ]);

            // Synchroniser types d'organisation
            $orgSync = [];
            foreach ($request->organisation_types as $orgCode) {
                $orgType = OrganisationType::where('code', $orgCode)->first();
                if ($orgType) {
                    $orgSync[$orgType->id] = [
                        'is_obligatoire' => $request->input("org_obligatoire.{$orgCode}", false) ? true : false,
                        'ordre' => $request->input("org_ordre.{$orgCode}", 0),
                    ];
                }
            }
            $documentType->organisationTypes()->sync($orgSync);

            // Synchroniser types d'opération
            $opSync = [];
            foreach ($request->operation_types as $opCode) {
                $opType = OperationType::where('code', $opCode)->first();
                if ($opType) {
                    $opSync[$opType->id] = [
                        'is_obligatoire' => $request->input("op_obligatoire.{$opCode}", false) ? true : false,
                        'ordre' => $request->input("op_ordre.{$opCode}", 0),
                    ];
                }
            }
            $documentType->operationTypes()->sync($opSync);

            DB::commit();

            Log::info('Type de document mis à jour', [
                'user_id' => auth()->id(),
                'document_type_id' => $documentType->id,
            ]);

            return redirect()
                ->route('admin.referentiels.document-types.show', $documentType->id)
                ->with('success', "Le type de document « {$documentType->libelle} » a été mis à jour.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur DocumentTypeController@update: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * DESTROY - Suppression
     * ========================================
     */
    public function destroy(int $id)
    {
        DB::beginTransaction();
        
        try {
            $documentType = DocumentType::with(['organisationTypes', 'operationTypes'])->findOrFail($id);

            // Vérifier s'il y a des relations
            $nbOrganisationTypes = $documentType->organisationTypes()->count();
            $nbOperationTypes = $documentType->operationTypes()->count();
            
            // Optionnel : Empêcher la suppression si le document est utilisé
            // Décommentez ces lignes si vous voulez bloquer la suppression
            /*
            if ($nbOrganisationTypes > 0 || $nbOperationTypes > 0) {
                DB::rollBack();
                return back()->with('error', 
                    "Impossible de supprimer : ce document est lié à {$nbOrganisationTypes} type(s) d'organisation et {$nbOperationTypes} type(s) d'opération."
                );
            }
            */

            $libelle = $documentType->libelle;

            // IMPORTANT : Détacher toutes les relations Many-to-Many AVANT la suppression
            $documentType->organisationTypes()->detach();
            $documentType->operationTypes()->detach();

            // Maintenant on peut supprimer
            $documentType->delete();

            DB::commit();

            Log::warning('Type de document supprimé', [
                'user_id' => auth()->id(),
                'document_type_id' => $id,
                'libelle' => $libelle,
            ]);

            return redirect()
                ->route('admin.referentiels.document-types.index')
                ->with('success', "Le type de document « {$libelle} » a été supprimé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur DocumentTypeController@destroy: ' . $e->getMessage(), [
                'document_type_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * ========================================
     * TOGGLE ACTIVE
     * ========================================
     */
    public function toggleActive(int $id)
    {
        try {
            $documentType = DocumentType::findOrFail($id);
            $documentType->toggleActive();

            $statut = $documentType->is_active ? 'activé' : 'désactivé';

            return back()->with('success', "Le type de document a été {$statut}.");

        } catch (\Exception $e) {
            Log::error('Erreur DocumentTypeController@toggleActive: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du changement de statut.');
        }
    }

    /**
     * ========================================
     * DUPLICATE
     * ========================================
     */
    public function duplicate(int $id)
    {
        DB::beginTransaction();

        try {
            $documentType = DocumentType::findOrFail($id);
            $newDocumentType = $documentType->duplicate();

            DB::commit();

            return redirect()
                ->route('admin.referentiels.document-types.edit', $newDocumentType->id)
                ->with('success', 'Type de document dupliqué avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur DocumentTypeController@duplicate: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la duplication.');
        }
    }
}