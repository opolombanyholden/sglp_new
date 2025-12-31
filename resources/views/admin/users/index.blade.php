{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Gestion des Utilisateurs')

@section('content')
    <div class="container-fluid">
        <!-- Header avec style gabonais moderne -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                    <div class="card-body text-white">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">
                                    <i class="fas fa-users me-2"></i>
                                    Gestion des Utilisateurs
                                </h2>
                                <p class="mb-0 opacity-90">Supervision complète de tous les comptes utilisateurs SGLP</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-user-plus me-2"></i>Nouvel Utilisateur
                                    </a>
                                    <button type="button" class="btn btn-outline-light btn-lg" onclick="location.reload()">
                                        <i class="fas fa-sync me-2"></i>Actualiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques Cards avec style gabonais -->
        <div class="row mb-4">
            <div class="col-md-2-5 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['total_users'] ?? 0 }}</h3>
                                <p class="mb-0 small opacity-90">Total Utilisateurs</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2-5 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['operators'] ?? 0 }}</h3>
                                <p class="mb-0 small opacity-90">Opérateurs</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-user-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 80%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2-5 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                    <div class="card-body text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['agents'] ?? 0 }}</h3>
                                <p class="mb-0 small">Agents</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-user-tie fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-dark" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2-5 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['admins'] ?? 0 }}</h3>
                                <p class="mb-0 small opacity-90">Administrateurs</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-user-shield fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 40%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-2-5 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['active_users'] ?? 0 }}</h3>
                                <p class="mb-0 small opacity-90">Comptes Actifs</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 90%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides modernisées -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="fas fa-bolt me-2" style="color: #ffcd00;"></i>
                            Actions Rapides
                        </h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.users.create') }}"
                                    class="card h-100 text-decoration-none quick-action-card">
                                    <div class="card-body text-center">
                                        <div class="bg-success rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                                            style="width: 60px; height: 60px; font-size: 1.5rem;">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <h6 class="fw-bold text-primary">Nouvel Utilisateur</h6>
                                        <p class="small text-muted mb-0">Créer un compte opérateur ou agent</p>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.users.operators') }}"
                                    class="card h-100 text-decoration-none quick-action-card">
                                    <div class="card-body text-center">
                                        <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                                            style="width: 60px; height: 60px; font-size: 1.5rem;">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <h6 class="fw-bold text-primary">Opérateurs</h6>
                                        <p class="small text-muted mb-0">Gérer les comptes opérateurs</p>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.users.agents') }}"
                                    class="card h-100 text-decoration-none quick-action-card">
                                    <div class="card-body text-center">
                                        <div class="bg-warning rounded-circle text-dark d-flex align-items-center justify-content-center mx-auto mb-3"
                                            style="width: 60px; height: 60px; font-size: 1.5rem;">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <h6 class="fw-bold text-primary">Agents</h6>
                                        <p class="small text-muted mb-0">Gérer les comptes agents</p>
                                    </div>
                                </a>
                            </div>

                            <div class="col-md-3 mb-3">
                                <a href="{{ route('admin.users.export.excel') }}"
                                    class="card h-100 text-decoration-none quick-action-card">
                                    <div class="card-body text-center">
                                        <div class="bg-danger rounded-circle text-white d-flex align-items-center justify-content-center mx-auto mb-3"
                                            style="width: 60px; height: 60px; font-size: 1.5rem;">
                                            <i class="fas fa-download"></i>
                                        </div>
                                        <h6 class="fw-bold text-primary">Export</h6>
                                        <p class="small text-muted mb-0">Télécharger la liste complète</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres de recherche modernisés -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-search text-success me-2"></i>
                                    Recherche
                                </label>
                                <input type="text" name="search" class="form-control form-control-lg"
                                    placeholder="Nom, email, NIP, téléphone..." value="{{ request('search') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-user-tag text-primary me-2"></i>
                                    Rôle
                                </label>
                                <select name="role" class="form-select form-select-lg">
                                    <option value="">Tous les rôles</option>
                                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrateur
                                    </option>
                                    <option value="agent" {{ request('role') === 'agent' ? 'selected' : '' }}>Agent</option>
                                    <option value="operator" {{ request('role') === 'operator' ? 'selected' : '' }}>
                                        Opérateur</option>
                                    <option value="new_system" {{ request('role') === 'new_system' ? 'selected' : '' }}>
                                        Système avancé</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-chart-line text-info me-2"></i>
                                    Statut
                                </label>
                                <select name="status" class="form-select form-select-lg">
                                    <option value="">Tous les statuts</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif
                                    </option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                        Inactif</option>
                                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>
                                        Suspendu</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En
                                        attente</option>
                                </select>
                            </div>

                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-search me-2"></i>Filtrer
                                </button>
                            </div>

                            <div class="col-md-2">
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-lg w-100">
                                    <i class="fas fa-times me-2"></i>Effacer
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des utilisateurs modernisé -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-table me-2" style="color: #003f7f;"></i>
                                Liste des Utilisateurs
                            </h5>
                            <span class="badge bg-primary">{{ $users->total() ?? 0 }} utilisateurs</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($users) && count($users) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">Utilisateur</th>
                                            <th class="border-0">Rôle</th>
                                            <th class="border-0">Statut</th>
                                            <th class="border-0">Dernière connexion</th>
                                            <th class="border-0">Statistiques</th>
                                            <th class="border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($users as $user)
                                            <tr class="user-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar me-3">
                                                            @php
                                                                $initials = '';
                                                                if ($user->nom && $user->prenom) {
                                                                    $initials = strtoupper(substr($user->nom, 0, 1) . substr($user->prenom, 0, 1));
                                                                } elseif ($user->name) {
                                                                    $nameParts = explode(' ', trim($user->name));
                                                                    $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                                                                } else {
                                                                    $initials = strtoupper(substr($user->email, 0, 2));
                                                                }
                                                            @endphp
                                                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                                                style="width: 45px; height: 45px; font-weight: bold;">
                                                                {{ $initials }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">{{ $user->name }}</h6>
                                                            <small class="text-muted">{{ $user->email }}</small>
                                                            @if($user->phone)
                                                                <br><small class="text-info">{{ $user->phone }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($user->roleModel)
                                                        <span class="badge"
                                                            style="background: linear-gradient(135deg, #6f42c1, #5a32a3); color: white;"
                                                            title="Système avancé de rôles">
                                                            {{ $user->roleModel->display_name }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="badge {{ $user->role === 'admin' ? 'bg-danger' : ($user->role === 'agent' ? 'bg-warning text-dark' : 'bg-primary') }}">
                                                            {{ ucfirst($user->role) }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $status = $user->status ?? 'active';
                                                        $isActive = $user->is_active;
                                                        if (!$isActive)
                                                            $status = 'inactive';
                                                    @endphp
                                                    <span
                                                        class="badge {{ $status === 'active' ? 'bg-success' : ($status === 'suspended' ? 'bg-danger' : 'bg-secondary') }}">
                                                        @if($status === 'active')
                                                            <i class="fas fa-check-circle me-1"></i>Actif
                                                        @elseif($status === 'inactive')
                                                            <i class="fas fa-pause-circle me-1"></i>Inactif
                                                        @elseif($status === 'suspended')
                                                            <i class="fas fa-ban me-1"></i>Suspendu
                                                        @elseif($status === 'pending')
                                                            <i class="fas fa-hourglass-half me-1"></i>En attente
                                                        @endif
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($user->last_login_at)
                                                        <div class="d-flex align-items-center">
                                                            @php $isOnline = $user->last_login_at->gt(now()->subHours(2)); @endphp
                                                            <div class="rounded-circle {{ $isOnline ? 'bg-success' : 'bg-secondary' }}"
                                                                style="width: 8px; height: 8px; margin-right: 8px;"></div>
                                                            <span title="{{ $user->last_login_at->format('d/m/Y H:i:s') }}">
                                                                {{ $user->last_login_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                        @if($isOnline)
                                                            <small class="text-success">En ligne</small>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">Jamais connecté</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="text-center">
                                                        @if($user->role === 'operator')
                                                            <div class="text-primary">
                                                                <i class="fas fa-building me-1"></i>
                                                                <strong>{{ $user->organisations_count ?? 0 }}</strong>
                                                            </div>
                                                            <small class="text-muted">Organisations</small>
                                                        @elseif($user->role === 'agent')
                                                            <div class="text-info">
                                                                <i class="fas fa-tasks me-1"></i>
                                                                <strong>{{ $user->current_workload ?? 0 }}</strong>
                                                            </div>
                                                            <small class="text-muted">Dossiers</small>
                                                            @if(isset($user->availability))
                                                                <br><span class="badge bg-light text-dark">{{ $user->availability }}</span>
                                                            @endif
                                                        @else
                                                            <div class="text-secondary">
                                                                <i class="fas fa-user me-1"></i>
                                                                <strong>{{ ucfirst($user->role) }}</strong>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                            onclick="viewUser({{ $user->id }})" title="Voir détails">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="{{ route('admin.users.edit', $user->id) }}"
                                                            class="btn btn-sm btn-outline-success" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        @if($user->id !== auth()->id())
                                                            <div class="btn-group" role="group">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                    data-toggle="dropdown" title="Plus d'actions">
                                                                    <i class="fas fa-ellipsis-v"></i>
                                                                </button>
                                                                <ul class="dropdown-menu">
                                                                    <li><a class="dropdown-item" href="#"
                                                                            onclick="toggleUserStatus({{ $user->id }})">
                                                                            <i class="fas fa-power-off me-2"></i>
                                                                            {{ $user->is_active ? 'Désactiver' : 'Activer' }}
                                                                        </a></li>
                                                                    <li><a class="dropdown-item" href="#"
                                                                            onclick="resetPassword({{ $user->id }})">
                                                                            <i class="fas fa-key me-2"></i>Réinitialiser mot de passe
                                                                        </a></li>
                                                                    @if(!$user->email_verified_at)
                                                                        <li><a class="dropdown-item text-success" href="#"
                                                                                onclick="activateAccount({{ $user->id }})">
                                                                                <i class="fas fa-user-check me-2"></i>Activer le compte
                                                                            </a></li>
                                                                    @endif
                                                                    <li>
                                                                        <hr class="dropdown-divider">
                                                                    </li>
                                                                    <li><a class="dropdown-item text-danger" href="#"
                                                                            onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                                                            <i class="fas fa-trash me-2"></i>Supprimer
                                                                        </a></li>
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination moderne -->
                            @if($users->hasPages())
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div class="text-muted">
                                        Affichage de {{ $users->firstItem() }} à {{ $users->lastItem() }} sur {{ $users->total() }}
                                        utilisateurs
                                    </div>
                                    <div>
                                        {{ $users->appends(request()->query())->links() }}
                                    </div>
                                </div>
                            @endif
                        @else
                            <!-- État vide avec style gabonais -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-users fa-5x text-muted opacity-50"></i>
                                </div>
                                <h4 class="text-muted mb-3">Aucun utilisateur trouvé</h4>
                                <p class="text-muted mb-4">
                                    @if(request()->hasAny(['search', 'role', 'status']))
                                        Aucun utilisateur ne correspond aux critères de recherche.
                                    @else
                                        Aucun utilisateur n'est encore enregistré dans le système.
                                    @endif
                                </p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-lg">
                                        <i class="fas fa-plus me-2"></i>Créer le premier utilisateur
                                    </a>
                                    @if(request()->hasAny(['search', 'role', 'status']))
                                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-lg">
                                            <i class="fas fa-times me-2"></i>Effacer les filtres
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Affichage Utilisateur -->
    <div class="modal fade" id="viewUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user me-2"></i>Détails de l'utilisateur
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userModalContent">
                    <!-- Contenu chargé via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-success" id="editUserFromModal">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmation Suppression -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer l'utilisateur <strong id="deleteUserName"></strong> ?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Cette action est irréversible et supprimera toutes les données associées.
                    </div>
                    <div id="constraintsWarning" style="display: none;">
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Contraintes détectées :</h6>
                            <ul id="constraintsList"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteUserBtn">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Styles gabonais complémentaires pour Bootstrap 5 */
        .col-md-2-5 {
            flex: 0 0 20%;
            max-width: 20%;
        }

        @media (max-width: 768px) {
            .col-md-2-5 {
                flex: 0 0 50%;
                max-width: 50%;
            }
        }

        .stats-card:hover {
            transform: translateY(-3px);
            transition: transform 0.3s ease;
        }

        .quick-action-card:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-decoration: none;
        }

        .user-row:hover {
            background-color: rgba(0, 158, 63, 0.05);
        }

        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
        }

        /* Animation d'entrée */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Notification moderne */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
    </style>

    <script>
        // JavaScript Bootstrap 5 compatible
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Module Gestion Utilisateurs SGLP chargé');
        });

        let currentUserId = null;

        // Voir les détails d'un utilisateur
        function viewUser(userId) {
            fetch(`/admin/users/${userId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const user = data.data;

                        const html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Informations personnelles
                                </h6>
                                <table class="table table-sm table-borderless">
                                    <tr><td class="fw-bold" style="width: 40%;">Nom complet:</td><td>${user.name}</td></tr>
                                    <tr><td class="fw-bold">Email:</td><td>${user.email}</td></tr>
                                    <tr><td class="fw-bold">Téléphone:</td><td>${user.phone || 'Non renseigné'}</td></tr>
                                    <tr><td class="fw-bold">NIP:</td><td>${user.nip || 'Non renseigné'}</td></tr>
                                    <tr><td class="fw-bold">Adresse:</td><td>${user.address || 'Non renseignée'}</td></tr>
                                    <tr><td class="fw-bold">Ville:</td><td>${user.city || 'Non renseignée'}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user-shield me-2"></i>Informations système
                                </h6>
                                <table class="table table-sm table-borderless">
                                    <tr><td class="fw-bold">Rôle:</td><td><span class="badge bg-primary">${user.role_label}</span></td></tr>
                                    <tr><td class="fw-bold">Statut:</td><td><span class="badge ${user.status === 'active' ? 'bg-success' : 'bg-secondary'}">${user.status_label}</span></td></tr>
                                    <tr><td class="fw-bold">Email vérifié:</td><td>${user.is_verified ? '✅ Oui' : '❌ Non'}</td></tr>
                                    <tr><td class="fw-bold">Dernière connexion:</td><td>${user.last_login}</td></tr>
                                    <tr><td class="fw-bold">Créé le:</td><td>${user.created_at}</td></tr>
                                    <tr><td class="fw-bold">Organisations:</td><td><span class="badge bg-info">${user.organisations_count}</span></td></tr>
                                </table>
                            </div>
                        </div>
                    `;

                        document.getElementById('userModalContent').innerHTML = html;
                        document.getElementById('editUserFromModal').onclick = () => {
                            window.location.href = `/admin/users/${user.id}/edit`;
                        };

                        const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                        modal.show();
                    }
                })
                .catch(error => {
                    showNotification('error', 'Erreur lors du chargement des détails');
                });
        }

        // Supprimer un utilisateur
        function deleteUser(userId, userName) {
            currentUserId = userId;
            document.getElementById('deleteUserName').textContent = userName;

            // Vérifier les contraintes
            fetch(`/admin/users/${userId}/check-constraints`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    const constraintsWarning = document.getElementById('constraintsWarning');
                    const constraintsList = document.getElementById('constraintsList');

                    if (data.success && data.has_constraints) {
                        constraintsList.innerHTML = '';
                        data.constraints.forEach(constraint => {
                            const li = document.createElement('li');
                            li.textContent = constraint;
                            constraintsList.appendChild(li);
                        });
                        constraintsWarning.style.display = 'block';
                    } else {
                        constraintsWarning.style.display = 'none';
                    }

                    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
                    modal.show();
                })
                .catch(error => {
                    document.getElementById('constraintsWarning').style.display = 'none';
                    const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
                    modal.show();
                });
        }

        // Confirmer suppression
        document.getElementById('confirmDeleteUserBtn').addEventListener('click', function () {
            if (!currentUserId) return;

            const btn = this;
            const originalText = btn.innerHTML;

            btn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Suppression...';
            btn.disabled = true;

            fetch(`/admin/users/${currentUserId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('deleteUserModal')).hide();
                        showNotification('success', data.message);
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'Erreur lors de la suppression');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });

        // Toggle statut utilisateur
        function toggleUserStatus(userId) {
            fetch(`/admin/users/${userId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'Erreur lors du changement de statut');
                });
        }

        // Réinitialiser mot de passe
        function resetPassword(userId) {
            if (!confirm('Voulez-vous vraiment réinitialiser le mot de passe de cet utilisateur ?')) {
                return;
            }

            fetch(`/admin/users/${userId}/reset-password`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'Erreur lors de la réinitialisation');
                });
        }

        // Activer le compte (forcer la vérification email)
        function activateAccount(userId) {
            if (!confirm('Voulez-vous vraiment activer ce compte ? L\'utilisateur pourra se connecter immédiatement.')) {
                return;
            }

            fetch(`/admin/users/${userId}/force-verify-email`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'Erreur lors de l\'activation du compte');
                });
        }

        // Système de notifications Bootstrap 5
        function showNotification(type, message, duration = 5000) {
            const colors = {
                success: 'alert-success',
                error: 'alert-danger',
                warning: 'alert-warning',
                info: 'alert-info'
            };

            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };

            const notification = document.createElement('div');
            notification.className = `alert ${colors[type]} alert-dismissible fade show notification`;
            notification.innerHTML = `
                <i class="${icons[type]} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification && notification.parentNode) {
                    const alert = new bootstrap.Alert(notification);
                    alert.close();
                }
            }, duration);
        }
    </script>
@endsection