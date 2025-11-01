<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arrondissement;
use App\Models\CommuneVille;
use App\Models\Departement;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class ArrondissementController extends Controller
{
    /**
     * Afficher la liste des arrondissements
     */
    public function index(Request $request)
    {
        $query = Arrondissement::with(['communeVille.departement.province']);

        // Filtrage par commune/ville
        if ($request->filled('commune_ville_id')) {
            $query->where('commune_ville_id', $request->commune_ville_id);
        }

        // Filtrage par département
        if ($request->filled('departement_id')) {
            $query->whereHas('communeVille', function($q) use ($request) {
                $q->where('departement_id', $request->departement_id);
            });
        }

        // Filtrage par numéro d'arrondissement
        if ($request->filled('numero_arrondissement')) {
            $query->where('numero_arrondissement', $request->numero_arrondissement);
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
                  ->orWhere('delegue', 'LIKE', "%{$search}%");
            });
        }

        $arrondissements = $query->ordered()->paginate(15);

        // Données pour les filtres
        $communesVilles = CommuneVille::with('departement.province')->active()->ordered()->get();
        $departements = Departement::with('province')->active()->ordered()->get();

        return view('admin.geolocalisation.arrondissements.index', compact(
            'arrondissements', 
            'communesVilles', 
            'departements'
        ));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $communesVilles = CommuneVille::with('departement.province')->active()->ordered()->get();
        $departements = Departement::with('province')->active()->ordered()->get();
        $provinces = Province::active()->ordered()->get();
        
        return view('admin.geolocalisation.arrondissements.create', compact(
            'communesVilles', 
            'departements', 
            'provinces'
        ));
    }

    /**
     * Enregistrer un nouvel arrondissement
     */
    public function store(Request $request)
    {
        $validator = $this->validateArrondissement($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $arrondissement = Arrondissement::create([
                'commune_ville_id' => $request->commune_ville_id,
                'nom' => $request->nom,
                'code' => $request->code,
                'numero_arrondissement' => $request->numero_arrondissement,
                'description' => $request->description,
                'superficie_km2' => $request->superficie_km2,
                'population_estimee' => $request->population_estimee,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'delegue' => $request->delegue,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'limites_geographiques' => $request->limites_geographiques,
                'services_publics' => $this->prepareJsonField($request, 'services_publics'),
                'equipements' => $this->prepareJsonField($request, 'equipements'),
                'metadata' => $this->prepareMetadata($request),
                'is_active' => $request->has('is_active') ? true : ($request->filled('is_active') ? $request->boolean('is_active') : true),
                'ordre_affichage' => $request->ordre_affichage
            ]);

            DB::commit();

            return redirect()->route('admin.geolocalisation.arrondissements.index')
                ->with('success', 'Arrondissement créé avec succès.');

        } catch (Exception $e) {
            DB::rollback();
            
            return back()
                ->withError('Erreur lors de la création : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher un arrondissement spécifique
     */
   public function show(Arrondissement $arrondissement)
    {
        $arrondissement->load([
            'communeVille.departement.province',
            'localites' => function($query) {
                $query->where('is_active', true)
                    ->orderBy('ordre_affichage')
                    ->orderBy('nom');
            }
        ]);

        // Statistiques
        $stats = [
            'quartiers_count' => $arrondissement->countQuartiers(),
            'organisations_count' => $arrondissement->countOrganisations(),
            'adherents_count' => $arrondissement->countAdherents(),
        ];

        return view('admin.geolocalisation.arrondissements.show', compact('arrondissement', 'stats'));
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(Arrondissement $arrondissement)
    {
        $arrondissement->load('communeVille.departement.province');
        $communesVilles = CommuneVille::with('departement.province')->active()->ordered()->get();
        $departements = Departement::with('province')->active()->ordered()->get();
        $provinces = Province::active()->ordered()->get();

        return view('admin.geolocalisation.arrondissements.edit', compact(
            'arrondissement',
            'communesVilles', 
            'departements', 
            'provinces'
        ));
    }

    /**
     * Mettre à jour un arrondissement
     */
    public function update(Request $request, Arrondissement $arrondissement)
    {
        $validator = $this->validateArrondissement($request, $arrondissement->id);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $arrondissement->update([
                'commune_ville_id' => $request->commune_ville_id,
                'nom' => $request->nom,
                'code' => $request->code,
                'numero_arrondissement' => $request->numero_arrondissement,
                'description' => $request->description,
                'superficie_km2' => $request->superficie_km2,
                'population_estimee' => $request->population_estimee,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'delegue' => $request->delegue,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'limites_geographiques' => $request->limites_geographiques,
                'services_publics' => $this->prepareJsonField($request, 'services_publics'),
                'equipements' => $this->prepareJsonField($request, 'equipements'),
                'metadata' => $this->prepareMetadata($request),
                'is_active' => $request->boolean('is_active', true),
                'ordre_affichage' => $request->ordre_affichage
            ]);

            DB::commit();

            return redirect()->route('admin.geolocalisation.arrondissements.index')
                ->with('success', 'Arrondissement modifié avec succès.');

        } catch (Exception $e) {
            DB::rollback();
            
            return back()
                ->withError('Erreur lors de la modification : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un arrondissement
     */
    public function destroy(Arrondissement $arrondissement)
    {
        try {
            // Vérifier si l'arrondissement peut être supprimé
            if (!$arrondissement->canBeDeleted()) {
                $blockers = implode(', ', $arrondissement->deletion_blockers);
                return back()->withError("Impossible de supprimer : {$blockers}.");
            }

            $arrondissement->delete();

            return redirect()->route('admin.geolocalisation.arrondissements.index')
                ->with('success', 'Arrondissement supprimé avec succès.');

        } catch (Exception $e) {
            return back()->withError('Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * API : Récupérer les arrondissements par commune/ville
     */
    public function getByCommuneVille(Request $request, $communeVilleId)
    {
        $arrondissements = Arrondissement::where('commune_ville_id', $communeVilleId)
            ->active()
            ->ordered()
            ->get(['id', 'nom', 'numero_arrondissement', 'code']);

        return response()->json($arrondissements);
    }

    /**
     * API : Récupérer les arrondissements par département
     */
    public function getByDepartement(Request $request, $departementId)
    {
        $arrondissements = Arrondissement::whereHas('communeVille', function($q) use ($departementId) {
                $q->where('departement_id', $departementId);
            })
            ->with('communeVille')
            ->active()
            ->ordered()
            ->get(['id', 'nom', 'numero_arrondissement', 'code', 'commune_ville_id']);

        return response()->json($arrondissements);
    }

    /**
     * API : Recherche d'arrondissements
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $results = Arrondissement::with('communeVille.departement.province')
            ->where('nom', 'LIKE', "%{$query}%")
            ->active()
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->nom_complet,
                    'commune_ville' => $item->communeVille->nom,
                    'departement' => $item->communeVille->departement->nom,
                    'province' => $item->communeVille->departement->province->nom
                ];
            });

        return response()->json(['results' => $results]);
    }

    /**
     * Basculer le statut actif/inactif
     */
    public function toggleStatus(Arrondissement $arrondissement)
    {
        try {
            $arrondissement->update([
                'is_active' => !$arrondissement->is_active
            ]);

            $status = $arrondissement->is_active ? 'activé' : 'désactivé';
            
            return response()->json([
                'success' => true,
                'message' => "Arrondissement {$status} avec succès.",
                'new_status' => $arrondissement->is_active
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut.'
            ], 500);
        }
    }

    /**
     * Obtenir le prochain numéro d'arrondissement pour une commune/ville
     */
    public function getNextNumero(Request $request, $communeVilleId)
    {
        $nextNumero = Arrondissement::getNextNumeroForCommuneVille($communeVilleId);
        
        return response()->json(['next_numero' => $nextNumero]);
    }

    /**
     * Validation des données d'arrondissement
     */
    private function validateArrondissement(Request $request, $excludeId = null)
    {
        $rules = [
            'commune_ville_id' => 'required|exists:communes_villes,id',
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:25',
            'numero_arrondissement' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'superficie_km2' => 'nullable|numeric|min:0',
            'population_estimee' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'delegue' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'limites_geographiques' => 'nullable|string',
            'services_publics' => 'nullable|string',
            'equipements' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'ordre_affichage' => 'nullable|integer|min:0'
        ];

        // Règles d'unicité
        $uniqueRules = [
            'nom' => "unique:arrondissements,nom,{$excludeId},id,commune_ville_id,{$request->commune_ville_id}",
            'code' => "unique:arrondissements,code,{$excludeId},id,commune_ville_id,{$request->commune_ville_id}",
            'numero_arrondissement' => "unique:arrondissements,numero_arrondissement,{$excludeId},id,commune_ville_id,{$request->commune_ville_id}"
        ];

        $rules = array_merge($rules, $uniqueRules);

        $messages = [
            'commune_ville_id.required' => 'La commune/ville est obligatoire.',
            'commune_ville_id.exists' => 'La commune/ville sélectionnée n\'existe pas.',
            'nom.required' => 'Le nom est obligatoire.',
            'nom.unique' => 'Ce nom existe déjà dans cette commune/ville.',
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà dans cette commune/ville.',
            'numero_arrondissement.unique' => 'Ce numéro d\'arrondissement existe déjà dans cette commune/ville.',
            'numero_arrondissement.min' => 'Le numéro d\'arrondissement doit être supérieur à 0.',
            'latitude.between' => 'La latitude doit être entre -90 et 90.',
            'longitude.between' => 'La longitude doit être entre -180 et 180.',
            'email.email' => 'L\'email doit avoir un format valide.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Préparer un champ JSON à partir d'une chaîne séparée par des virgules
     */
    private function prepareJsonField(Request $request, $fieldName)
    {
        if (!$request->filled($fieldName)) {
            return null;
        }

        $value = $request->get($fieldName);
        
        if (is_string($value)) {
            $items = array_map('trim', explode(',', $value));
            $items = array_filter($items, 'strlen'); // Supprimer les éléments vides
            return empty($items) ? null : $items;
        }

        return $value;
    }

    /**
     * Préparer les métadonnées
     */
    private function prepareMetadata(Request $request)
    {
        $metadata = [];

        // Informations de transport
        if ($request->filled('transport_public')) {
            $metadata['transport_public'] = $request->boolean('transport_public');
        }

        // Informations de sécurité
        if ($request->filled('niveau_securite')) {
            $metadata['niveau_securite'] = $request->niveau_securite;
        }

        // Autres informations
        if ($request->filled('autres_infos')) {
            $metadata['autres_infos'] = $request->autres_infos;
        }

        // Informations démographiques
        if ($request->filled('densite_population')) {
            $metadata['densite_population'] = $request->densite_population;
        }

        return empty($metadata) ? null : $metadata;
    }
}