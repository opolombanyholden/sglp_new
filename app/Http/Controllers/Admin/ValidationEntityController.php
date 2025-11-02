<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ValidationEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ValidationEntityController extends Controller
{
    /**
     * Afficher la liste des entités de validation
     */
    public function index(Request $request)
    {
        try {
            // Si c'est une requête AJAX pour DataTables
            if ($request->ajax()) {
                $query = ValidationEntity::query();
                
                // Filtres
                if ($request->filled('type')) {
                    $query->where('type', $request->type);
                }
                
                if ($request->filled('is_active')) {
                    $query->where('is_active', $request->is_active);
                }
                
                // Recherche globale
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function($q) use ($search) {
                        $q->where('nom', 'like', "%{$search}%")
                          ->orWhere('code', 'like', "%{$search}%")
                          ->orWhere('description', 'like', "%{$search}%");
                    });
                }
                
                $entities = $query->orderBy('created_at', 'desc')->get();
                
                return response()->json([
                    'success' => true,
                    'data' => $entities
                ]);
            }
            
            // Vue normale
            $entities = ValidationEntity::orderBy('created_at', 'desc')->paginate(15);
            
            return view('admin.validation-entities.index', compact('entities'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage des entités: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du chargement des données'
                ], 500);
            }
            
            return back()->with('error', 'Erreur lors du chargement des entités de validation.');
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        return view('admin.validation-entities.create');
    }

    /**
     * Enregistrer une nouvelle entité
     */
    public function store(Request $request)
    {
        try {
            // Validation
            $validated = $request->validate([
                'code' => 'required|string|max:255|unique:validation_entities,code',
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:direction,service,departement,commission,externe',
                'email_notification' => 'nullable|email|max:255',
                'capacite_traitement' => 'required|integer|min:1|max:1000',
                'horaires_travail' => 'nullable|json',
                'is_active' => 'boolean'
            ], [
                'code.required' => 'Le code est obligatoire',
                'code.unique' => 'Ce code existe déjà',
                'nom.required' => 'Le nom est obligatoire',
                'type.required' => 'Le type est obligatoire',
                'type.in' => 'Le type sélectionné n\'est pas valide',
                'email_notification.email' => 'L\'adresse email n\'est pas valide',
                'capacite_traitement.required' => 'La capacité de traitement est obligatoire',
                'capacite_traitement.min' => 'La capacité doit être au moins 1',
                'capacite_traitement.max' => 'La capacité ne peut pas dépasser 1000',
                'horaires_travail.json' => 'Le format des horaires n\'est pas valide'
            ]);
            
            // Préparer les données
            $data = [
                'code' => strtoupper($validated['code']),
                'nom' => $validated['nom'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'email_notification' => $validated['email_notification'] ?? null,
                'capacite_traitement' => $validated['capacite_traitement'],
                'horaires_travail' => $validated['horaires_travail'] ?? json_encode([
                    'lundi' => ['08:00', '17:00'],
                    'mardi' => ['08:00', '17:00'],
                    'mercredi' => ['08:00', '17:00'],
                    'jeudi' => ['08:00', '17:00'],
                    'vendredi' => ['08:00', '15:00']
                ]),
                'is_active' => $request->has('is_active') ? 1 : 1, // Actif par défaut
                'created_at' => now(),
                'updated_at' => now()
            ];
            
            // Insertion avec DB::table pour éviter les attributs automatiques du modèle
            $id = DB::table('validation_entities')->insertGetId($data);
            
            Log::info('Entité de validation créée', ['id' => $id, 'code' => $data['code']]);
            
            return redirect()
                ->route('admin.validation-entities.index')
                ->with('success', 'Entité de validation créée avec succès.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'entité: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Erreur lors de la création de l\'entité: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'une entité
     */
    public function show($id)
    {
        try {
            $entity = DB::table('validation_entities')->where('id', $id)->first();
            
            if (!$entity) {
                return redirect()
                    ->route('admin.validation-entities.index')
                    ->with('error', 'Entité non trouvée.');
            }
            
            // Récupérer les statistiques
            $stats = [
                'total_validations' => DB::table('dossier_validations')
                    ->where('validation_entity_id', $id)
                    ->count(),
                    
                'validations_en_cours' => DB::table('dossier_validations')
                    ->where('validation_entity_id', $id)
                    ->where('decision', 'en_attente')
                    ->count(),
                    
                'validations_approuvees' => DB::table('dossier_validations')
                    ->where('validation_entity_id', $id)
                    ->where('decision', 'approuve')
                    ->count(),
                    
                'validations_rejetees' => DB::table('dossier_validations')
                    ->where('validation_entity_id', $id)
                    ->where('decision', 'rejete')
                    ->count(),
                    
                'workflow_steps_assignes' => DB::table('workflow_step_entities')
                    ->where('validation_entity_id', $id)
                    ->count()
            ];
            
            return view('admin.validation-entities.show', compact('entity', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'entité: ' . $e->getMessage());
            
            return redirect()
                ->route('admin.validation-entities.index')
                ->with('error', 'Erreur lors de l\'affichage de l\'entité.');
        }
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $entity = DB::table('validation_entities')->where('id', $id)->first();
            
            if (!$entity) {
                return redirect()
                    ->route('admin.validation-entities.index')
                    ->with('error', 'Entité non trouvée.');
            }
            
            return view('admin.validation-entities.edit', compact('entity'));
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement du formulaire d\'édition: ' . $e->getMessage());
            
            return redirect()
                ->route('admin.validation-entities.index')
                ->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Mettre à jour une entité
     */
    public function update(Request $request, $id)
    {
        try {
            // Vérifier que l'entité existe
            $entity = DB::table('validation_entities')->where('id', $id)->first();
            
            if (!$entity) {
                return redirect()
                    ->route('admin.validation-entities.index')
                    ->with('error', 'Entité non trouvée.');
            }
            
            // Validation
            $validated = $request->validate([
                'code' => 'required|string|max:255|unique:validation_entities,code,' . $id,
                'nom' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:direction,service,departement,commission,externe',
                'email_notification' => 'nullable|email|max:255',
                'capacite_traitement' => 'required|integer|min:1|max:1000',
                'horaires_travail' => 'nullable|json',
                'is_active' => 'boolean'
            ]);
            
            // Préparer les données
            $data = [
                'code' => strtoupper($validated['code']),
                'nom' => $validated['nom'],
                'description' => $validated['description'] ?? null,
                'type' => $validated['type'],
                'email_notification' => $validated['email_notification'] ?? null,
                'capacite_traitement' => $validated['capacite_traitement'],
                'horaires_travail' => $validated['horaires_travail'] ?? $entity->horaires_travail,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'updated_at' => now()
            ];
            
            // Mise à jour
            DB::table('validation_entities')->where('id', $id)->update($data);
            
            Log::info('Entité de validation mise à jour', ['id' => $id, 'code' => $data['code']]);
            
            return redirect()
                ->route('admin.validation-entities.show', $id)
                ->with('success', 'Entité mise à jour avec succès.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'entité: ' . $e->getMessage());
            
            return back()
                ->with('error', 'Erreur lors de la mise à jour: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une entité
     */
    public function destroy($id)
    {
        try {
            // Vérifier que l'entité existe
            $entity = DB::table('validation_entities')->where('id', $id)->first();
            
            if (!$entity) {
                return redirect()
                    ->route('admin.validation-entities.index')
                    ->with('error', 'Entité non trouvée.');
            }
            
            // Vérifier si l'entité est utilisée
            $usageCount = DB::table('workflow_step_entities')
                ->where('validation_entity_id', $id)
                ->count();
                
            if ($usageCount > 0) {
                return redirect()
                    ->route('admin.validation-entities.index')
                    ->with('error', 'Impossible de supprimer cette entité car elle est utilisée dans ' . $usageCount . ' workflow(s).');
            }
            
            // Suppression
            DB::table('validation_entities')->where('id', $id)->delete();
            
            Log::info('Entité de validation supprimée', ['id' => $id, 'code' => $entity->code]);
            
            return redirect()
                ->route('admin.validation-entities.index')
                ->with('success', 'Entité supprimée avec succès.');
                
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'entité: ' . $e->getMessage());
            
            return redirect()
                ->route('admin.validation-entities.index')
                ->with('error', 'Erreur lors de la suppression de l\'entité.');
        }
    }

    /**
     * Activer/Désactiver une entité
     */
    public function toggleStatus($id)
    {
        try {
            $entity = DB::table('validation_entities')->where('id', $id)->first();
            
            if (!$entity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entité non trouvée'
                ], 404);
            }
            
            $newStatus = $entity->is_active ? 0 : 1;
            
            DB::table('validation_entities')
                ->where('id', $id)
                ->update([
                    'is_active' => $newStatus,
                    'updated_at' => now()
                ]);
            
            Log::info('Statut entité modifié', [
                'id' => $id,
                'nouveau_statut' => $newStatus ? 'actif' : 'inactif'
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Statut modifié avec succès',
                'is_active' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut'
            ], 500);
        }
    }
}