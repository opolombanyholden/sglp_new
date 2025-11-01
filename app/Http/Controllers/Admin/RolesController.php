<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RolesController extends Controller
{
    /**
     * Constructor - Middleware admin requis
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

   /**
     * Afficher la liste des rôles - VERSION CORRIGÉE AVEC FILTRES AJAX
     * Route: GET /admin/roles
     */
    public function index(Request $request)
    {
        try {
            // Construction de la requête avec optimisations
            $query = Role::with(['permissions', 'users'])
                ->withCount(['permissions', 'users'])
                ->orderBy('level', 'desc')
                ->orderBy('display_name');

            // Application des filtres
            $this->applyFilters($query, $request);

            // Support AJAX/JSON - CORRECTION CRITIQUE
            if ($request->wantsJson() || $request->ajax() || $request->has('api')) {
                $roles = $query->get();
                
                $formattedRoles = $roles->map(function($role) {
                    return $this->formatRoleForApi($role);
                });

                return response()->json([
                    'success' => true,
                    'data' => $formattedRoles,
                    'total' => $formattedRoles->count(),
                    'stats' => $this->calculateStats()
                ]);
            }

            // Vue normale pour chargement initial
            $roles = $query->paginate(15);
            $stats = $this->calculateStats();

            return view('admin.roles.index', compact('roles', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur RolesController@index: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors du chargement des rôles',
                    'data' => [],
                    'total' => 0
                ], 500);
            }
            
            return view('admin.roles.index', [
                'roles' => collect(),
                'stats' => $this->getDefaultStats()
            ])->with('error', 'Erreur lors du chargement des rôles.');
        }
    }

    

    /**
     * MÉTHODE AJOUTÉE - Afficher le formulaire de création
     * Route: GET /admin/roles/create
     */
    public function create()
    {
        try {
            $permissions = Permission::orderBy('category')->orderBy('name')->get();
            $permissionsByCategory = $permissions->groupBy('category');
            
            return view('admin.roles.create', compact('permissions', 'permissionsByCategory'));
        } catch (\Exception $e) {
            Log::error('Erreur RolesController@create: ' . $e->getMessage());
            return redirect()->route('admin.roles.index')
                ->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * MÉTHODE AJOUTÉE - Afficher le formulaire d'édition
     * Route: GET /admin/roles/{id}/edit
     */
    public function edit($id)
    {
        try {
            $role = Role::with('permissions')->findOrFail($id);
            $permissions = Permission::orderBy('category')->orderBy('name')->get();
            $permissionsByCategory = $permissions->groupBy('category');
            $rolePermissions = $role->permissions->pluck('id')->toArray();
            
            return view('admin.roles.edit', compact('role', 'permissions', 'permissionsByCategory', 'rolePermissions'));
        } catch (\Exception $e) {
            Log::error('Erreur RolesController@edit: ' . $e->getMessage());
            return redirect()->route('admin.roles.index')
                ->with('error', 'Rôle non trouvé.');
        }
    }

   /**
 * Afficher un rôle spécifique - VERSION CORRIGÉE AVEC VRAIES DONNÉES
 * Route: GET /admin/roles/{id}
 */
public function show($id)
{
    try {
        // ✅ RÉCUPÉRER LE RÔLE AVEC TOUTES SES RELATIONS
        $role = Role::with(['permissions', 'users'])
            ->withCount(['permissions', 'users'])
            ->findOrFail($id);

        // ✅ RÉCUPÉRER LES PERMISSIONS RÉELLES GROUPÉES PAR CATÉGORIE
        $permissionsByCategory = $role->permissions->groupBy('category');

        // ✅ DÉFINIR LES INFORMATIONS DES CATÉGORIES
        $categoryColors = [
            'users' => '#009e3f',
            'orgs' => '#003f7f', 
            'dossiers' => '#ffcd00',
            'workflow' => '#8b1538',
            'system' => '#6c757d',
            'content' => '#17a2b8',
            'reports' => '#fd7e14',
            'api' => '#e83e8c'
        ];
        
        $categoryIcons = [
            'users' => 'users',
            'orgs' => 'building',
            'dossiers' => 'folder',
            'workflow' => 'cogs',
            'system' => 'shield-alt',
            'content' => 'edit',
            'reports' => 'chart-line',
            'api' => 'code'
        ];

        $categoryLabels = [
            'users' => 'Utilisateurs',
            'orgs' => 'Organisations',
            'dossiers' => 'Dossiers',
            'workflow' => 'Workflow',
            'system' => 'Système',
            'content' => 'Contenu',
            'reports' => 'Rapports',
            'api' => 'API'
        ];

        // ✅ CALCULER LES STATISTIQUES RÉELLES
        $roleStats = [
            'users_count' => $role->users_count,
            'permissions_count' => $role->permissions_count,
            'days_since_creation' => $role->created_at ? $role->created_at->diffInDays() : 0,
            'is_system' => $this->isSystemRole($role),
            'can_be_deleted' => !$this->isSystemRole($role) && $role->users_count === 0
        ];

        // ✅ VÉRIFIER SI C'EST UNE REQUÊTE JSON/API
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => array_merge($role->toArray(), [
                    'permissions_by_category' => $permissionsByCategory,
                    'stats' => $roleStats,
                    'category_info' => [
                        'colors' => $categoryColors,
                        'icons' => $categoryIcons,
                        'labels' => $categoryLabels
                    ]
                ])
            ]);
        }

        // ✅ PASSER LES VRAIES DONNÉES À LA VUE
        return view('admin.roles.show', compact(
            'role',
            'permissionsByCategory',
            'categoryColors',
            'categoryIcons', 
            'categoryLabels',
            'roleStats'
        ));

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        if (request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle non trouvé'
            ], 404);
        }
        
        return redirect()->route('admin.roles.index')
            ->with('error', 'Rôle non trouvé.');
    } catch (\Exception $e) {
        Log::error('Erreur RolesController@show: ' . $e->getMessage(), [
            'role_id' => $id,
            'trace' => $e->getTraceAsString()
        ]);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement du rôle'
            ], 500);
        }
        
        return redirect()->route('admin.roles.index')
            ->with('error', 'Erreur lors du chargement du rôle.');
    }
}

/**
 * Vérifier si un rôle est un rôle système
 */
private function isSystemRole($role)
{
    try {
        if (method_exists($role, 'isSystemRole')) {
            return $role->isSystemRole();
        }
        
        // Fallback : vérifier par nom
        $systemRoles = ['super_admin', 'admin', 'agent', 'operator'];
        return in_array($role->name, $systemRoles);
    } catch (\Exception $e) {
        Log::warning('Erreur vérification rôle système: ' . $e->getMessage());
        return false;
    }
}


    /**
     * ✅ CORRECTION MÉTHODE UPDATE - Gestion correcte des checkboxes
     * Route: PUT /admin/roles/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            \Log::info('=== DÉBUT MODIFICATION RÔLE ===', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'input_data' => $request->except(['_token', '_method', 'permissions']),
                'permissions_count' => count($request->input('permissions', [])),
                'user_id' => auth()->id()
            ]);

            // ✅ SÉCURITÉ - Empêcher modification des rôles système critiques
            if ($role->isSystemRole() && in_array($role->name, ['super_admin'])) {
                $message = 'Ce rôle système ne peut pas être modifié';
                
                \Log::warning('TENTATIVE MODIFICATION RÔLE SYSTÈME', [
                    'role_id' => $role->id,
                    'role_name' => $role->name,
                    'user_id' => auth()->id()
                ]);
                
                if ($request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 403);
                }
                
                return redirect()->back()->with('error', $message);
            }

            // ✅ PREPROCESSING - Convertir checkbox "on" vers boolean
            $inputData = $request->all();
            
            if (isset($inputData['is_active'])) {
                if ($inputData['is_active'] === 'on' || $inputData['is_active'] === '1' || $inputData['is_active'] === true) {
                    $inputData['is_active'] = true;
                } else {
                    $inputData['is_active'] = false;
                }
            } else {
                // Si checkbox non cochée, elle n'est pas envoyée
                $inputData['is_active'] = false;
            }

            // ✅ VALIDATION COMPLÈTE
            $validator = Validator::make($inputData, [
                'name' => [
                    'required',
                    'string',
                    'max:50',
                    'regex:/^[a-z_]+$/',
                    Rule::unique('roles')->ignore($role->id)
                ],
                'display_name' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'color' => 'nullable|string|regex:/^#[a-f0-9]{6}$/i',
                'level' => 'required|integer|between:1,10',
                'is_active' => 'boolean',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id'
            ], [
                'name.required' => 'Le nom du rôle est obligatoire.',
                'name.unique' => 'Ce nom de rôle existe déjà.',
                'name.regex' => 'Le nom ne peut contenir que des lettres minuscules et tirets bas.',
                'display_name.required' => 'Le nom d\'affichage est obligatoire.',
                'level.required' => 'Le niveau hiérarchique est obligatoire.',
                'level.integer' => 'Le niveau doit être un nombre entier.',
                'level.between' => 'Le niveau doit être entre 1 et 10.',
                'color.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB).',
                'is_active.boolean' => 'Le statut doit être vrai ou faux.'
            ]);

            if ($validator->fails()) {
                \Log::warning('ÉCHEC VALIDATION MODIFICATION RÔLE', [
                    'role_id' => $role->id,
                    'errors' => $validator->errors()->toArray(),
                    'original_input' => $request->all(),
                    'processed_input' => $inputData,
                    'user_id' => auth()->id()
                ]);

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Données invalides',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
            }

            // ✅ SAUVEGARDE DES ANCIENNES DONNÉES POUR LE LOG
            $oldData = $role->toArray();

            DB::beginTransaction();

            // ✅ MISE À JOUR DU RÔLE
            $updateData = [
                'name' => strtolower(trim($inputData['name'])),
                'display_name' => trim($inputData['display_name']),
                'description' => trim($inputData['description'] ?? ''),
                'color' => $inputData['color'] ?? $role->color,
                'level' => (int) $inputData['level'],
                'is_active' => (bool) $inputData['is_active']
            ];

            $role->update($updateData);

            \Log::info('RÔLE MIS À JOUR', [
                'role_id' => $role->id,
                'old_data' => $oldData,
                'new_data' => $updateData,
                'changes' => array_diff_assoc($updateData, $oldData)
            ]);

            // ✅ SYNCHRONISATION DES PERMISSIONS
            if ($request->has('permissions')) {
                $permissionsInput = $request->input('permissions', []);
                
                if (is_array($permissionsInput)) {
                    // Vérifier que les permissions existent
                    $validPermissions = Permission::whereIn('id', $permissionsInput)->pluck('id')->toArray();
                    $role->permissions()->sync($validPermissions);
                    
                    \Log::info('PERMISSIONS SYNCHRONISÉES', [
                        'role_id' => $role->id,
                        'permissions_before' => $oldData['permissions'] ?? [],
                        'permissions_after' => $validPermissions,
                        'permissions_count' => count($validPermissions)
                    ]);
                } else {
                    // Supprimer toutes les permissions
                    $role->permissions()->sync([]);
                    
                    \Log::info('TOUTES LES PERMISSIONS SUPPRIMÉES', [
                        'role_id' => $role->id
                    ]);
                }
            }

            DB::commit();

            // ✅ LOG DE SUCCÈS
            \Log::info('=== RÔLE MODIFIÉ AVEC SUCCÈS ===', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'display_name' => $role->display_name,
                'level' => $role->level,
                'is_active' => $role->is_active,
                'permissions_count' => $role->permissions()->count(),
                'updated_by' => auth()->id()
            ]);

            // ✅ RÉPONSE
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Rôle '{$role->display_name}' mis à jour avec succès",
                    'data' => [
                        'id' => $role->id,
                        'name' => $role->name,
                        'display_name' => $role->display_name,
                        'level' => $role->level,
                        'is_active' => $role->is_active,
                        'permissions_count' => $role->permissions()->count()
                    ]
                ]);
            }

            return redirect()->route('admin.roles.show', $role->id)
                ->with('success', "Rôle '{$role->display_name}' mis à jour avec succès");

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('=== ERREUR MODIFICATION RÔLE ===', [
                'role_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['_token', '_method', 'permissions']),
                'user_id' => auth()->id(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la mise à jour du rôle: ' . $e->getMessage(),
                    'debug' => app()->environment('local') ? [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ] : null
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour du rôle: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un rôle - CORRIGÉ
     * Route: DELETE /admin/roles/{id}
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Vérifications de sécurité
            if ($role->isSystemRole()) {
                $message = 'Les rôles système ne peuvent pas être supprimés';
                
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 403);
                }
                
                return redirect()->back()->with('error', $message);
            }

            if ($role->users()->count() > 0) {
                $message = 'Ce rôle est utilisé par des utilisateurs et ne peut pas être supprimé';
                
                if (request()->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }
                
                return redirect()->back()->with('error', $message);
            }

            DB::beginTransaction();

            $roleName = $role->display_name;

            // Supprimer les relations permissions
            $role->permissions()->detach();

            // Supprimer le rôle
            $role->delete();

            DB::commit();

            // Log de l'action
            Log::warning('Rôle supprimé', [
                'role_id' => $id,
                'role_name' => $roleName,
                'deleted_by' => auth()->id()
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Rôle '{$roleName}' supprimé avec succès"
                ]);
            }

            return redirect()->route('admin.roles.index')
                ->with('success', "Rôle '{$roleName}' supprimé avec succès");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur RolesController@destroy: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression du rôle'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Erreur lors de la suppression du rôle');
        }
    }

    /**
 * ✅ MÉTHODE CORRIGÉE - Mettre à jour les permissions d'un rôle
 * Route: PUT /admin/roles/{id}/permissions
 */
public function updatePermissions(Request $request, $id)
{
    try {
        $role = Role::findOrFail($id);
        
        // Vérifications de sécurité
        if ($role->isSystemRole() && in_array($role->name, ['super_admin'])) {
            $message = 'Les permissions du rôle système ne peuvent pas être modifiées';
            
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 403);
            }
            
            return redirect()->back()->with('error', $message);
        }
        
        // Validation
        $validator = Validator::make($request->all(), [
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ], [
            'permissions.array' => 'Le format des permissions est incorrect',
            'permissions.*.exists' => 'Une ou plusieurs permissions n\'existent pas'
        ]);

        if ($validator->fails()) {
            Log::warning('Échec validation permissions', [
                'role_id' => $role->id,
                'errors' => $validator->errors()->toArray(),
                'user_id' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        DB::beginTransaction();

        // Récupérer les anciennes permissions pour le log
        $oldPermissions = $role->permissions->pluck('id')->toArray();
        
        // Synchroniser les permissions
        $newPermissions = $request->input('permissions', []);
        
        // Vérifier que les permissions existent réellement
        $validPermissions = Permission::whereIn('id', $newPermissions)->pluck('id')->toArray();
        
        $role->permissions()->sync($validPermissions);

        DB::commit();

        // Log de l'action
        Log::info('Permissions mises à jour', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'old_permissions' => $oldPermissions,
            'new_permissions' => $validPermissions,
            'permissions_added' => array_diff($validPermissions, $oldPermissions),
            'permissions_removed' => array_diff($oldPermissions, $validPermissions),
            'total_permissions' => count($validPermissions),
            'updated_by' => auth()->id()
        ]);

        $message = 'Permissions mises à jour avec succès';
        if (count($validPermissions) !== count($newPermissions)) {
            $message .= ' (certaines permissions invalides ont été ignorées)';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'role_id' => $role->id,
                    'permissions_count' => count($validPermissions),
                    'updated_permissions' => $validPermissions
                ]
            ]);
        }

        return redirect()->route('admin.roles.show', $role->id)
            ->with('success', $message);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle non trouvé'
            ], 404);
        }
        
        return redirect()->route('admin.roles.index')
            ->with('error', 'Rôle non trouvé');
            
    } catch (\Exception $e) {
        DB::rollBack();
        
        Log::error('Erreur RolesController@updatePermissions: ' . $e->getMessage(), [
            'role_id' => $id,
            'request_data' => $request->except(['_token', '_method']),
            'trace' => $e->getTraceAsString(),
            'user_id' => auth()->id()
        ]);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des permissions'
            ], 500);
        }
        
        return redirect()->back()
            ->with('error', 'Erreur lors de la mise à jour des permissions')
            ->withInput();
    }
}

    public function bulkOperations(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:activate,deactivate,delete',
                'role_ids' => 'required|array|min:1',
                'role_ids.*' => 'integer|exists:roles,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $roles = Role::whereIn('id', $request->role_ids)->get();
            $processed = [];
            $errors = [];

            DB::beginTransaction();

            foreach ($roles as $role) {
                // Empêcher les opérations sur les rôles système critiques
                if ($role->isSystemRole() && in_array($role->name, ['super_admin'])) {
                    $errors[] = "Le rôle système '{$role->display_name}' ne peut pas être modifié";
                    continue;
                }

                switch ($request->action) {
                    case 'activate':
                        $role->update(['is_active' => true]);
                        $processed[] = "Rôle '{$role->display_name}' activé";
                        break;
                        
                    case 'deactivate':
                        $role->update(['is_active' => false]);
                        $processed[] = "Rôle '{$role->display_name}' désactivé";
                        break;
                        
                    case 'delete':
                        if ($role->users()->count() > 0) {
                            $errors[] = "Rôle '{$role->display_name}' utilisé par des utilisateurs";
                            continue 2;
                        }
                        $role->permissions()->detach();
                        $role->delete();
                        $processed[] = "Rôle '{$role->display_name}' supprimé";
                        break;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($processed) . ' opération(s) effectuée(s)',
                'data' => [
                    'processed' => $processed,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur RolesController@bulkOperations: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors des opérations en lot'
            ], 500);
        }
    }

// ========================================================================
// MÉTHODES MANQUANTES À AJOUTER DANS RolesController.php
// ========================================================================

/**
 * ✅ MÉTHODE STORE ROBUSTE AVEC DEBUGGING COMPLET
 * Cette version inclut des logs détaillés pour identifier le problème
 */
public function store(Request $request)
{
    // ✅ LOG INITIAL pour debugging
    \Log::info('=== DÉBUT CRÉATION RÔLE ===', [
        'input_data' => $request->except(['_token', 'permissions']),
        'permissions_count' => count($request->input('permissions', [])),
        'user_id' => auth()->id()
    ]);

    try {
        // ✅ VALIDATION RENFORCÉE
        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'unique:roles,name',
                'regex:/^[a-z0-9_]+$/'
            ],
            'display_name' => 'required|string|min:3|max:100',
            'description' => 'nullable|string|max:500',
            'level' => 'required|integer|min:1|max:10',
            'color' => 'nullable|string|regex:/^#[a-f0-9]{6}$/i',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ], [
            // Messages d'erreur en français
            'name.required' => 'Le nom du rôle est obligatoire.',
            'name.unique' => 'Ce nom de rôle existe déjà.',
            'name.regex' => 'Le nom ne peut contenir que des lettres minuscules, chiffres et tirets bas.',
            'name.min' => 'Le nom doit contenir au moins 3 caractères.',
            'display_name.required' => 'Le nom d\'affichage est obligatoire.',
            'display_name.min' => 'Le nom d\'affichage doit contenir au moins 3 caractères.',
            'level.required' => 'Le niveau hiérarchique est obligatoire.',
            'level.integer' => 'Le niveau doit être un nombre entier.',
            'level.min' => 'Le niveau minimum est 1.',
            'level.max' => 'Le niveau maximum est 10.',
            'color.regex' => 'La couleur doit être au format hexadécimal (#RRGGBB).'
        ]);

        // ✅ GESTION DES ERREURS DE VALIDATION
        if ($validator->fails()) {
            \Log::warning('ÉCHEC VALIDATION RÔLE', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->except(['_token', 'permissions']),
                'user_id' => auth()->id()
            ]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
        }

        // ✅ PRÉPARATION DES DONNÉES
        $roleData = [
            'name' => strtolower(trim($request->name)),
            'display_name' => trim($request->display_name),
            'description' => trim($request->description ?? ''),
            'level' => (int) $request->level,
            'color' => $request->color ?: '#009e3f',
            'is_active' => $request->has('is_active') ? (bool) $request->is_active : true,
            'is_system' => false // Toujours false pour les rôles créés manuellement
        ];

        \Log::info('DONNÉES RÔLE PRÉPARÉES', [
            'role_data' => $roleData,
            'permissions_to_assign' => count($request->input('permissions', []))
        ]);

        // ✅ DÉBUT TRANSACTION
        DB::beginTransaction();

        // ✅ CRÉATION DU RÔLE
        $role = new Role();
        $role->fill($roleData);
        
        \Log::info('AVANT SAUVEGARDE', [
            'role_attributes' => $role->getAttributes(),
            'fillable_fields' => $role->getFillable()
        ]);

        $saved = $role->save();

        if (!$saved) {
            throw new \Exception('Échec de la sauvegarde en base de données');
        }

        \Log::info('RÔLE CRÉÉ AVEC SUCCÈS', [
            'role_id' => $role->id,
            'role_data' => $role->toArray()
        ]);

        // ✅ ASSIGNATION DES PERMISSIONS
        $permissionsInput = $request->input('permissions', []);
        if (!empty($permissionsInput) && is_array($permissionsInput)) {
            try {
                // Vérifier que les permissions existent
                $validPermissions = Permission::whereIn('id', $permissionsInput)->pluck('id')->toArray();
                
                if (count($validPermissions) > 0) {
                    $role->permissions()->sync($validPermissions);
                    \Log::info('PERMISSIONS ASSIGNÉES', [
                        'role_id' => $role->id,
                        'permissions_assigned' => count($validPermissions),
                        'permission_ids' => $validPermissions
                    ]);
                }
            } catch (\Exception $permError) {
                \Log::error('ERREUR ASSIGNATION PERMISSIONS', [
                    'role_id' => $role->id,
                    'error' => $permError->getMessage(),
                    'permissions_input' => $permissionsInput
                ]);
                // Ne pas faire échouer toute l'opération pour un problème de permissions
            }
        }

        // ✅ VALIDATION FINALE
        $role = $role->fresh(); // Recharger depuis la DB
        
        if (!$role) {
            throw new \Exception('Le rôle n\'a pas été trouvé après création');
        }

        DB::commit();

        // ✅ LOG DE SUCCÈS
        \Log::info('=== RÔLE CRÉÉ AVEC SUCCÈS ===', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'display_name' => $role->display_name,
            'level' => $role->level,
            'permissions_count' => $role->permissions()->count(),
            'created_by' => auth()->id(),
            'duration' => 'Création terminée'
        ]);

        // ✅ RÉPONSE SELON LE TYPE DE REQUÊTE
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Rôle '{$role->display_name}' créé avec succès",
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'level' => $role->level,
                    'is_active' => $role->is_active,
                    'permissions_count' => $role->permissions()->count()
                ],
                'redirect' => route('admin.roles.show', $role->id)
            ], 201);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Rôle '{$role->display_name}' créé avec succès")
            ->with('role_created', $role->id);

    } catch (\Illuminate\Database\QueryException $dbError) {
        DB::rollBack();
        
        \Log::error('=== ERREUR BASE DE DONNÉES ===', [
            'sql_error' => $dbError->getMessage(),
            'sql_code' => $dbError->getCode(),
            'input_data' => $request->except(['_token', 'permissions']),
            'user_id' => auth()->id()
        ]);
        
        $errorMessage = 'Erreur de base de données lors de la création du rôle';
        
        // Analyser le type d'erreur SQL
        if (str_contains($dbError->getMessage(), 'Duplicate entry')) {
            $errorMessage = 'Ce nom de rôle existe déjà';
        } elseif (str_contains($dbError->getMessage(), 'cannot be null')) {
            $errorMessage = 'Champ obligatoire manquant';
        } elseif (str_contains($dbError->getMessage(), 'foreign key constraint')) {
            $errorMessage = 'Erreur de contrainte de clé étrangère';
        }
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'debug' => app()->environment('local') ? $dbError->getMessage() : null
            ], 500);
        }
        
        return redirect()->back()
            ->with('error', $errorMessage)
            ->withInput();
            
    } catch (\Exception $e) {
        DB::rollBack();
        
        \Log::error('=== ERREUR GÉNÉRALE CRÉATION RÔLE ===', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'input' => $request->except(['_token', 'permissions']),
            'user_id' => auth()->id(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du rôle: ' . $e->getMessage(),
                'debug' => app()->environment('local') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ] : null
            ], 500);
        }
        
        return redirect()->back()
            ->with('error', 'Erreur lors de la création du rôle: ' . $e->getMessage())
            ->withInput();
    }
}

/**
 * MÉTHODE CORRIGÉE - permissions() - Afficher la gestion des permissions
 * Route: GET /admin/roles/{id}/permissions
 */
public function permissions($id)
{
    try {
        $role = Role::with('permissions')->findOrFail($id);
        
        // ✅ RÉCUPÉRER TOUTES LES PERMISSIONS
        $allPermissions = Permission::orderBy('category')->orderBy('name')->get();
        $permissionsByCategory = $allPermissions->groupBy('category');
        
        // ✅ RÉCUPÉRER LES PERMISSIONS DU RÔLE
        $rolePermissions = $role->permissions;
        $rolePermissionIds = $rolePermissions->pluck('id')->toArray();
        
        // ✅ DÉFINIR LES CATÉGORIES AVEC TOUTES LES INFORMATIONS
        $categories = [
            'users' => [
                'icon' => 'users',
                'label' => 'Utilisateurs',
                'description' => 'Gestion des comptes utilisateurs',
                'color' => '#009e3f'
            ],
            'orgs' => [
                'icon' => 'building',
                'label' => 'Organisations',
                'description' => 'Gestion des organisations',
                'color' => '#003f7f'
            ],
            'dossiers' => [
                'icon' => 'folder',
                'label' => 'Dossiers',
                'description' => 'Gestion des dossiers',
                'color' => '#ffcd00'
            ],
            'workflow' => [
                'icon' => 'cogs',
                'label' => 'Workflow',
                'description' => 'Gestion des processus',
                'color' => '#8b1538'
            ],
            'system' => [
                'icon' => 'shield-alt',
                'label' => 'Système',
                'description' => 'Administration système',
                'color' => '#6c757d'
            ],
            'content' => [
                'icon' => 'edit',
                'label' => 'Contenu',
                'description' => 'Gestion du contenu',
                'color' => '#17a2b8'
            ],
            'reports' => [
                'icon' => 'chart-line',
                'label' => 'Rapports',
                'description' => 'Rapports et analytics',
                'color' => '#fd7e14'
            ],
            'api' => [
                'icon' => 'code',
                'label' => 'API',
                'description' => 'API et intégrations',
                'color' => '#e83e8c'
            ]
        ];
        
        // ✅ PASSER TOUTES LES VARIABLES ATTENDUES
        return view('admin.roles.permissions', compact(
            'role',
            'allPermissions',
            'permissionsByCategory',
            'rolePermissions',
            'rolePermissionIds',
            'categories'
        ));
        
    } catch (\Exception $e) {
        Log::error('Erreur RolesController@permissions: ' . $e->getMessage());
        return redirect()->route('admin.roles.index')
            ->with('error', 'Rôle non trouvé.');
    }
}

/**
 * MÉTHODE MANQUANTE - duplicate() - Dupliquer un rôle
 * Route: POST /admin/roles/{id}/duplicate
 */
public function duplicate($id)
{
    try {
        $originalRole = Role::with('permissions')->findOrFail($id);
        
        if ($originalRole->isSystemRole()) {
            return redirect()->back()
                ->with('error', 'Les rôles système ne peuvent pas être dupliqués');
        }
        
        DB::beginTransaction();
        
        // Créer le nom unique pour la copie
        $baseName = $originalRole->name;
        $counter = 1;
        
        do {
            $newName = $baseName . '_copie_' . $counter;
            $exists = Role::where('name', $newName)->exists();
            $counter++;
        } while ($exists && $counter <= 100);
        
        if ($counter > 100) {
            throw new \Exception('Impossible de générer un nom unique pour la copie');
        }
        
        // Créer la copie
        $newRole = Role::create([
            'name' => $newName,
            'display_name' => $originalRole->display_name . ' (Copie)',
            'description' => $originalRole->description . ' - Copie du rôle ' . $originalRole->display_name,
            'color' => $originalRole->color,
            'level' => $originalRole->level,
            'is_active' => false, // Désactivé par défaut
            'is_system' => false
        ]);
        
        // Copier les permissions
        $permissionIds = $originalRole->permissions->pluck('id')->toArray();
        $newRole->permissions()->sync($permissionIds);
        
        DB::commit();
        
        Log::info('Rôle dupliqué', [
            'original_role_id' => $originalRole->id,
            'new_role_id' => $newRole->id,
            'duplicated_by' => auth()->id()
        ]);
        
        return redirect()->route('admin.roles.edit', $newRole->id)
            ->with('success', "Rôle '{$newRole->display_name}' créé par duplication");
            
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur RolesController@duplicate: ' . $e->getMessage());
        
        return redirect()->back()
            ->with('error', 'Erreur lors de la duplication du rôle');
    }
}

/**
 * MÉTHODE MANQUANTE - toggleStatus() - Basculer le statut actif/inactif
 * Route: PATCH /admin/roles/{id}/toggle-status
 */
public function toggleStatus($id)
{
    try {
        $role = Role::findOrFail($id);
        
        // Vérifications de sécurité
        if ($role->isSystemRole() && $role->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Les rôles système actifs ne peuvent pas être désactivés'
            ], 403);
        }
        
        $oldStatus = $role->is_active;
        $role->is_active = !$role->is_active;
        $role->save();
        
        Log::info('Statut du rôle modifié', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'old_status' => $oldStatus,
            'new_status' => $role->is_active,
            'changed_by' => auth()->id()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $role->is_active ? 'Rôle activé' : 'Rôle désactivé',
            'is_active' => $role->is_active
        ]);
        
    } catch (\Exception $e) {
        Log::error('Erreur RolesController@toggleStatus: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du changement de statut'
        ], 500);
    }
}

/**
 * MÉTHODE MANQUANTE - validateName() - Valider le nom d'un rôle
 * Route: POST /admin/roles/validate-name
 */
public function validateName(Request $request)
{
    $name = $request->input('name');
    $roleId = $request->input('role_id'); // Pour les modifications
    
    if (!$name) {
        return response()->json([
            'valid' => false,
            'message' => 'Le nom est requis'
        ]);
    }
    
    if (!preg_match('/^[a-z0-9_]+$/', $name)) {
        return response()->json([
            'valid' => false,
            'message' => 'Seuls les lettres minuscules, chiffres et tirets bas sont autorisés'
        ]);
    }
    
    if (strlen($name) < 3) {
        return response()->json([
            'valid' => false,
            'message' => 'Le nom doit contenir au moins 3 caractères'
        ]);
    }
    
    // Vérifier l'unicité
    $query = Role::where('name', $name);
    if ($roleId) {
        $query->where('id', '!=', $roleId);
    }
    
    if ($query->exists()) {
        return response()->json([
            'valid' => false,
            'message' => 'Ce nom de rôle existe déjà'
        ]);
    }
    
    return response()->json([
        'valid' => true,
        'message' => 'Nom disponible'
    ]);
}

/**
* Rechercher des rôles - SYNTAXE CORRIGÉE
* Route: GET /admin/roles/search
*/

public function search(Request $request)
{
    try {
        $query = Role::with(['permissions', 'users'])
                    ->withCount(['permissions', 'users']);
        
        // Appliquer les mêmes filtres que l'index
        $this->applyFilters($query, $request);
        
        $roles = $query->orderBy('level', 'desc')
                      ->orderBy('name')
                      ->take(50)
                      ->get();
        
        $formattedRoles = $roles->map(function ($role) {
            return $this->formatRoleForApi($role);
        });
        
        return response()->json([
            'success' => true,
            'data' => $formattedRoles,
            'total' => $formattedRoles->count()
        ]);
        
    } catch (\Exception $e) {
        Log::error('Erreur RolesController@search: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la recherche',
            'data' => [],
            'total' => 0
        ], 500);
    }
}

/**
 * Exporter les rôles
 */
public function export(Request $request)
{
    try {
        $roles = Role::with(['permissions', 'users'])
                    ->withCount(['permissions', 'users'])
                    ->orderBy('level', 'desc')
                    ->get();

        $export = $roles->map(function ($role) {
            return [
                'ID' => $role->id,
                'Nom' => $role->name,
                'Nom d\'affichage' => $role->display_name,
                'Description' => $role->description,
                'Niveau' => $role->level,
                'Actif' => $role->is_active ? 'Oui' : 'Non',
                'Système' => in_array($role->name, array_keys(Role::getSystemRoles())) ? 'Oui' : 'Non',
                'Utilisateurs' => $role->users_count,
                'Permissions' => $role->permissions_count,
                'Créé le' => $role->created_at->format('d/m/Y H:i'),
            ];
        });

        $filename = 'roles_sglp_' . now()->format('Y-m-d_H-i-s') . '.json';

        return response()->json($export)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Type', 'application/json; charset=utf-8');

    } catch (\Exception $e) {
        Log::error('Erreur RolesController@export: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'export'
        ], 500);
    }
}

    /**
     * Initialiser les rôles système
     */
    public function initSystemRoles()
    {
        try {
            DB::beginTransaction();

            $systemRoles = Role::getSystemRoles();
            $createdRoles = [];

            foreach ($systemRoles as $roleName => $roleData) {
                $existingRole = Role::where('name', $roleName)->first();
                
                if (!$existingRole) {
                    $role = Role::create([
                        'name' => $roleName,
                        'display_name' => $roleData['display_name'],
                        'description' => $roleData['description'],
                        'color' => $roleData['color'],
                        'level' => $roleData['level'],
                        'is_active' => true,
                        'is_system' => true
                    ]);
                    $createdRoles[] = $role->display_name;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rôles système initialisés avec succès',
                'data' => [
                    'created_count' => count($createdRoles),
                    'created_roles' => $createdRoles
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur RolesController@initSystemRoles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation des rôles système'
            ], 500);
        }
    }


    /**
     * Appliquer les filtres à la requête
     */
    private function applyFilters($query, Request $request)
    {
        // Filtre de recherche textuelle
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('display_name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtre par niveau
        if ($request->filled('niveau') || $request->filled('level')) {
            $level = $request->filled('niveau') ? $request->niveau : $request->level;
            $query->where('level', $level);
        }

        // Filtre par type (système/personnalisé)
        if ($request->filled('type')) {
            $systemRoles = array_keys(Role::getSystemRoles());
            if ($request->type === 'system') {
                $query->whereIn('name', $systemRoles);
            } elseif ($request->type === 'custom') {
                $query->whereNotIn('name', $systemRoles);
            }
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $isActive = $request->statut === 'actif';
            $query->where('is_active', $isActive);
        }
    }


    /**
    * Formater un rôle pour l'API
    */
    private function formatRoleForApi($role)
    {
        $systemRoles = array_keys(Role::getSystemRoles());
        
        return [
            'id' => $role->id,
            'name' => $role->name,
            'display_name' => $role->display_name ?: $role->name,
            'description' => $role->description ?: 'Aucune description',
            'level' => $role->level ?: 1,
            'is_active' => $role->is_active ?: true,
            'is_system' => in_array($role->name, $systemRoles),
            'users_count' => $role->users_count ?: 0,
            'permissions_count' => $role->permissions_count ?: 0,
            'color' => $role->color ?: '#009e3f',
            'created_at' => $role->created_at ? $role->created_at->format('c') : null,
        ];
    }

    /**
    * Calculer les statistiques des rôles
    */
    private function calculateStats()
    {
        try {
            $systemRoles = array_keys(Role::getSystemRoles());
            
            return [
                'total_roles' => Role::count(),
                'active_roles' => Role::where('is_active', true)->count(),
                'system_roles' => Role::whereIn('name', $systemRoles)->count(),
                'custom_roles' => Role::whereNotIn('name', $systemRoles)->count(),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur calculateStats: ' . $e->getMessage());
            return $this->getDefaultStats();
        }
    }

    /**
    * Statistiques par défaut en cas d'erreur
    */
    private function getDefaultStats()
    {
        return [
            'total_roles' => 0,
            'active_roles' => 0,
            'system_roles' => 0,
            'custom_roles' => 0,
        ];
    }



}