<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommuneVille;
use App\Models\Departement;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

class CommuneVilleController extends Controller
{
    /**
     * Afficher la liste des communes/villes
     */
    public function index(Request $request)
    {
        $query = CommuneVille::with(['departement.province']);

        // Filtrage par département
        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }

        // Filtrage par type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
                  ->orWhere('maire', 'LIKE', "%{$search}%");
            });
        }

        $communesVilles = $query->ordered()->paginate(15);

        // Données pour les filtres - CORRECTION ICI
        $departements = Departement::with('province')
                                   ->where('is_active', true)
                                   ->orderBy('ordre_affichage')
                                   ->orderBy('nom')
                                   ->get();

        return view('admin.geolocalisation.communes_villes.index', compact('communesVilles', 'departements'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(Request $request)
    {
        $communeVille = new CommuneVille();
        
        // Département pré-sélectionné si fourni dans l'URL
        if ($request->filled('departement_id')) {
            $communeVille->departement_id = $request->departement_id;
        }

        // CORRECTION ICI
        $departements = Departement::with('province')
                                   ->where('is_active', true)
                                   ->orderBy('ordre_affichage')
                                   ->orderBy('nom')
                                   ->get();
        
        $provinces = Province::where('is_active', true)
                            ->orderBy('ordre_affichage')
                            ->orderBy('nom')
                            ->get();
        
        return view('admin.geolocalisation.communes_villes.create', compact('communeVille', 'departements', 'provinces'));
    }

    /**
     * Enregistrer une nouvelle commune/ville
     */
    public function store(Request $request)
    {
        $validator = $this->validateCommuneVille($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $communeVille = CommuneVille::create([
                'departement_id' => $request->departement_id,
                'nom' => $request->nom,
                'code' => $request->code,
                'type' => $request->type,
                'statut' => $request->statut,
                'description' => $request->description,
                'superficie_km2' => $request->superficie_km2,
                'population_estimee' => $request->population_estimee,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'maire' => $request->maire,
                'date_creation' => $request->date_creation,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'site_web' => $request->site_web,
                'metadata' => $this->prepareMetadata($request),
                'is_active' => $request->boolean('is_active', true),
                'ordre_affichage' => $request->ordre_affichage ?? 0
            ]);

            DB::commit();

            return redirect()->route('admin.geolocalisation.communes.show', $communeVille)
                ->with('success', "La commune/ville '{$communeVille->nom}' a été créée avec succès.");

        } catch (Exception $e) {
            DB::rollback();
            
            return back()
                ->withError('Erreur lors de la création : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher une commune/ville spécifique
     */
    public function show(CommuneVille $communeVille)
    {
        $communeVille->load([
            'departement.province',
            'arrondissements' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('ordre_affichage')
                      ->orderBy('nom');
            }
        ]);

        // Statistiques
        $stats = [
            'arrondissements_count' => $communeVille->arrondissements()->count(),
            'organisations_count' => $communeVille->countOrganisations(),
            'adherents_count' => $communeVille->countAdherents(),
        ];

        return view('admin.geolocalisation.communes_villes.show', compact('communeVille', 'stats'));
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(CommuneVille $communeVille)
    {
        $communeVille->load('departement.province');
        
        // CORRECTION ICI
        $departements = Departement::with('province')
                                   ->where('is_active', true)
                                   ->orderBy('ordre_affichage')
                                   ->orderBy('nom')
                                   ->get();
        
        $provinces = Province::where('is_active', true)
                            ->orderBy('ordre_affichage')
                            ->orderBy('nom')
                            ->get();

        return view('admin.geolocalisation.communes_villes.edit', compact('communeVille', 'departements', 'provinces'));
    }

    /**
     * Mettre à jour une commune/ville
     */
    public function update(Request $request, CommuneVille $communeVille)
    {
        $validator = $this->validateCommuneVille($request, $communeVille->id);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $communeVille->update([
                'departement_id' => $request->departement_id,
                'nom' => $request->nom,
                'code' => $request->code,
                'type' => $request->type,
                'statut' => $request->statut,
                'description' => $request->description,
                'superficie_km2' => $request->superficie_km2,
                'population_estimee' => $request->population_estimee,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'maire' => $request->maire,
                'date_creation' => $request->date_creation,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'site_web' => $request->site_web,
                'metadata' => $this->prepareMetadata($request),
                'is_active' => $request->boolean('is_active', true),
                'ordre_affichage' => $request->ordre_affichage ?? 0
            ]);

            DB::commit();

            return redirect()->route('admin.geolocalisation.communes.show', $communeVille)
                ->with('success', "La commune/ville '{$communeVille->nom}' a été modifiée avec succès.");

        } catch (Exception $e) {
            DB::rollback();
            
            return back()
                ->withError('Erreur lors de la modification : ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une commune/ville
     */
    public function destroy(CommuneVille $communeVille)
    {
        try {
            // Vérifier s'il y a des arrondissements liés
            $arrondissementsCount = $communeVille->arrondissements()->count();
            if ($arrondissementsCount > 0) {
                return back()->withError("Impossible de supprimer : cette commune/ville contient {$arrondissementsCount} arrondissement(s).");
            }

            // Vérifier s'il y a des organisations liées
            $organisationsCount = $communeVille->countOrganisations();
            if ($organisationsCount > 0) {
                return back()->withError("Impossible de supprimer : cette commune/ville est liée à {$organisationsCount} organisation(s).");
            }

            // Vérifier s'il y a des adhérents liés
            $adherentsCount = $communeVille->countAdherents();
            if ($adherentsCount > 0) {
                return back()->withError("Impossible de supprimer : cette commune/ville est liée à {$adherentsCount} adhérent(s).");
            }

            DB::beginTransaction();
            
            $nom = $communeVille->nom;
            $communeVille->delete();
            
            DB::commit();

            return redirect()->route('admin.geolocalisation.communes.index')
                ->with('success', "La commune/ville '{$nom}' a été supprimée avec succès.");

        } catch (Exception $e) {
            DB::rollBack();
            
            return back()->withError('Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * API : Récupérer les communes/villes par département
     */
    public function byDepartement(Departement $departement)
    {
        $communesVilles = $departement->communesVilles()
                                     ->where('is_active', true)
                                     ->orderBy('ordre_affichage')
                                     ->orderBy('nom')
                                     ->select('id', 'nom', 'type', 'code')
                                     ->get();

        return response()->json($communesVilles);
    }

    /**
     * API : Recherche de communes/villes
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $results = CommuneVille::with('departement.province')
            ->where('nom', 'LIKE', "%{$query}%")
            ->where('is_active', true)
            ->orderBy('nom')
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
    public function toggleStatus(CommuneVille $communeVille)
    {
        try {
            DB::beginTransaction();
            
            $communeVille->update([
                'is_active' => !$communeVille->is_active
            ]);
            
            DB::commit();

            $status = $communeVille->is_active ? 'activée' : 'désactivée';
            
            return response()->json([
                'success' => true,
                'message' => "Commune/Ville {$status} avec succès.",
                'new_status' => $communeVille->is_active
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut.'
            ], 500);
        }
    }

    /**
     * Export des communes/villes
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $query = CommuneVille::with('departement.province')->ordered();
        
        // Appliquer les filtres
        if ($request->filled('departement_id')) {
            $query->where('departement_id', $request->departement_id);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        $communesVilles = $query->get();

        $data = $communesVilles->map(function ($communeVille) {
            return [
                'ID' => $communeVille->id,
                'Nom' => $communeVille->nom,
                'Code' => $communeVille->code,
                'Type' => ucfirst($communeVille->type),
                'Département' => $communeVille->departement->nom,
                'Province' => $communeVille->departement->province->nom,
                'Maire' => $communeVille->maire,
                'Population' => $communeVille->population_estimee,
                'Superficie (km²)' => $communeVille->superficie_km2,
                'Statut' => $communeVille->is_active ? 'Actif' : 'Inactif',
                'Créé le' => $communeVille->created_at->format('d/m/Y H:i'),
            ];
        });

        if ($format === 'json') {
            return response()->json($data);
        }

        // CSV export
        $filename = 'communes_villes_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // BOM pour UTF-8
            fputs($file, "\xEF\xBB\xBF");
            
            // Headers
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()), ';');
                
                // Data rows
                foreach ($data as $row) {
                    fputcsv($file, array_values($row), ';');
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Actions groupées
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'communes' => 'required|array|min:1',
            'communes.*' => 'exists:communes_villes,id',
        ]);

        try {
            DB::beginTransaction();

            $communesVilles = CommuneVille::whereIn('id', $request->communes)->get();
            $count = $communesVilles->count();

            switch ($request->action) {
                case 'activate':
                    $communesVilles->each->update(['is_active' => true]);
                    $message = "{$count} commune(s)/ville(s) activée(s) avec succès.";
                    break;

                case 'deactivate':
                    $communesVilles->each->update(['is_active' => false]);
                    $message = "{$count} commune(s)/ville(s) désactivée(s) avec succès.";
                    break;

                case 'delete':
                    // Vérifier les contraintes avant suppression
                    foreach ($communesVilles as $communeVille) {
                        if ($communeVille->hasArrondissements() || 
                            $communeVille->countOrganisations() > 0 ||
                            $communeVille->countAdherents() > 0) {
                            throw new \Exception("Impossible de supprimer '{$communeVille->nom}' : données liées présentes.");
                        }
                    }
                    $communesVilles->each->delete();
                    $message = "{$count} commune(s)/ville(s) supprimée(s) avec succès.";
                    break;
            }

            DB::commit();

            return redirect()
                ->route('admin.geolocalisation.communes.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->route('admin.geolocalisation.communes.index')
                ->with('error', 'Erreur lors de l\'action groupée : ' . $e->getMessage());
        }
    }

    /**
     * Validation des données de commune/ville
     */
    private function validateCommuneVille(Request $request, $excludeId = null)
    {
        $rules = [
            'departement_id' => 'required|exists:departements,id',
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:20',
            'type' => 'required|in:commune,ville',
            'statut' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'superficie_km2' => 'nullable|numeric|min:0',
            'population_estimee' => 'nullable|integer|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'maire' => 'nullable|string|max:255',
            'date_creation' => 'nullable|date',
            'telephone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'site_web' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'ordre_affichage' => 'nullable|integer|min:0'
        ];

        // Règles d'unicité
        $uniqueRules = [
            'nom' => "unique:communes_villes,nom,{$excludeId},id,departement_id,{$request->departement_id}",
            'code' => "unique:communes_villes,code,{$excludeId},id,departement_id,{$request->departement_id}"
        ];

        $rules = array_merge($rules, $uniqueRules);

        $messages = [
            'departement_id.required' => 'Le département est obligatoire.',
            'departement_id.exists' => 'Le département sélectionné n\'existe pas.',
            'nom.required' => 'Le nom est obligatoire.',
            'nom.unique' => 'Ce nom existe déjà dans ce département.',
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà dans ce département.',
            'type.required' => 'Le type est obligatoire.',
            'type.in' => 'Le type doit être "commune" ou "ville".',
            'latitude.between' => 'La latitude doit être entre -90 et 90.',
            'longitude.between' => 'La longitude doit être entre -180 et 180.',
            'email.email' => 'L\'email doit avoir un format valide.',
            'site_web.url' => 'Le site web doit avoir un format d\'URL valide.'
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Préparer les métadonnées
     */
    private function prepareMetadata(Request $request)
    {
        $metadata = [];

        // Services publics
        if ($request->filled('services_publics')) {
            $services = array_map('trim', explode(',', $request->services_publics));
            $metadata['services_publics'] = array_filter($services);
        }

        // Équipements
        if ($request->filled('equipements')) {
            $equipements = array_map('trim', explode(',', $request->equipements));
            $metadata['equipements'] = array_filter($equipements);
        }

        // Autres informations
        if ($request->filled('autres_infos')) {
            $metadata['autres_infos'] = $request->autres_infos;
        }

        return empty($metadata) ? null : $metadata;
    }
}