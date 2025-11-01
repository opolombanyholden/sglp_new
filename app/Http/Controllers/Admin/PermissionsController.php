<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\PermissionStoreRequest;
use App\Http\Requests\PermissionUpdateRequest;

class PermissionsController extends Controller
{
    /**
     * Constructor - Middleware admin requis
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Afficher la liste des permissions - VERSION OPTIMISÉE AVEC SUPPORT AJAX
     */
    public function index(Request $request)
    {
        try {
            // Cache des statistiques pour optimiser les performances
            $stats = Cache::remember('permissions_stats', 300, function () {
                return $this->calculateStats();
            });

            // 🔥 SUPPORT AJAX/JSON - AJOUT CRITIQUE
            if ($request->get('api') == '1' || $request->wantsJson() || $request->expectsJson()) {
                return $this->handleAjaxRequest($request, $stats);
            }

            // Vue classique pour chargement initial
            return view('admin.permissions.index', [
                'totalPermissions' => $stats['total_permissions'],
                'permissionsSysteme' => $stats['system_permissions'],
                'utilisateursAvecPermissions' => $stats['users_with_permissions'],
                'permissionsHauteSecurite' => $stats['high_risk_permissions']
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur PermissionsController@index: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            if ($request->wantsJson() || $request->get('api') == '1') {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du chargement des permissions',
                    'permissions' => [],
                    'total' => 0
                ], 500);
            }
            
            return view('admin.permissions.index', [
                'totalPermissions' => 0,
                'permissionsSysteme' => 0,
                'utilisateursAvecPermissions' => 0,
                'permissionsHauteSecurite' => 0
            ])->with('error', 'Erreur lors du chargement des permissions');
        }
    }

    /**
     * 🔥 NOUVELLE MÉTHODE : Gestion des requêtes AJAX/JSON
     */
    private function handleAjaxRequest(Request $request, array $stats)
    {
        try {
            // Construction de la requête avec optimisations
            $query = Permission::with(['roles' => function($q) {
                $q->select('id', 'name', 'display_name');
            }])->withCount('roles');
            
            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            // Filtre par catégorie
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            
            // Filtre par niveau de risque
            if ($request->filled('risk')) {
                $query->where(function($q) use ($request) {
                    $riskPattern = $this->getRiskPattern($request->risk);
                    $q->where('name', 'like', $riskPattern);
                });
            }
            
            // Filtre par type (système/personnalisé)
            if ($request->filled('type')) {
                if ($request->type === 'system') {
                    $systemPermissions = collect(Permission::getSystemPermissions())
                                       ->flatten()->keys()->toArray();
                    $query->whereIn('name', $systemPermissions);
                } else {
                    $systemPermissions = collect(Permission::getSystemPermissions())
                                       ->flatten()->keys()->toArray();
                    $query->whereNotIn('name', $systemPermissions);
                }
            }
            
            // Récupérer les permissions filtrées
            $permissions = $query->orderBy('category')
                                ->orderBy('name')
                                ->get();

            // Formatter les données pour le JavaScript (format attendu par la vue)
            $formattedPermissions = $permissions->map(function($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name ?? $permission->name,
                    'description' => $permission->description ?? 'Aucune description',
                    'category' => $permission->category ?? 'other',
                    'risk_level' => $this->calculateRiskLevel($permission->name),
                    'is_system' => $this->isSystemPermission($permission),
                    'roles_count' => $permission->roles_count ?? 0,
                    'users_count' => $this->getUsersCountForPermission($permission),
                    'created_at' => $permission->created_at->toISOString(),
                ];
            });

            return response()->json([
                'success' => true,
                'permissions' => $formattedPermissions,
                'total' => $formattedPermissions->count(),
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur handleAjaxRequest: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des permissions',
                'permissions' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Créer une nouvelle permission
     */
    public function create()
    {
        $categories = [
            'users' => 'Gestion des Utilisateurs',
            'organizations' => 'Gestion des Organisations', 
            'workflow' => 'Workflow et Processus',
            'system' => 'Administration Système',
            'content' => 'Gestion du Contenu',
            'reports' => 'Rapports et Statistiques'
        ];
        
        return view('admin.permissions.create', compact('categories'));
    }

    /**
     * Afficher une permission spécifique - OPTIMISÉ POUR PHP 8.3
     */
    public function show($id)
    {
        try {
            $permission = Permission::with(['roles' => function($q) {
                $q->select('id', 'name', 'display_name', 'color', 'level');
            }])
            ->withCount(['roles'])
            ->findOrFail($id);

            // Enrichir avec statistiques
            $permissionData = [
                'id' => $permission->id,
                'name' => $permission->name,
                'display_name' => $permission->display_name,
                'category' => $permission->category,
                'description' => $permission->description,
                'risk_level' => $this->calculateRiskLevel($permission->name),
                'is_system' => $this->isSystemPermission($permission),
                'roles_count' => $permission->roles_count,
                'users_count' => $this->getUsersCountForPermission($permission),
                'created_at' => $permission->created_at->toISOString(),
                'updated_at' => $permission->updated_at?->toISOString(),
                'roles' => $permission->roles->map(function($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                        'color' => $role->color ?? '#009e3f',
                        'level' => $role->level ?? 1
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $permissionData
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission non trouvée'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Erreur PermissionsController@show: ' . $e->getMessage(), [
                'permission_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de la permission'
            ], 500);
        }
    }

    /**
     * Éditer une permission
     */
    public function edit($id)
    {
        try {
            $permission = Permission::findOrFail($id);
            
            // Enrichir avec métadonnées
            $permission->risk_level = $this->calculateRiskLevel($permission->name);
            $permission->isSystemPermission = $this->isSystemPermission($permission);
            
            if ($permission->isSystemPermission && $this->isCriticalSystemPermission($permission)) {
                return redirect()->route('admin.permissions.index')
                    ->with('error', 'Les permissions système critiques ne peuvent pas être modifiées');
            }

            $categories = [
                'users' => 'Gestion des Utilisateurs',
                'organizations' => 'Gestion des Organisations', 
                'workflow' => 'Workflow et Processus',
                'system' => 'Administration Système',
                'content' => 'Gestion du Contenu',
                'reports' => 'Rapports et Statistiques'
            ];
            
            return view('admin.permissions.edit', compact('permission', 'categories'));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Permission non trouvée');
        }
    }

    /**
     * Créer une nouvelle permission - OPTIMISÉ AVEC REQUEST VALIDATION
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    'unique:permissions,name',
                    'regex:/^[a-z]+\.[a-z_]+$/'
                ],
                'display_name' => 'required|string|max:150',
                'category' => 'required|string|max:50|in:users,organizations,workflow,system,content,reports',
                'description' => 'nullable|string|max:500',
                'risk_level' => 'nullable|in:low,medium,high'
            ], [
                'name.regex' => 'Le nom de la permission doit suivre le format "catégorie.action"',
                'name.unique' => 'Cette permission existe déjà',
                'category.in' => 'Catégorie non valide'
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Données invalides',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Vérifier cohérence nom/catégorie
            $nameParts = explode('.', $request->name);
            if (count($nameParts) !== 2 || $nameParts[0] !== $request->category) {
                $error = 'Le nom de la permission doit correspondre à la catégorie sélectionnée';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $error
                    ], 422);
                }
                
                return redirect()->back()
                    ->with('error', $error)
                    ->withInput();
            }

            DB::beginTransaction();

            // Créer la permission
            $permission = Permission::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'category' => $request->category,
                'description' => $request->description
            ]);

            DB::commit();

            // Vider le cache des statistiques
            Cache::forget('permissions_stats');

            // Log de l'action
            Log::info('Nouvelle permission créée', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name,
                'category' => $permission->category,
                'created_by' => auth()->id()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Permission '{$permission->display_name}' créée avec succès",
                    'data' => $permission->fresh()->toArray()
                ]);
            }
            
            return redirect()->route('admin.permissions.index')
                ->with('success', "Permission '{$permission->display_name}' créée avec succès");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur PermissionsController@store: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création de la permission'
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de la permission')
                ->withInput();
        }
    }

    /**
     * Mettre à jour une permission - OPTIMISÉ AVEC REQUEST VALIDATION
     */
    public function update(Request $request, $id)
    {
        try {
            $permission = Permission::findOrFail($id);

            // Empêcher modification des permissions système critiques
            if ($this->isSystemPermission($permission) && $this->isCriticalSystemPermission($permission)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Les permissions système critiques ne peuvent pas être modifiées'
                    ], 403);
                }
                
                return redirect()->back()
                    ->with('error', 'Les permissions système critiques ne peuvent pas être modifiées');
            }

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    'regex:/^[a-z]+\.[a-z_]+$/',
                    Rule::unique('permissions')->ignore($permission->id)
                ],
                'display_name' => 'required|string|max:150',
                'category' => 'required|string|max:50|in:users,organizations,workflow,system,content,reports',
                'description' => 'nullable|string|max:500',
                'risk_level' => 'nullable|in:low,medium,high'
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Données invalides',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Vérifier cohérence nom/catégorie
            $nameParts = explode('.', $request->name);
            if (count($nameParts) !== 2 || $nameParts[0] !== $request->category) {
                $error = 'Le nom de la permission doit correspondre à la catégorie sélectionnée';
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $error
                    ], 422);
                }
                
                return redirect()->back()
                    ->with('error', $error)
                    ->withInput();
            }

            DB::beginTransaction();

            // Sauvegarder les anciennes valeurs pour le log
            $oldData = $permission->toArray();

            // Mettre à jour la permission
            $permission->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'category' => $request->category,
                'description' => $request->description
            ]);

            DB::commit();

            // Vider le cache
            Cache::forget('permissions_stats');

            // Log de l'action
            Log::info('Permission mise à jour', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name,
                'updated_by' => auth()->id(),
                'changes' => array_diff_assoc($permission->fresh()->toArray(), $oldData)
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Permission '{$permission->display_name}' mise à jour avec succès",
                    'data' => $permission->fresh()->toArray()
                ]);
            }
            
            return redirect()->route('admin.permissions.index')
                ->with('success', "Permission '{$permission->display_name}' mise à jour avec succès");

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission non trouvée'
                ], 404);
            }
            
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Permission non trouvée');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur PermissionsController@update: ' . $e->getMessage(), [
                'permission_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour de la permission'
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour de la permission')
                ->withInput();
        }
    }

    /**
     * Supprimer une permission - SÉCURISÉ POUR PHP 8.3
     */
    public function destroy($id)
    {
        try {
            $permission = Permission::findOrFail($id);

            // Vérifications de sécurité renforcées
            if ($this->isSystemPermission($permission)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les permissions système ne peuvent pas être supprimées'
                ], 403);
            }

            $rolesCount = $permission->roles()->count();
            if ($rolesCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Cette permission est utilisée par {$rolesCount} rôle(s) et ne peut pas être supprimée"
                ], 422);
            }

            DB::beginTransaction();

            $permissionName = $permission->display_name;
            $permissionData = $permission->toArray();

            // Supprimer la permission
            $permission->delete();

            DB::commit();

            // Vider le cache
            Cache::forget('permissions_stats');

            // Log de l'action
            Log::warning('Permission supprimée', [
                'permission_id' => $id,
                'permission_name' => $permissionName,
                'permission_data' => $permissionData,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Permission '{$permissionName}' supprimée avec succès"
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission non trouvée'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur PermissionsController@destroy: ' . $e->getMessage(), [
                'permission_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la permission'
            ], 500);
        }
    }

    /**
     * Initialiser les permissions système - SÉCURISÉ POUR PHP 8.3
     */
    public function initSystemPermissions()
    {
        try {
            $user = auth()->user();
            if (!($user->role === 'admin' || 
                ($user->roleModel && $user->roleModel->name === 'super_admin') ||
                method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seul un administrateur peut initialiser les permissions système'
                ], 403);
            }

            DB::beginTransaction();

            $systemPermissions = Permission::getSystemPermissions();
            $createdPermissions = [];
            $skippedPermissions = [];

            foreach ($systemPermissions as $category => $permissions) {
                foreach ($permissions as $permissionName => $displayName) {
                    $existingPermission = Permission::where('name', $permissionName)->first();
                    
                    if (!$existingPermission) {
                        $permission = Permission::create([
                            'name' => $permissionName,
                            'display_name' => $displayName,
                            'category' => $category,
                            'description' => "Permission système pour {$displayName}"
                        ]);
                        $createdPermissions[] = $permission->display_name;
                    } else {
                        $skippedPermissions[] = $displayName;
                    }
                }
            }

            DB::commit();

            // Vider le cache
            Cache::forget('permissions_stats');

            // Log de l'action
            Log::warning('Permissions système initialisées', [
                'created_permissions' => $createdPermissions,
                'skipped_permissions' => $skippedPermissions,
                'initialized_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permissions système initialisées avec succès',
                'data' => [
                    'created_count' => count($createdPermissions),
                    'skipped_count' => count($skippedPermissions),
                    'created_permissions' => $createdPermissions,
                    'skipped_permissions' => $skippedPermissions
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur PermissionsController@initSystemPermissions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation des permissions système'
            ], 500);
        }
    }

    /**
     * Validation du nom de permission
     */
    public function validateName(Request $request)
    {
        try {
            $name = $request->get('name');
            $currentId = $request->get('current_id');

            $query = Permission::where('name', $name);
            if ($currentId) {
                $query->where('id', '!=', $currentId);
            }

            $exists = $query->exists();

            return response()->json([
                'success' => true,
                'available' => !$exists,
                'message' => $exists ? 'Ce nom de permission est déjà utilisé' : 'Nom de permission disponible'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur PermissionsController@validateName: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation'
            ], 500);
        }
    }

    /**
     * API de recherche pour autocomplete
     */
    public function apiSearch(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $limit = min($request->get('limit', 10), 50);

            $permissions = Permission::where('name', 'like', "%{$query}%")
                                   ->orWhere('display_name', 'like', "%{$query}%")
                                   ->limit($limit)
                                   ->get(['id', 'name', 'display_name', 'category']);

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur PermissionsController@apiSearch: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * API des catégories
     */
    public function apiCategories()
    {
        try {
            $categories = [
                'users' => 'Gestion des Utilisateurs',
                'orgs' => 'Gestion des Organisations',  // ✅ Cohérent avec Role.php
                'dossiers' => 'Gestion des Dossiers',   // ✅ Ajouté
                'workflow' => 'Workflow et Processus',
                'system' => 'Administration Système',
                'content' => 'Gestion du Contenu',
                'reports' => 'Rapports et Statistiques'
            ];

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur PermissionsController@apiCategories: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des catégories'
            ], 500);
        }
    }

    /**
     * Exporter les permissions - OPTIMISÉ
     */
    public function export(Request $request)
    {
        try {
            $query = Permission::with(['roles:id,name,display_name'])
                              ->withCount('roles');
            
            // Appliquer les mêmes filtres que l'index
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            
            $permissions = $query->orderBy('category')
                                ->orderBy('name')
                                ->get();

            $export = $permissions->map(function ($permission) {
                return [
                    'ID' => $permission->id,
                    'Nom' => $permission->name,
                    'Nom d\'affichage' => $permission->display_name,
                    'Catégorie' => $permission->category,
                    'Description' => $permission->description,
                    'Niveau de risque' => $this->calculateRiskLevel($permission->name),
                    'Système' => $this->isSystemPermission($permission) ? 'Oui' : 'Non',
                    'Rôles' => $permission->roles_count,
                    'Utilisateurs' => $this->getUsersCountForPermission($permission),
                    'Créé le' => $permission->created_at->format('d/m/Y H:i'),
                ];
            });

            $filename = 'permissions_sglp_' . now()->format('Y-m-d_H-i-s') . '.json';

            // Log de l'export
            Log::info('Export des permissions effectué', [
                'exported_by' => auth()->id(),
                'permissions_count' => $permissions->count(),
                'filename' => $filename,
                'filters' => $request->only(['search', 'category', 'risk', 'type'])
            ]);

            return response()->json($export)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Type', 'application/json; charset=utf-8');

        } catch (\Exception $e) {
            Log::error('Erreur PermissionsController@export: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export'
            ], 500);
        }
    }

    /**
     * Opérations en lot - Suppression multiple
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'permission_ids' => 'required|array|min:1',
                'permission_ids.*' => 'integer|exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'IDs de permissions invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $permissions = Permission::whereIn('id', $request->permission_ids)->get();
            $deleted = [];
            $errors = [];

            DB::beginTransaction();

            foreach ($permissions as $permission) {
                if ($this->isSystemPermission($permission)) {
                    $errors[] = "Permission système '{$permission->display_name}' ne peut pas être supprimée";
                    continue;
                }

                if ($permission->roles()->count() > 0) {
                    $errors[] = "Permission '{$permission->display_name}' est utilisée par des rôles";
                    continue;
                }

                $deleted[] = $permission->display_name;
                $permission->delete();
            }

            DB::commit();
            Cache::forget('permissions_stats');

            return response()->json([
                'success' => true,
                'message' => count($deleted) . ' permission(s) supprimée(s)',
                'data' => [
                    'deleted' => $deleted,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur PermissionsController@bulkDelete: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression en lot'
            ], 500);
        }
    }

    // =================================================================
    // MÉTHODES UTILITAIRES PRIVÉES - OPTIMISÉES POUR PHP 8.3
    // =================================================================

    /**
     * Calculer les statistiques des permissions
     */
    private function calculateStats(): array
    {
        try {
            $totalPermissions = Permission::count();
            $systemPermissions = $this->getSystemPermissionsCount();
            $usersWithPermissions = $this->getUsersWithPermissionsCount();
            $highRiskPermissions = $this->getHighRiskPermissionsCount();

            return [
                'total_permissions' => $totalPermissions,
                'system_permissions' => $systemPermissions,
                'users_with_permissions' => $usersWithPermissions,
                'high_risk_permissions' => $highRiskPermissions,
                'categories_count' => Permission::distinct('category')->count('category')
            ];
        } catch (\Exception $e) {
            Log::error('Erreur calculateStats: ' . $e->getMessage());
            return $this->getDefaultStats();
        }
    }

    /**
     * Statistiques par défaut en cas d'erreur
     */
    private function getDefaultStats(): array
    {
        return [
            'total_permissions' => 0,
            'system_permissions' => 0,
            'users_with_permissions' => 0,
            'high_risk_permissions' => 0,
            'categories_count' => 0
        ];
    }

    /**
     * Calculer le niveau de risque d'une permission
     */
    private function calculateRiskLevel(string $permissionName): string
    {
        $highRiskPatterns = ['delete', 'destroy', 'system', 'config', 'admin', 'manage', 'permissions'];
        $mediumRiskPatterns = ['create', 'edit', 'update', 'validate', 'assign', 'reject'];
        
        $lowerName = strtolower($permissionName);
        
        foreach ($highRiskPatterns as $pattern) {
            if (str_contains($lowerName, $pattern)) {
                return 'high';
            }
        }
        
        foreach ($mediumRiskPatterns as $pattern) {
            if (str_contains($lowerName, $pattern)) {
                return 'medium';
            }
        }
        
        return 'low';
    }

    /**
     * Obtenir le pattern pour le niveau de risque
     */
    private function getRiskPattern(string $risk): string
    {
        return match($risk) {
            'high' => '%delete%',
            'medium' => '%create%',
            'low' => '%view%',
            default => '%%'
        };
    }

    /**
     * Vérifier si une permission est système
     */
    private function isSystemPermission(Permission $permission): bool
    {
        // Vérifier par catégorie
        $systemCategories = ['system', 'api', 'admin'];
        if (in_array($permission->category, $systemCategories)) {
            return true;
        }
        
        // Vérifier par pattern de nom
        $systemPatterns = ['system.', 'admin.', 'api.'];
        foreach ($systemPatterns as $pattern) {
            if (str_starts_with($permission->name, $pattern)) {
                return true;
            }
        }

        // Vérifier dans la liste des permissions système prédéfinies
        try {
            $systemPermissions = collect(Permission::getSystemPermissions())
                               ->flatten()->keys()->toArray();
            
            return in_array($permission->name, $systemPermissions);
        } catch (\Exception $e) {
            Log::error('Erreur isSystemPermission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier si une permission système est critique
     */
    private function isCriticalSystemPermission(Permission $permission): bool
    {
        $criticalPermissions = [
            'system.manage',
            'system.config', 
            'admin.permissions',
            'admin.users',
            'admin.roles'
        ];
        
        return in_array($permission->name, $criticalPermissions);
    }

    /**
     * Compter les utilisateurs ayant une permission spécifique
     */
    private function getUsersCountForPermission(Permission $permission): int
    {
        try {
            // Vérifier d'abord si c'est le nouveau système avec role_id
            if (\Schema::hasColumn('users', 'role_id')) {
                return User::whereHas('roleModel.permissions', function ($query) use ($permission) {
                    $query->where('permissions.id', $permission->id);
                })->distinct()->count();
            }
            
            // Fallback pour l'ancien système avec role string
            return $permission->roles()->withCount('users')->get()->sum('users_count');
            
        } catch (\Exception $e) {
            Log::error('Erreur getUsersCountForPermission: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Compter les utilisateurs ayant des permissions
     */
    private function getUsersWithPermissionsCount(): int
    {
        try {
            if (method_exists(User::class, 'roleModel')) {
                return User::whereHas('roleModel.permissions')->distinct()->count();
            }
            
            return User::whereHas('roles.permissions')->distinct()->count();
            
        } catch (\Exception $e) {
            Log::error('Erreur getUsersWithPermissionsCount: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Compter les permissions système
     */
    private function getSystemPermissionsCount(): int
    {
        try {
            $systemPermissions = collect(Permission::getSystemPermissions())
                               ->flatten()->keys()->toArray();
            
            return Permission::whereIn('name', $systemPermissions)->count();
        } catch (\Exception $e) {
            Log::error('Erreur getSystemPermissionsCount: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Compter les permissions à haut risque
     */
    private function getHighRiskPermissionsCount(): int
    {
        try {
            return Permission::where(function($query) {
                $query->where('name', 'like', '%delete%')
                      ->orWhere('name', 'like', '%destroy%')
                      ->orWhere('name', 'like', '%system%')
                      ->orWhere('name', 'like', '%config%')
                      ->orWhere('name', 'like', '%admin%')
                      ->orWhere('name', 'like', '%manage%');
            })->count();
        } catch (\Exception $e) {
            Log::error('Erreur getHighRiskPermissionsCount: ' . $e->getMessage());
            return 0;
        }
    }


    // SOLUTION : Ajoutez cette méthode dans votre modèle Permission.php :

public static function getSystemPermissions()
{
    return [
        // Gestion des utilisateurs
        'users' => [
            'users.view' => 'Voir les utilisateurs',
            'users.create' => 'Créer des utilisateurs',
            'users.edit' => 'Modifier les utilisateurs',
            'users.delete' => 'Supprimer les utilisateurs',
                'users.manage_roles' => 'Gérer les rôles utilisateurs',
                'users.export' => 'Exporter les utilisateurs',
            ],

            // Gestion des organisations  
            'organizations' => [
                'organizations.view' => 'Voir les organisations',
                'organizations.create' => 'Créer des organisations',
                'organizations.edit' => 'Modifier les organisations',
                'organizations.delete' => 'Supprimer les organisations',
                'organizations.validate' => 'Valider les organisations',
                'organizations.export' => 'Exporter les organisations',
            ],

            // Workflow de validation
            'workflow' => [
                'workflow.view' => 'Voir le workflow',
                'workflow.manage' => 'Gérer le workflow',
                'workflow.validate' => 'Valider dans le workflow',
                'workflow.assign' => 'Attribuer dans le workflow',
                'workflow.override' => 'Contourner le workflow',
            ],

            // Gestion du système
            'system' => [
                'system.access' => 'Accès administration système',
                'system.settings' => 'Modifier les paramètres système',
                'system.maintenance' => 'Mode maintenance',
                'system.backup' => 'Sauvegardes système',
                'system.logs' => 'Consulter les journaux',
            ],

            // Gestion des contenus
            'content' => [
                'content.view' => 'Voir le contenu',
                'content.create' => 'Créer du contenu',
                'content.edit' => 'Modifier le contenu',
                'content.delete' => 'Supprimer le contenu',
                'content.publish' => 'Publier le contenu',
            ],

            // Rapports et statistiques
            'reports' => [
                'reports.view' => 'Consulter les rapports',
                'reports.create' => 'Créer des rapports',
                'reports.export' => 'Exporter les rapports',
                'reports.analytics' => 'Accès aux analytics',
            ],
        ];
    }
}