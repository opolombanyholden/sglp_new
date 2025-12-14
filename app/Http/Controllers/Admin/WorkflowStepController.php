<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\OrganisationType;
use App\Models\OperationType;

/**
 * ============================================================================
 * WORKFLOWSTEPCONTROLLER - GESTION DES ÉTAPES DE WORKFLOW
 * ============================================================================
 * 
 * Contrôleur pour gérer les étapes du workflow de validation SGLP
 * 
 * Version 2.1 - Correction migration ENUM vers FK
 * Date: 11/11/2025
 * 
 * CORRECTIFS VERSION 2.1 :
 * ✅ Migration complète ENUM → FK (organisation_type_id, operation_type_id)
 * ✅ Jointures avec organisation_types et operation_types partout
 * ✅ Récupération dynamique des types depuis la DB
 * ✅ Conversion code → ID pour tous filtres et requêtes
 * ✅ Enrichissement des résultats avec noms lisibles
 * ✅ Suppression de tous les arrays hardcodés
 * 
 * @package App\Http\Controllers\Admin
 * @version 2.1
 */
class WorkflowStepController extends Controller
{
    /**
     * Nom de la table
     */
    protected $table = 'workflow_steps';

    /**
     * ========================================================================
     * HELPERS - CONVERSION CODE ↔ ID
     * ========================================================================
     */

    /**
     * Convertir code organisation en ID
     */
    protected function getOrganisationTypeId(string $code): ?int
    {
        return OrganisationType::where('code', $code)->value('id');
    }

    /**
     * Convertir code opération en ID
     */
    protected function getOperationTypeId(string $code): ?int
    {
        return OperationType::where('code', $code)->value('id');
    }

    /**
     * Récupérer tous les types d'organisations depuis la DB
     */
    private function getTypesOrganisations(): array
    {
        return OrganisationType::orderBy('nom')
            ->pluck('nom', 'code')
            ->toArray();
    }

    /**
     * Récupérer tous les types d'opérations depuis la DB
     */
    private function getTypesOperations(): array
    {
        return OperationType::orderBy('libelle')
            ->pluck('libelle', 'code')
            ->toArray();
    }

    /**
     * ========================================================================
     * INDEX - LISTE DES ÉTAPES AVEC TIMELINE VISUELLE
     * ========================================================================
     */
    public function index(Request $request)
    {
        try {
            // Récupération des filtres
            $search = $request->input('search');
            $typeOrganisation = $request->input('type_organisation');
            $typeOperation = $request->input('type_operation');
            $statut = $request->input('statut');
            $perPage = $request->input('per_page', 15);

            // ✅ Construction de la requête AVEC JOINTURES
            $query = DB::table($this->table . ' as ws')
                ->leftJoin('organisation_types as ot', 'ws.organisation_type_id', '=', 'ot.id')
                ->leftJoin('operation_types as opt', 'ws.operation_type_id', '=', 'opt.id')
                ->select(
                    'ws.*',
                    'ot.code as type_organisation',
                    'ot.nom as type_organisation_nom',
                    'opt.code as type_operation',
                    'opt.libelle as type_operation_nom'
                );

            // Filtre recherche
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ws.libelle', 'LIKE', "%{$search}%")
                        ->orWhere('ws.description', 'LIKE', "%{$search}%")
                        ->orWhere('ws.code', 'LIKE', "%{$search}%")
                        ->orWhere('ot.nom', 'LIKE', "%{$search}%")
                        ->orWhere('opt.libelle', 'LIKE', "%{$search}%");
                });
            }

            // ✅ Filtre type organisation avec conversion code → ID
            if ($typeOrganisation && $typeOrganisation !== 'all') {
                $orgTypeId = $this->getOrganisationTypeId($typeOrganisation);
                if ($orgTypeId) {
                    $query->where('ws.organisation_type_id', $orgTypeId);
                }
            }

            // ✅ Filtre type opération avec conversion code → ID
            if ($typeOperation && $typeOperation !== 'all') {
                $opTypeId = $this->getOperationTypeId($typeOperation);
                if ($opTypeId) {
                    $query->where('ws.operation_type_id', $opTypeId);
                }
            }

            // Filtre statut
            if ($statut !== null && $statut !== '') {
                $query->where('ws.is_active', $statut);
            }

            // ✅ Tri
            $query->orderBy('ot.nom', 'asc')
                ->orderBy('opt.libelle', 'asc')
                ->orderBy('ws.numero_passage', 'asc');

            // Requête AJAX pour JSON
            if ($request->expectsJson()) {
                $steps = $query->get();

                // Enrichir avec statistiques
                foreach ($steps as $step) {
                    $step->entities_count = $this->getEntitiesCount($step->id);
                    $step->avg_processing_time = $this->getAvgProcessingTime($step->id);
                    $step->dossiers_en_cours = $this->getDossiersEnCoursCount($step->id);
                    $step->total_dossiers_traites = $this->getTotalDossiersPassesByStep($step->id);
                    $step->taux_approbation = $this->getTauxApprobation($step->id);
                }

                return response()->json([
                    'success' => true,
                    'data' => $steps,
                    'total' => count($steps)
                ]);
            }

            // Pagination pour la vue
            $steps = $query->paginate($perPage);

            // Enrichir avec statistiques pour la vue
            foreach ($steps as $step) {
                $step->entities_count = $this->getEntitiesCount($step->id);
                $step->avg_processing_time = $this->getAvgProcessingTime($step->id);
                $step->dossiers_en_cours = $this->getDossiersEnCoursCount($step->id);
                $step->total_dossiers_traites = $this->getTotalDossiersPassesByStep($step->id);
                $step->taux_approbation = $this->getTauxApprobation($step->id);
            }

            // Statistiques globales
            $stats = [
                'total_steps' => DB::table($this->table)->count(),
                'active_steps' => DB::table($this->table)->where('is_active', 1)->count(),
                'inactive_steps' => DB::table($this->table)->where('is_active', 0)->count(),
                'avg_step_duration' => $this->getGlobalAvgDuration(),
                'steps_par_type' => $this->getStepsCountByType()
            ];

            // Types pour les filtres
            $typesOrganisations = $this->getTypesOrganisations();
            $typesOperations = $this->getTypesOperations();

            return view('admin.workflow-steps.index', compact(
                'steps',
                'stats',
                'typesOrganisations',
                'typesOperations'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur WorkflowStepController@index : ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du chargement des étapes'
                ], 500);
            }

            return back()->with('error', 'Erreur lors du chargement des étapes : ' . $e->getMessage());
        }
    }

    /**
     * ========================================================================
     * CREATE - AFFICHER LE FORMULAIRE DE CRÉATION
     * ========================================================================
     */
    public function create()
    {
        try {
            // Types d'organisations disponibles
            $typesOrganisations = $this->getTypesOrganisations();

            // Types d'opérations disponibles
            $typesOperations = $this->getTypesOperations();

            // Récupérer la liste des entités de validation disponibles
            $entitiesDisponibles = DB::table('validation_entities')
                ->where('is_active', 1)
                ->orderBy('nom', 'asc')
                ->get();

            // Prochain numéro de passage disponible
            $nextOrder = DB::table($this->table)->max('numero_passage') + 1;

            // Récupérer les templates de documents disponibles
            $templates = DB::table('document_templates')
                ->where('is_active', 1)
                ->orderBy('nom', 'asc')
                ->select('id', 'nom', 'code')
                ->get();

            return view('admin.workflow-steps.create', compact(
                'typesOrganisations',
                'typesOperations',
                'entitiesDisponibles',
                'nextOrder',
                'templates'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur WorkflowStepController@create : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'affichage du formulaire : ' . $e->getMessage());
        }
    }

    /**
     * ========================================================================
     * STORE - ENREGISTRER UNE NOUVELLE ÉTAPE
     * ========================================================================
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:255|unique:workflow_steps,code',
                'libelle' => 'required|string|max:255',
                'description' => 'nullable|string',
                'numero_passage' => 'required|integer|min:1',
                'type_organisation' => 'required|string|in:association,ong,parti_politique,confession_religieuse',
                'type_operation' => 'required|string|in:creation,modification,cessation,ajout_adherent,retrait_adherent,declaration_activite,changement_statutaire',
                'champs_requis' => 'nullable|json',
                'delai_traitement' => 'nullable|integer|min:1',
                'permet_rejet' => 'nullable|boolean',
                'permet_commentaire' => 'nullable|boolean',
                'genere_document' => 'nullable|boolean',
                'template_document' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean'
            ], [
                'code.required' => 'Le code est obligatoire',
                'code.unique' => 'Ce code existe déjà',
                'libelle.required' => 'Le libellé est obligatoire',
                'numero_passage.required' => 'Le numéro de passage est obligatoire',
                'type_organisation.required' => 'Le type d\'organisation est obligatoire',
                'type_operation.required' => 'Le type d\'opération est obligatoire'
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // ✅ Convertir codes en IDs
            $orgTypeId = $this->getOrganisationTypeId($request->input('type_organisation'));
            $opTypeId = $this->getOperationTypeId($request->input('type_operation'));

            if (!$orgTypeId || !$opTypeId) {
                throw new \Exception('Type organisation ou opération invalide');
            }

            // ✅ Vérifier unicité de numero_passage pour ce type_organisation + type_operation avec FK
            $existingStep = DB::table($this->table)
                ->where('organisation_type_id', $orgTypeId)
                ->where('operation_type_id', $opTypeId)
                ->where('numero_passage', $request->input('numero_passage'))
                ->exists();

            if ($existingStep) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce numéro de passage existe déjà pour ce type d\'organisation et d\'opération'
                    ], 422);
                }

                return back()
                    ->withErrors(['numero_passage' => 'Ce numéro de passage existe déjà pour ce type d\'organisation et d\'opération'])
                    ->withInput();
            }

            // ✅ Préparer les données avec FK
            $data = [
                'code' => strtoupper($request->input('code')),
                'libelle' => $request->input('libelle'),
                'description' => $request->input('description'),
                'numero_passage' => $request->input('numero_passage'),
                'organisation_type_id' => $orgTypeId,
                'operation_type_id' => $opTypeId,
                'champs_requis' => $request->input('champs_requis', '[]'),
                'delai_traitement' => $request->input('delai_traitement', 48),
                'permet_rejet' => $request->input('permet_rejet', 1),
                'permet_commentaire' => $request->input('permet_commentaire', 1),
                'genere_document' => $request->input('genere_document', 0),
                'template_document' => $request->input('template_document'),
                'is_active' => $request->input('is_active', 1),
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Insertion dans la base de données
            $stepId = DB::table($this->table)->insertGetId($data);

            // Assigner les entités de validation si spécifiées
            if ($request->has('entities') && is_array($request->input('entities'))) {
                $ordre = 1;
                foreach ($request->input('entities') as $entityId) {
                    DB::table('workflow_step_entities')->insert([
                        'workflow_step_id' => $stepId,
                        'validation_entity_id' => $entityId,
                        'ordre' => $ordre++,
                        'is_optional' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            // Log de l'activité
            Log::info('Étape workflow créée', [
                'step_id' => $stepId,
                'code' => $data['code'],
                'libelle' => $data['libelle'],
                'organisation_type_id' => $orgTypeId,
                'operation_type_id' => $opTypeId,
                'user_id' => auth()->id()
            ]);

            // Réponse selon le type de requête
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Étape créée avec succès',
                    'step_id' => $stepId,
                    'redirect' => route('admin.workflow-steps.show', $stepId)
                ]);
            }

            return redirect()
                ->route('admin.workflow-steps.show', $stepId)
                ->with('success', 'Étape créée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur WorkflowStepController@store : ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'étape : ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->with('error', 'Erreur lors de la création de l\'étape : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ========================================================================
     * SHOW - AFFICHER LES DÉTAILS D'UNE ÉTAPE
     * ========================================================================
     */
    public function show($id)
    {
        try {
            // ✅ Récupérer l'étape avec jointures
            $step = DB::table($this->table . ' as ws')
                ->leftJoin('organisation_types as ot', 'ws.organisation_type_id', '=', 'ot.id')
                ->leftJoin('operation_types as opt', 'ws.operation_type_id', '=', 'opt.id')
                ->where('ws.id', $id)
                ->select(
                    'ws.*',
                    'ot.code as type_organisation',
                    'ot.nom as type_organisation_nom',
                    'opt.code as type_operation',
                    'opt.libelle as type_operation_nom'
                )
                ->first();

            if (!$step) {
                return back()->with('error', 'Étape non trouvée');
            }

            // Récupérer les entités assignées
            $entities = DB::table('workflow_step_entities as wse')
                ->join('validation_entities as ve', 've.id', '=', 'wse.validation_entity_id')
                ->where('wse.workflow_step_id', $id)
                ->select('ve.*', 'wse.ordre', 'wse.is_optional')
                ->orderBy('wse.ordre', 'asc')
                ->get();

            // Statistiques détaillées
            $stats = [
                'entities_count' => count($entities),
                'avg_processing_time' => $this->getAvgProcessingTime($id),
                'dossiers_en_cours' => $this->getDossiersEnCoursCount($id),
                'total_dossiers_traites' => $this->getTotalDossiersPassesByStep($id),
                'taux_approbation' => $this->getTauxApprobation($id),
                'delai_moyen_reel' => $this->getDelaiMoyenReel($id),
                'dossiers_en_retard' => $this->getDossiersEnRetard($id)
            ];

            // Dossiers récents
            $recentDossiers = $this->getRecentDossiersForStep($id, 5);

            return view('admin.workflow-steps.show', compact(
                'step',
                'entities',
                'stats',
                'recentDossiers'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur WorkflowStepController@show : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'affichage des détails : ' . $e->getMessage());
        }
    }

    /**
     * ========================================================================
     * EDIT - AFFICHER LE FORMULAIRE D'ÉDITION
     * ========================================================================
     */
    public function edit($id)
    {
        try {
            // ✅ Récupérer l'étape avec jointures
            $step = DB::table($this->table . ' as ws')
                ->leftJoin('organisation_types as ot', 'ws.organisation_type_id', '=', 'ot.id')
                ->leftJoin('operation_types as opt', 'ws.operation_type_id', '=', 'opt.id')
                ->where('ws.id', $id)
                ->select(
                    'ws.*',
                    'ot.code as type_organisation',
                    'ot.nom as type_organisation_nom',
                    'opt.code as type_operation',
                    'opt.libelle as type_operation_nom'
                )
                ->first();

            if (!$step) {
                return back()->with('error', 'Étape non trouvée');
            }

            // Types d'organisations disponibles
            $typesOrganisations = $this->getTypesOrganisations();

            // Types d'opérations disponibles
            $typesOperations = $this->getTypesOperations();

            // Récupérer les entités assignées
            $assignedEntities = DB::table('workflow_step_entities as wse')
                ->join('validation_entities as ve', 've.id', '=', 'wse.validation_entity_id')
                ->where('wse.workflow_step_id', $id)
                ->select('ve.*', 'wse.ordre', 'wse.is_optional')
                ->orderBy('wse.ordre', 'asc')
                ->get();

            // Liste de toutes les entités disponibles
            $entitiesDisponibles = DB::table('validation_entities')
                ->where('is_active', 1)
                ->orderBy('nom', 'asc')
                ->get();

            // Récupérer les templates de documents disponibles
            $templates = DB::table('document_templates')
                ->where('is_active', 1)
                ->orderBy('nom', 'asc')
                ->select('id', 'nom', 'code')
                ->get();

            return view('admin.workflow-steps.edit', compact(
                'step',
                'typesOrganisations',
                'typesOperations',
                'assignedEntities',
                'entitiesDisponibles',
                'templates'
            ));

        } catch (\Exception $e) {
            Log::error('Erreur WorkflowStepController@edit : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'affichage du formulaire : ' . $e->getMessage());
        }
    }

    /**
     * ========================================================================
     * UPDATE - METTRE À JOUR UNE ÉTAPE
     * ========================================================================
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Récupérer l'étape existante
            $step = DB::table($this->table)->where('id', $id)->first();

            if (!$step) {
                return back()->with('error', 'Étape non trouvée');
            }

            // Validation des données
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:255|unique:workflow_steps,code,' . $id,
                'libelle' => 'required|string|max:255',
                'description' => 'nullable|string',
                'numero_passage' => 'required|integer|min:1',
                'type_organisation' => 'required|string|in:association,ong,parti_politique,confession_religieuse',
                'type_operation' => 'required|string|in:creation,modification,cessation,ajout_adherent,retrait_adherent,declaration_activite,changement_statutaire',
                'champs_requis' => 'nullable|json',
                'delai_traitement' => 'nullable|integer|min:1',
                'permet_rejet' => 'nullable|boolean',
                'permet_commentaire' => 'nullable|boolean',
                'genere_document' => 'nullable|boolean',
                'template_document' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean'
            ], [
                'code.required' => 'Le code est obligatoire',
                'code.unique' => 'Ce code existe déjà',
                'libelle.required' => 'Le libellé est obligatoire',
                'numero_passage.required' => 'Le numéro de passage est obligatoire'
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // ✅ Convertir codes en IDs
            $orgTypeId = $this->getOrganisationTypeId($request->input('type_organisation'));
            $opTypeId = $this->getOperationTypeId($request->input('type_operation'));

            if (!$orgTypeId || !$opTypeId) {
                throw new \Exception('Type organisation ou opération invalide');
            }

            // ✅ Vérifier unicité numero_passage avec FK (sauf pour l'étape actuelle)
            $existingStep = DB::table($this->table)
                ->where('organisation_type_id', $orgTypeId)
                ->where('operation_type_id', $opTypeId)
                ->where('numero_passage', $request->input('numero_passage'))
                ->where('id', '!=', $id)
                ->exists();

            if ($existingStep) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ce numéro de passage existe déjà'
                    ], 422);
                }

                return back()
                    ->withErrors(['numero_passage' => 'Ce numéro de passage existe déjà'])
                    ->withInput();
            }

            // ✅ Préparer les données de mise à jour avec FK
            $data = [
                'code' => strtoupper($request->input('code')),
                'libelle' => $request->input('libelle'),
                'description' => $request->input('description'),
                'numero_passage' => $request->input('numero_passage'),
                'organisation_type_id' => $orgTypeId,
                'operation_type_id' => $opTypeId,
                'champs_requis' => $request->input('champs_requis', '[]'),
                'delai_traitement' => $request->input('delai_traitement', 48),
                'permet_rejet' => $request->input('permet_rejet', 1),
                'permet_commentaire' => $request->input('permet_commentaire', 1),
                'genere_document' => $request->input('genere_document', 0),
                'template_document' => $request->input('template_document'),
                'is_active' => $request->input('is_active', 1),
                'updated_at' => now()
            ];

            // Mise à jour dans la base de données
            DB::table($this->table)->where('id', $id)->update($data);

            // Mettre à jour les entités assignées
            if ($request->has('entities')) {
                // Supprimer les anciennes assignations
                DB::table('workflow_step_entities')
                    ->where('workflow_step_id', $id)
                    ->delete();

                // Ajouter les nouvelles
                if (is_array($request->input('entities'))) {
                    $ordre = 1;
                    foreach ($request->input('entities') as $entityId) {
                        DB::table('workflow_step_entities')->insert([
                            'workflow_step_id' => $id,
                            'validation_entity_id' => $entityId,
                            'ordre' => $ordre++,
                            'is_optional' => 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            }

            DB::commit();

            // Log de l'activité
            Log::info('Étape workflow mise à jour', [
                'step_id' => $id,
                'code' => $data['code'],
                'libelle' => $data['libelle'],
                'user_id' => auth()->id(),
                'changes' => array_diff_assoc($data, (array) $step)
            ]);

            // Réponse selon le type de requête
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Étape mise à jour avec succès',
                    'redirect' => route('admin.workflow-steps.show', $id)
                ]);
            }

            return redirect()
                ->route('admin.workflow-steps.show', $id)
                ->with('success', 'Étape mise à jour avec succès');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur WorkflowStepController@update : ' . $e->getMessage(), [
                'step_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour : ' . $e->getMessage()
                ], 500);
            }

            return back()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * ========================================================================
     * DESTROY - SUPPRIMER UNE ÉTAPE
     * ========================================================================
     */
    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Vérifier que l'étape existe
            $step = DB::table($this->table)->where('id', $id)->first();

            if (!$step) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Étape non trouvée'
                    ], 404);
                }
                return back()->with('error', 'Étape non trouvée');
            }

            // Vérifier si l'étape est utilisée dans des dossiers
            $dossiersCount = DB::table('dossiers')
                ->where('current_step_id', $id)
                ->count();

            if ($dossiersCount > 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Impossible de supprimer : {$dossiersCount} dossier(s) utilisent cette étape"
                    ], 422);
                }

                return back()->with('error', "Impossible de supprimer : {$dossiersCount} dossier(s) utilisent cette étape");
            }

            // Vérifier si l'étape a des validations historiques
            $validationsCount = DB::table('dossier_validations')
                ->where('workflow_step_id', $id)
                ->count();

            if ($validationsCount > 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Impossible de supprimer : {$validationsCount} validation(s) historiques existent. Désactivez l'étape à la place."
                    ], 422);
                }

                return back()->with('error', "Impossible de supprimer : {$validationsCount} validation(s) historiques existent. Désactivez l'étape à la place.");
            }

            // Supprimer les assignations d'entités
            DB::table('workflow_step_entities')
                ->where('workflow_step_id', $id)
                ->delete();

            // Supprimer l'étape
            DB::table($this->table)->where('id', $id)->delete();

            DB::commit();

            // Log de l'activité
            Log::info('Étape workflow supprimée', [
                'step_id' => $id,
                'code' => $step->code,
                'libelle' => $step->libelle,
                'user_id' => auth()->id()
            ]);

            // Réponse selon le type de requête
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Étape supprimée avec succès',
                    'redirect' => route('admin.workflow-steps.index')
                ]);
            }

            return redirect()
                ->route('admin.workflow-steps.index')
                ->with('success', 'Étape supprimée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur WorkflowStepController@destroy : ' . $e->getMessage(), [
                'step_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression'
                ], 500);
            }

            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * ========================================================================
     * TOGGLESTATUS - ACTIVER/DÉSACTIVER UNE ÉTAPE (AJAX)
     * ========================================================================
     */
    public function toggleStatus($id)
    {
        try {
            // Vérifier que l'étape existe
            $step = DB::table($this->table)->where('id', $id)->first();

            if (!$step) {
                return response()->json([
                    'success' => false,
                    'message' => 'Étape non trouvée'
                ], 404);
            }

            // Inverser le statut
            $newStatus = !$step->is_active;

            // Mise à jour
            DB::table($this->table)
                ->where('id', $id)
                ->update([
                    'is_active' => $newStatus,
                    'updated_at' => now()
                ]);

            // Log de l'activité
            Log::info('Statut étape workflow modifié', [
                'step_id' => $id,
                'code' => $step->code,
                'libelle' => $step->libelle,
                'old_status' => $step->is_active,
                'new_status' => $newStatus,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => $newStatus ? 'Étape activée avec succès' : 'Étape désactivée avec succès',
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur WorkflowStepController@toggleStatus : ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification du statut'
            ], 500);
        }
    }

    /**
     * ========================================================================
     * REORDER - RÉORGANISER L'ORDRE DES ÉTAPES (SUPPORTE 2 FORMATS)
     * ========================================================================
     */
    public function reorder(Request $request)
    {
        // Vérifier quel format de données est envoyé
        if ($request->has('steps')) {
            // Format ANCIEN (depuis index.blade.php avec DataTables)
            return $this->reorderOldFormat($request);
        } else {
            // Format NOUVEAU (depuis timeline.blade.php)
            return $this->reorderNewFormat($request);
        }
    }

    /**
     * Réorganiser avec l'ancien format (DataTables)
     */
    private function reorderOldFormat(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'steps' => 'required|array',
                'steps.*.id' => 'required|integer|exists:workflow_steps,id',
                'steps.*.numero_passage' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Mettre à jour l'ordre de chaque étape
            foreach ($request->input('steps') as $stepData) {
                DB::table($this->table)
                    ->where('id', $stepData['id'])
                    ->update([
                        'numero_passage' => $stepData['numero_passage'],
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            // Log de l'activité
            Log::info('Ordre des étapes workflow modifié (ancien format)', [
                'steps_count' => count($request->input('steps')),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ordre des étapes mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur reorderOldFormat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation'
            ], 500);
        }
    }

    /**
     * Réorganiser avec le nouveau format (Timeline drag & drop)
     */
    private function reorderNewFormat(Request $request)
    {
        $validated = $request->validate([
            'type_organisation' => 'required|string',
            'type_operation' => 'required|string',
            'step_ids' => 'required|array',
            'step_ids.*' => 'integer|exists:workflow_steps,id'
        ]);

        DB::beginTransaction();

        try {
            // ✅ Convertir codes en IDs
            $orgTypeId = $this->getOrganisationTypeId($validated['type_organisation']);
            $opTypeId = $this->getOperationTypeId($validated['type_operation']);

            if (!$orgTypeId || !$opTypeId) {
                throw new \Exception('Type organisation ou opération invalide');
            }

            // Mettre à jour le numero_passage de chaque étape selon l'ordre dans step_ids
            foreach ($validated['step_ids'] as $index => $stepId) {
                DB::table($this->table)
                    ->where('id', $stepId)
                    ->where('organisation_type_id', $orgTypeId)
                    ->where('operation_type_id', $opTypeId)
                    ->update([
                        'numero_passage' => $index + 1,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            // Log de l'activité
            Log::info('Ordre des étapes workflow modifié (timeline)', [
                'type_organisation' => $validated['type_organisation'],
                'type_operation' => $validated['type_operation'],
                'steps_count' => count($validated['step_ids']),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Ordre des étapes mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur reorderNewFormat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ========================================================================
     * DUPLICATE - DUPLIQUER UNE ÉTAPE
     * ========================================================================
     */
    public function duplicate($id)
    {
        DB::beginTransaction();

        try {
            // Récupérer l'étape source
            $sourceStep = DB::table($this->table)->where('id', $id)->first();

            if (!$sourceStep) {
                return back()->with('error', 'Étape non trouvée');
            }

            // Créer le code de la copie
            $newCode = $sourceStep->code . '_COPIE_' . date('Ymd_His');

            // Préparer les données de la nouvelle étape
            $data = [
                'code' => $newCode,
                'libelle' => $sourceStep->libelle . ' (Copie)',
                'description' => $sourceStep->description,
                'numero_passage' => DB::table($this->table)->max('numero_passage') + 1,
                'organisation_type_id' => $sourceStep->organisation_type_id,
                'operation_type_id' => $sourceStep->operation_type_id,
                'champs_requis' => $sourceStep->champs_requis,
                'delai_traitement' => $sourceStep->delai_traitement,
                'permet_rejet' => $sourceStep->permet_rejet,
                'permet_commentaire' => $sourceStep->permet_commentaire,
                'genere_document' => $sourceStep->genere_document,
                'template_document' => $sourceStep->template_document,
                'is_active' => 0, // Désactivé par défaut
                'created_at' => now(),
                'updated_at' => now()
            ];

            // Créer la nouvelle étape
            $newStepId = DB::table($this->table)->insertGetId($data);

            // Copier les entités assignées
            $entities = DB::table('workflow_step_entities')
                ->where('workflow_step_id', $id)
                ->get();

            foreach ($entities as $entity) {
                DB::table('workflow_step_entities')->insert([
                    'workflow_step_id' => $newStepId,
                    'validation_entity_id' => $entity->validation_entity_id,
                    'ordre' => $entity->ordre,
                    'is_optional' => $entity->is_optional,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            // Log de l'activité
            Log::info('Étape workflow dupliquée', [
                'source_step_id' => $id,
                'new_step_id' => $newStepId,
                'user_id' => auth()->id()
            ]);

            return redirect()
                ->route('admin.workflow-steps.edit', $newStepId)
                ->with('success', 'Étape dupliquée avec succès. Vous pouvez maintenant la modifier.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur WorkflowStepController@duplicate : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la duplication : ' . $e->getMessage());
        }
    }

    /**
     * ========================================================================
     * EXPORT - EXPORTER LES CONFIGURATIONS
     * ========================================================================
     */
    public function export(Request $request)
    {
        try {
            $typeOrganisation = $request->input('type_organisation');
            $typeOperation = $request->input('type_operation');

            // ✅ Récupérer les étapes avec jointures
            $query = DB::table($this->table . ' as ws')
                ->leftJoin('organisation_types as ot', 'ws.organisation_type_id', '=', 'ot.id')
                ->leftJoin('operation_types as opt', 'ws.operation_type_id', '=', 'opt.id')
                ->select(
                    'ws.*',
                    'ot.code as type_organisation',
                    'ot.nom as type_organisation_nom',
                    'opt.code as type_operation',
                    'opt.libelle as type_operation_nom'
                );

            // ✅ Convertir codes en IDs pour filtres
            if ($typeOrganisation) {
                $orgTypeId = $this->getOrganisationTypeId($typeOrganisation);
                if ($orgTypeId) {
                    $query->where('ws.organisation_type_id', $orgTypeId);
                }
            }

            if ($typeOperation) {
                $opTypeId = $this->getOperationTypeId($typeOperation);
                if ($opTypeId) {
                    $query->where('ws.operation_type_id', $opTypeId);
                }
            }

            $steps = $query->orderBy('ws.numero_passage', 'asc')->get();

            // Enrichir avec les entités
            foreach ($steps as $step) {
                $step->entities = DB::table('workflow_step_entities as wse')
                    ->join('validation_entities as ve', 've.id', '=', 'wse.validation_entity_id')
                    ->where('wse.workflow_step_id', $step->id)
                    ->select('ve.code', 've.nom', 'wse.ordre', 'wse.is_optional')
                    ->orderBy('wse.ordre', 'asc')
                    ->get();
            }

            // Préparer le JSON
            $export = [
                'exported_at' => now()->toDateTimeString(),
                'exported_by' => auth()->user()->name ?? 'Système',
                'filters' => [
                    'type_organisation' => $typeOrganisation,
                    'type_operation' => $typeOperation
                ],
                'steps_count' => count($steps),
                'steps' => $steps
            ];

            // Générer le fichier JSON
            $filename = 'workflow_steps_export_' . date('Ymd_His') . '.json';
            $json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            return response($json)
                ->header('Content-Type', 'application/json')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Erreur WorkflowStepController@export : ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'export : ' . $e->getMessage());
        }
    }

    /**
     * ========================================================================
     * STATISTICS - STATISTIQUES DÉTAILLÉES D'UNE ÉTAPE (AJAX)
     * ========================================================================
     */
    public function statistics($id)
    {
        try {
            // ✅ Vérifier que l'étape existe avec jointures
            $step = DB::table($this->table . ' as ws')
                ->leftJoin('organisation_types as ot', 'ws.organisation_type_id', '=', 'ot.id')
                ->leftJoin('operation_types as opt', 'ws.operation_type_id', '=', 'opt.id')
                ->where('ws.id', $id)
                ->select(
                    'ws.*',
                    'ot.code as type_organisation',
                    'ot.nom as type_organisation_nom',
                    'opt.code as type_operation',
                    'opt.libelle as type_operation_nom'
                )
                ->first();

            if (!$step) {
                return response()->json([
                    'success' => false,
                    'message' => 'Étape non trouvée'
                ], 404);
            }

            // Statistiques complètes
            $stats = [
                'step_info' => [
                    'code' => $step->code,
                    'libelle' => $step->libelle,
                    'type_organisation' => $step->type_organisation_nom,
                    'type_operation' => $step->type_operation_nom,
                    'numero_passage' => $step->numero_passage
                ],
                'dossiers' => [
                    'en_cours' => $this->getDossiersEnCoursCount($id),
                    'total_traites' => $this->getTotalDossiersPassesByStep($id),
                    'en_retard' => $this->getDossiersEnRetard($id)
                ],
                'performance' => [
                    'delai_prevu' => $step->delai_traitement . ' heures',
                    'delai_moyen_reel' => $this->getDelaiMoyenReel($id),
                    'taux_respect_delai' => $this->getTauxRespectDelai($id)
                ],
                'validation' => [
                    'taux_approbation' => $this->getTauxApprobation($id),
                    'nombre_rejets' => $this->getNombreRejets($id)
                ],
                'entities' => [
                    'count' => $this->getEntitiesCount($id),
                    'list' => $this->getEntitiesList($id)
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur WorkflowStepController@statistics : ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques'
            ], 500);
        }
    }

    // ========================================================================
    // MÉTHODES PRIVÉES - HELPERS (IMPLÉMENTÉES)
    // ========================================================================

    /**
     * Compter le nombre d'entités assignées à une étape
     */
    private function getEntitiesCount($stepId)
    {
        return DB::table('workflow_step_entities')
            ->where('workflow_step_id', $stepId)
            ->count();
    }

    /**
     * Liste des entités assignées
     */
    private function getEntitiesList($stepId)
    {
        return DB::table('workflow_step_entities as wse')
            ->join('validation_entities as ve', 've.id', '=', 'wse.validation_entity_id')
            ->where('wse.workflow_step_id', $stepId)
            ->select('ve.code', 've.nom', 'wse.ordre')
            ->orderBy('wse.ordre', 'asc')
            ->get();
    }

    /**
     * Obtenir le temps moyen de traitement d'une étape (IMPLÉMENTÉ)
     */
    private function getAvgProcessingTime($stepId)
    {
        $avgMinutes = DB::table('dossier_validations')
            ->where('workflow_step_id', $stepId)
            ->whereNotNull('duree_traitement')
            ->avg('duree_traitement');

        if (!$avgMinutes) {
            return 'N/A';
        }

        // Convertir les minutes en heures ou jours
        if ($avgMinutes < 60) {
            return round($avgMinutes) . ' min';
        } elseif ($avgMinutes < 1440) { // Moins de 24h
            return round($avgMinutes / 60, 1) . ' heures';
        } else {
            return round($avgMinutes / 1440, 1) . ' jours';
        }
    }

    /**
     * Compter les dossiers actuellement à cette étape (IMPLÉMENTÉ)
     */
    private function getDossiersEnCoursCount($stepId)
    {
        return DB::table('dossiers')
            ->where('current_step_id', $stepId)
            ->whereIn('statut', ['soumis', 'en_cours'])
            ->count();
    }

    /**
     * Obtenir la durée moyenne globale de toutes les étapes (IMPLÉMENTÉ)
     */
    private function getGlobalAvgDuration()
    {
        $avgMinutes = DB::table('dossier_validations')
            ->whereNotNull('duree_traitement')
            ->avg('duree_traitement');

        if (!$avgMinutes) {
            return 'N/A';
        }

        if ($avgMinutes < 1440) {
            return round($avgMinutes / 60, 1) . ' heures';
        } else {
            return round($avgMinutes / 1440, 1) . ' jours';
        }
    }

    /**
     * Obtenir le nombre total de dossiers passés par une étape (IMPLÉMENTÉ)
     */
    private function getTotalDossiersPassesByStep($stepId)
    {
        return DB::table('dossier_validations')
            ->where('workflow_step_id', $stepId)
            ->distinct('dossier_id')
            ->count('dossier_id');
    }

    /**
     * Obtenir le taux d'approbation d'une étape (IMPLÉMENTÉ)
     */
    private function getTauxApprobation($stepId)
    {
        $total = DB::table('dossier_validations')
            ->where('workflow_step_id', $stepId)
            ->whereIn('decision', ['approuve', 'rejete'])
            ->count();

        if ($total == 0) {
            return 0;
        }

        $approuves = DB::table('dossier_validations')
            ->where('workflow_step_id', $stepId)
            ->where('decision', 'approuve')
            ->count();

        return round(($approuves / $total) * 100, 1);
    }

    /**
     * Obtenir les dossiers récents traités à cette étape (IMPLÉMENTÉ)
     */
    private function getRecentDossiersForStep($stepId, $limit = 10)
    {
        return DB::table('dossier_validations as dv')
            ->join('dossiers as d', 'd.id', '=', 'dv.dossier_id')
            ->join('organisations as o', 'o.id', '=', 'd.organisation_id')
            ->where('dv.workflow_step_id', $stepId)
            ->select(
                'd.id',
                'd.numero_dossier',
                'o.nom as organisation_nom',
                'dv.decision',
                'dv.decided_at',
                'dv.duree_traitement'
            )
            ->orderBy('dv.decided_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir le délai moyen réel de traitement (IMPLÉMENTÉ)
     */
    private function getDelaiMoyenReel($stepId)
    {
        $avgMinutes = DB::table('dossier_validations')
            ->where('workflow_step_id', $stepId)
            ->whereNotNull('duree_traitement')
            ->avg('duree_traitement');

        if (!$avgMinutes) {
            return 'N/A';
        }

        // Retourner en heures
        return round($avgMinutes / 60, 1) . ' heures';
    }

    /**
     * Compter les dossiers en retard (IMPLÉMENTÉ)
     */
    private function getDossiersEnRetard($stepId)
    {
        // Récupérer le délai prévu
        $step = DB::table($this->table)->where('id', $stepId)->first();

        if (!$step) {
            return 0;
        }

        $delaiHeures = $step->delai_traitement;

        // Compter les dossiers en cours qui dépassent le délai
        return DB::table('dossiers as d')
            ->join('dossier_validations as dv', function ($join) use ($stepId) {
                $join->on('d.id', '=', 'dv.dossier_id')
                    ->where('dv.workflow_step_id', $stepId)
                    ->where('dv.decision', 'en_attente');
            })
            ->where('d.current_step_id', $stepId)
            ->whereRaw('TIMESTAMPDIFF(HOUR, dv.assigned_at, NOW()) > ?', [$delaiHeures])
            ->count();
    }

    /**
     * Taux de respect des délais (IMPLÉMENTÉ)
     */
    private function getTauxRespectDelai($stepId)
    {
        $step = DB::table($this->table)->where('id', $stepId)->first();

        if (!$step) {
            return 0;
        }

        $delaiMinutes = $step->delai_traitement * 60;

        $total = DB::table('dossier_validations')
            ->where('workflow_step_id', $stepId)
            ->whereNotNull('duree_traitement')
            ->count();

        if ($total == 0) {
            return 0;
        }

        $respectes = DB::table('dossier_validations')
            ->where('workflow_step_id', $stepId)
            ->whereNotNull('duree_traitement')
            ->where('duree_traitement', '<=', $delaiMinutes)
            ->count();

        return round(($respectes / $total) * 100, 1);
    }

    /**
     * Nombre de rejets (IMPLÉMENTÉ)
     */
    private function getNombreRejets($stepId)
    {
        return DB::table('dossier_validations')
            ->where('workflow_step_id', $stepId)
            ->where('decision', 'rejete')
            ->count();
    }

    /**
     * ✅ Compte des steps par type (CORRIGÉ avec FK)
     */
    private function getStepsCountByType()
    {
        $counts = DB::table($this->table . ' as ws')
            ->join('organisation_types as ot', 'ws.organisation_type_id', '=', 'ot.id')
            ->select('ot.code', DB::raw('count(*) as count'))
            ->where('ws.is_active', 1)
            ->groupBy('ot.code')
            ->get()
            ->pluck('count', 'code')
            ->toArray();

        return $counts;
    }

    /**
     * ========================================================================
     * CONFIGURE - MATRICE ÉTAPES × ENTITÉS
     * ========================================================================
     */
    public function configure(Request $request)
    {
        // Filtres
        $typeOrganisation = $request->get('type_organisation', 'association');
        $typeOperation = $request->get('type_operation', 'creation');

        // ✅ Convertir codes en IDs
        $orgTypeId = $this->getOrganisationTypeId($typeOrganisation);
        $opTypeId = $this->getOperationTypeId($typeOperation);

        if (!$orgTypeId || !$opTypeId) {
            return back()->with('error', 'Type organisation ou opération invalide');
        }

        // ✅ Récupérer toutes les étapes pour ce contexte avec jointures
        $steps = DB::table('workflow_steps as ws')
            ->leftJoin('organisation_types as ot', 'ws.organisation_type_id', '=', 'ot.id')
            ->leftJoin('operation_types as opt', 'ws.operation_type_id', '=', 'opt.id')
            ->where('ws.organisation_type_id', $orgTypeId)
            ->where('ws.operation_type_id', $opTypeId)
            ->select('ws.*', 'ot.nom as type_organisation_nom', 'opt.libelle as type_operation_nom')
            ->orderBy('ws.numero_passage')
            ->get();

        // Récupérer toutes les entités actives
        $entities = DB::table('validation_entities')
            ->where('is_active', 1)
            ->orderBy('type')
            ->orderBy('nom')
            ->get();

        // ✅ Récupérer les assignations existantes
        $assignments = DB::table('workflow_step_entities as wse')
            ->join('workflow_steps as ws', 'wse.workflow_step_id', '=', 'ws.id')
            ->where('ws.organisation_type_id', $orgTypeId)
            ->where('ws.operation_type_id', $opTypeId)
            ->select('wse.*')
            ->get()
            ->groupBy('workflow_step_id');

        // Construire la matrice
        $matrix = [];
        foreach ($steps as $step) {
            $stepAssignments = $assignments->get($step->id, collect());
            $matrix[$step->id] = [
                'step' => $step,
                'entities' => $stepAssignments->pluck('validation_entity_id')->toArray(),
                'details' => $stepAssignments->keyBy('validation_entity_id')
            ];
        }

        // ✅ Types pour les filtres (depuis DB)
        $typesOrganisation = $this->getTypesOrganisations();
        $typesOperation = $this->getTypesOperations();

        return view('admin.workflow-steps.configure', compact(
            'steps',
            'entities',
            'matrix',
            'typeOrganisation',
            'typeOperation',
            'typesOrganisation',
            'typesOperation'
        ));
    }

    /**
     * Sauvegarder la configuration de la matrice
     */
    public function saveConfiguration(Request $request)
    {
        $validated = $request->validate([
            'type_organisation' => 'required|string',
            'type_operation' => 'required|string',
            'assignments' => 'required|array'
        ]);

        DB::beginTransaction();

        try {
            // ✅ Convertir codes en IDs
            $orgTypeId = $this->getOrganisationTypeId($validated['type_organisation']);
            $opTypeId = $this->getOperationTypeId($validated['type_operation']);

            if (!$orgTypeId || !$opTypeId) {
                throw new \Exception('Type organisation ou opération invalide');
            }

            // Récupérer tous les steps pour ce contexte
            $stepIds = DB::table('workflow_steps')
                ->where('organisation_type_id', $orgTypeId)
                ->where('operation_type_id', $opTypeId)
                ->pluck('id')
                ->toArray();

            // Supprimer toutes les assignations existantes pour ces steps
            DB::table('workflow_step_entities')
                ->whereIn('workflow_step_id', $stepIds)
                ->delete();

            // Recréer les assignations
            foreach ($validated['assignments'] as $stepId => $entities) {
                if (empty($entities))
                    continue;

                foreach ($entities as $index => $entityData) {
                    DB::table('workflow_step_entities')->insert([
                        'workflow_step_id' => $stepId,
                        'validation_entity_id' => $entityData['entity_id'],
                        'ordre' => $index + 1,
                        'is_optional' => $entityData['is_optional'] ?? 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Configuration enregistrée avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur saveConfiguration: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ========================================================================
     * TIMELINE - RÉORGANISATION DRAG & DROP
     * ========================================================================
     */
    public function timeline(Request $request)
    {
        // Filtres
        $typeOrganisation = $request->get('type_organisation', 'association');
        $typeOperation = $request->get('type_operation', 'creation');

        // ✅ Convertir codes en IDs
        $orgTypeId = $this->getOrganisationTypeId($typeOrganisation);
        $opTypeId = $this->getOperationTypeId($typeOperation);

        if (!$orgTypeId || !$opTypeId) {
            return back()->with('error', 'Type organisation ou opération invalide');
        }

        // ✅ Récupérer les étapes avec leurs entités et noms lisibles
        $steps = DB::table('workflow_steps as ws')
            ->leftJoin('organisation_types as ot', 'ws.organisation_type_id', '=', 'ot.id')
            ->leftJoin('operation_types as opt', 'ws.operation_type_id', '=', 'opt.id')
            ->where('ws.organisation_type_id', $orgTypeId)
            ->where('ws.operation_type_id', $opTypeId)
            ->select('ws.*', 'ot.nom as type_organisation_nom', 'opt.libelle as type_operation_nom')
            ->orderBy('ws.numero_passage')
            ->get();

        // Pour chaque étape, récupérer ses entités
        foreach ($steps as $step) {
            $step->entities = DB::table('workflow_step_entities as wse')
                ->join('validation_entities as ve', 'wse.validation_entity_id', '=', 've.id')
                ->where('wse.workflow_step_id', $step->id)
                ->select('ve.id', 've.nom', 've.code', 've.type', 'wse.ordre', 'wse.is_optional')
                ->orderBy('wse.ordre')
                ->get();
        }

        // ✅ Types pour les filtres (depuis DB)
        $typesOrganisation = $this->getTypesOrganisations();
        $typesOperation = $this->getTypesOperations();

        return view('admin.workflow-steps.timeline', compact(
            'steps',
            'typeOrganisation',
            'typeOperation',
            'typesOrganisation',
            'typesOperation'
        ));
    }

    /**
     * Assigner une entité à une étape (AJAX)
     */
    public function assignEntity(Request $request, $stepId)
    {
        $validated = $request->validate([
            'entity_id' => 'required|integer|exists:validation_entities,id',
            'is_optional' => 'boolean'
        ]);

        try {
            // Vérifier si l'assignation existe déjà
            $exists = DB::table('workflow_step_entities')
                ->where('workflow_step_id', $stepId)
                ->where('validation_entity_id', $validated['entity_id'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette entité est déjà assignée à cette étape'
                ], 400);
            }

            // Récupérer le prochain ordre
            $maxOrdre = DB::table('workflow_step_entities')
                ->where('workflow_step_id', $stepId)
                ->max('ordre');

            // Créer l'assignation
            DB::table('workflow_step_entities')->insert([
                'workflow_step_id' => $stepId,
                'validation_entity_id' => $validated['entity_id'],
                'ordre' => ($maxOrdre ?? 0) + 1,
                'is_optional' => $validated['is_optional'] ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Entité assignée avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur assignEntity: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation'
            ], 500);
        }
    }

    /**
     * Retirer une entité d'une étape (AJAX)
     */
    public function removeEntity($stepId, $entityId)
    {
        try {
            $deleted = DB::table('workflow_step_entities')
                ->where('workflow_step_id', $stepId)
                ->where('validation_entity_id', $entityId)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Entité retirée avec succès'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Assignation non trouvée'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Erreur removeEntity: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression'
            ], 500);
        }
    }

    /**
     * Réorganiser l'ordre des entités d'une étape (AJAX)
     */
    public function reorderEntities(Request $request, $stepId)
    {
        $validated = $request->validate([
            'entity_ids' => 'required|array',
            'entity_ids.*' => 'integer|exists:validation_entities,id'
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['entity_ids'] as $index => $entityId) {
                DB::table('workflow_step_entities')
                    ->where('workflow_step_id', $stepId)
                    ->where('validation_entity_id', $entityId)
                    ->update([
                        'ordre' => $index + 1,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Ordre mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur reorderEntities: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réorganisation'
            ], 500);
        }
    }
}