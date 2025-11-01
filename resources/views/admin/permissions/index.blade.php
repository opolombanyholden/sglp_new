@extends('layouts.admin')

@section('title', 'Gestion des Permissions')

@section('content')
<div class="container-fluid">
    <!-- Header avec couleur gabonaise bleue pour "Permissions" -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-key me-2"></i>
                                Gestion des Permissions
                            </h2>
                            <p class="mb-0 opacity-90">Administration des droits d'accès et permissions système SGLP</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge bg-light text-dark fs-6 me-3">
                                {{ $totalPermissions ?? 0 }} permissions
                            </span>
                            <a href="{{ route('admin.permissions.create') }}" class="btn btn-light btn-lg me-2">
                                <i class="fas fa-plus me-2"></i>
                                Nouvelle
                            </a>
                            <button onclick="refreshTable()" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-sync me-2"></i>
                                Actualiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Cards avec style gabonais -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1" data-count="{{ $totalPermissions ?? 0 }}">{{ $totalPermissions ?? 0 }}</h3>
                            <p class="mb-0 small opacity-90">Total Permissions</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-key fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: 100%"></div>
                    </div>
                    <small class="opacity-75 mt-1 d-block">
                        <i class="fas fa-arrow-up me-1"></i>Système actif
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1" data-count="{{ $permissionsSysteme ?? 0 }}">{{ $permissionsSysteme ?? 0 }}</h3>
                            <p class="mb-0 small opacity-90">Permissions Système</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-cog fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: {{ $totalPermissions > 0 ? round(($permissionsSysteme / $totalPermissions) * 100) : 0 }}%"></div>
                    </div>
                    <small class="opacity-75 mt-1 d-block">
                        <i class="fas fa-shield-alt me-1"></i>Protégées
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1" data-count="{{ $utilisateursAvecPermissions ?? 0 }}">{{ $utilisateursAvecPermissions ?? 0 }}</h3>
                            <p class="mb-0 small">Utilisateurs Actifs</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-dark" style="width: 85%"></div>
                    </div>
                    <small class="opacity-75 mt-1 d-block">
                        Avec permissions
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm stats-card" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1" data-count="{{ $permissionsHauteSecurite ?? 0 }}">{{ $permissionsHauteSecurite ?? 0 }}</h3>
                            <p class="mb-0 small opacity-90">Haute Sécurité</p>
                        </div>
                        <div class="icon-circle">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                    <div class="progress mt-2" style="height: 4px;">
                        <div class="progress-bar bg-light" style="width: {{ $totalPermissions > 0 ? round(($permissionsHauteSecurite / $totalPermissions) * 100) : 0 }}%"></div>
                    </div>
                    <small class="opacity-75 mt-1 d-block">
                        <i class="fas fa-lock me-1"></i>Critiques
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.permissions.index') }}" id="filtersForm">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-0 bg-light" 
                                           placeholder="Rechercher..." 
                                           name="search" 
                                           id="searchInput" 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select border-0 bg-light" name="category" id="filterCategory">
                                    <option value="">Toutes catégories</option>
                                    <option value="users" {{ request('category') == 'users' ? 'selected' : '' }}>Utilisateurs</option>
                                    <option value="organizations" {{ request('category') == 'organizations' ? 'selected' : '' }}>Organisations</option>
                                    <option value="workflow" {{ request('category') == 'workflow' ? 'selected' : '' }}>Workflow</option>
                                    <option value="system" {{ request('category') == 'system' ? 'selected' : '' }}>Système</option>
                                    <option value="content" {{ request('category') == 'content' ? 'selected' : '' }}>Contenu</option>
                                    <option value="reports" {{ request('category') == 'reports' ? 'selected' : '' }}>Rapports</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select border-0 bg-light" name="risk" id="filterRisk">
                                    <option value="">Tous niveaux</option>
                                    <option value="low" {{ request('risk') == 'low' ? 'selected' : '' }}>Faible</option>
                                    <option value="medium" {{ request('risk') == 'medium' ? 'selected' : '' }}>Moyen</option>
                                    <option value="high" {{ request('risk') == 'high' ? 'selected' : '' }}>Élevé</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select border-0 bg-light" name="type" id="filterType">
                                    <option value="">Tous types</option>
                                    <option value="system" {{ request('type') == 'system' ? 'selected' : '' }}>Système</option>
                                    <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>Personnalisé</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group w-100" role="group">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search me-2"></i>Filtrer
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                    <a href="{{ route('admin.permissions.export', request()->query()) }}" class="btn btn-outline-success">
                                        <i class="fas fa-download me-2"></i>Exporter
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions en lot -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <input type="checkbox" id="selectAll" class="form-check-input me-2">
                                <label for="selectAll" class="form-check-label me-3">Sélectionner tout</label>
                                <span id="selectedCount" class="badge bg-light text-dark">0 sélectionné(s)</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="btn-group" role="group">
                                <button onclick="deleteSelection()" class="btn btn-danger" disabled id="btnDelete">
                                    <i class="fas fa-trash me-1"></i>Supprimer
                                </button>
                                <button onclick="exportSelection()" class="btn btn-warning" disabled id="btnExport">
                                    <i class="fas fa-download me-1"></i>Exporter
                                </button>
                                <button onclick="initSystemPermissions()" class="btn btn-info">
                                    <i class="fas fa-cog me-1"></i>Initialiser Système
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Permissions -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2" style="color: #003f7f;"></i>
                            Liste des Permissions
                        </h5>
                        <span class="badge bg-primary">{{ $totalPermissions ?? 0 }} permissions</span>
                    </div>
                </div>
                <div class="card-body">
                    @if($totalPermissions > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">
                                            <input type="checkbox" class="form-check-input" id="selectAllTable">
                                        </th>
                                        <th class="border-0">Permission</th>
                                        <th class="border-0">Code</th>
                                        <th class="border-0">Catégorie</th>
                                        <th class="border-0">Type</th>
                                        <th class="border-0">Risque</th>
                                        <th class="border-0">Rôles</th>
                                        <th class="border-0">Utilisateurs</th>
                                        <th class="border-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="permissionsTableBody">
                                    <!-- Permissions chargées dynamiquement -->
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Chargement...</span>
                                            </div>
                                            <p class="mt-2 text-muted">Chargement des permissions...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- État vide -->
                        <div class="text-center py-5" id="emptyState">
                            <div class="mb-4">
                                <i class="fas fa-key fa-5x text-muted opacity-50"></i>
                            </div>
                            <h4 class="text-muted mb-3">Aucune permission trouvée</h4>
                            <p class="text-muted mb-4">Aucune permission ne correspond aux critères de recherche ou aucune permission n'existe.</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-lg">
                                    <i class="fas fa-plus me-2"></i>Créer une permission
                                </a>
                                <button class="btn btn-outline-info btn-lg" onclick="initSystemPermissions()">
                                    <i class="fas fa-cog me-2"></i>Initialiser système
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) tricolore -->
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()">
            <i class="fas fa-plus fab-icon"></i>
        </div>
        <div class="fab-options">
            <a href="{{ route('admin.permissions.create') }}" class="fab-option" style="background: #009e3f;" title="Nouvelle permission">
                <i class="fas fa-plus"></i>
            </a>
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Exporter sélection" onclick="exportSelection()">
                <i class="fas fa-download"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Initialiser système" onclick="initSystemPermissions()">
                <i class="fas fa-cog"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal de détails -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-key me-2"></i>
                    Détails de la permission
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="permissionDetails">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2 text-muted">Chargement des détails...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-warning" onclick="editCurrentPermission()" id="editPermissionBtn" style="display: none;">
                    <i class="fas fa-edit me-2"></i>Modifier
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h5>Êtes-vous sûr de vouloir supprimer cette permission ?</h5>
                    <p class="text-muted">Cette action est irréversible et supprimera toutes les associations avec les rôles.</p>
                    <div id="permissionToDelete" class="alert alert-info mt-3" style="display: none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash me-1"></i>
                    Supprimer définitivement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Template pour les lignes de permissions -->
<template id="permissionRowTemplate">
    <tr class="permission-row">
        <td>
            <input type="checkbox" class="form-check-input permission-checkbox" value="">
        </td>
        <td>
            <div class="d-flex align-items-center">
                <div class="permission-icon me-3">
                    <div class="icon-circle bg-primary">
                        <i class="fas fa-key text-white"></i>
                    </div>
                </div>
                <div>
                    <h6 class="mb-1 permission-display-name"></h6>
                    <small class="text-muted permission-description"></small>
                </div>
            </div>
        </td>
        <td>
            <code class="text-primary permission-name"></code>
        </td>
        <td>
            <span class="badge permission-category"></span>
        </td>
        <td>
            <span class="badge permission-type"></span>
        </td>
        <td>
            <span class="risk-badge permission-risk"></span>
        </td>
        <td>
            <span class="badge bg-primary permission-roles-count"></span>
        </td>
        <td>
            <span class="badge bg-success permission-users-count"></span>
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-primary btn-view" title="Voir détails">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning btn-edit" title="Modifier">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="Supprimer">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
</template>

<style>
/* Styles conformes au design terminés.blade.php */
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

.permission-row {
    transition: background-color 0.2s ease;
}

.permission-row:hover {
    background-color: rgba(0, 63, 127, 0.05);
}

.permission-icon .icon-circle {
    width: 40px;
    height: 40px;
    font-size: 1rem;
}

.risk-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 2px solid;
}

.risk-low {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.risk-medium {
    background: #fff3cd;
    color: #856404;
    border-color: #ffeaa7;
}

.risk-high {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

/* FAB Style gabonais conforme */
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
    background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.3s ease;
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
    text-decoration: none;
}

.fab-option:hover {
    transform: scale(1.1);
    color: white;
    text-decoration: none;
}

/* Animation d'entrée conforme */
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

.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

.text-sm {
    font-size: 0.9rem;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.loading-overlay.active {
    opacity: 1;
    visibility: visible;
}

.loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #003f7f;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
let selectedPermissions = [];
let currentPermissionId = null;
let allPermissions = [];

// Configuration globale
const CONFIG = {
    routes: {
        index: '{{ route("admin.permissions.index") }}',
        show: '{{ route("admin.permissions.show", ":id") }}',
        edit: '{{ route("admin.permissions.edit", ":id") }}',
        destroy: '{{ route("admin.permissions.destroy", ":id") }}',
        export: '{{ route("admin.permissions.export") }}',
        bulkDelete: '{{ route("admin.permissions.bulk-delete") }}',
        initSystem: '{{ route("admin.permissions.init-system-permissions") }}'
    },
    csrf: '{{ csrf_token() }}'
};

// Initialisation au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    loadPermissions();
    initEventListeners();
});

// Charger les permissions depuis l'API
async function loadPermissions() {
    try {
        const urlParams = new URLSearchParams(window.location.search);
        const apiUrl = new URL(CONFIG.routes.index);
        
        // Ajouter les paramètres de filtre
        for (const [key, value] of urlParams) {
            if (value) apiUrl.searchParams.set(key, value);
        }
        
        // Ajouter le paramètre API pour obtenir JSON
        apiUrl.searchParams.set('api', '1');
        
        const response = await fetch(apiUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Erreur lors du chargement des permissions');
        }
        
        const data = await response.json();
        allPermissions = data.permissions || [];
        
        displayPermissions(allPermissions);
        
    } catch (error) {
        console.error('Erreur:', error);
        showError('Erreur lors du chargement des permissions');
        displayEmptyState();
    }
}

// Afficher les permissions dans le tableau
function displayPermissions(permissions) {
    const tbody = document.getElementById('permissionsTableBody');
    const template = document.getElementById('permissionRowTemplate');
    
    if (!permissions || permissions.length === 0) {
        displayEmptyState();
        return;
    }
    
    tbody.innerHTML = '';
    
    permissions.forEach(permission => {
        const row = template.content.cloneNode(true);
        
        // Remplir les données
        row.querySelector('.permission-checkbox').value = permission.id;
        row.querySelector('.permission-display-name').textContent = permission.display_name || permission.name;
        row.querySelector('.permission-description').textContent = permission.description || 'Aucune description';
        row.querySelector('.permission-name').textContent = permission.name;
        
        // Catégorie avec icône et couleur
        const categoryBadge = row.querySelector('.permission-category');
        const categoryInfo = getCategoryInfo(permission.category);
        categoryBadge.innerHTML = `<i class="${categoryInfo.icon} me-1"></i>${categoryInfo.label}`;
        categoryBadge.className = `badge ${categoryInfo.class}`;
        
        // Type
        const typeBadge = row.querySelector('.permission-type');
        const isSystem = permission.is_system || permission.category === 'system';
        typeBadge.textContent = isSystem ? 'Système' : 'Personnalisé';
        typeBadge.className = `badge ${isSystem ? 'bg-secondary' : 'bg-warning text-dark'}`;
        
        // Risque
        const riskBadge = row.querySelector('.permission-risk');
        const riskLevel = permission.risk_level || calculateRiskLevel(permission.name);
        riskBadge.textContent = getRiskLabel(riskLevel);
        riskBadge.className = `risk-badge risk-${riskLevel}`;
        
        // Compteurs
        row.querySelector('.permission-roles-count').textContent = permission.roles_count || 0;
        row.querySelector('.permission-users-count').textContent = permission.users_count || 0;
        
        // Icône de la permission
        const iconCircle = row.querySelector('.permission-icon .icon-circle');
        iconCircle.innerHTML = `<i class="${categoryInfo.icon} text-white"></i>`;
        iconCircle.className = `icon-circle ${categoryInfo.bgClass}`;
        
        // Event listeners pour les boutons
        row.querySelector('.btn-view').addEventListener('click', () => viewPermission(permission.id));
        row.querySelector('.btn-edit').addEventListener('click', () => editPermission(permission.id));
        row.querySelector('.btn-delete').addEventListener('click', () => deletePermission(permission.id));
        
        tbody.appendChild(row);
    });
    
    // Réinitialiser les event listeners pour les checkboxes
    initSelectionHandlers();
}

// Afficher l'état vide
function displayEmptyState() {
    const tbody = document.getElementById('permissionsTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-key fa-5x text-muted opacity-50"></i>
                </div>
                <h4 class="text-muted mb-3">Aucune permission trouvée</h4>
                <p class="text-muted mb-4">Aucune permission ne correspond aux critères de recherche.</p>
                <button class="btn btn-outline-primary btn-lg" onclick="resetFilters()">
                    <i class="fas fa-undo me-2"></i>Réinitialiser les filtres
                </button>
            </td>
        </tr>
    `;
}

// Initialiser les event listeners
function initEventListeners() {
    // Recherche en temps réel
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            applyFilters();
        }, 300);
    });
    
    // Filtres auto-submit
    ['filterCategory', 'filterRisk', 'filterType'].forEach(id => {
        document.getElementById(id).addEventListener('change', applyFilters);
    });
    
    // FAB
    document.addEventListener('click', function(event) {
        const fabMenu = document.getElementById('fabMenu');
        if (!fabMenu.contains(event.target)) {
            fabMenu.classList.remove('active');
        }
    });
}

// Initialiser la gestion de sélection
function initSelectionHandlers() {
    // Sélectionner tout
    const selectAll = document.getElementById('selectAll');
    const selectAllTable = document.getElementById('selectAllTable');
    
    selectAll.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.permission-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        selectAllTable.checked = this.checked;
        updateSelectedCount();
    });
    
    selectAllTable.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.permission-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        selectAll.checked = this.checked;
        updateSelectedCount();
    });
    
    // Checkboxes individuelles
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            
            const totalCheckboxes = document.querySelectorAll('.permission-checkbox').length;
            const checkedCheckboxes = document.querySelectorAll('.permission-checkbox:checked').length;
            
            selectAll.checked = totalCheckboxes === checkedCheckboxes;
            selectAllTable.checked = totalCheckboxes === checkedCheckboxes;
        });
    });
}

// Mettre à jour le compteur de sélection
function updateSelectedCount() {
    const checkedBoxes = document.querySelectorAll('.permission-checkbox:checked');
    const count = checkedBoxes.length;
    
    document.getElementById('selectedCount').textContent = `${count} sélectionné(s)`;
    
    selectedPermissions = Array.from(checkedBoxes).map(cb => cb.value);
    
    // Activer/désactiver les boutons d'action
    const buttons = ['btnDelete', 'btnExport'];
    buttons.forEach(btnId => {
        const btn = document.getElementById(btnId);
        if (btn) btn.disabled = count === 0;
    });
}

// Fonctions principales
async function viewPermission(id) {
    currentPermissionId = id;
    const modal = new bootstrap.Modal(document.getElementById('viewModal'));
    modal.show();
    
    try {
        const response = await fetch(CONFIG.routes.show.replace(':id', id), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) throw new Error('Erreur lors du chargement');
        
        const data = await response.json();
        if (data.success) {
            document.getElementById('permissionDetails').innerHTML = generatePermissionDetailsHTML(data.data);
            document.getElementById('editPermissionBtn').style.display = 'inline-block';
        } else {
            throw new Error(data.message || 'Erreur inconnue');
        }
    } catch (error) {
        console.error('Erreur:', error);
        document.getElementById('permissionDetails').innerHTML = `
            <div class="text-center text-danger">
                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                <h5>Erreur</h5>
                <p>${error.message}</p>
            </div>
        `;
    }
}

function editPermission(id) {
    window.location.href = CONFIG.routes.edit.replace(':id', id);
}

function editCurrentPermission() {
    if (currentPermissionId) {
        editPermission(currentPermissionId);
    }
}

async function deletePermission(id) {
    currentPermissionId = id;
    
    // Trouver la permission dans les données
    const permission = allPermissions.find(p => p.id == id);
    if (permission) {
        document.getElementById('permissionToDelete').innerHTML = `
            <strong>Permission:</strong> ${permission.display_name || permission.name}<br>
            <strong>Code:</strong> <code>${permission.name}</code>
        `;
        document.getElementById('permissionToDelete').style.display = 'block';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
    
    document.getElementById('confirmDelete').onclick = async function() {
        try {
            showLoading();
            
            const response = await fetch(CONFIG.routes.destroy.replace(':id', id), {
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
                modal.hide();
                await loadPermissions(); // Recharger la liste
            } else {
                throw new Error(data.message || 'Erreur lors de la suppression');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showError(error.message);
        } finally {
            hideLoading();
        }
    };
}

// Actions en lot
async function deleteSelection() {
    if (selectedPermissions.length === 0) {
        showError('Veuillez sélectionner au moins une permission');
        return;
    }
    
    if (!confirm(`Êtes-vous sûr de vouloir supprimer ${selectedPermissions.length} permission(s) ?`)) {
        return;
    }
    
    try {
        showLoading();
        
        const response = await fetch(CONFIG.routes.bulkDelete, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CONFIG.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                permission_ids: selectedPermissions
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message);
            selectedPermissions = [];
            updateSelectedCount();
            await loadPermissions();
        } else {
            throw new Error(data.message || 'Erreur lors de la suppression');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError(error.message);
    } finally {
        hideLoading();
    }
}

function exportSelection() {
    if (selectedPermissions.length === 0) {
        showError('Veuillez sélectionner au moins une permission');
        return;
    }
    
    const params = new URLSearchParams();
    selectedPermissions.forEach(id => params.append('ids[]', id));
    
    window.open(`${CONFIG.routes.export}?${params.toString()}`);
    showSuccess(`Export de ${selectedPermissions.length} permission(s) lancé`);
}

async function initSystemPermissions() {
    if (!confirm('Voulez-vous initialiser les permissions système ? Cette opération créera toutes les permissions prédéfinies.')) {
        return;
    }
    
    try {
        showLoading();
        
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
            showSuccess(`${data.data.created_count} permission(s) créée(s) avec succès`);
            await loadPermissions();
        } else {
            throw new Error(data.message || 'Erreur lors de l\'initialisation');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showError(error.message);
    } finally {
        hideLoading();
    }
}

// Fonctions utilitaires
function toggleFAB() {
    document.getElementById('fabMenu').classList.toggle('active');
}

function refreshTable() {
    loadPermissions();
}

function applyFilters() {
    document.getElementById('filtersForm').submit();
}

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('filterCategory').value = '';
    document.getElementById('filterRisk').value = '';
    document.getElementById('filterType').value = '';
    applyFilters();
}

// Fonctions d'assistance
function getCategoryInfo(category) {
    const categories = {
        'users': { label: 'Utilisateurs', icon: 'fas fa-users', class: 'bg-info', bgClass: 'bg-primary' },
        'organizations': { label: 'Organisations', icon: 'fas fa-building', class: 'bg-success', bgClass: 'bg-success' },
        'workflow': { label: 'Workflow', icon: 'fas fa-project-diagram', class: 'bg-warning text-dark', bgClass: 'bg-warning' },
        'system': { label: 'Système', icon: 'fas fa-cogs', class: 'bg-dark', bgClass: 'bg-danger' },
        'content': { label: 'Contenu', icon: 'fas fa-file-alt', class: 'bg-secondary', bgClass: 'bg-secondary' },
        'reports': { label: 'Rapports', icon: 'fas fa-chart-bar', class: 'bg-info', bgClass: 'bg-info' }
    };
    
    return categories[category] || { label: category, icon: 'fas fa-key', class: 'bg-light text-dark', bgClass: 'bg-primary' };
}

function calculateRiskLevel(permissionName) {
    const name = permissionName.toLowerCase();
    
    if (['delete', 'destroy', 'system', 'config', 'admin', 'manage'].some(pattern => name.includes(pattern))) {
        return 'high';
    }
    
    if (['create', 'edit', 'update', 'validate', 'assign'].some(pattern => name.includes(pattern))) {
        return 'medium';
    }
    
    return 'low';
}

function getRiskLabel(level) {
    const labels = { 'high': 'Élevé', 'medium': 'Moyen', 'low': 'Faible' };
    return labels[level] || 'Non défini';
}

function generatePermissionDetailsHTML(permission) {
    return `
        <div class="row">
            <div class="col-md-6">
                <div class="card border-0 bg-light mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Informations générales
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Nom :</td>
                                <td>${permission.display_name || permission.name}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Code :</td>
                                <td><code class="text-primary">${permission.name}</code></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Catégorie :</td>
                                <td><span class="badge bg-info">${permission.category}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Niveau de risque :</td>
                                <td><span class="risk-badge risk-${permission.risk_level}">${getRiskLabel(permission.risk_level)}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Type :</td>
                                <td>
                                    <span class="badge ${permission.is_system ? 'bg-secondary' : 'bg-warning text-dark'}">
                                        ${permission.is_system ? 'Système' : 'Personnalisé'}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 bg-light mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            Utilisation
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td class="fw-bold" style="width: 40%;">Rôles assignés :</td>
                                <td><span class="badge bg-primary">${permission.roles_count || 0}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Utilisateurs :</td>
                                <td><span class="badge bg-success">${permission.users_count || 0}</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Créé le :</td>
                                <td>${new Date(permission.created_at).toLocaleDateString('fr-FR')}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Statut :</td>
                                <td><span class="badge bg-success">Actif</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        ${permission.description ? `
        <div class="row">
            <div class="col-12">
                <div class="card border-0 bg-light">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>
                            Description
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">${permission.description}</p>
                    </div>
                </div>
            </div>
        </div>
        ` : ''}
    `;
}

// Fonctions de notification
function showLoading() {
    let overlay = document.querySelector('.loading-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(overlay);
    }
    overlay.classList.add('active');
}

function hideLoading() {
    const overlay = document.querySelector('.loading-overlay');
    if (overlay) {
        overlay.classList.remove('active');
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
        success: { icon: 'fas fa-check-circle', bgClass: 'alert-success' },
        error: { icon: 'fas fa-exclamation-triangle', bgClass: 'alert-danger' },
        warning: { icon: 'fas fa-exclamation-circle', bgClass: 'alert-warning' },
        info: { icon: 'fas fa-info-circle', bgClass: 'alert-info' }
    };
    
    const config = typeConfig[type] || typeConfig.info;
    
    const notification = document.createElement('div');
    notification.className = `alert ${config.bgClass} alert-dismissible fade show`;
    notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; border-radius: 15px; box-shadow: 0 8px 32px rgba(0,0,0,0.15);';
    notification.innerHTML = `
        <i class="${config.icon} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto-dismiss
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

console.log('🔑 Permissions SGLP - Version fonctionnelle chargée');
</script>
@endsection