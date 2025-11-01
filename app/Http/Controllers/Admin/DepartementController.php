<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departement;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DepartementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Departement::query()->avecProvince();

        // Filtrage par province
        if ($request->filled('province_id') && $request->province_id !== '') {
            $query->parProvince($request->province_id);
        }

        // Filtrage par statut
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('is_active', $request->statut === 'actif');
        }

        // Filtrage par type de subdivision
        if ($request->filled('type_subdivision')) {
            $query->parTypeSubdivision($request->type_subdivision);
        }

        // Recherche
        if ($request->filled('recherche')) {
            $query->recherche($request->recherche);
        }

        // Tri
        $sortField = $request->get('sort', 'ordre_affichage');
        $sortDirection = $request->get('direction', 'asc');
        
        if (in_array($sortField, ['nom', 'code', 'chef_lieu', 'population_estimee', 'superficie_km2', 'ordre_affichage', 'created_at'])) {
            if ($sortField === 'ordre_affichage') {
                $query->orderBy('ordre_affichage')->orderBy('nom');
            } else {
                $query->orderBy($sortField, $sortDirection);
            }
        } else {
            $query->parOrdre();
        }

        $departements = $query->withCount(['communesVilles', 'cantons', 'organisations', 'adherents'])
                            ->paginate(20)
                            ->withQueryString();

        $provinces = Province::actif()->parOrdre()->get();

        return view('admin.geolocalisation.departements.index', compact('departements', 'provinces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $departement = new Departement();
        
        // Province pré-sélectionnée si fournie dans l'URL
        if ($request->filled('province_id')) {
            $departement->province_id = $request->province_id;
        }

        $provinces = Province::actif()->parOrdre()->get();
        
        return view('admin.geolocalisation.departements.create', compact('departement', 'provinces'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departements', 'nom')->where('province_id', $request->province_id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('departements', 'code')->where('province_id', $request->province_id),
            ],
            'chef_lieu' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'superficie_km2' => 'nullable|numeric|min:0|max:999999.99',
            'population_estimee' => 'nullable|integer|min:0|max:99999999',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'ordre_affichage' => 'integer|min:0|max:999',
        ], [
            'province_id.required' => 'La province est obligatoire.',
            'province_id.exists' => 'La province sélectionnée n\'existe pas.',
            'nom.required' => 'Le nom du département est obligatoire.',
            'nom.unique' => 'Ce département existe déjà dans cette province.',
            'code.unique' => 'Ce code est déjà utilisé dans cette province.',
            'superficie_km2.numeric' => 'La superficie doit être un nombre.',
            'population_estimee.integer' => 'La population doit être un nombre entier.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ]);

        try {
            DB::beginTransaction();

            $departement = Departement::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.geolocalisation.departements.show', $departement)
                ->with('success', "Le département '{$departement->nom}' a été créé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Departement $departement)
    {
        $departement->load([
            'province',
            'communesVilles' => function($query) {
                $query->where('is_active', true)->orderBy('ordre_affichage')->orderBy('nom')->withCount(['arrondissements']);
            },
            'cantons' => function($query) {
                $query->where('is_active', true)->orderBy('ordre_affichage')->orderBy('nom')->withCount(['regroupements']);
            }
        ]);

        $statistiques = $departement->getStatistiques();

        return view('admin.geolocalisation.departements.show', compact('departement', 'statistiques'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Departement $departement)
    {
        $provinces = Province::actif()->parOrdre()->get();
        
        return view('admin.geolocalisation.departements.edit', compact('departement', 'provinces'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Departement $departement)
    {
        $validated = $request->validate([
            'province_id' => 'required|exists:provinces,id',
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departements', 'nom')
                    ->where('province_id', $request->province_id)
                    ->ignore($departement->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:15',
                Rule::unique('departements', 'code')
                    ->where('province_id', $request->province_id)
                    ->ignore($departement->id),
            ],
            'chef_lieu' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'superficie_km2' => 'nullable|numeric|min:0|max:999999.99',
            'population_estimee' => 'nullable|integer|min:0|max:99999999',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'ordre_affichage' => 'integer|min:0|max:999',
        ], [
            'province_id.required' => 'La province est obligatoire.',
            'province_id.exists' => 'La province sélectionnée n\'existe pas.',
            'nom.required' => 'Le nom du département est obligatoire.',
            'nom.unique' => 'Ce département existe déjà dans cette province.',
            'code.unique' => 'Ce code est déjà utilisé dans cette province.',
            'superficie_km2.numeric' => 'La superficie doit être un nombre.',
            'population_estimee.integer' => 'La population doit être un nombre entier.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ]);

        try {
            DB::beginTransaction();

            $departement->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.geolocalisation.departements.show', $departement)
                ->with('success', "Le département '{$departement->nom}' a été mis à jour avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Departement $departement)
    {
        try {
            // Vérifier si le département peut être supprimé
            $communesCount = $departement->communesVilles()->count();
            $cantonsCount = $departement->cantons()->count();
            $organisationsCount = $departement->organisations()->count();
            $adherentsCount = $departement->adherents()->count();
            $etablissementsCount = $departement->etablissements()->count();

            if ($communesCount > 0 || $cantonsCount > 0 || $organisationsCount > 0 || $adherentsCount > 0 || $etablissementsCount > 0) {
                return redirect()
                    ->route('admin.geolocalisation.departements.index')
                    ->with('error', "Impossible de supprimer le département '{$departement->nom}' : il contient des données liées ({$communesCount} communes/villes, {$cantonsCount} cantons, {$organisationsCount} organisations, {$adherentsCount} adhérents, {$etablissementsCount} établissements).");
            }

            DB::beginTransaction();

            $nom = $departement->nom;
            $departement->delete();

            DB::commit();

            return redirect()
                ->route('admin.geolocalisation.departements.index')
                ->with('success', "Le département '{$nom}' a été supprimé avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->route('admin.geolocalisation.departements.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the departement.
     */
    public function toggleStatus(Departement $departement)
    {
        try {
            DB::beginTransaction();

            $departement->update(['is_active' => !$departement->is_active]);
            
            DB::commit();

            $statut = $departement->is_active ? 'activé' : 'désactivé';
            
            return redirect()
                ->back()
                ->with('success', "Le département '{$departement->nom}' a été {$statut}.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors du changement de statut : ' . $e->getMessage());
        }
    }

    /**
     * Get departements for API/AJAX requests.
     */
    public function api(Request $request)
    {
        $query = Departement::actif()->parOrdre();

        if ($request->filled('province_id')) {
            $query->parProvince($request->province_id);
        }

        if ($request->filled('q')) {
            $query->recherche($request->q);
        }

        $departements = $query->select('id', 'nom', 'code', 'chef_lieu', 'province_id')
                            ->with('province:id,nom')
                            ->limit(50)
                            ->get()
                            ->map(function($departement) {
                                return $departement->toApiArray();
                            });

        return response()->json($departements);
    }

    /**
     * Get departements by province for API.
     */
    public function apiByProvince(Province $province)
    {
        $departements = $province->departementsActifs()
                                ->select('id', 'nom', 'code', 'chef_lieu')
                                ->get()
                                ->map(function($departement) {
                                    return [
                                        'id' => $departement->id,
                                        'nom' => $departement->nom,
                                        'code' => $departement->code,
                                        'chef_lieu' => $departement->chef_lieu,
                                    ];
                                });

        return response()->json($departements);
    }

    /**
     * Export departements data.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $query = Departement::avecProvince()->parOrdre();
        
        // Appliquer les mêmes filtres que l'index
        if ($request->filled('province_id')) {
            $query->parProvince($request->province_id);
        }
        
        $departements = $query->get();

        $data = $departements->map(function ($departement) {
            return [
                'ID' => $departement->id,
                'Province' => $departement->province->nom,
                'Département' => $departement->nom,
                'Code' => $departement->code,
                'Chef-lieu' => $departement->chef_lieu,
                'Population' => $departement->population_estimee,
                'Superficie (km²)' => $departement->superficie_km2,
                'Densité (hab/km²)' => $departement->densite,
                'Communes/Villes' => $departement->getNombreCommunesVilles(),
                'Cantons' => $departement->getNombreCantons(),
                'Type subdivision' => $departement->type_subdivision,
                'Statut' => $departement->is_active ? 'Actif' : 'Inactif',
                'Créé le' => $departement->created_at->format('d/m/Y H:i'),
            ];
        });

        if ($format === 'json') {
            return response()->json($data);
        }

        // CSV export
        $filename = 'departements_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
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
     * Bulk actions on multiple departements.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'departements' => 'required|array|min:1',
            'departements.*' => 'exists:departements,id',
        ]);

        try {
            DB::beginTransaction();

            $departements = Departement::whereIn('id', $request->departements)->get();
            $count = $departements->count();

            switch ($request->action) {
                case 'activate':
                    $departements->each->update(['is_active' => true]);
                    $message = "{$count} département(s) activé(s) avec succès.";
                    break;

                case 'deactivate':
                    $departements->each->update(['is_active' => false]);
                    $message = "{$count} département(s) désactivé(s) avec succès.";
                    break;

                case 'delete':
                    // Vérifier les contraintes avant suppression (CORRECTION: ajout de adherents et etablissements)
                    foreach ($departements as $departement) {
                        if ($departement->communesVilles()->count() > 0 || 
                            $departement->cantons()->count() > 0 || 
                            $departement->organisations()->count() > 0 ||
                            $departement->adherents()->count() > 0 ||
                            $departement->etablissements()->count() > 0) {
                            throw new \Exception("Impossible de supprimer '{$departement->nom}' : données liées présentes.");
                        }
                    }
                    $departements->each->delete();
                    $message = "{$count} département(s) supprimé(s) avec succès.";
                    break;
            }

            DB::commit();

            return redirect()
                ->route('admin.geolocalisation.departements.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->route('admin.geolocalisation.departements.index')
                ->with('error', 'Erreur lors de l\'action groupée : ' . $e->getMessage());
        }
    }

    /**
     * Get communes/villes of a departement.
     */
    public function communesVilles(Departement $departement)
    {
        $communesVilles = $departement->communesVillesActives()
                                   ->withCount(['arrondissements'])
                                   ->get();

        return view('admin.geolocalisation.departements.subdivisions.communes', 
                   compact('departement', 'communesVilles'));
    }

    /**
     * Get cantons of a departement.
     */
    public function cantons(Departement $departement)
    {
        $cantons = $departement->cantonsActifs()
                             ->withCount(['regroupements'])
                             ->get();

        return view('admin.geolocalisation.departements.subdivisions.cantons', 
                   compact('departement', 'cantons'));
    }
}