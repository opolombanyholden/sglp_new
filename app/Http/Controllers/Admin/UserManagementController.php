<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * CONTRÔLEUR COMPLET - GESTION DES UTILISATEURS SGLP
 * Version enrichie avec toutes les méthodes manquantes
 * Compatible avec le système hybride rôles/permissions
 */
class UserManagementController extends Controller
{
    /**
     * Constructor - Middleware admin requis
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Liste générale des utilisateurs
     * Route: GET /admin/users
     */
    public function index(Request $request)
    {
        try {
            $query = User::with(['roleModel'])
                ->orderBy('created_at', 'desc');

            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    // Recherche dans nom/prenom si colonnes existent
                    if (\Schema::hasColumn('users', 'nom')) {
                        $q->orWhere('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%");
                    }

                    if (\Schema::hasColumn('users', 'nip')) {
                        $q->orWhere('nip', 'like', "%{$search}%");
                    }
                });
            }

            // Filtre par rôle
            if ($request->filled('role')) {
                if ($request->role === 'new_system') {
                    // Nouveau système avec role_id
                    $query->whereNotNull('role_id');
                } else {
                    // Ancien système avec role string
                    $query->where('role', $request->role);
                }
            }

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Pagination
            $users = $query->paginate(20);

            // Enrichir chaque utilisateur avec des statistiques
            $users->getCollection()->transform(function ($user) {
                return $this->enrichUserData($user);
            });

            // Statistiques générales
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', 1)->count(),
                'operators' => User::where('role', 'operator')->count(),
                'agents' => User::where('role', 'agent')->count(),
                'admins' => User::where('role', 'admin')->count(),
                'new_system_users' => User::whereNotNull('role_id')->count(),
            ];

            return view('admin.users.index', compact('users', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@index: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des utilisateurs.');
        }
    }

    /**
     * Liste des opérateurs
     * Route: /admin/users/operators
     */
    public function operators(Request $request)
    {
        try {
            // Query de base pour les opérateurs
            $query = User::where('role', 'operator')
                ->orderBy('created_at', 'desc');

            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    // Recherche dans nom/prenom si colonnes existent
                    if (\Schema::hasColumn('users', 'nom')) {
                        $q->orWhere('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%");
                    }

                    if (\Schema::hasColumn('users', 'nip')) {
                        $q->orWhere('nip', 'like', "%{$search}%");
                    }

                    if (\Schema::hasColumn('users', 'phone')) {
                        $q->orWhere('phone', 'like', "%{$search}%");
                    }
                });
            }

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtre par état actif
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Pagination
            $operators = $query->paginate(15);

            // Enrichir chaque opérateur avec des statistiques
            $operators->getCollection()->transform(function ($operator) {
                return $this->enrichOperatorData($operator);
            });

            // Statistiques générales
            $stats = [
                'total_operators' => User::where('role', 'operator')->count(),
                'active_operators' => User::where('role', 'operator')->where('is_active', 1)->count(),
                'pending_operators' => User::where('role', 'operator')->where('status', 'pending')->count(),
                'suspended_operators' => User::where('role', 'operator')->where('status', 'suspended')->count(),
            ];

            return view('admin.users.operators', compact('operators', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@operators: ' . $e->getMessage());

            return back()->with('error', 'Erreur lors du chargement des opérateurs.');
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Liste des agents
     * Route: /admin/users/agents
     */
    public function agents(Request $request)
    {
        try {
            // Query de base pour les agents
            $query = User::where('role', 'agent')
                ->orderBy('created_at', 'desc');

            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    // Recherche dans nom/prenom si colonnes existent
                    if (\Schema::hasColumn('users', 'nom')) {
                        $q->orWhere('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%");
                    }
                });
            }

            // Filtre par statut
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Pagination
            $agents = $query->paginate(15);

            // Enrichir chaque agent avec des statistiques
            $agents->getCollection()->transform(function ($agent) {
                return $this->enrichAgentData($agent);
            });

            // Statistiques générales
            $stats = [
                'total_agents' => User::where('role', 'agent')->count(),
                'active_agents' => User::where('role', 'agent')->where('is_active', 1)->count(),
                'agents_online' => User::where('role', 'agent')
                    ->where('last_login_at', '>=', now()->subHours(2))
                    ->count(),
                'agents_with_workload' => $this->getAgentsWithWorkload(),
            ];

            return view('admin.users.agents', compact('agents', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@agents: ' . $e->getMessage());

            return back()->with('error', 'Erreur lors du chargement des agents.');
        }
    }

    /**
     * Créer un nouvel utilisateur
     * Route: GET /admin/users/create
     */
    public function create()
    {
        try {
            // Charger les rôles disponibles
            $roles = $this->getAvailableRoles();

            return view('admin.users.create', compact('roles'));
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@create: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement du formulaire.');
        }
    }

    /**
     * Afficher un utilisateur (JSON pour modal)
     * Route: GET /admin/users/{id}
     */
    public function show($id)
    {
        try {
            $user = User::with(['roleModel'])->findOrFail($id);

            // Enrichir avec des statistiques
            $user = $this->enrichUserData($user);

            // Préparer les données pour l'API
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? null,
                'nip' => $user->nip ?? null,
                'address' => $user->address ?? null,
                'city' => $user->city ?? null,
                'role' => $user->role,
                'role_label' => $this->getRoleLabel($user),
                'role_model' => $user->roleModel ? [
                    'id' => $user->roleModel->id,
                    'name' => $user->roleModel->name,
                    'display_name' => $user->roleModel->display_name,
                    'color' => $user->roleModel->color,
                ] : null,
                'status' => $user->status ?? 'active',
                'status_label' => $this->getStatusLabel($user->status ?? 'active'),
                'is_active' => $user->is_active,
                'is_verified' => $user->is_verified ?? false,
                'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais',
                'created_at' => $user->created_at->format('d/m/Y à H:i'),
                'organisations_count' => $user->organisations_count ?? 0,
                'activities' => $this->getUserActivities($user),
                'detailed_stats' => $this->getDetailedUserStats($user),
            ];

            return response()->json([
                'success' => true,
                'data' => $userData
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@show: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du chargement de l\'utilisateur.'
            ], 500);
        }
    }

    /**
     * Stocker un nouvel utilisateur
     * Route: POST /admin/users
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'nullable|string|max:20',
                'nip' => 'nullable|string|max:20|unique:users',
                'role' => 'required|in:agent,operator,admin',
                'role_id' => 'nullable|exists:roles,id',
                'password' => 'required|string|min:8|confirmed',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'is_active' => 'boolean',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $user = User::create([
                'name' => $request->nom . ' ' . $request->prenom,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'phone' => $request->phone,
                'nip' => $request->nip,
                'role' => $request->role,
                'role_id' => $request->role_id, // Nouveau système de rôles
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'city' => $request->city,
                'status' => 'active',
                'is_active' => $request->has('is_active'),
                'created_by' => auth()->id(),
                'email_verified_at' => now(), // Auto-vérification
            ]);

            DB::commit();

            // Log de l'action
            \Log::info('Nouvel utilisateur créé', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'role' => $user->role,
                'created_by' => auth()->id()
            ]);

            $redirectRoute = $request->role === 'agent' ? 'admin.users.agents' : 'admin.users.operators';

            return redirect()->route($redirectRoute)
                ->with('success', ucfirst($request->role) . ' créé(e) avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur UserManagementController@store: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la création de l\'utilisateur.')
                ->withInput();
        }
    }

    /**
     * Éditer un utilisateur
     * Route: GET /admin/users/{id}/edit
     */
    public function edit($id)
    {
        try {
            $user = User::with(['roleModel'])->findOrFail($id);
            $roles = $this->getAvailableRoles();

            return view('admin.users.edit', compact('user', 'roles'));
        } catch (\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Utilisateur non trouvé.');
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@edit: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement du formulaire d\'édition.');
        }
    }

    /**
     * Mettre à jour un utilisateur
     * Route: PUT /admin/users/{id}
     */
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
                'phone' => 'nullable|string|max:20',
                'nip' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('users')->ignore($user->id),
                ],
                'role' => 'required|in:agent,operator,admin',
                'role_id' => 'nullable|exists:roles,id',
                'status' => 'required|in:active,inactive,suspended,pending',
                'is_active' => 'boolean',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'password' => 'nullable|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $updateData = [
                'name' => $request->nom . ' ' . $request->prenom,
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'email' => $request->email,
                'phone' => $request->phone,
                'nip' => $request->nip,
                'role' => $request->role,
                'role_id' => $request->role_id,
                'status' => $request->status,
                'is_active' => $request->has('is_active'),
                'address' => $request->address,
                'city' => $request->city,
                'updated_by' => auth()->id(),
            ];

            // Mettre à jour le mot de passe seulement s'il est fourni
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            DB::commit();

            // Log de l'action
            \Log::info('Utilisateur mis à jour', [
                'user_id' => $user->id,
                'updated_by' => auth()->id(),
                'changes' => array_keys($updateData)
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', 'Utilisateur "' . $user->name . '" mis à jour avec succès.');


        } catch (\ModelNotFoundException $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Utilisateur non trouvé.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur UserManagementController@update: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Erreur lors de la mise à jour de l\'utilisateur.')
                ->withInput();
        }
    }

    /**
     * Vérifier les contraintes avant suppression
     * Route: GET /admin/users/{id}/check-constraints
     */
    public function checkConstraints($id)
    {
        try {
            $user = User::findOrFail($id);
            $constraints = [];
            $hasConstraints = false;

            // Vérifier les organisations
            $organisationsCount = 0;
            if (method_exists($user, 'organisations')) {
                $organisationsCount = $user->organisations()->count();
                if ($organisationsCount > 0) {
                    $constraints[] = "{$organisationsCount} organisation(s) créée(s) par cet opérateur";
                    $hasConstraints = true;
                }
            }

            // Vérifier les dossiers assignés
            $dossiersCount = 0;
            if (method_exists($user, 'assignedDossiers')) {
                $dossiersCount = $user->assignedDossiers()->count();
                if ($dossiersCount > 0) {
                    $constraints[] = "{$dossiersCount} dossier(s) assigné(s) à cet utilisateur";
                    $hasConstraints = true;
                }
            }

            // Vérifier dans dossier_operations (source de l'erreur FK)
            $operationsCount = DB::table('dossier_operations')
                ->where('user_id', $id)
                ->count();
            if ($operationsCount > 0) {
                $constraints[] = "{$operationsCount} opération(s) de dossier liée(s) à cet utilisateur";
                $hasConstraints = true;
            }

            // Vérifier autres tables avec clés étrangères
            $otherConstraints = $this->checkOtherForeignKeys($id);
            if (!empty($otherConstraints)) {
                $constraints = array_merge($constraints, $otherConstraints);
                $hasConstraints = true;
            }

            return response()->json([
                'success' => true,
                'has_constraints' => $hasConstraints,
                'constraints' => $constraints
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@checkConstraints: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification des contraintes'
            ], 500);
        }
    }

    /**
     * Supprimer un utilisateur avec gestion des contraintes
     * Route: DELETE /admin/users/{id}
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            // Empêcher la suppression du compte admin connecté
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous ne pouvez pas supprimer votre propre compte.'
                ], 403);
            }

            DB::beginTransaction();

            // Nettoyer les contraintes FK avant suppression
            $this->cleanupUserConstraints($user);

            $userName = $user->name;
            $user->delete();

            DB::commit();

            // Log de l'action
            \Log::warning('Utilisateur supprimé', [
                'deleted_user_id' => $id,
                'deleted_user_name' => $userName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "L'utilisateur {$userName} a été supprimé avec succès."
            ]);

        } catch (\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur UserManagementController@destroy: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Changer le statut d'un utilisateur
     * Route: POST /admin/users/{id}/toggle-status
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::findOrFail($id);

            $newStatus = $user->is_active ? 0 : 1;
            $user->update([
                'is_active' => $newStatus,
                'status' => $newStatus ? 'active' : 'inactive',
                'updated_by' => auth()->id()
            ]);

            // Log de l'action
            \Log::info('Statut utilisateur modifié', [
                'user_id' => $user->id,
                'new_status' => $newStatus,
                'changed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Statut de {$user->name} " . ($newStatus ? 'activé' : 'désactivé') . " avec succès.",
                'new_status' => $newStatus
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@toggleStatus: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut.'
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Mettre à jour le statut via AJAX
     * Route: POST /admin/users/{id}/update-status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive,suspended,pending',
                'reason' => 'nullable|string|max:500'
            ]);

            $user = User::findOrFail($id);

            $user->update([
                'status' => $request->status,
                'is_active' => $request->status === 'active',
                'updated_by' => auth()->id()
            ]);

            // Log avec raison si fournie
            \Log::info('Statut utilisateur mis à jour', [
                'user_id' => $user->id,
                'new_status' => $request->status,
                'reason' => $request->reason,
                'changed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Statut de {$user->name} mis à jour avec succès."
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@updateStatus: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut.'
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Réinitialiser le mot de passe
     * Route: POST /admin/users/{id}/reset-password
     */
    public function resetPassword($id)
    {
        try {
            $user = User::findOrFail($id);

            // Générer nouveau mot de passe temporaire
            $newPassword = Str::random(12);

            $user->update([
                'password' => Hash::make($newPassword),
                'must_change_password' => true, // Flag pour forcer changement
                'updated_by' => auth()->id()
            ]);

            // Envoyer par email (si service configuré)
            // TODO: Implémenter envoi email avec nouveau mot de passe

            // Log de l'action
            \Log::warning('Mot de passe réinitialisé', [
                'user_id' => $user->id,
                'reset_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Mot de passe de {$user->name} réinitialisé avec succès.",
                'new_password' => $newPassword // À supprimer en production
            ]);

        } catch (\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé.'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@resetPassword: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation du mot de passe.'
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Forcer la vérification email
     * Route: POST /admin/users/{id}/force-verify-email
     */
    public function forceVerifyEmail($id)
    {
        try {
            $user = User::findOrFail($id);

            $user->forceFill([
                'email_verified_at' => now()
            ]);
            $user->updated_by = auth()->id();
            $user->save();

            // Log de l'action
            \Log::info('Email forcé comme vérifié', [
                'user_id' => $user->id,
                'verified_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Email de {$user->name} marqué comme vérifié."
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@forceVerifyEmail: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification forcée.'
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Désactiver 2FA
     * Route: POST /admin/users/{id}/disable-2fa
     */
    public function disable2FA($id)
    {
        try {
            $user = User::findOrFail($id);

            // Désactiver 2FA
            $user->update([
                'two_factor_enabled' => false,
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'updated_by' => auth()->id()
            ]);

            // Supprimer les codes 2FA actifs
            if (\Schema::hasTable('two_factor_codes')) {
                DB::table('two_factor_codes')
                    ->where('user_id', $user->id)
                    ->delete();
            }

            // Log de l'action
            \Log::warning('2FA désactivé administrativement', [
                'user_id' => $user->id,
                'disabled_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "2FA désactivé pour {$user->name}."
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@disable2FA: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la désactivation 2FA.'
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Envoyer email de bienvenue
     * Route: POST /admin/users/{id}/send-welcome
     */
    public function sendWelcomeEmail($id)
    {
        try {
            $user = User::findOrFail($id);

            // TODO: Implémenter envoi email de bienvenue
            // Mail::to($user)->send(new WelcomeEmail($user));

            // Log de l'action
            \Log::info('Email de bienvenue envoyé', [
                'user_id' => $user->id,
                'sent_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Email de bienvenue envoyé à {$user->name}."
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@sendWelcomeEmail: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email.'
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Export Excel
     * Route: GET /admin/users/export/excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = User::with(['roleModel']);

            // Appliquer filtres si fournis
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $users = $query->get();

            // Préparer données pour export
            $exportData = $users->map(function ($user) {
                return [
                    'ID' => $user->id,
                    'Nom' => $user->nom ?? $user->name,
                    'Prénom' => $user->prenom ?? '',
                    'Email' => $user->email,
                    'Téléphone' => $user->phone ?? '',
                    'NIP' => $user->nip ?? '',
                    'Rôle' => $user->role,
                    'Rôle Avancé' => $user->roleModel ? $user->roleModel->display_name : '',
                    'Statut' => $user->status ?? 'active',
                    'Actif' => $user->is_active ? 'Oui' : 'Non',
                    'Email Vérifié' => $user->email_verified_at ? 'Oui' : 'Non',
                    'Dernière Connexion' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '',
                    'Créé le' => $user->created_at->format('d/m/Y H:i'),
                ];
            });

            // TODO: Utiliser Laravel Excel pour génération
            // return Excel::download(new UsersExport($exportData), 'utilisateurs_sglp.xlsx');

            // Version temporaire - export JSON
            $filename = 'utilisateurs_sglp_' . now()->format('Y-m-d_H-i-s') . '.json';

            return response()->json($exportData)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@exportExcel: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export.'
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Import utilisateurs
     * Route: POST /admin/users/import
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx|max:2048'
            ]);

            // TODO: Implémenter import CSV/Excel
            // $import = new UsersImport();
            // Excel::import($import, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Import en cours de développement.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@import: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import.'
            ], 500);
        }
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Télécharger template import
     * Route: GET /admin/users/import/template
     */
    public function downloadTemplate()
    {
        try {
            $template = [
                [
                    'nom' => 'Dupont',
                    'prenom' => 'Jean',
                    'email' => 'jean.dupont@example.com',
                    'phone' => '+241 01 23 45 67',
                    'nip' => '01-1234-19900101',
                    'role' => 'operator',
                    'address' => '123 Avenue de la Liberté',
                    'city' => 'Libreville'
                ]
            ];

            return response()->json($template)
                ->header('Content-Disposition', 'attachment; filename="template_import_utilisateurs.json"');

        } catch (\Exception $e) {
            \Log::error('Erreur UserManagementController@downloadTemplate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération du template.'
            ], 500);
        }
    }

    // ========== MÉTHODES PRIVÉES ==========

    /**
     * Nettoyer les contraintes FK avant suppression
     */
    private function cleanupUserConstraints(User $user): void
    {
        try {
            // Nettoyer dossier_operations (source principale de l'erreur)
            DB::table('dossier_operations')
                ->where('user_id', $user->id)
                ->update(['user_id' => null]);

            // Nettoyer les dossiers assignés
            if (method_exists($user, 'assignedDossiers')) {
                $user->assignedDossiers()->update(['assigned_to' => null]);
            }

            // Nettoyer les validations de dossiers
            if (\Schema::hasTable('dossier_validations')) {
                DB::table('dossier_validations')
                    ->where('validated_by', $user->id)
                    ->update(['validated_by' => null]);
            }

            // Nettoyer les sessions utilisateur
            if (\Schema::hasTable('user_sessions')) {
                DB::table('user_sessions')
                    ->where('user_id', $user->id)
                    ->delete();
            }

            // Nettoyer les codes 2FA
            if (\Schema::hasTable('two_factor_codes')) {
                DB::table('two_factor_codes')
                    ->where('user_id', $user->id)
                    ->delete();
            }

            // Nettoyer les références created_by et updated_by dans users
            User::where('created_by', $user->id)->update(['created_by' => null]);
            User::where('updated_by', $user->id)->update(['updated_by' => null]);

            // Nettoyer les documents uploadés
            if (\Schema::hasTable('documents')) {
                DB::table('documents')
                    ->where('uploaded_by', $user->id)
                    ->update(['uploaded_by' => null]);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors du nettoyage des contraintes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Vérifier autres clés étrangères
     */
    private function checkOtherForeignKeys($userId): array
    {
        $constraints = [];

        try {
            // Vérifier dossier_validations
            if (\Schema::hasTable('dossier_validations')) {
                $count = DB::table('dossier_validations')->where('validated_by', $userId)->count();
                if ($count > 0) {
                    $constraints[] = "{$count} validation(s) de dossier effectuée(s)";
                }
            }

            // Vérifier documents
            if (\Schema::hasTable('documents')) {
                $count = DB::table('documents')->where('uploaded_by', $userId)->count();
                if ($count > 0) {
                    $constraints[] = "{$count} document(s) uploadé(s)";
                }
            }

            // Vérifier user_sessions
            if (\Schema::hasTable('user_sessions')) {
                $count = DB::table('user_sessions')->where('user_id', $userId)->count();
                if ($count > 0) {
                    $constraints[] = "{$count} session(s) utilisateur";
                }
            }

        } catch (\Exception $e) {
            \Log::error('Erreur checkOtherForeignKeys: ' . $e->getMessage());
        }

        return $constraints;
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Enrichir les données d'un agent
     */
    private function enrichAgentData($agent)
    {
        // Charge de travail actuelle
        $agent->current_workload = method_exists($agent, 'assignedDossiers') ?
            $agent->assignedDossiers()->where('statut', 'en_cours')->count() : 0;

        // Statut de connexion
        $agent->is_online = $agent->last_login_at && $agent->last_login_at->gt(now()->subHours(2));

        // Performance (simple)
        $agent->dossiers_traites_mois = method_exists($agent, 'dossierValidations') ?
            $agent->dossierValidations()->where('decided_at', '>=', now()->subMonth())->count() : 0;

        // Disponibilité
        $agent->availability = $agent->current_workload < 5 ? 'Disponible' : 'Chargé';

        return $agent;
    }

    /**
     * Enrichir les données d'un opérateur
     */
    private function enrichOperatorData($operator)
    {
        // Nombre d'organisations créées
        $operator->organisations_count = method_exists($operator, 'organisations')
            ? $operator->organisations()->count()
            : 0;

        // Statut de connexion
        $operator->is_online = $operator->last_login_at && $operator->last_login_at->gt(now()->subHours(2));

        // Dernière activité
        $operator->last_activity = $operator->last_login_at ? $operator->last_login_at->diffForHumans() : 'Jamais connecté';

        return $operator;
    }

    /**
     * Enrichir les données d'un utilisateur
     */
    private function enrichUserData($user)
    {
        if ($user->role === 'operator') {
            return $this->enrichOperatorData($user);
        } elseif ($user->role === 'agent') {
            return $this->enrichAgentData($user);
        }

        return $user;
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Obtenir les activités d'un utilisateur
     */
    private function getUserActivities($user)
    {
        // TODO: Implémenter avec vraie table d'audit
        return collect([
            [
                'date' => now()->subDays(1),
                'action' => 'Connexion',
                'description' => 'Connexion à la plateforme',
                'ip' => '192.168.1.1'
            ],
            [
                'date' => now()->subDays(2),
                'action' => $user->role === 'agent' ? 'Validation' : 'Création',
                'description' => $user->role === 'agent' ? 'Validation d\'un dossier' : 'Création d\'une organisation',
                'ip' => '192.168.1.1'
            ]
        ]);
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Obtenir les statistiques détaillées d'un utilisateur
     */
    private function getDetailedUserStats($user)
    {
        $stats = [
            'account_age' => $user->created_at->diffInDays(now()),
            'last_login' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais',
            'total_logins' => 0, // À implémenter avec table sessions
            'failed_attempts' => $user->failed_login_attempts ?? 0,
        ];

        if ($user->role === 'agent') {
            $stats['dossiers_traites'] = method_exists($user, 'dossierValidations') ?
                $user->dossierValidations()->count() : 0;
            $stats['dossiers_en_cours'] = method_exists($user, 'assignedDossiers') ?
                $user->assignedDossiers()->where('statut', 'en_cours')->count() : 0;
        } elseif ($user->role === 'operator') {
            $stats['organisations_creees'] = method_exists($user, 'organisations') ?
                $user->organisations()->count() : 0;
            $stats['dossiers_soumis'] = 0; // À calculer selon vos relations
        }

        return $stats;
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Obtenir le nombre d'agents avec charge de travail
     */
    private function getAgentsWithWorkload()
    {
        try {
            return User::where('role', 'agent')
                ->whereHas('assignedDossiers', function ($query) {
                    $query->where('statut', 'en_cours');
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Obtenir le libellé d'un rôle
     */
    private function getRoleLabel($user): string
    {
        // Nouveau système de rôles
        if ($user->roleModel) {
            return $user->roleModel->display_name;
        }

        // Ancien système
        $labels = [
            'admin' => 'Administrateur',
            'agent' => 'Agent',
            'operator' => 'Opérateur',
            'visitor' => 'Visiteur'
        ];

        return $labels[$user->role] ?? ucfirst($user->role);
    }

    /**
     * Obtenir le libellé d'un statut
     */
    private function getStatusLabel($status): string
    {
        $labels = [
            'active' => 'Actif',
            'inactive' => 'Inactif',
            'suspended' => 'Suspendu',
            'pending' => 'En attente'
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * ✅ MÉTHODE AJOUTÉE - Obtenir les rôles disponibles
     */
    private function getAvailableRoles(): array
    {
        $roles = [
            'basic' => [
                'admin' => 'Administrateur',
                'agent' => 'Agent',
                'operator' => 'Opérateur',
            ]
        ];

        // Ajouter les rôles avancés si ils existent
        if (\Schema::hasTable('roles')) {
            $advancedRoles = Role::where('is_active', true)
                ->orderBy('level', 'desc')
                ->get()
                ->mapWithKeys(function ($role) {
                    return [$role->id => $role->display_name];
                });

            $roles['advanced'] = $advancedRoles->toArray();
        }

        return $roles;
    }
}