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
     * Afficher la liste des rôles - ADAPTÉ POUR BOOTSTRAP 4 + DESIGN GABONAIS
     * Route: GET /admin/roles
     */
    public function index(Request $request)
    {
        try {
            // Charger les rôles avec leurs relations
            $query = Role::with(['permissions', 'users'])
                ->withCount(['permissions', 'users'])
                ->orderBy('level', 'desc')
                ->orderBy('display_name');

            // Filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            if ($request->filled('type')) {
                if ($request->type === 'system') {
                    $query->where(function($q) {
                        $systemRoles = array_keys(Role::getSystemRoles());
                        $q->whereIn('name', $systemRoles);
                    });
                } else {
                    $query->where(function($q) {
                        $systemRoles = array_keys(Role::getSystemRoles());
                        $q->whereNotIn('name', $systemRoles);
                    });
                }
            }

            if ($request->filled('active')) {
                $query->where('is_active', $request->active);
            }

            $roles = $query->get();

            // Statistiques
            $stats = [
                'total_roles' => Role::count(),
                'active_roles' => Role::where('is_active', true)->count(),
                'system_roles' => Role::where(function($q) {
                    $systemRoles = array_keys(Role::getSystemRoles());
                    $q->whereIn('name', $systemRoles);
                })->count(),
                'custom_roles' => Role::where(function($q) {
                    $systemRoles = array_keys(Role::getSystemRoles());
                    $q->whereNotIn('name', $systemRoles);
                })->count(),
            ];

            return view('admin.roles.index', compact('roles', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Erreur RolesController@index: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')
                ->with('error', 'Erreur lors du chargement des rôles.');
        }
    }

    /**
     * Afficher un rôle spécifique - ADAPTÉ POUR API BOOTSTRAP 4
     * Route: GET /admin/roles/{id}
     */
    public function show($id)
    {
        try {
            $role = Role::with(['permissions', 'users'])
                ->withCount(['permissions', 'users'])
                ->findOrFail($id);

            // Utiliser la méthode toApiArray existante si disponible, sinon format manuel
            if (method_exists($role, 'toApiArray')) {
                $roleData = $role->toApiArray();
            } else {
                // Format manuel pour compatibilité
                $roleData = [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name,
                    'description' => $role->description,
                    'level' => $role->level,
                    'color' => $role->color ?? '#009e3f',
                    'is_active' => $role->is_active,
                    'is_system' => $role->isSystemRole(),
                    'users_count' => $role->users_count,
                    'permissions_count' => $role->permissions_count,
                    'created_at' => $role->created_at->toISOString(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $roleData
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle non trouvé'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur RolesController@show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement du rôle'
            ], 500);
        }
    }

    /**
     * Créer un nouveau rôle - VERSION ADAPTÉE BOOTSTRAP 4
     * Route: POST /admin/roles
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50|unique:roles,name|regex:/^[a-z_]+$/',
                'display_name' => 'required|string|max:100',
                'description' => 'nullable|string|max:500',
                'color' => 'nullable|string|regex:/^#[a-f0-9]{6}$/i',
                'level' => 'required|integer|between:1,10',
                'is_active' => 'boolean',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Créer le rôle avec couleur gabonaise par défaut
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'color' => $request->color ?? '#009e3f', // Couleur gabonaise par défaut
                'level' => $request->level,
                'is_active' => $request->has('is_active') ? $request->is_active : true
            ]);

            // Assigner les permissions
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            }

            // Valider la cohérence PNGDI si la méthode existe
            if (method_exists($role, 'validateForPNGDI')) {
                $validationErrors = $role->validateForPNGDI();
                if (!empty($validationErrors)) {
                    \Log::warning('Problèmes de validation PNGDI pour le rôle', [
                        'role_id' => $role->id,
                        'errors' => $validationErrors
                    ]);
                }
            }

            DB::commit();

            // Log de l'action
            \Log::info('Nouveau rôle créé', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'created_by' => auth()->id(),
                'permissions_count' => count($request->permissions ?? [])
            ]);

            return response()->json([
                'success' => true,
                'message' => "Rôle '{$role->display_name}' créé avec succès",
                'data' => $role->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur RolesController@store: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du rôle'
            ], 500);
        }
    }

    /**
     * Mettre à jour un rôle - VERSION ADAPTÉE BOOTSTRAP 4
     * Route: PUT /admin/roles/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            // Empêcher modification des rôles système critiques
            if ($role->isSystemRole() && in_array($role->name, ['super_admin', 'SUPER_ADMIN'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce rôle système ne peut pas être modifié'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
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
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Sauvegarder les anciennes valeurs pour le log
            $oldData = $role->toArray();

            // Mettre à jour le rôle
            $role->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'color' => $request->color ?? $role->color,
                'level' => $request->level,
                'is_active' => $request->has('is_active') ? $request->is_active : $role->is_active
            ]);

            // Synchroniser les permissions
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            }

            // Valider la cohérence PNGDI si la méthode existe
            if (method_exists($role, 'validateForPNGDI')) {
                $validationErrors = $role->validateForPNGDI();
                if (!empty($validationErrors)) {
                    \Log::warning('Problèmes de validation PNGDI après mise à jour', [
                        'role_id' => $role->id,
                        'errors' => $validationErrors
                    ]);
                }
            }

            DB::commit();

            // Log de l'action
            \Log::info('Rôle mis à jour', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'updated_by' => auth()->id(),
                'old_data' => $oldData,
                'new_data' => $role->fresh()->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Rôle '{$role->display_name}' mis à jour avec succès",
                'data' => $role->fresh()
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle non trouvé'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur RolesController@update: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du rôle'
            ], 500);
        }
    }

    /**
     * Supprimer un rôle - VERSION ADAPTÉE BOOTSTRAP 4
     * Route: DELETE /admin/roles/{id}
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Vérifications de sécurité
            if ($role->isSystemRole()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les rôles système ne peuvent pas être supprimés'
                ], 403);
            }

            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce rôle est utilisé par des utilisateurs et ne peut pas être supprimé'
                ], 422);
            }

            DB::beginTransaction();

            $roleName = $role->display_name;

            // Supprimer les relations permissions
            $role->permissions()->detach();

            // Supprimer le rôle
            $role->delete();

            DB::commit();

            // Log de l'action
            \Log::warning('Rôle supprimé', [
                'role_id' => $id,
                'role_name' => $roleName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Rôle '{$roleName}' supprimé avec succès"
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle non trouvé'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur RolesController@destroy: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du rôle'
            ], 500);
        }
    }

    /**
     * Dupliquer un rôle - MÉTHODE EXISTANTE CONSERVÉE
     * Route: POST /admin/roles/{id}/duplicate
     */
    public function duplicate($id)
    {
        try {
            $originalRole = Role::with('permissions')->findOrFail($id);

            // Générer un nouveau nom unique
            $baseName = $originalRole->name . '_copy';
            $newName = $baseName;
            $counter = 1;
            
            while (Role::where('name', $newName)->exists()) {
                $newName = $baseName . '_' . $counter;
                $counter++;
            }

            DB::beginTransaction();

            // Créer la copie
            if (method_exists($originalRole, 'duplicate')) {
                $newRole = $originalRole->duplicate($newName, $originalRole->display_name . ' (Copie)');
            } else {
                // Méthode de duplication manuelle si la méthode n'existe pas
                $newRole = $originalRole->replicate();
                $newRole->name = $newName;
                $newRole->display_name = $originalRole->display_name . ' (Copie)';
                $newRole->save();
                
                // Copier les permissions
                $newRole->permissions()->sync($originalRole->permissions->pluck('id'));
            }

            DB::commit();

            // Log de l'action
            \Log::info('Rôle dupliqué', [
                'original_role_id' => $originalRole->id,
                'new_role_id' => $newRole->id,
                'duplicated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Rôle dupliqué avec succès",
                'data' => $newRole
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle non trouvé'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur RolesController@duplicate: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la duplication du rôle'
            ], 500);
        }
    }

    /**
     * Activer/Désactiver un rôle - MÉTHODE EXISTANTE CONSERVÉE
     * Route: PATCH /admin/roles/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Empêcher la désactivation des rôles système critiques
            if ($role->isSystemRole() && in_array($role->name, ['super_admin', 'SUPER_ADMIN']) && $role->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le rôle Super Administrateur ne peut pas être désactivé'
                ], 403);
            }

            $newStatus = !$role->is_active;
            $role->update(['is_active' => $newStatus]);

            // Log de l'action
            \Log::info('Statut du rôle modifié', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'new_status' => $newStatus,
                'changed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Rôle " . ($newStatus ? 'activé' : 'désactivé') . " avec succès",
                'data' => ['is_active' => $newStatus]
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle non trouvé'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur RolesController@toggleStatus: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut'
            ], 500);
        }
    }

    /**
     * Obtenir les utilisateurs d'un rôle - MÉTHODE EXISTANTE CONSERVÉE
     * Route: GET /admin/roles/{id}/users
     */
    public function getUsers($id)
    {
        try {
            $role = Role::findOrFail($id);
            
            $users = $role->users()
                ->select(['id', 'name', 'email', 'is_active', 'last_login_at', 'created_at'])
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_active' => $user->is_active,
                        'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais',
                        'created_at' => $user->created_at->format('d/m/Y')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle non trouvé'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur RolesController@getUsers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des utilisateurs'
            ], 500);
        }
    }

    /**
     * Statistiques détaillées d'un rôle - MÉTHODE EXISTANTE CONSERVÉE
     * Route: GET /admin/roles/{id}/stats
     */
    public function getStats($id)
    {
        try {
            $role = Role::with(['permissions', 'users'])->findOrFail($id);

            $stats = [
                // Statistiques de base
                'users_count' => $role->users()->count(),
                'active_users_count' => $role->users()->where('is_active', true)->count(),
                'permissions_count' => $role->permissions()->count(),
                
                // Utilisation par type d'organisation
                'organisations_managed' => method_exists($role, 'getManagedOrganizationTypes') 
                    ? $role->getManagedOrganizationTypes() 
                    : [],
                
                // Répartition des permissions par catégorie
                'permissions_by_category' => $role->permissions()
                    ->select('category', DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'category' => $item->category,
                            'count' => $item->count
                        ];
                    }),
                
                // Activité récente des utilisateurs
                'recent_user_activity' => $role->users()
                    ->where('last_login_at', '>=', now()->subDays(7))
                    ->count(),
                
                // Métriques de sécurité
                'security_metrics' => [
                    'is_system' => $role->isSystemRole(),
                    'is_admin_level' => $role->level >= 8,
                    'level' => $role->level,
                    'high_risk_permissions' => $role->permissions()
                        ->whereRaw("name LIKE '%delete%' OR name LIKE '%system%'")
                        ->count()
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Rôle non trouvé'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur RolesController@getStats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques'
            ], 500);
        }
    }

    /**
     * Exporter les rôles - MÉTHODE EXISTANTE CONSERVÉE
     * Route: GET /admin/roles/export
     */
    public function export(Request $request)
    {
        try {
            $roles = Role::with(['permissions', 'users'])
                ->orderBy('level', 'desc')
                ->get();

            $export = $roles->map(function ($role) {
                if (method_exists($role, 'toExportArray')) {
                    return $role->toExportArray();
                } else {
                    // Format manuel si la méthode n'existe pas
                    return [
                        'ID' => $role->id,
                        'Nom' => $role->name,
                        'Nom d\'affichage' => $role->display_name,
                        'Description' => $role->description,
                        'Niveau' => $role->level,
                        'Actif' => $role->is_active ? 'Oui' : 'Non',
                        'Système' => $role->isSystemRole() ? 'Oui' : 'Non',
                        'Utilisateurs' => $role->users->count(),
                        'Permissions' => $role->permissions->count(),
                        'Créé le' => $role->created_at->format('d/m/Y H:i'),
                    ];
                }
            });

            $filename = 'roles_sglp_' . now()->format('Y-m-d_H-i-s') . '.json';

            // Log de l'export
            \Log::info('Export des rôles effectué', [
                'exported_by' => auth()->id(),
                'roles_count' => $roles->count(),
                'filename' => $filename
            ]);

            return response()->json($export)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Erreur RolesController@export: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export'
            ], 500);
        }
    }

    /**
     * Initialiser les rôles système - MÉTHODE EXISTANTE ADAPTÉE
     * Route: POST /admin/roles/init-system-roles
     */
    public function initSystemRoles()
    {
        try {
            // Vérifier les permissions d'administration
            $user = auth()->user();
            if (!$user || (!$user->hasRole('super_admin') && !method_exists($user, 'isSuperAdmin'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seul un super administrateur peut initialiser les rôles système'
                ], 403);
            }

            DB::beginTransaction();

            $systemRoles = Role::getSystemRoles();
            $createdRoles = [];

            foreach ($systemRoles as $roleName => $roleData) {
                $existingRole = Role::where('name', $roleName)->first();
                
                if (!$existingRole) {
                    if (method_exists(Role::class, 'createSystemRole')) {
                        $role = Role::createSystemRole($roleName, $roleData);
                    } else {
                        // Création manuelle si la méthode n'existe pas
                        $role = Role::create([
                            'name' => $roleName,
                            'display_name' => $roleData['display_name'],
                            'description' => $roleData['description'],
                            'color' => $roleData['color'] ?? '#009e3f',
                            'level' => $roleData['level'],
                            'is_active' => true
                        ]);
                    }
                    $createdRoles[] = $role->display_name;
                }
            }

            DB::commit();

            // Log de l'action
            \Log::warning('Rôles système initialisés', [
                'created_roles' => $createdRoles,
                'initialized_by' => auth()->id()
            ]);

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
            \Log::error('Erreur RolesController@initSystemRoles: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'initialisation des rôles système'
            ], 500);
        }
    }
}