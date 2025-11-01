<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Localite;
use App\Models\Arrondissement;
use App\Models\Regroupement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class LocaliteController extends Controller
{
    /**
     * Afficher la liste des localités
     */
    public function index(Request $request)
    {
        $query = Localite::with([
            'arrondissement.communeVille.departement.province',
            'regroupement.canton.departement.province'
        ]);

        // Filtrage par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filtrage par arrondissement
        if ($request->filled('arrondissement_id')) {
            $query->where('arrondissement_id', $request->arrondissement_id);
        }

        // Filtrage par regroupement
        if ($request->filled('regroupement_id')) {
            $query->where('regroupement_id', $request->regroupement_id);
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

        $localites = $query->orderBy('nom')->paginate(20);

        // Données pour les filtres
        $arrondissements = Arrondissement::with('communeVille')
            ->active()
            ->orderBy('nom')
            ->get();

        $regroupements = Regroupement::with('canton')
            ->active()
            ->orderBy('nom')
            ->get();

        return view('admin.geolocalisation.localites.index', compact('localites', 'arrondissements', 'regroupements'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(Request $request)
    {
        $type = $request->get('type', Localite::TYPE_QUARTIER);

        // Vérifier que le type est valide
        if (!in_array($type, [Localite::TYPE_QUARTIER, Localite::TYPE_VILLAGE])) {
            $type = Localite::TYPE_QUARTIER;
        }

        // Charger les données selon le type
        if ($type === Localite::TYPE_QUARTIER) {
            $arrondissements = Arrondissement::with('communeVille.departement')
                ->active()
                ->orderBy('nom')
                ->get();
            $regroupements = collect();
        } else {
            $regroupements = Regroupement::with('canton.departement')
                ->active()
                ->orderBy('nom')
                ->get();
            $arrondissements = collect();
        }

        return view('admin.geolocalisation.localites.create', compact('type', 'arrondissements', 'regroupements'));
    }

    /**
     * Enregistrer une nouvelle localité
     */
    public function store(Request $request)
    {
        $validator = $this->validateLocalite($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $data = [
                'type' => $request->type,
                'nom' => $request->nom,
                'code' => $request->code,
                'description' => $request->description,
                'population_estimee' => $request->population_estimee,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_active' => $request->boolean('is_active', true),
                'ordre_affichage' => $request->ordre_affichage ?? 0
            ];

            // Ajouter l'ID parent selon le type
            if ($request->type === Localite::TYPE_QUARTIER) {
                $data['arrondissement_id'] = $request->arrondissement_id;
                $data['regroupement_id'] = null;
            } else {
                $data['regroupement_id'] = $request->regroupement_id;
                $data['arrondissement_id'] = null;
            }

            $localite = Localite::create($data);

            DB::commit();

            $typeLibelle = $localite->type_libelle;

            return redirect()->route('admin.geolocalisation.localites.show', $localite)
                ->with('success', "{$typeLibelle} \"{$localite->nom}\" créé(e) avec succès !");

        } catch (Exception $e) {
            DB::rollback();

            return back()
                ->withError('Erreur lors de la création : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'une localité
     */
    public function show(Localite $localite)
    {
        $localite->load([
            'arrondissement.communeVille.departement.province',
            'regroupement.canton.departement.province'
        ]);

        return view('admin.geolocalisation.localites.show', compact('localite'));
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(Localite $localite)
    {
        $localite->load([
            'arrondissement.communeVille.departement',
            'regroupement.canton.departement'
        ]);

        // Charger les données selon le type
        if ($localite->type === Localite::TYPE_QUARTIER) {
            $arrondissements = Arrondissement::with('communeVille.departement')
                ->active()
                ->orderBy('nom')
                ->get();
            $regroupements = collect();
        } else {
            $regroupements = Regroupement::with('canton.departement')
                ->active()
                ->orderBy('nom')
                ->get();
            $arrondissements = collect();
        }

        return view('admin.geolocalisation.localites.edit', compact('localite', 'arrondissements', 'regroupements'));
    }

    /**
     * Mettre à jour une localité
     */
    public function update(Request $request, Localite $localite)
    {
        $validator = $this->validateLocalite($request, $localite->id);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $data = [
                'type' => $request->type,
                'nom' => $request->nom,
                'code' => $request->code,
                'description' => $request->description,
                'population_estimee' => $request->population_estimee,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'is_active' => $request->boolean('is_active', true),
                'ordre_affichage' => $request->ordre_affichage ?? 0
            ];

            // Mettre à jour l'ID parent selon le type
            if ($request->type === Localite::TYPE_QUARTIER) {
                $data['arrondissement_id'] = $request->arrondissement_id;
                $data['regroupement_id'] = null;
            } else {
                $data['regroupement_id'] = $request->regroupement_id;
                $data['arrondissement_id'] = null;
            }

            $localite->update($data);

            DB::commit();

            $typeLibelle = $localite->type_libelle;

            return redirect()->route('admin.geolocalisation.localites.show', $localite)
                ->with('success', "{$typeLibelle} \"{$localite->nom}\" mis(e) à jour avec succès !");

        } catch (Exception $e) {
            DB::rollback();

            return back()
                ->withError('Erreur lors de la mise à jour : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une localité
     */
    public function destroy(Localite $localite)
    {
        try {
            // Vérifier si la localité peut être supprimée
            $verification = $localite->peutEtreSupprime();

            if (!$verification['peut_supprimer']) {
                return back()->withError($verification['raison']);
            }

            DB::beginTransaction();

            $nom = $localite->nom;
            $typeLibelle = $localite->type_libelle;

            $localite->delete();

            DB::commit();

            return redirect()->route('admin.geolocalisation.localites.index')
                ->with('success', "{$typeLibelle} \"{$nom}\" supprimé(e) avec succès !");

        } catch (Exception $e) {
            DB::rollback();

            return back()->withError('Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Basculer le statut actif/inactif
     */
    public function toggleStatus(Localite $localite)
    {
        try {
            $localite->update([
                'is_active' => !$localite->is_active
            ]);

            $status = $localite->is_active ? 'activée' : 'désactivée';
            $typeLibelle = $localite->type_libelle;

            return redirect()->back()
                ->with('success', "{$typeLibelle} \"{$localite->nom}\" {$status} avec succès !");

        } catch (Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors du changement de statut : ' . $e->getMessage());
        }
    }

    /**
     * API : Localités par arrondissement
     */
    public function byArrondissement($arrondissementId)
    {
        $localites = Localite::where('arrondissement_id', $arrondissementId)
            ->where('type', Localite::TYPE_QUARTIER)
            ->active()
            ->orderBy('nom')
            ->get(['id', 'nom', 'code']);

        return response()->json($localites);
    }

    /**
     * API : Localités par regroupement
     */
    public function byRegroupement($regroupementId)
    {
        $localites = Localite::where('regroupement_id', $regroupementId)
            ->where('type', Localite::TYPE_VILLAGE)
            ->active()
            ->orderBy('nom')
            ->get(['id', 'nom', 'code']);

        return response()->json($localites);
    }

    /**
     * Validation des données de localité
     */
    private function validateLocalite(Request $request, $excludeId = null)
    {
        $rules = [
            'type' => 'required|in:' . Localite::TYPE_QUARTIER . ',' . Localite::TYPE_VILLAGE,
            'nom' => 'required|string|max:255',
            'code' => 'nullable|string|max:35',
            'description' => 'nullable|string|max:1000',
            'population_estimee' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'ordre_affichage' => 'nullable|integer|min:0'
        ];

        // Règles conditionnelles selon le type
        if ($request->type === Localite::TYPE_QUARTIER) {
            $rules['arrondissement_id'] = 'required|exists:arrondissements,id';
            $rules['regroupement_id'] = 'nullable';
        } else {
            $rules['regroupement_id'] = 'required|exists:regroupements,id';
            $rules['arrondissement_id'] = 'nullable';
        }

        // Règle d'unicité du code
        if ($request->filled('code')) {
            $codeRule = 'unique:localites,code';
            if ($excludeId) {
                $codeRule .= ',' . $excludeId;
            }
            $rules['code'] .= '|' . $codeRule;
        }

        $messages = [
            'type.required' => 'Le type est obligatoire.',
            'nom.required' => 'Le nom est obligatoire.',
            'arrondissement_id.required' => 'L\'arrondissement est obligatoire pour un quartier.',
            'regroupement_id.required' => 'Le regroupement est obligatoire pour un village.',
            'code.unique' => 'Ce code existe déjà.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }
}