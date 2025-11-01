{{-- resources/views/admin/roles/index.blade.php - Version Complètement Mise à Jour --}}
@extends('layouts.admin')
@section('title', 'Gestion des Rôles')

@section('content')
<div class="container-fluid">
    <!-- Header moderne avec couleur gabonaise bleue pour "Rôles" -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-user-shield mr-2"></i>
                                Gestion des Rôles
                            </h2>
                            <p class="mb-0" style="opacity: 0.9;">Configuration des rôles et permissions du système SGLP avec hiérarchie gabonaise</p>
                        </div>
                        <div class="col-md-4 text-right">
                            <span class="badge badge-light text-dark" style="font-size: 1rem; margin-right: 1rem;">
                                {{ $stats['total_roles'] ?? 9 }} rôles
                            </span>
                            <button onclick="refreshPage()" class="btn btn-light btn-lg mr-2">
                                <i class="fas fa-sync mr-2"></i>
                                Actualiser
                            </button>
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-plus mr-2"></i>
                                Nouveau Rôle
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Cards avec style gabonais amélioré -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $stats['total_roles'] ?? 9 }}</h3>
                            <p class="mb-0 small" style="opacity: 0.9;">Total Rôles</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-user-shield fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 100%"></div>
                    </div>
                    <small style="opacity: 0.75;" class="mt-1 d-block">
                        <i class="fas fa-arrow-up mr-1"></i>Système complet
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $stats['system_roles'] ?? 8 }}</h3>
                            <p class="mb-0 small" style="opacity: 0.9;">Rôles Système</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-shield-alt fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 85%"></div>
                    </div>
                    <small style="opacity: 0.75;" class="mt-1 d-block">
                        <i class="fas fa-lock mr-1"></i>Protégés
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $stats['active_roles'] ?? 9 }}</h3>
                            <p class="mb-0 small" style="opacity: 0.9;">Rôles Actifs</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 90%"></div>
                    </div>
                    <small style="opacity: 0.75;" class="mt-1 d-block">
                        Objectif: 100%
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">{{ $stats['custom_roles'] ?? 1 }}</h3>
                            <p class="mb-0 small">Rôles Personnalisés</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-user-cog fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" style="width: 65%"></div>
                    </div>
                    <small style="opacity: 0.75;" class="mt-1 d-block">
                        Configurables
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Actions améliorés -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text border-0 bg-light">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control border-0 bg-light" placeholder="Rechercher un rôle..." id="searchInput" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control border-0 bg-light" id="filterNiveau">
                                <option value="">Tous niveaux</option>
                                <option value="10" {{ request('niveau') == '10' ? 'selected' : '' }}>Niveau 10</option>
                                <option value="9" {{ request('niveau') == '9' ? 'selected' : '' }}>Niveau 9</option>
                                <option value="8" {{ request('niveau') == '8' ? 'selected' : '' }}>Niveau 8</option>
                                <option value="6" {{ request('niveau') == '6' ? 'selected' : '' }}>Niveau 6</option>
                                <option value="4" {{ request('niveau') == '4' ? 'selected' : '' }}>Niveau 4</option>
                                <option value="2" {{ request('niveau') == '2' ? 'selected' : '' }}>Niveau 2</option>
                                <option value="1" {{ request('niveau') == '1' ? 'selected' : '' }}>Niveau 1</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control border-0 bg-light" id="filterType">
                                <option value="">Tous types</option>
                                <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>Système</option>
                                <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Personnalisé</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-control border-0 bg-light" id="filterStatut">
                                <option value="">Tous statuts</option>
                                <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-outline-success" onclick="exporterRoles()">
                                    <i class="fas fa-download mr-2"></i>Exporter
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="initSystemRoles()">
                                    <i class="fas fa-shield-alt mr-2"></i>Init Système
                                </button>
                                <button type="button" class="btn btn-outline-primary" onclick="refreshPage()">
                                    <i class="fas fa-sync mr-2"></i>Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions en lot améliorées -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <input type="checkbox" id="selectAll" class="form-check-input mr-2">
                                <label for="selectAll" class="form-check-label mr-3">Sélectionner tout</label>
                                <span id="selectedCount" class="badge badge-light text-dark">0 sélectionné(s)</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="btn-group" role="group">
                                <button onclick="activerSelection()" class="btn btn-success" disabled id="btnActiver">
                                    <i class="fas fa-check mr-1"></i>Activer
                                </button>
                                <button onclick="desactiverSelection()" class="btn btn-warning" disabled id="btnDesactiver">
                                    <i class="fas fa-pause mr-1"></i>Désactiver
                                </button>
                                <button onclick="exporterSelection()" class="btn btn-info" disabled id="btnExporter">
                                    <i class="fas fa-download mr-1"></i>Exporter
                                </button>
                                <button onclick="supprimerSelection()" class="btn btn-danger" disabled id="btnSupprimer">
                                    <i class="fas fa-trash mr-1"></i>Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Rôles -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-user-shield mr-2" style="color: #003f7f;"></i>
                            Liste des Rôles du Système
                        </h5>
                        <span class="badge badge-primary">{{ $stats['total_roles'] ?? 9 }} rôles</span>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($roles) && count($roles) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover" id="rolesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">
                                            <input type="checkbox" class="form-check-input" id="selectAllTable">
                                        </th>
                                        <th class="border-0">Rôle</th>
                                        <th class="border-0">Niveau</th>
                                        <th class="border-0">Type</th>
                                        <th class="border-0">Utilisateurs</th>
                                        <th class="border-0">Permissions</th>
                                        <th class="border-0">Statut</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles ?? [] as $role)
                                    <tr class="role-row">
                                        <td>
                                            <input type="checkbox" class="form-check-input role-checkbox" value="{{ $role->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="role-status-icon mr-3">
                                                    @if(in_array($role->name ?? '', ['super_admin', 'admin_general', 'admin_associations', 'admin_religieuses', 'admin_politiques', 'moderateur', 'operateur', 'auditeur']))
                                                        <div class="status-circle bg-primary">
                                                            <i class="fas fa-shield-alt text-white"></i>
                                                        </div>
                                                    @else
                                                        <div class="status-circle bg-secondary">
                                                            <i class="fas fa-user-cog text-white"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">{{ $role->display_name ?? $role->name }}</h6>
                                                    <small class="text-muted">{{ $role->description ?? 'Aucune description' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <div class="level-indicator bg-primary text-white">
                                                    {{ $role->level ?? 1 }}
                                                </div>
                                                <small class="text-muted">/ 10</small>
                                            </div>
                                        </td>
                                        <td>
                                            @if(in_array($role->name ?? '', ['super_admin', 'admin_general', 'admin_associations', 'admin_religieuses', 'admin_politiques', 'moderateur', 'operateur', 'auditeur']))
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-shield-alt mr-1"></i>Système
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-user-cog mr-1"></i>Personnalisé
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <div class="d-flex flex-column">
                                                    <strong class="text-primary">{{ $role->users_count ?? 0 }}</strong>
                                                    <small class="text-muted">utilisateurs</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <div class="d-flex flex-column">
                                                    <strong class="text-success">{{ $role->permissions_count ?? 0 }}</strong>
                                                    <small class="text-muted">permissions</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($role->is_active ?? true)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check mr-1"></i>Actif
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times mr-1"></i>Inactif
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="voirRole({{ $role->id }})" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-success" onclick="modifierRole({{ $role->id }})" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                @if(!in_array($role->name ?? '', ['super_admin', 'admin_general', 'admin_associations', 'admin_religieuses', 'admin_politiques', 'moderateur', 'operateur', 'auditeur']) && ($role->users_count ?? 0) === 0)
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerRole({{ $role->id }}, '{{ addslashes($role->display_name ?? $role->name) }}')" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" title="Plus">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="#" onclick="copierRole({{ $role->id }})">
                                                            <i class="fas fa-copy mr-2"></i>Dupliquer
                                                        </a>
                                                        <a class="dropdown-item" href="#" onclick="exporterRole({{ $role->id }})">
                                                            <i class="fas fa-download mr-2"></i>Exporter
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        @if($role->is_active ?? true)
                                                            <a class="dropdown-item text-warning" href="#" onclick="desactiverRole({{ $role->id }})">
                                                                <i class="fas fa-pause mr-2"></i>Désactiver
                                                            </a>
                                                        @else
                                                            <a class="dropdown-item text-success" href="#" onclick="activerRole({{ $role->id }})">
                                                                <i class="fas fa-play mr-2"></i>Activer
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="mb-4">
                                                <i class="fas fa-user-shield fa-5x text-muted" style="opacity: 0.5;"></i>
                                            </div>
                                            <h4 class="text-muted mb-3">Aucun rôle configuré</h4>
                                            <p class="text-muted mb-4">Les rôles système et personnalisés apparaîtront ici.</p>
                                            <div class="d-flex justify-content-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-lg">
                                                        <i class="fas fa-plus mr-2"></i>Créer le premier rôle
                                                    </a>
                                                    <button class="btn btn-outline-primary btn-lg" onclick="initSystemRoles()">
                                                        <i class="fas fa-shield-alt mr-2"></i>Rôles système
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(isset($roles) && method_exists($roles, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $roles->links() }}
                            </div>
                        @endif
                    @else
                        <!-- État vide premium -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-user-shield fa-5x text-muted" style="opacity: 0.5;"></i>
                            </div>
                            <h4 class="text-muted mb-3">Aucun rôle configuré</h4>
                            <p class="text-muted mb-4">Commencez par créer des rôles ou initialiser les rôles système pour configurer les permissions.</p>
                            <div class="d-flex justify-content-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus mr-2"></i>Créer le premier rôle
                                    </a>
                                    <button class="btn btn-outline-primary btn-lg" onclick="initSystemRoles()">
                                        <i class="fas fa-shield-alt mr-2"></i>Initialiser rôles système
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) tricolore spécialisé rôles -->
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()">
            <i class="fas fa-tools fab-icon"></i>
        </div>
        <div class="fab-options">
            <button class="fab-option" style="background: #003f7f;" title="Nouveau rôle" onclick="window.location.href='{{ route('admin.roles.create') }}'">
                <i class="fas fa-plus"></i>
            </button>
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Initialiser système" onclick="initSystemRoles()">
                <i class="fas fa-shield-alt"></i>
            </button>
            <button class="fab-option" style="background: #009e3f;" title="Exporter" onclick="exporterRoles()">
                <i class="fas fa-download"></i>
            </button>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="loading-overlay" style="display: none;">
    <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
        <span class="visually-hidden sr-only">Chargement...</span>
    </div>
</div>

<style>
/* Styles gabonais améliorés pour vue rôles - Style termines.blade.php */
.stats-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 15px;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}

.icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255,255,255,0.1);
}

.role-row {
    transition: background-color 0.2s ease;
}

.role-row:hover {
    background-color: rgba(0, 63, 127, 0.05);
}

.status-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.level-indicator {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-weight: bold;
    font-size: 0.9rem;
}

/* FAB Style gabonais pour rôles */
.fab-container {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    z-index: 1000;
}

.fab-menu {
    position: relative;
}

.fab-main {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #003f7f 0%, #ffcd00 50%, #009e3f 100%);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease;
    border: none;
}

.fab-main:hover {
    transform: scale(1.1);
}

.fab-icon {
    color: white;
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.fab-options {
    position: absolute;
    bottom: 70px;
    right: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.fab-menu.active .fab-options {
    opacity: 1;
    visibility: visible;
}

.fab-menu.active .fab-icon {
    transform: rotate(45deg);
}

.fab-option {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: none;
    color: white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.fab-option:hover {
    transform: scale(1.1);
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

/* Animation d'entrée */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

/* Styles pour les badges et indicateurs */
.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

.text-sm {
    font-size: 0.9rem;
}

/* Compatibility Bootstrap 4 - Bootstrap 5 mixins */
.mr-1 { margin-right: 0.25rem !important; }
.mr-2 { margin-right: 0.5rem !important; }
.mr-3 { margin-right: 1rem !important; }
</style>

<script>
// ========================================================================
// JAVASCRIPT CORRIGÉ POUR FILTRES FONCTIONNELS - VERSION FINALE
// ========================================================================

let selectedRoles = [];
let allRoles = [];
let isLoading = false;

// Configuration globale
const CONFIG = {
    routes: {
        index: '{{ route("admin.roles.index") }}',
        show: '{{ route("admin.roles.show", ":id") }}',
        edit: '{{ route("admin.roles.edit", ":id") }}',
        destroy: '{{ route("admin.roles.destroy", ":id") }}',
        search: '{{ route("admin.roles.search") }}',
        duplicate: '{{ route("admin.roles.duplicate", ":id") }}',
        toggleStatus: '{{ route("admin.roles.toggle-status", ":id") }}',
        bulkOperations: '{{ route("admin.roles.bulk-operations") }}',
        export: '{{ route("admin.roles.export") }}',
        initSystem: '{{ route("admin.roles.init-system") }}'
    },
    csrf: '{{ csrf_token() }}'
};

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Module Gestion Rôles SGLP initialisé');
    initEventListeners();
    loadRoles(); // Charger les rôles au démarrage
});

// ========================================================================
// GESTION DES FILTRES - SECTION CORRIGÉE
// ========================================================================

/**
 * Initialiser tous les event listeners
 */
function initEventListeners() {
    // Recherche en temps réel
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 300);
        });
    }
    
    // Event listeners pour tous les filtres
    const filterElements = ['filterNiveau', 'filterType', 'filterStatut'];
    filterElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', function() {
                console.log(`Filtre ${id} changé:`, this.value);
                applyFilters();
            });
        }
    });

    // Sélection multiple
    initSelectionHandlers();
}

/**
 * Appliquer tous les filtres - FONCTION CORRIGÉE
 */
function applyFilters() {
    if (isLoading) return;
    
    console.log('📍 Application des filtres...');
    
    // Récupérer les valeurs des filtres
    const filters = {
        search: document.getElementById('searchInput')?.value || '',
        niveau: document.getElementById('filterNiveau')?.value || '',
        type: document.getElementById('filterType')?.value || '',
        statut: document.getElementById('filterStatut')?.value || ''
    };
    
    console.log('Filtres appliqués:', filters);
    
    // Construire l'URL avec les paramètres
    const params = new URLSearchParams();
    params.append('api', '1'); // Important pour le mode AJAX
    
    if (filters.search) params.append('search', filters.search);
    if (filters.niveau) params.append('niveau', filters.niveau);
    if (filters.type) params.append('type', filters.type);
    if (filters.statut) params.append('statut', filters.statut);
    
    const url = `${CONFIG.routes.index}?${params.toString()}`;
    
    // Afficher le loading
    showLoading(true);
    
    // Effectuer la requête AJAX
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-Token': CONFIG.csrf
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('✅ Réponse reçue:', data);
        
        if (data.success) {
            allRoles = data.data || [];
            displayRoles(allRoles);
            updateStats(data.stats || {});
        } else {
            throw new Error(data.message || 'Erreur inconnue');
        }
    })
    .catch(error => {
        console.error('❌ Erreur lors du filtrage:', error);
        showError('Erreur lors du filtrage des rôles');
        displayEmptyState();
    })
    .finally(() => {
        showLoading(false);
    });
}

/**
 * Charger les rôles depuis l'API
 */
async function loadRoles() {
    if (isLoading) return;
    
    try {
        showLoading(true);
        console.log('📡 Chargement des rôles...');
        
        const response = await fetch(`${CONFIG.routes.index}?api=1`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Erreur lors du chargement');
        }
        
        const data = await response.json();
        
        if (data.success) {
            allRoles = data.data || [];
            displayRoles(allRoles);
            updateStats(data.stats || {});
            console.log('✅ Rôles chargés:', allRoles.length);
        } else {
            throw new Error(data.message);
        }
        
    } catch (error) {
        console.error('❌ Erreur loadRoles:', error);
        showError('Erreur lors du chargement des rôles');
        displayEmptyState();
    } finally {
        showLoading(false);
    }
}

/**
 * Afficher les rôles dans le tableau
 */
function displayRoles(roles) {
    const tbody = document.querySelector('#rolesTable tbody');
    
    if (!tbody) {
        console.error('❌ Tableau des rôles introuvable');
        return;
    }
    
    if (!roles || roles.length === 0) {
        displayEmptyState();
        return;
    }
    
    console.log(`📋 Affichage de ${roles.length} rôles`);
    
    let html = '';
    roles.forEach(role => {
        html += generateRoleRowHtml(role);
    });
    
    tbody.innerHTML = html;
    initSelectionHandlers();
}

/**
 * Générer le HTML d'une ligne de rôle
 */
function generateRoleRowHtml(role) {
    return `
        <tr class="role-row">
            <td>
                <input type="checkbox" class="form-check-input role-checkbox" value="${role.id}">
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <div class="role-status-icon mr-3">
                        <div class="status-circle ${role.is_system ? 'bg-primary' : 'bg-secondary'}">
                            <i class="fas ${role.is_system ? 'fa-shield-alt' : 'fa-user-cog'} text-white"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="mb-1">${role.display_name || role.name}</h6>
                        <small class="text-muted">${role.description || 'Aucune description'}</small>
                    </div>
                </div>
            </td>
            <td>
                <div class="text-center">
                    <div class="level-indicator bg-primary text-white">${role.level || 1}</div>
                    <small class="text-muted">/ 10</small>
                </div>
            </td>
            <td>
                <span class="badge ${role.is_system ? 'badge-primary' : 'badge-secondary'}">
                    <i class="fas ${role.is_system ? 'fa-shield-alt' : 'fa-user-cog'} mr-1"></i>
                    ${role.is_system ? 'Système' : 'Personnalisé'}
                </span>
            </td>
            <td>
                <div class="text-center">
                    <strong class="text-primary">${role.users_count || 0}</strong>
                    <br><small class="text-muted">utilisateurs</small>
                </div>
            </td>
            <td>
                <div class="text-center">
                    <strong class="text-success">${role.permissions_count || 0}</strong>
                    <br><small class="text-muted">permissions</small>
                </div>
            </td>
            <td>
                <span class="badge ${role.is_active ? 'badge-success' : 'badge-danger'}">
                    <i class="fas ${role.is_active ? 'fa-check' : 'fa-times'} mr-1"></i>
                    ${role.is_active ? 'Actif' : 'Inactif'}
                </span>
            </td>
            <td>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="voirRole(${role.id})" title="Voir détails">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="modifierRole(${role.id})" title="Modifier">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${!role.is_system && (role.users_count || 0) === 0 ? 
                        `<button type="button" class="btn btn-sm btn-outline-danger" onclick="supprimerRole(${role.id}, '${escapeHtml(role.display_name || role.name)}')" title="Supprimer">
                            <i class="fas fa-trash"></i>
                        </button>` : ''
                    }
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown" title="Plus">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#" onclick="copierRole(${role.id})">
                                <i class="fas fa-copy mr-2"></i>Dupliquer
                            </a>
                            <a class="dropdown-item" href="#" onclick="exporterRole(${role.id})">
                                <i class="fas fa-download mr-2"></i>Exporter
                            </a>
                            <div class="dropdown-divider"></div>
                            ${role.is_active ? 
                                `<a class="dropdown-item text-warning" href="#" onclick="desactiverRole(${role.id})">
                                    <i class="fas fa-pause mr-2"></i>Désactiver
                                </a>` :
                                `<a class="dropdown-item text-success" href="#" onclick="activerRole(${role.id})">
                                    <i class="fas fa-play mr-2"></i>Activer
                                </a>`
                            }
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    `;
}

/**
 * Échapper le HTML pour éviter les injections XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

/**
 * Afficher l'état vide
 */
function displayEmptyState() {
    const tbody = document.querySelector('#rolesTable tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-user-shield fa-5x text-muted" style="opacity: 0.5;"></i>
                    </div>
                    <h4 class="text-muted mb-3">Aucun rôle trouvé</h4>
                    <p class="text-muted mb-4">Aucun rôle ne correspond aux critères de recherche.</p>
                    <button class="btn btn-outline-primary btn-lg" onclick="resetFilters()">
                        <i class="fas fa-undo mr-2"></i>Réinitialiser les filtres
                    </button>
                </td>
            </tr>
        `;
    }
}

/**
 * Mettre à jour les statistiques
 */
function updateStats(stats) {
    // Mise à jour des cartes de statistiques si elles existent
    const totalElement = document.querySelector('[data-stat="total"]');
    if (totalElement) totalElement.textContent = stats.total_roles || 0;
    
    const activeElement = document.querySelector('[data-stat="active"]');
    if (activeElement) activeElement.textContent = stats.active_roles || 0;
    
    const systemElement = document.querySelector('[data-stat="system"]');
    if (systemElement) systemElement.textContent = stats.system_roles || 0;
    
    const customElement = document.querySelector('[data-stat="custom"]');
    if (customElement) customElement.textContent = stats.custom_roles || 0;
}

/**
 * Réinitialiser tous les filtres
 */
function resetFilters() {
    console.log('🔄 Réinitialisation des filtres');
    
    document.getElementById('searchInput').value = '';
    document.getElementById('filterNiveau').value = '';
    document.getElementById('filterType').value = '';
    document.getElementById('filterStatut').value = '';
    
    loadRoles(); // Recharger sans filtres
}

// ========================================================================
// FONCTIONS ACTIONS (MAINTENUES)
// ========================================================================

function voirRole(roleId) {
    window.location.href = CONFIG.routes.show.replace(':id', roleId);
}

function modifierRole(roleId) {
    window.location.href = CONFIG.routes.edit.replace(':id', roleId);
}

async function supprimerRole(roleId, roleName = null) {
    const roleDisplay = roleName || `rôle #${roleId}`;
    
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le ${roleDisplay} ?\n\nCette action est irréversible.`)) {
        return;
    }
    
    try {
        showLoading(true);
        
        const response = await fetch(CONFIG.routes.destroy.replace(':id', roleId), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CONFIG.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            await loadRoles();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Erreur suppression:', error);
        showError(error.message);
    } finally {
        showLoading(false);
    }
}

function copierRole(roleId) {
    if (confirm('Voulez-vous créer une copie de ce rôle ?')) {
        window.location.href = CONFIG.routes.duplicate.replace(':id', roleId);
    }
}

function exporterRole(roleId) {
    window.open(CONFIG.routes.export + '?role_id=' + roleId);
}

function activerRole(roleId) {
    toggleRoleStatus(roleId, true);
}

function desactiverRole(roleId) {
    toggleRoleStatus(roleId, false);
}

async function toggleRoleStatus(roleId, newStatus) {
    const action = newStatus ? 'activer' : 'désactiver';
    
    if (!confirm(`Êtes-vous sûr de vouloir ${action} ce rôle ?`)) return;
    
    try {
        showLoading(true);
        
        const response = await fetch(CONFIG.routes.toggleStatus.replace(':id', roleId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CONFIG.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ is_active: newStatus })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            await loadRoles();
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Erreur toggle status:', error);
        showError(error.message);
    } finally {
        showLoading(false);
    }
}

function refreshPage() {
    loadRoles();
}

function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    if (fabMenu) fabMenu.classList.toggle('active');
}

function exporterRoles() {
    window.open(CONFIG.routes.export);
}

async function initSystemRoles() {
    if (!confirm('Initialiser les rôles système ?\n\nCette action créera les rôles de base du système PNGDI.')) {
        return;
    }
    
    try {
        showLoading(true);
        
        const response = await fetch(CONFIG.routes.initSystem, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CONFIG.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => loadRoles(), 2000);
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Erreur init system:', error);
        showError(error.message);
    } finally {
        showLoading(false);
    }
}

// ========================================================================
// FONCTIONS UTILITAIRES
// ========================================================================

function initSelectionHandlers() {
    // Logique de sélection multiple (maintenue)
    document.querySelectorAll('.role-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
}

function updateSelectedCount() {
    const checkedBoxes = document.querySelectorAll('.role-checkbox:checked');
    selectedRoles = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
    
    const selectedCountElement = document.getElementById('selectedCount');
    if (selectedCountElement) {
        selectedCountElement.textContent = `${selectedRoles.length} sélectionné(s)`;
    }
}

function showLoading(show = true) {
    isLoading = show;
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = show ? 'flex' : 'none';
    }
}

function showSuccess(message) {
    showNotification(message, 'success');
}

function showError(message) {
    showNotification(message, 'error');
}

function showNotification(message, type = 'info') {
    const typeConfig = {
        success: { icon: 'fa-check-circle', bgClass: 'alert-success' },
        error: { icon: 'fa-exclamation-triangle', bgClass: 'alert-danger' },
        warning: { icon: 'fa-exclamation-circle', bgClass: 'alert-warning' },
        info: { icon: 'fa-info-circle', bgClass: 'alert-info' }
    };
    
    const config = typeConfig[type] || typeConfig.info;
    
    const notification = document.createElement('div');
    notification.className = `alert ${config.bgClass} alert-dismissible fade show`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; border-radius: 15px; box-shadow: 0 8px 32px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <i class="fas ${config.icon} mr-2"></i>${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

console.log('✅ JavaScript des filtres rôles chargé et opérationnel');
</script>
@endsection