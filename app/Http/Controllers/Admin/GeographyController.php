<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Province;

class GeographyController extends Controller
{
    /**
     * Liste des provinces
     * Route: GET /admin/geolocalisation/provinces
     */
    public function provinces(Request $request)
    {
        try {
            $provinces = Province::query()
                ->withCount(['departements', 'organisations', 'adherents'])
                ->when($request->recherche, function ($q) use ($request) {
                    $q->where('nom', 'like', "%{$request->recherche}%")
                        ->orWhere('code', 'like', "%{$request->recherche}%")
                        ->orWhere('chef_lieu', 'like', "%{$request->recherche}%");
                })
                ->when($request->statut, function ($q) use ($request) {
                    $q->where('is_active', $request->statut === 'actif');
                })
                ->orderBy($request->sort ?? 'ordre_affichage')
                ->paginate(15);

            return view('admin.geolocalisation.provinces.index', compact('provinces'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors du chargement des provinces: ' . $e->getMessage());
        }
    }

    /**
     * Afficher le formulaire de création
     * Route: GET /admin/geolocalisation/provinces/create
     */
    public function createProvince()
    {
        $province = new Province();
        return view('admin.geolocalisation.provinces.create', compact('province'));
    }

    /**
     * Enregistrer une nouvelle province
     * Route: POST /admin/geolocalisation/provinces
     */
    public function storeProvince(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:provinces',
            'chef_lieu' => 'nullable|string|max:255',
        ]);

        Province::create($request->all());

        return redirect()
            ->route('admin.geolocalisation.provinces.index')
            ->with('success', 'Province créée avec succès');
    }

    /**
     * Afficher le formulaire d'édition
     * Route: GET /admin/geolocalisation/provinces/{id}/edit
     */
    public function editProvince($id)
    {
        $province = Province::findOrFail($id);
        return view('admin.geolocalisation.provinces.edit', compact('province'));
    }

    /**
     * Mettre à jour une province
     * Route: PUT /admin/geolocalisation/provinces/{id}
     */
    public function updateProvince(Request $request, $id)
    {
        $province = Province::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:provinces,code,' . $id,
            'chef_lieu' => 'nullable|string|max:255',
        ]);

        $province->update($request->all());

        return redirect()
            ->route('admin.geolocalisation.provinces.index')
            ->with('success', 'Province mise à jour avec succès');
    }

    /**
     * Supprimer une province
     * Route: DELETE /admin/geolocalisation/provinces/{id}
     */
    public function deleteProvince($id)
    {
        $province = Province::findOrFail($id);
        $province->delete();

        return redirect()
            ->route('admin.geolocalisation.provinces.index')
            ->with('success', 'Province supprimée avec succès');
    }

    /**
     * Afficher les détails d'une province
     * Route: GET /admin/geolocalisation/provinces/{id}
     */
    public function showProvince($id)
    {
        $province = Province::with(['departements', 'organisations', 'adherents'])
            ->findOrFail($id);
        return view('admin.geolocalisation.provinces.show', compact('province'));
    }

    /**
     * Activer/Désactiver une province
     * Route: PATCH /admin/geolocalisation/provinces/{id}/toggle-status
     */
    public function toggleStatusProvince($id)
    {
        $province = Province::findOrFail($id);
        $province->update(['is_active' => !$province->is_active]);

        return redirect()
            ->back()
            ->with('success', 'Statut de la province mis à jour');
    }

    /**
     * Actions groupées sur les provinces
     * Route: POST /admin/geolocalisation/provinces/bulk-action
     */
    public function bulkActionProvince(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'provinces' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->provinces as $provinceId) {
            $province = Province::find($provinceId);
            if ($province) {
                switch ($request->action) {
                    case 'activate':
                        $province->update(['is_active' => true]);
                        $count++;
                        break;
                    case 'deactivate':
                        $province->update(['is_active' => false]);
                        $count++;
                        break;
                    case 'delete':
                        $province->delete();
                        $count++;
                        break;
                }
            }
        }

        return redirect()
            ->back()
            ->with('success', "{$count} province(s) traitée(s) avec succès");
    }

    /**
     * Exporter les provinces
     * Route: GET /admin/geolocalisation/provinces/export
     */
    public function exportProvinces(Request $request)
    {
        $provinces = Province::all();

        // Export CSV basique
        $filename = 'provinces_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($provinces) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Code', 'Chef-lieu', 'Statut']);

            foreach ($provinces as $province) {
                fputcsv($file, [
                    $province->id,
                    $province->nom,
                    $province->code,
                    $province->chef_lieu,
                    $province->is_active ? 'Actif' : 'Inactif'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Liste des arrondissements
     */
    public function arrondissements(Request $request)
    {
        try {
            $arrondissements = \App\Models\Arrondissement::query()
                ->with(['communeVille.departement.province'])
                ->when($request->departement_id, function ($q) use ($request) {
                    $q->whereHas('communeVille', function ($sq) use ($request) {
                        $sq->where('departement_id', $request->departement_id);
                    });
                })
                ->when($request->commune_ville_id, function ($q) use ($request) {
                    $q->where('commune_ville_id', $request->commune_ville_id);
                })
                ->when($request->numero_arrondissement, function ($q) use ($request) {
                    $q->where('numero_arrondissement', $request->numero_arrondissement);
                })
                ->when($request->has('is_active') && $request->is_active !== '', function ($q) use ($request) {
                    $q->where('is_active', $request->is_active);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($sq) use ($request) {
                        $sq->where('nom', 'like', "%{$request->search}%")
                            ->orWhere('code', 'like', "%{$request->search}%")
                            ->orWhere('delegue', 'like', "%{$request->search}%");
                    });
                })
                ->orderBy($request->sort ?? 'nom')
                ->paginate(15);

            $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
            $communesVilles = \App\Models\CommuneVille::with('departement')->orderBy('nom')->get();

            return view('admin.geolocalisation.arrondissements.index', compact('arrondissements', 'departements', 'communesVilles'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function createArrondissement()
    {
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        $communesVilles = \App\Models\CommuneVille::with('departement')->orderBy('nom')->get();
        return view('admin.geolocalisation.arrondissements.create', compact('departements', 'communesVilles'));
    }

    public function storeArrondissement(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:arrondissements',
            'commune_ville_id' => 'required|exists:communes_villes,id',
            'numero_arrondissement' => 'nullable|integer|min:1',
            'delegue' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'population_estimee' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        \App\Models\Arrondissement::create($request->all());
        return redirect()->route('admin.geolocalisation.arrondissements.index')
            ->with('success', 'Arrondissement créé avec succès');
    }

    public function showArrondissement($id)
    {
        $arrondissement = \App\Models\Arrondissement::with(['communeVille.departement.province'])->findOrFail($id);
        return view('admin.geolocalisation.arrondissements.show', compact('arrondissement'));
    }

    public function editArrondissement($id)
    {
        $arrondissement = \App\Models\Arrondissement::findOrFail($id);
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        $communesVilles = \App\Models\CommuneVille::with('departement')->orderBy('nom')->get();
        return view('admin.geolocalisation.arrondissements.edit', compact('arrondissement', 'departements', 'communesVilles'));
    }

    public function updateArrondissement(Request $request, $id)
    {
        $arrondissement = \App\Models\Arrondissement::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:arrondissements,code,' . $id,
            'commune_ville_id' => 'required|exists:communes_villes,id',
            'numero_arrondissement' => 'nullable|integer|min:1',
            'delegue' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'population_estimee' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);
        $arrondissement->update($request->all());
        return redirect()->route('admin.geolocalisation.arrondissements.index')
            ->with('success', 'Arrondissement mis à jour');
    }

    public function deleteArrondissement($id)
    {
        \App\Models\Arrondissement::findOrFail($id)->delete();
        return redirect()->route('admin.geolocalisation.arrondissements.index')
            ->with('success', 'Arrondissement supprimé');
    }

    public function toggleStatusArrondissement($id)
    {
        $arrondissement = \App\Models\Arrondissement::findOrFail($id);
        $arrondissement->update(['is_active' => !$arrondissement->is_active]);
        return back()->with('success', 'Statut mis à jour avec succès');
    }

    public function bulkActionArrondissement(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'arrondissements' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->arrondissements as $id) {
            $arrondissement = \App\Models\Arrondissement::find($id);
            if ($arrondissement) {
                if ($request->action == 'delete')
                    $arrondissement->delete();
                else
                    $arrondissement->update(['is_active' => $request->action == 'activate']);
                $count++;
            }
        }
        return back()->with('success', "{$count} arrondissement(s) traité(s)");
    }

    public function exportArrondissements()
    {
        $arrondissements = \App\Models\Arrondissement::with('communeVille.departement.province')->get();
        $filename = 'arrondissements_' . date('Y-m-d') . '.csv';

        $callback = function () use ($arrondissements) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Code', 'Numéro', 'Commune/Ville', 'Département', 'Province', 'Délégué', 'Population', 'Statut']);
            foreach ($arrondissements as $a) {
                fputcsv($file, [
                    $a->id,
                    $a->nom,
                    $a->code,
                    $a->numero_arrondissement ?? '',
                    $a->communeVille->nom ?? '',
                    $a->communeVille->departement->nom ?? '',
                    $a->communeVille->departement->province->nom ?? '',
                    $a->delegue ?? '',
                    $a->population_estimee ?? '',
                    $a->is_active ? 'Actif' : 'Inactif'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Liste des cantons
     */
    public function cantons(Request $request)
    {
        try {
            $cantons = \App\Models\Canton::query()
                ->with(['departement.province'])
                ->when($request->departement_id, function ($q) use ($request) {
                    $q->where('departement_id', $request->departement_id);
                })
                ->when($request->has('is_active') && $request->is_active !== '', function ($q) use ($request) {
                    $q->where('is_active', $request->is_active);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($sq) use ($request) {
                        $sq->where('nom', 'like', "%{$request->search}%")
                            ->orWhere('code', 'like', "%{$request->search}%")
                            ->orWhere('chef_lieu', 'like', "%{$request->search}%");
                    });
                })
                ->orderBy($request->sort ?? 'nom')
                ->paginate(15);

            $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();

            return view('admin.geolocalisation.cantons.index', compact('cantons', 'departements'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function createCanton()
    {
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        return view('admin.geolocalisation.cantons.create', compact('departements'));
    }

    public function storeCanton(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:cantons',
            'departement_id' => 'required|exists:departements,id',
            'chef_lieu' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        \App\Models\Canton::create($request->all());
        return redirect()->route('admin.geolocalisation.cantons.index')
            ->with('success', 'Canton créé avec succès');
    }

    public function showCanton($id)
    {
        $canton = \App\Models\Canton::with(['departement.province'])->findOrFail($id);
        return view('admin.geolocalisation.cantons.show', compact('canton'));
    }

    public function editCanton($id)
    {
        $canton = \App\Models\Canton::findOrFail($id);
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        return view('admin.geolocalisation.cantons.edit', compact('canton', 'departements'));
    }

    public function updateCanton(Request $request, $id)
    {
        $canton = \App\Models\Canton::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:cantons,code,' . $id,
            'departement_id' => 'required|exists:departements,id',
            'chef_lieu' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        $canton->update($request->all());
        return redirect()->route('admin.geolocalisation.cantons.index')
            ->with('success', 'Canton mis à jour');
    }

    public function deleteCanton($id)
    {
        \App\Models\Canton::findOrFail($id)->delete();
        return redirect()->route('admin.geolocalisation.cantons.index')
            ->with('success', 'Canton supprimé');
    }

    public function toggleStatusCanton($id)
    {
        $canton = \App\Models\Canton::findOrFail($id);
        $canton->update(['is_active' => !$canton->is_active]);
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès'
        ]);
    }

    public function bulkActionCanton(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'cantons' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->cantons as $id) {
            $canton = \App\Models\Canton::find($id);
            if ($canton) {
                if ($request->action == 'delete')
                    $canton->delete();
                else
                    $canton->update(['is_active' => $request->action == 'activate']);
                $count++;
            }
        }
        return back()->with('success', "{$count} canton(s) traité(s)");
    }

    public function exportCantons()
    {
        $cantons = \App\Models\Canton::with('departement.province')->get();
        $filename = 'cantons_' . date('Y-m-d') . '.csv';

        $callback = function () use ($cantons) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Code', 'Chef-lieu', 'Département', 'Province', 'Statut']);
            foreach ($cantons as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->nom,
                    $c->code,
                    $c->chef_lieu ?? '',
                    $c->departement->nom ?? '',
                    $c->departement->province->nom ?? '',
                    $c->is_active ? 'Actif' : 'Inactif'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Liste des regroupements
     */
    public function regroupements(Request $request)
    {
        try {
            $regroupements = \App\Models\Regroupement::query()
                ->with(['canton.departement.province'])
                ->when($request->canton_id, function ($q) use ($request) {
                    $q->where('canton_id', $request->canton_id);
                })
                ->when($request->departement_id, function ($q) use ($request) {
                    $q->whereHas('canton', function ($sq) use ($request) {
                        $sq->where('departement_id', $request->departement_id);
                    });
                })
                ->when($request->has('is_active') && $request->is_active !== '', function ($q) use ($request) {
                    $q->where('is_active', $request->is_active);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($sq) use ($request) {
                        $sq->where('nom', 'like', "%{$request->search}%")
                            ->orWhere('code', 'like', "%{$request->search}%")
                            ->orWhere('chef_lieu', 'like', "%{$request->search}%");
                    });
                })
                ->orderBy($request->sort ?? 'nom')
                ->paginate(15);

            $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
            $cantons = \App\Models\Canton::with('departement')->orderBy('nom')->get();

            return view('admin.geolocalisation.regroupements.index', compact('regroupements', 'departements', 'cantons'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function createRegroupement()
    {
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        $cantons = \App\Models\Canton::with('departement')->orderBy('nom')->get();
        return view('admin.geolocalisation.regroupements.create', compact('departements', 'cantons'));
    }

    public function storeRegroupement(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:regroupements',
            'canton_id' => 'required|exists:cantons,id',
            'chef_lieu' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        \App\Models\Regroupement::create($request->all());
        return redirect()->route('admin.geolocalisation.regroupements.index')
            ->with('success', 'Regroupement créé avec succès');
    }

    public function showRegroupement($id)
    {
        $regroupement = \App\Models\Regroupement::with(['canton.departement.province'])->findOrFail($id);
        return view('admin.geolocalisation.regroupements.show', compact('regroupement'));
    }

    public function editRegroupement($id)
    {
        $regroupement = \App\Models\Regroupement::findOrFail($id);
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        $cantons = \App\Models\Canton::with('departement')->orderBy('nom')->get();
        return view('admin.geolocalisation.regroupements.edit', compact('regroupement', 'departements', 'cantons'));
    }

    public function updateRegroupement(Request $request, $id)
    {
        $regroupement = \App\Models\Regroupement::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:regroupements,code,' . $id,
            'canton_id' => 'required|exists:cantons,id',
            'chef_lieu' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);
        $regroupement->update($request->all());
        return redirect()->route('admin.geolocalisation.regroupements.index')
            ->with('success', 'Regroupement mis à jour');
    }

    public function deleteRegroupement($id)
    {
        \App\Models\Regroupement::findOrFail($id)->delete();
        return redirect()->route('admin.geolocalisation.regroupements.index')
            ->with('success', 'Regroupement supprimé');
    }

    public function toggleStatusRegroupement($id)
    {
        $regroupement = \App\Models\Regroupement::findOrFail($id);
        $regroupement->update(['is_active' => !$regroupement->is_active]);
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès'
        ]);
    }

    public function bulkActionRegroupement(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'regroupements' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->regroupements as $id) {
            $regroupement = \App\Models\Regroupement::find($id);
            if ($regroupement) {
                if ($request->action == 'delete')
                    $regroupement->delete();
                else
                    $regroupement->update(['is_active' => $request->action == 'activate']);
                $count++;
            }
        }
        return back()->with('success', "{$count} regroupement(s) traité(s)");
    }

    public function exportRegroupements()
    {
        $regroupements = \App\Models\Regroupement::with('canton.departement.province')->get();
        $filename = 'regroupements_' . date('Y-m-d') . '.csv';

        $callback = function () use ($regroupements) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Code', 'Chef-lieu', 'Canton', 'Département', 'Province', 'Statut']);
            foreach ($regroupements as $r) {
                fputcsv($file, [
                    $r->id,
                    $r->nom,
                    $r->code,
                    $r->chef_lieu ?? '',
                    $r->canton->nom ?? '',
                    $r->canton->departement->nom ?? '',
                    $r->canton->departement->province->nom ?? '',
                    $r->is_active ? 'Actif' : 'Inactif'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Liste des localités
     */
    public function localites(Request $request)
    {
        try {
            $localites = \App\Models\Localite::query()
                ->with(['regroupement.canton.departement.province'])
                ->when($request->regroupement_id, function ($q) use ($request) {
                    $q->where('regroupement_id', $request->regroupement_id);
                })
                ->when($request->canton_id, function ($q) use ($request) {
                    $q->whereHas('regroupement', function ($sq) use ($request) {
                        $sq->where('canton_id', $request->canton_id);
                    });
                })
                ->when($request->departement_id, function ($q) use ($request) {
                    $q->whereHas('regroupement.canton', function ($sq) use ($request) {
                        $sq->where('departement_id', $request->departement_id);
                    });
                })
                ->when($request->type, function ($q) use ($request) {
                    $q->where('type', $request->type);
                })
                ->when($request->has('is_active') && $request->is_active !== '', function ($q) use ($request) {
                    $q->where('is_active', $request->is_active);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($sq) use ($request) {
                        $sq->where('nom', 'like', "%{$request->search}%")
                            ->orWhere('code', 'like', "%{$request->search}%")
                            ->orWhere('chef_lieu', 'like', "%{$request->search}%");
                    });
                })
                ->orderBy($request->sort ?? 'nom')
                ->paginate(15);

            $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
            $cantons = \App\Models\Canton::with('departement')->orderBy('nom')->get();
            $regroupements = \App\Models\Regroupement::with('canton')->orderBy('nom')->get();
            $arrondissements = \App\Models\Arrondissement::with('communeVille')->orderBy('nom')->get();
            $communesVilles = \App\Models\CommuneVille::with('departement')->orderBy('nom')->get();

            return view('admin.geolocalisation.localites.index', compact('localites', 'departements', 'cantons', 'regroupements', 'arrondissements', 'communesVilles'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }


    public function createLocalite(Request $request)
    {
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        $cantons = \App\Models\Canton::with('departement')->orderBy('nom')->get();
        $regroupements = \App\Models\Regroupement::with('canton')->orderBy('nom')->get();
        $arrondissements = \App\Models\Arrondissement::with('communeVille')->orderBy('nom')->get();
        $communesVilles = \App\Models\CommuneVille::with('departement')->orderBy('nom')->get();
        $type = $request->input('type', 'village'); // Par défaut 'village' si non spécifié
        return view('admin.geolocalisation.localites.create', compact('departements', 'cantons', 'regroupements', 'arrondissements', 'communesVilles', 'type'));
    }

    public function storeLocalite(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:localites',
            'regroupement_id' => 'required|exists:regroupements,id',
            'type' => 'required|in:village,quartier',
            'chef_lieu' => 'nullable|string|max:255',
            'population_estimee' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        \App\Models\Localite::create($request->all());
        return redirect()->route('admin.geolocalisation.localites.index')
            ->with('success', 'Localité créée avec succès');
    }

    public function showLocalite($id)
    {
        $localite = \App\Models\Localite::with(['regroupement.canton.departement.province'])->findOrFail($id);
        return view('admin.geolocalisation.localites.show', compact('localite'));
    }

    public function editLocalite($id)
    {
        $localite = \App\Models\Localite::findOrFail($id);
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        $cantons = \App\Models\Canton::with('departement')->orderBy('nom')->get();
        $regroupements = \App\Models\Regroupement::with('canton')->orderBy('nom')->get();
        return view('admin.geolocalisation.localites.edit', compact('localite', 'departements', 'cantons', 'regroupements'));
    }

    public function updateLocalite(Request $request, $id)
    {
        $localite = \App\Models\Localite::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:localites,code,' . $id,
            'regroupement_id' => 'required|exists:regroupements,id',
            'type' => 'required|in:village,quartier',
            'chef_lieu' => 'nullable|string|max:255',
            'population_estimee' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);
        $localite->update($request->all());
        return redirect()->route('admin.geolocalisation.localites.index')
            ->with('success', 'Localité mise à jour');
    }

    public function deleteLocalite($id)
    {
        \App\Models\Localite::findOrFail($id)->delete();
        return redirect()->route('admin.geolocalisation.localites.index')
            ->with('success', 'Localité supprimée');
    }

    public function toggleStatusLocalite($id)
    {
        $localite = \App\Models\Localite::findOrFail($id);
        $localite->update(['is_active' => !$localite->is_active]);
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès'
        ]);
    }

    public function bulkActionLocalite(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'localites' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->localites as $id) {
            $localite = \App\Models\Localite::find($id);
            if ($localite) {
                if ($request->action == 'delete')
                    $localite->delete();
                else
                    $localite->update(['is_active' => $request->action == 'activate']);
                $count++;
            }
        }
        return back()->with('success', "{$count} localité(s) traitée(s)");
    }

    public function exportLocalites()
    {
        $localites = \App\Models\Localite::with('regroupement.canton.departement.province')->get();
        $filename = 'localites_' . date('Y-m-d') . '.csv';

        $callback = function () use ($localites) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Code', 'Type', 'Chef-lieu', 'Regroupement', 'Canton', 'Département', 'Province', 'Population', 'Statut']);
            foreach ($localites as $l) {
                fputcsv($file, [
                    $l->id,
                    $l->nom,
                    $l->code,
                    ucfirst($l->type),
                    $l->chef_lieu ?? '',
                    $l->regroupement->nom ?? '',
                    $l->regroupement->canton->nom ?? '',
                    $l->regroupement->canton->departement->nom ?? '',
                    $l->regroupement->canton->departement->province->nom ?? '',
                    $l->population_estimee ?? '',
                    $l->is_active ? 'Actif' : 'Inactif'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    // ==========================================
    // MODULE DÉPARTEMENTS
    // ==========================================

    public function departements(Request $request)
    {
        try {
            $departements = \App\Models\Departement::query()
                ->with(['province'])
                ->when($request->recherche, function ($q) use ($request) {
                    $q->where('nom', 'like', "%{$request->recherche}%")
                        ->orWhere('code', 'like', "%{$request->recherche}%");
                })
                ->when($request->statut, function ($q) use ($request) {
                    $q->where('is_active', $request->statut === 'actif');
                })
                ->when($request->province_id, function ($q) use ($request) {
                    $q->where('province_id', $request->province_id);
                })
                ->orderBy($request->sort ?? 'ordre_affichage')
                ->paginate(15);

            $provinces = Province::orderBy('nom')->get();

            return view('admin.geolocalisation.departements.index', compact('departements', 'provinces'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function createDepartement()
    {
        $provinces = Province::orderBy('nom')->get();
        return view('admin.geolocalisation.departements.create', compact('provinces'));
    }

    public function storeDepartement(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departements',
            'province_id' => 'required|exists:provinces,id',
        ]);

        \App\Models\Departement::create($request->all());
        return redirect()->route('admin.geolocalisation.departements.index')
            ->with('success', 'Département créé avec succès');
    }

    public function showDepartement($id)
    {
        $departement = \App\Models\Departement::with(['province'])->findOrFail($id);
        return view('admin.geolocalisation.departements.show', compact('departement'));
    }

    public function editDepartement($id)
    {
        $departement = \App\Models\Departement::findOrFail($id);
        $provinces = Province::orderBy('nom')->get();
        return view('admin.geolocalisation.departements.edit', compact('departement', 'provinces'));
    }

    public function updateDepartement(Request $request, $id)
    {
        $departement = \App\Models\Departement::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:departements,code,' . $id,
            'province_id' => 'required|exists:provinces,id',
        ]);
        $departement->update($request->all());
        return redirect()->route('admin.geolocalisation.departements.index')
            ->with('success', 'Département mis à jour');
    }

    public function deleteDepartement($id)
    {
        \App\Models\Departement::findOrFail($id)->delete();
        return redirect()->route('admin.geolocalisation.departements.index')
            ->with('success', 'Département supprimé');
    }

    public function toggleStatusDepartement($id)
    {
        $dept = \App\Models\Departement::findOrFail($id);
        $dept->update(['is_active' => !$dept->is_active]);
        return back()->with('success', 'Statut mis à jour');
    }

    public function bulkActionDepartement(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'departements' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->departements as $id) {
            $dept = \App\Models\Departement::find($id);
            if ($dept) {
                if ($request->action == 'delete')
                    $dept->delete();
                else
                    $dept->update(['is_active' => $request->action == 'activate']);
                $count++;
            }
        }
        return back()->with('success', "{$count} département(s) traité(s)");
    }

    public function exportDepartements()
    {
        $departements = \App\Models\Departement::with('province')->get();
        $filename = 'departements_' . date('Y-m-d') . '.csv';

        $callback = function () use ($departements) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Code', 'Province', 'Statut']);
            foreach ($departements as $d) {
                fputcsv($file, [$d->id, $d->nom, $d->code, $d->province->nom ?? '', $d->is_active ? 'Actif' : 'Inactif']);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    // ==========================================
    // MODULE COMMUNES/VILLES
    // ==========================================

    public function communesVilles(Request $request)
    {
        try {
            $communesVilles = \App\Models\CommuneVille::query()
                ->with(['departement.province'])
                ->when($request->search, function ($q) use ($request) {
                    $q->where('nom', 'like', "%{$request->search}%")
                        ->orWhere('code', 'like', "%{$request->search}%")
                        ->orWhere('maire', 'like', "%{$request->search}%");
                })
                ->when($request->departement_id, function ($q) use ($request) {
                    $q->where('departement_id', $request->departement_id);
                })
                ->when($request->type, function ($q) use ($request) {
                    $q->where('type', $request->type);
                })
                ->when($request->has('is_active') && $request->is_active !== '', function ($q) use ($request) {
                    $q->where('is_active', $request->is_active);
                })
                ->orderBy($request->sort ?? 'nom')
                ->paginate(15);

            $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();

            return view('admin.geolocalisation.communes_villes.index', compact('communesVilles', 'departements'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    public function createCommuneVille()
    {
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        return view('admin.geolocalisation.communes_villes.create', compact('departements'));
    }

    public function storeCommuneVille(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:communes_villes',
            'departement_id' => 'required|exists:departements,id',
            'type' => 'required|in:commune,ville',
            'maire' => 'nullable|string|max:255',
            'population_estimee' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);

        \App\Models\CommuneVille::create($request->all());
        return redirect()->route('admin.geolocalisation.communes-villes.index')
            ->with('success', 'Commune/Ville créée avec succès');
    }

    public function showCommuneVille($id)
    {
        $communeVille = \App\Models\CommuneVille::with(['departement.province'])->findOrFail($id);
        return view('admin.geolocalisation.communes_villes.show', compact('communeVille'));
    }

    public function editCommuneVille($id)
    {
        $communeVille = \App\Models\CommuneVille::findOrFail($id);
        $departements = \App\Models\Departement::with('province')->orderBy('nom')->get();
        return view('admin.geolocalisation.communes_villes.edit', compact('communeVille', 'departements'));
    }

    public function updateCommuneVille(Request $request, $id)
    {
        $communeVille = \App\Models\CommuneVille::findOrFail($id);
        $request->validate([
            'nom' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:communes_villes,code,' . $id,
            'departement_id' => 'required|exists:departements,id',
            'type' => 'required|in:commune,ville',
            'maire' => 'nullable|string|max:255',
            'population_estimee' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ]);
        $communeVille->update($request->all());
        return redirect()->route('admin.geolocalisation.communes-villes.index')
            ->with('success', 'Commune/Ville mise à jour');
    }

    public function deleteCommuneVille($id)
    {
        \App\Models\CommuneVille::findOrFail($id)->delete();
        return redirect()->route('admin.geolocalisation.communes-villes.index')
            ->with('success', 'Commune/Ville supprimée');
    }

    public function toggleStatusCommuneVille($id)
    {
        $communeVille = \App\Models\CommuneVille::findOrFail($id);
        $communeVille->update(['is_active' => !$communeVille->is_active]);
        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès'
        ]);
    }

    public function bulkActionCommuneVille(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'communes_villes' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->communes_villes as $id) {
            $communeVille = \App\Models\CommuneVille::find($id);
            if ($communeVille) {
                if ($request->action == 'delete')
                    $communeVille->delete();
                else
                    $communeVille->update(['is_active' => $request->action == 'activate']);
                $count++;
            }
        }
        return back()->with('success', "{$count} commune(s)/ville(s) traitée(s)");
    }

    public function exportCommunesVilles()
    {
        $communesVilles = \App\Models\CommuneVille::with('departement.province')->get();
        $filename = 'communes_villes_' . date('Y-m-d') . '.csv';

        $callback = function () use ($communesVilles) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Nom', 'Code', 'Type', 'Département', 'Province', 'Maire', 'Population', 'Statut']);
            foreach ($communesVilles as $cv) {
                fputcsv($file, [
                    $cv->id,
                    $cv->nom,
                    $cv->code,
                    $cv->type,
                    $cv->departement->nom ?? '',
                    $cv->departement->province->nom ?? '',
                    $cv->maire ?? '',
                    $cv->population_estimee ?? '',
                    $cv->is_active ? 'Actif' : 'Inactif'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }
}
