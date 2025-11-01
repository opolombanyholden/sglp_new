<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProvinceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Province::query();

        // Filtrage par statut
        if ($request->has('statut') && $request->statut !== '') {
            $query->where('is_active', $request->statut === 'actif');
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

        $provinces = $query->withCount(['departements', 'organisations', 'adherents'])
                          ->paginate(15)
                          ->withQueryString();

        return view('admin.geolocalisation.provinces.index', compact('provinces'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $province = new Province();
        
        return view('admin.geolocalisation.provinces.create', compact('province'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255|unique:provinces,nom',
            'code' => 'nullable|string|max:10|unique:provinces,code',
            'chef_lieu' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'superficie_km2' => 'nullable|numeric|min:0|max:999999.99',
            'population_estimee' => 'nullable|integer|min:0|max:99999999',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
            'ordre_affichage' => 'integer|min:0|max:999',
        ], [
            'nom.required' => 'Le nom de la province est obligatoire.',
            'nom.unique' => 'Cette province existe déjà.',
            'code.unique' => 'Ce code est déjà utilisé.',
            'superficie_km2.numeric' => 'La superficie doit être un nombre.',
            'population_estimee.integer' => 'La population doit être un nombre entier.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ]);

        try {
            DB::beginTransaction();

            $province = Province::create($validated);

            DB::commit();

            return redirect()
                ->route('admin.geolocalisation.provinces.index', $province)
                ->with('success', "La province '{$province->nom}' a été créée avec succès.");

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
    public function show(Province $province)
    {
        $province->load(['departements' => function($query) {
            $query->actif()->parOrdre()->withCount(['communesVilles', 'cantons']);
        }]);

        $statistiques = $province->getStatistiques();

        return view('admin.geolocalisation.provinces.show', compact('province', 'statistiques'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Province $province)
    {
        return view('admin.geolocalisation.provinces.edit', compact('province'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Province $province)
    {
        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('provinces', 'nom')->ignore($province->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:10',
                Rule::unique('provinces', 'code')->ignore($province->id),
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
            'nom.required' => 'Le nom de la province est obligatoire.',
            'nom.unique' => 'Cette province existe déjà.',
            'code.unique' => 'Ce code est déjà utilisé.',
            'superficie_km2.numeric' => 'La superficie doit être un nombre.',
            'population_estimee.integer' => 'La population doit être un nombre entier.',
            'latitude.between' => 'La latitude doit être comprise entre -90 et 90.',
            'longitude.between' => 'La longitude doit être comprise entre -180 et 180.',
        ]);

        try {
            DB::beginTransaction();

            $province->update($validated);

            DB::commit();

            return redirect()
                ->route('admin.geolocalisation.provinces.index', $province)
                ->with('success', "La province '{$province->nom}' a été mise à jour avec succès.");

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
    public function destroy(Province $province)
    {
        try {
            // Vérifier si la province peut être supprimée
            $departementsCount = $province->departements()->count();
            $organisationsCount = $province->organisations()->count();
            $adherentsCount = $province->adherents()->count();

            if ($departementsCount > 0 || $organisationsCount > 0 || $adherentsCount > 0) {
                return redirect()
                    ->route('admin.geolocalisation.provinces.index')
                    ->with('error', "Impossible de supprimer la province '{$province->nom}' : elle contient des données liées ({$departementsCount} départements, {$organisationsCount} organisations, {$adherentsCount} adhérents).");
            }

            DB::beginTransaction();

            $nom = $province->nom;
            $province->delete();

            DB::commit();

            return redirect()
                ->route('admin.geolocalisation.provinces.index')
                ->with('success', "La province '{$nom}' a été supprimée avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->route('admin.geolocalisation.provinces.index')
                ->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Toggle the active status of the province.
     */
    public function toggleStatus(Province $province)
    {
        try {
            DB::beginTransaction();

            $province->update(['is_active' => !$province->is_active]);
            
            DB::commit();

            $statut = $province->is_active ? 'activée' : 'désactivée';
            
            return redirect()
                ->back()
                ->with('success', "La province '{$province->nom}' a été {$statut}.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Erreur lors du changement de statut : ' . $e->getMessage());
        }
    }

    /**
     * Get provinces for API/AJAX requests.
     */
    public function api(Request $request)
    {
        $query = Province::actif()->parOrdre();

        if ($request->filled('q')) {
            $query->recherche($request->q);
        }

        $provinces = $query->select('id', 'nom', 'code', 'chef_lieu')
                          ->limit(50)
                          ->get();

        return response()->json($provinces);
    }

    /**
     * Export provinces data.
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $provinces = Province::with(['departements'])
                            ->parOrdre()
                            ->get();

        $data = $provinces->map(function ($province) {
            return [
                'ID' => $province->id,
                'Nom' => $province->nom,
                'Code' => $province->code,
                'Chef-lieu' => $province->chef_lieu,
                'Population' => $province->population_estimee,
                'Superficie (km²)' => $province->superficie_km2,
                'Densité (hab/km²)' => $province->densite,
                'Départements' => $province->departements->count(),
                'Statut' => $province->is_active ? 'Actif' : 'Inactif',
                'Créé le' => $province->created_at->format('d/m/Y H:i'),
            ];
        });

        if ($format === 'json') {
            return response()->json($data);
        }

        // CSV export
        $filename = 'provinces_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
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
     * Bulk actions on multiple provinces.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'provinces' => 'required|array|min:1',
            'provinces.*' => 'exists:provinces,id',
        ]);

        try {
            DB::beginTransaction();

            $provinces = Province::whereIn('id', $request->provinces)->get();
            $count = $provinces->count();

            switch ($request->action) {
                case 'activate':
                    $provinces->each->update(['is_active' => true]);
                    $message = "{$count} province(s) activée(s) avec succès.";
                    break;

                case 'deactivate':
                    $provinces->each->update(['is_active' => false]);
                    $message = "{$count} province(s) désactivée(s) avec succès.";
                    break;

                case 'delete':
                    // Vérifier les contraintes avant suppression
                    foreach ($provinces as $province) {
                        if ($province->departements()->count() > 0 || 
                            $province->organisations()->count() > 0) {
                            throw new \Exception("Impossible de supprimer '{$province->nom}' : données liées présentes.");
                        }
                    }
                    $provinces->each->delete();
                    $message = "{$count} province(s) supprimée(s) avec succès.";
                    break;
            }

            DB::commit();

            return redirect()
                ->route('admin.geolocalisation.provinces.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->route('admin.geolocalisation.provinces.index')
                ->with('error', 'Erreur lors de l\'action groupée : ' . $e->getMessage());
        }
    }
}