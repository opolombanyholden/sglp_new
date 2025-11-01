<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Canton;
use App\Models\Departement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class CantonController extends Controller
{
    /**
     * Afficher la liste des cantons
     */
    public function index(Request $request)
    {
        $query = Canton::with(['departement.province']);

        // Filtrage par département
        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }

        // Filtrage par statut
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Recherche textuelle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $cantons = $query->ordered()->paginate(15);

        // Données pour les filtres
        $departements = Departement::with('province')->active()->ordered()->get();

        return view('admin.geolocalisation.cantons.index', compact('cantons', 'departements'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $departements = Departement::with('province')->active()->ordered()->get();
        
        return view('admin.geolocalisation.cantons.create', compact('departements'));
    }

    /**
     * Enregistrer un nouveau canton
     */
    public function store(Request $request)
    {
        $validator = $this->validateCanton($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $canton = Canton::create([
                'departement_id' => $request->departement_id,
                'nom' => $request->nom,
                'code' => $request->code,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true)
            ]);

            DB::commit();

            return redirect()->route('admin.geolocalisation.cantons.index')
                ->with('success', 'Canton créé avec succès.');

        } catch (Exception $e) {
            DB::rollback();
            
            return back()
                ->withError('Erreur lors de la création : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher un canton spécifique
     */
    public function show(Canton $canton)
    {
        $canton->load([
            'departement.province',
            'regroupements' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('ordre_affichage')
                      ->orderBy('nom');
            }
        ]);

        // Statistiques - Utiliser des méthodes sûres
        $stats = [
            'regroupements_count' => $canton->regroupements()->count(),
            'villages_count' => 0, // À implémenter selon la structure
            'organisations_count' => $canton->organisations()->count(),
            'adherents_count' => $canton->adherents()->count(),
        ];

        return view('admin.geolocalisation.cantons.show', compact('canton', 'stats'));
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(Canton $canton)
    {
        $canton->load('departement.province');
        $departements = Departement::with('province')->active()->ordered()->get();

        return view('admin.geolocalisation.cantons.edit', compact('canton', 'departements'));
    }

    /**
     * Mettre à jour un canton
     */
    public function update(Request $request, Canton $canton)
    {
        $validator = $this->validateCanton($request, $canton->id);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $canton->update([
                'departement_id' => $request->departement_id,
                'nom' => $request->nom,
                'code' => $request->code,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true)
            ]);

            DB::commit();

            return redirect()->route('admin.geolocalisation.cantons.index')
                ->with('success', 'Canton modifié avec succès.');

        } catch (Exception $e) {
            DB::rollback();
            
            return back()
                ->withError('Erreur lors de la modification : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un canton
     */
    public function destroy(Canton $canton)
    {
        try {
            // Vérifier si le canton peut être supprimé
            if (!$canton->canBeDeleted()) {
                $blockers = implode(', ', $canton->deletion_blockers);
                return back()->withError("Impossible de supprimer : {$blockers}.");
            }

            $canton->delete();

            return redirect()->route('admin.geolocalisation.cantons.index')
                ->with('success', 'Canton supprimé avec succès.');

        } catch (Exception $e) {
            return back()->withError('Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * API : Récupérer les cantons par département
     */
    public function getByDepartement(Request $request, $departementId)
    {
        $cantons = Canton::where('departement_id', $departementId)
            ->active()
            ->ordered()
            ->get(['id', 'nom', 'code']);

        return response()->json($cantons);
    }

    /**
     * API : Recherche de cantons
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $results = Canton::with('departement.province')
            ->where('nom', 'LIKE', "%{$query}%")
            ->active()
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->nom_complet,
                    'departement' => $item->departement->nom,
                    'province' => $item->departement->province->nom
                ];
            });

        return response()->json(['results' => $results]);
    }

    /**
     * Basculer le statut actif/inactif
     */
    public function toggleStatus(Canton $canton)
    {
        try {
            $canton->update([
                'is_active' => !$canton->is_active
            ]);

            $status = $canton->is_active ? 'activé' : 'désactivé';
            
            return response()->json([
                'success' => true,
                'message' => "Canton {$status} avec succès.",
                'new_status' => $canton->is_active
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut.'
            ], 500);
        }
    }

    /**
     * Validation des données de canton
     */
    private function validateCanton(Request $request, $excludeId = null)
    {
        $rules = [
            'departement_id' => 'required|exists:departements,id',
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ];

        // Règles d'unicité
        $uniqueRules = [
            'nom' => "unique:cantons,nom,{$excludeId},id,departement_id,{$request->departement_id}",
            'code' => "unique:cantons,code,{$excludeId},id"
        ];

        $rules = array_merge($rules, $uniqueRules);

        $messages = [
            'departement_id.required' => 'Le département est obligatoire.',
            'departement_id.exists' => 'Le département sélectionné n\'existe pas.',
            'nom.required' => 'Le nom est obligatoire.',
            'nom.unique' => 'Ce nom existe déjà dans ce département.',
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}