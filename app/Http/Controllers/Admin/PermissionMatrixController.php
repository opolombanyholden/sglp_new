<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissionMatrixController extends Controller
{
    /**
     * Afficher la matrice des permissions
     */
    public function index()
    {
        // Ne pas calculer les stats ici pour éviter les erreurs
        // Les stats seront chargées via AJAX
        return view('admin.permissions.matrix');
    }

    /**
     * Obtenir les données de la matrice via API
     */
    public function data(Request $request)
    {
        try {
            $filters = $request->get('filters', []);
            
            // Charger les rôles avec leurs permissions
            $rolesQuery = Role::with('permissions');
            
            if (!empty($filters['roleType'])) {
                if ($filters['roleType'] === 'system') {
                    $rolesQuery->where('is_system', true);
                } else {
                    $rolesQuery->where('is_system', false);
                }
            }
            
            $roles = $rolesQuery->get()->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'display_name' => $role->display_name ?? $role->name,
                    'level' => $role->level ?? 1,
                    'is_system' => $role->is_system ?? false,
                    'permissions' => $role->permissions->pluck('id')->toArray()
                ];
            });

            // Charger les permissions
            $permissionsQuery = Permission::query();
            
            if (!empty($filters['category'])) {
                $permissionsQuery->where('category', $filters['category']);
            }
            
            if (!empty($filters['risk'])) {
                // Filtrage par risque sera fait côté application
                // car risk_level est un attribut calculé
            }
            
            $permissions = $permissionsQuery->get()->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'display_name' => $permission->display_name ?? $permission->name,
                    'category' => $permission->category ?? 'other',
                    'risk_level' => $permission->risk_level // Utilise l'attribut calculé
                ];
            });

            // Filtrer par risque si nécessaire
            if (!empty($filters['risk'])) {
                $permissions = $permissions->filter(function ($permission) use ($filters) {
                    return $permission['risk_level'] === $filters['risk'];
                })->values();
            }

            $stats = $this->calculateStatistics();

            return response()->json([
                'success' => true,
                'data' => [
                    'roles' => $roles,
                    'permissions' => $permissions
                ],
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans PermissionMatrixController@data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement des données: ' . $e->getMessage(),
                'data' => [
                    'roles' => [],
                    'permissions' => []
                ],
                'stats' => $this->calculateStatistics()
            ], 500);
        }
    }

    /**
     * Mettre à jour les associations permissions-rôles
     */
    public function update(Request $request)
    {
        $request->validate([
            'changes' => 'required|array',
            'changes.*.roleId' => 'required|integer|exists:roles,id',
            'changes.*.permissionId' => 'required|integer|exists:permissions,id',
            'changes.*.action' => 'required|in:add,remove'
        ]);

        try {
            DB::beginTransaction();

            $successCount = 0;
            
            foreach ($request->changes as $change) {
                $role = Role::findOrFail($change['roleId']);
                $permission = Permission::findOrFail($change['permissionId']);

                if ($change['action'] === 'add') {
                    if (!$role->permissions()->where('permissions.id', $permission->id)->exists()) {
                        $role->permissions()->attach($permission->id, [
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        $successCount++;
                    }
                } else {
                    if ($role->permissions()->where('permissions.id', $permission->id)->exists()) {
                        $role->permissions()->detach($permission->id);
                        $successCount++;
                    }
                }
            }

            DB::commit();

            $stats = $this->calculateStatistics();

            return response()->json([
                'success' => true,
                'message' => "$successCount modification(s) appliquée(s) avec succès",
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Effectuer un audit des permissions
     */
    public function audit(Request $request)
    {
        try {
            $audit = [
                'security_alerts' => $this->getSecurityAlerts(),
                'recommendations' => $this->getRecommendations(),
                'unused_permissions' => $this->getUnusedPermissions(),
                'overprivileged_roles' => $this->getOverprivilegedRoles(),
                'underprivileged_roles' => $this->getUnderprivilegedRoles(),
                'duplicate_permissions' => $this->getDuplicatePermissions()
            ];

            return response()->json([
                'success' => true,
                'data' => $audit
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'audit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exporter la matrice
     */
    public function export(Request $request)
    {
        try {
            $filters = $request->get('filters', []);
            $view = $request->get('view', 'matrix');

            // Génération du fichier Excel avec PhpSpreadsheet
            // Cette partie nécessite l'installation de PhpSpreadsheet
            
            $filename = 'matrice-permissions-' . date('Y-m-d-H-i') . '.xlsx';
            
            // Pour l'instant, retourner un CSV simple
            $csvData = $this->generateCSVData($filters);
            
            return response($csvData, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer les statistiques
     */
    private function calculateStatistics()
    {
        try {
            $totalRoles = Role::count();
            $totalPermissions = Permission::count();
            
            // ✅ CORRECTION : Utiliser le bon nom de table
            $totalAssignments = DB::table('role_permissions')->count();
            
            $coverageRate = $totalPermissions > 0 ? 
                (Permission::whereHas('roles')->count() / $totalPermissions) * 100 : 0;
                
            $unusedPermissions = Permission::whereDoesntHave('roles')->count();
            
            // ✅ CORRECTION : Gérer le cas où la colonne risk_level n'existe pas
            $highRiskCount = 0;
            if (Schema::hasColumn('permissions', 'risk_level')) {
                $highRiskCount = Permission::where('risk_level', 'high')->count();
            }
            
            return [
                'total_roles' => $totalRoles,
                'total_permissions' => $totalPermissions,
                'total_assignments' => $totalAssignments,
                'coverage_rate' => round($coverageRate, 1),
                'unused_permissions' => $unusedPermissions,
                'high_risk_count' => $highRiskCount
            ];
            
        } catch (\Exception $e) {
            // Retourner des valeurs par défaut en cas d'erreur
            return [
                'total_roles' => 0,
                'total_permissions' => 0,
                'total_assignments' => 0,
                'coverage_rate' => 0,
                'unused_permissions' => 0,
                'high_risk_count' => 0
            ];
        }
    }

    /**
     * Obtenir les catégories de permissions
     */
    private function getCategories()
    {
        return [
            'users' => [
                'label' => 'Utilisateurs',
                'icon' => 'fas fa-users',
                'description' => 'Gestion des utilisateurs'
            ],
            'organizations' => [
                'label' => 'Organisations',
                'icon' => 'fas fa-building',
                'description' => 'Gestion des organisations'
            ],
            'workflow' => [
                'label' => 'Workflow',
                'icon' => 'fas fa-project-diagram',
                'description' => 'Gestion des processus'
            ],
            'system' => [
                'label' => 'Système',
                'icon' => 'fas fa-cogs',
                'description' => 'Configuration système'
            ],
            'content' => [
                'label' => 'Contenu',
                'icon' => 'fas fa-file-alt',
                'description' => 'Gestion du contenu'
            ],
            'reports' => [
                'label' => 'Rapports',
                'icon' => 'fas fa-chart-bar',
                'description' => 'Génération de rapports'
            ]
        ];
    }

    /**
     * Obtenir les alertes de sécurité
     */
    private function getSecurityAlerts()
    {
        $alerts = [];

        // Rôles avec trop de permissions critiques
        $criticalRoles = Role::whereHas('permissions', function ($query) {
            $query->where('risk_level', 'high');
        })->withCount(['permissions' => function ($query) {
            $query->where('risk_level', 'high');
        }])->having('permissions_count', '>', 5)->get();

        if ($criticalRoles->count() > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Rôles avec permissions critiques excessives détectés",
                'count' => $criticalRoles->count(),
                'details' => $criticalRoles->pluck('display_name')->toArray()
            ];
        }

        // Permissions non utilisées depuis longtemps
        $unusedPermissions = Permission::whereDoesntHave('roles')->count();
        if ($unusedPermissions > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "Permissions non utilisées détectées",
                'count' => $unusedPermissions
            ];
        }

        return $alerts;
    }

    /**
     * Obtenir les recommandations
     */
    private function getRecommendations()
    {
        $recommendations = [];

        // Recommandation de consolidation
        $duplicatePermissions = $this->getDuplicatePermissions();
        if (count($duplicatePermissions) > 0) {
            $recommendations[] = [
                'type' => 'optimization',
                'message' => "Consolidez les permissions similaires",
                'priority' => 'medium'
            ];
        }

        // Recommandation d'audit régulier
        $recommendations[] = [
            'type' => 'security',
            'message' => "Effectuez un audit de sécurité mensuel",
            'priority' => 'high'
        ];

        return $recommendations;
    }

    /**
     * Autres méthodes d'assistance
     */
    private function getUnusedPermissions()
    {
        return Permission::whereDoesntHave('roles')
            ->select('id', 'name', 'display_name', 'category')
            ->get()
            ->toArray();
    }

    private function getOverprivilegedRoles()
    {
        return Role::withCount('permissions')
            ->having('permissions_count', '>', 20)
            ->get()
            ->toArray();
    }

    private function getUnderprivilegedRoles()
    {
        return Role::withCount('permissions')
            ->having('permissions_count', '<', 3)
            ->where('level', '>', 2)
            ->get()
            ->toArray();
    }

    private function getDuplicatePermissions()
    {
        // Logique pour détecter les permissions similaires
        return [];
    }

    private function generateCSVData($filters)
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        $csv = "Role,";
        $csv .= $permissions->pluck('display_name')->implode(',') . "\n";

        foreach ($roles as $role) {
            $csv .= $role->display_name . ",";
            foreach ($permissions as $permission) {
                $hasPermission = $role->permissions->contains('id', $permission->id);
                $csv .= ($hasPermission ? 'OUI' : 'NON') . ",";
            }
            $csv .= "\n";
        }

        return $csv;
    }
}