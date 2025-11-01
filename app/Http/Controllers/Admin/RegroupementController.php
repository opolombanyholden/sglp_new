<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Regroupement;
use App\Models\Canton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class RegroupementController extends Controller
{
    /**
     * Afficher la liste des regroupements
     */
    public function index(Request $request)
    {
        $query = Regroupement::with(['canton.departement.province']);

        // Filtrage par canton
        if ($request->filled('canton_id')) {
            $query->where('canton_id', $request->canton_id);
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

        $regroupements = $query->orderBy('nom')->paginate(15);

        // Données pour les filtres
        $cantons = Canton::with('departement')->active()->orderBy('nom')->get();

        return view('admin.geolocalisation.regroupements.index', compact('regroupements', 'cantons'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $cantons = Canton::with('departement')->active()->orderBy('nom')->get();
        
        return view('admin.geolocalisation.regroupements.create', compact('cantons'));
    }

    /**
     * Enregistrer un nouveau regroupement
     */
    public function store(Request $request)
    {
        $validator = $this->validateRegroupement($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $regroupement = Regroupement::create([
                'canton_id' => $request->canton_id,
                'nom' => $request->nom,
                'code' => $request->code,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true)
            ]);

            DB::commit();

            return redirect()->route('admin.geolocalisation.regroupements.index')
                ->with('success', 'Regroupement créé avec succès.');

        } catch (Exception $e) {
            DB::rollback();
            
            return back()
                ->withError('Erreur lors de la création : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher un regroupement spécifique
     */
    public function show(Regroupement $regroupement)
    {
        $regroupement->load([
            'canton.departement.province'
        ]);

        return view('admin.geolocalisation.regroupements.show', compact('regroupement'));
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(Regroupement $regroupement)
    {
        $regroupement->load('canton.departement.province');
        $cantons = Canton::with('departement')->active()->orderBy('nom')->get();

        return view('admin.geolocalisation.regroupements.edit', compact('regroupement', 'cantons'));
    }

    /**
     * Mettre à jour un regroupement
     */
    public function update(Request $request, Regroupement $regroupement)
    {
        $validator = $this->validateRegroupement($request, $regroupement->id);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $regroupement->update([
                'canton_id' => $request->canton_id,
                'nom' => $request->nom,
                'code' => $request->code,
                'description' => $request->description,
                'is_active' => $request->boolean('is_active', true)
            ]);

            DB::commit();

            return redirect()->route('admin.geolocalisation.regroupements.index')
                ->with('success', 'Regroupement modifié avec succès.');

        } catch (Exception $e) {
            DB::rollback();
            
            return back()
                ->withError('Erreur lors de la modification : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un regroupement
     */
    public function destroy(Regroupement $regroupement)
    {
        try {
            $regroupement->delete();

            return redirect()->route('admin.geolocalisation.regroupements.index')
                ->with('success', 'Regroupement supprimé avec succès.');

        } catch (Exception $e) {
            return back()->withError('Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * API : Récupérer les regroupements par canton
     */
    public function getByCanton(Request $request, $cantonId)
    {
        $regroupements = Regroupement::where('canton_id', $cantonId)
            ->where('is_active', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'code']);

        return response()->json($regroupements);
    }

    /**
     * API : Recherche de regroupements
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $results = Regroupement::with('canton.departement.province')
            ->where('nom', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->nom,
                    'canton' => $item->canton->nom,
                    'departement' => $item->canton->departement->nom
                ];
            });

        return response()->json(['results' => $results]);
    }

    /**
     * Basculer le statut actif/inactif
     */
    public function toggleStatus(Regroupement $regroupement)
    {
        try {
            $regroupement->update([
                'is_active' => !$regroupement->is_active
            ]);

            $status = $regroupement->is_active ? 'activé' : 'désactivé';
            
            return response()->json([
                'success' => true,
                'message' => "Regroupement {$status} avec succès.",
                'new_status' => $regroupement->is_active
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut.'
            ], 500);
        }
    }

    /**
     * Validation des données de regroupement
     */
    private function validateRegroupement(Request $request, $excludeId = null)
    {
        $rules = [
            'canton_id' => 'required|exists:cantons,id',
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ];

        // Règles d'unicité
        $uniqueRules = [
            'nom' => "unique:regroupements,nom,{$excludeId},id,canton_id,{$request->canton_id}",
            'code' => "unique:regroupements,code,{$excludeId},id"
        ];

        $rules = array_merge($rules, $uniqueRules);

        $messages = [
            'canton_id.required' => 'Le canton est obligatoire.',
            'canton_id.exists' => 'Le canton sélectionné n\'existe pas.',
            'nom.required' => 'Le nom est obligatoire.',
            'nom.unique' => 'Ce nom existe déjà dans ce canton.',
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}