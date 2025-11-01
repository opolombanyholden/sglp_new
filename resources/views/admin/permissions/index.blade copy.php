{{-- resources/views/admin/permissions/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Gestion des Permissions')

@push('styles')
<style>
/* Variables CSS */
:root {
    --gabon-blue: #003f7f;
    --gabon-green: #009639;
    --gabon-yellow: #fcd116;
    --gabon-red: #e74c3c;
}

/* Styles généraux */
.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
    margin-bottom: 1.5rem;
}

.permission-category-card {
    transition: all 0.3s ease;
    border: 1px solid #e3e6f0;
}

.permission-category-card:hover {
    box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
}

.category-header {
    padding: 1.25rem;
    color: white;
    position: relative;
}

.category-header.users {
    background: linear-gradient(135deg, #4e73df, #224abe);
}

.category-header.organizations {
    background: linear-gradient(135deg, #1cc88a, #13855c);
}

.category-header.workflow {
    background: linear-gradient(135deg, #f6c23e, #d4aa00);
}

.category-header.system {
    background: linear-gradient(135deg, #e74a3b, #c23321);
}

.category-header.content {
    background: linear-gradient(135deg, #36b9cc, #258391);
}

.category-header.reports {
    background: linear-gradient(135deg, #6f42c1, #5a32a3);
}

.category-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.category-title {
    font-size: 1.1rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
}

.category-stats {
    font-size: 0.9rem;
    opacity: 0.9;
    margin: 0;
}

.permissions-grid {
    padding: 1.5rem;
    background: #f8f9fc;
}

.permission-item {
    background: white;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.permission-item:hover {
    border-color: var(--gabon-blue);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.permission-info {
    flex: 1;
}

.permission-name {
    font-weight: 600;
    color: var(--gabon-blue);
    margin: 0 0 0.25rem 0;
    font-size: 0.95rem;
}

.permission-description {
    color: #6c757d;
    font-size: 0.85rem;
    margin: 0 0 0.5rem 0;
    line-height: 1.4;
}

.permission-code {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.8rem;
    color: #666;
    background: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.permission-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.permission-risk {
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.risk-low {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.risk-medium {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.risk-high {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.permission-stats {
    text-align: center;
    min-width: 50px;
}

.permission-count {
    display: block;
    font-weight: 700;
    color: var(--gabon-blue);
    font-size: 1.1rem;
}

.permission-label {
    color: #6c757d;
    font-size: 0.7rem;
    text-transform: uppercase;
}

.permission-actions {
    display: flex;
    gap: 0.25rem;
}

.btn-permission {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-permission-view {
    background: #e3f2fd;
    color: #1976d2;
}

.btn-permission-edit {
    background: #fff3e0;
    color: #f57c00;
}

.btn-permission-delete {
    background: #ffebee;
    color: #d32f2f;
}

.btn-permission:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Filtres */
.filters-container {
    background: white;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.filter-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.5rem;
}

.filter-input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.9rem;
    transition: border-color 0.2s ease;
}

.filter-input:focus {
    outline: none;
    border-color: var(--gabon-blue);
    box-shadow: 0 0 0 3px rgba(79, 115, 223, 0.1);
}

.btn-filter {
    background: var(--gabon-blue);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.btn-filter:hover {
    background: #224abe;
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 3rem 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .permission-item {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .permission-meta {
        justify-content: space-between;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- En-tête avec statistiques -->
    <div class="row">
        <div class="col-12">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-key text-primary me-2"></i>
                    Gestion des Permissions
                </h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Nouvelle Permission
                    </a>
                    <button type="button" class="btn btn-secondary" onclick="exportPermissions()">
                        <i class="fas fa-download me-1"></i>
                        Exporter
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques rapides -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Permissions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total_permissions'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-key fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Permissions Système
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['system_permissions'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cog fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Utilisateurs avec Permissions
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['users_with_permissions'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Permissions Haute Sécurité
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['high_risk_permissions'] ?? 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters-container">
        <form method="GET" action="{{ route('admin.permissions.index') }}">
            <div class="filter-row">
                <div class="filter-group">
                    <label class="filter-label">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="filter-input" placeholder="Nom ou description de la permission...">
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Catégorie</label>
                    <select name="category" class="filter-input">
                        <option value="">Toutes les catégories</option>
                        <option value="users" {{ request('category') === 'users' ? 'selected' : '' }}>Utilisateurs</option>
                        <option value="organizations" {{ request('category') === 'organizations' ? 'selected' : '' }}>Organisations</option>
                        <option value="workflow" {{ request('category') === 'workflow' ? 'selected' : '' }}>Workflow</option>
                        <option value="system" {{ request('category') === 'system' ? 'selected' : '' }}>Système</option>
                        <option value="content" {{ request('category') === 'content' ? 'selected' : '' }}>Contenu</option>
                        <option value="reports" {{ request('category') === 'reports' ? 'selected' : '' }}>Rapports</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Niveau de Risque</label>
                    <select name="risk" class="filter-input">
                        <option value="">Tous les niveaux</option>
                        <option value="low" {{ request('risk') === 'low' ? 'selected' : '' }}>Faible</option>
                        <option value="medium" {{ request('risk') === 'medium' ? 'selected' : '' }}>Moyen</option>
                        <option value="high" {{ request('risk') === 'high' ? 'selected' : '' }}>Élevé</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Type</label>
                    <select name="type" class="filter-input">
                        <option value="">Tous les types</option>
                        <option value="system" {{ request('type') === 'system' ? 'selected' : '' }}>Système</option>
                        <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>Personnalisé</option>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="btn-filter">
                        <i class="fas fa-search me-1"></i>
                        Filtrer
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Liste des permissions par catégorie -->
    @forelse($permissionsByCategory as $category => $categoryPermissions)
        <div class="permission-category-card">
            <div class="category-header {{ $category }}">
                <div class="d-flex align-items-center">
                    <div class="category-icon">
                        @switch($category)
                            @case('users')
                                <i class="fas fa-users"></i>
                                @break
                            @case('organizations')
                                <i class="fas fa-building"></i>
                                @break
                            @case('workflow')
                                <i class="fas fa-project-diagram"></i>
                                @break
                            @case('system')
                                <i class="fas fa-cogs"></i>
                                @break
                            @case('content')
                                <i class="fas fa-edit"></i>
                                @break
                            @case('reports')
                                <i class="fas fa-chart-bar"></i>
                                @break
                            @default
                                <i class="fas fa-key"></i>
                        @endswitch
                    </div>
                    <div>
                        <h4 class="category-title">
                            @switch($category)
                                @case('users')
                                    Gestion des Utilisateurs
                                    @break
                                @case('organizations')
                                    Gestion des Organisations
                                    @break
                                @case('workflow')
                                    Workflow et Processus
                                    @break
                                @case('system')
                                    Administration Système
                                    @break
                                @case('content')
                                    Gestion du Contenu
                                    @break
                                @case('reports')
                                    Rapports et Statistiques
                                    @break
                                @default
                                    {{ ucfirst($category) }}
                            @endswitch
                        </h4>
                        <p class="category-stats">
                            {{ $categoryPermissions->count() }} permission{{ $categoryPermissions->count() > 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="permissions-grid">
                @forelse($categoryPermissions as $permission)
                <div class="permission-item">
                    <div class="permission-info">
                        <h5 class="permission-name">{{ $permission->display_name ?? $permission->name }}</h5>
                        <p class="permission-description">{{ $permission->description ?? 'Aucune description disponible' }}</p>
                        <div class="permission-code">
                            <i class="fas fa-code me-1"></i>{{ $permission->name }}
                        </div>
                    </div>
                    
                    <div class="permission-meta">
                        <div class="permission-risk risk-{{ $permission->risk_level ?? 'low' }}">
                            {{ strtoupper($permission->risk_level ?? 'low') }}
                        </div>
                        
                        <div class="permission-stats">
                            <span class="permission-count">{{ $permission->roles_count ?? 0 }}</span>
                            <small class="permission-label">Rôles</small>
                        </div>
                        
                        <div class="permission-actions">
                            <button type="button" class="btn-permission btn-permission-view" 
                                    onclick="viewPermission({{ $permission->id }})" title="Voir détails">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if(!($permission->is_system ?? false))
                            <button type="button" class="btn-permission btn-permission-edit" 
                                    onclick="editPermission({{ $permission->id }})" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn-permission btn-permission-delete" 
                                    onclick="deletePermission({{ $permission->id }}, '{{ addslashes($permission->display_name ?? $permission->name) }}')" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i class="fas fa-key fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Aucune permission dans cette catégorie</h5>
                    <p class="text-muted">Cette catégorie ne contient actuellement aucune permission.</p>
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="card shadow">
            <div class="card-body">
                <div class="empty-state">
                    <i class="fas fa-key"></i>
                    <h4 class="text-gray-600">Aucune permission configurée</h4>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'category', 'risk', 'type']))
                            Aucune permission ne correspond aux critères de filtrage.
                            <br>
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-link">
                                Réinitialiser les filtres
                            </a>
                        @else
                            Commencez par créer votre première permission.
                            <br>
                            <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus me-1"></i>
                                Créer une permission
                            </a>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer la permission "<strong id="permissionName"></strong>" ?</p>
                <div class="alert alert-warning">
                    <strong>Attention :</strong> Cette action est irréversible et supprimera également 
                    toutes les associations avec les rôles.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash me-1"></i>
                    Supprimer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de détails de permission -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key text-primary me-2"></i>
                    Détails de la permission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="permissionDetails">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Variables globales
let permissionToDelete = null;

// Fonctions principales
function viewPermission(id) {
    fetch(`/admin/permissions/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('permissionDetails').innerHTML = generatePermissionDetailsHTML(data.permission);
                new bootstrap.Modal(document.getElementById('viewModal')).show();
            } else {
                alert('Erreur lors du chargement des détails de la permission');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement des détails');
        });
}

function editPermission(id) {
    window.location.href = `/admin/permissions/${id}/edit`;
}

function deletePermission(id, name) {
    permissionToDelete = id;
    document.getElementById('permissionName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function exportPermissions() {
    const searchParams = new URLSearchParams(window.location.search);
    window.location.href = `/admin/permissions/export?${searchParams.toString()}`;
}

// Génération du HTML des détails
function generatePermissionDetailsHTML(permission) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted">Informations générales</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Nom :</strong></td>
                        <td>${permission.display_name || permission.name}</td>
                    </tr>
                    <tr>
                        <td><strong>Code :</strong></td>
                        <td><code>${permission.name}</code></td>
                    </tr>
                    <tr>
                        <td><strong>Catégorie :</strong></td>
                        <td><span class="badge bg-info">${permission.category || 'Non définie'}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Niveau de risque :</strong></td>
                        <td><span class="badge bg-${getRiskColor(permission.risk_level)}">${permission.risk_level || 'low'}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Type :</strong></td>
                        <td>${permission.is_system ? 'Système' : 'Personnalisé'}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">Utilisation</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Rôles assignés :</strong></td>
                        <td>${permission.roles_count || 0}</td>
                    </tr>
                    <tr>
                        <td><strong>Créé le :</strong></td>
                        <td>${formatDate(permission.created_at)}</td>
                    </tr>
                    <tr>
                        <td><strong>Modifié le :</strong></td>
                        <td>${formatDate(permission.updated_at)}</td>
                    </tr>
                </table>
            </div>
        </div>
        ${permission.description ? `
        <div class="row mt-3">
            <div class="col-12">
                <h6 class="text-muted">Description</h6>
                <p class="text-muted">${permission.description}</p>
            </div>
        </div>
        ` : ''}
        ${permission.roles && permission.roles.length > 0 ? `
        <div class="row mt-3">
            <div class="col-12">
                <h6 class="text-muted">Rôles associés</h6>
                <div class="d-flex flex-wrap gap-1">
                    ${permission.roles.map(role => `<span class="badge bg-secondary">${role.display_name || role.name}</span>`).join('')}
                </div>
            </div>
        </div>
        ` : ''}
    `;
}

// Fonctions utilitaires
function getRiskColor(level) {
    switch(level) {
        case 'high': return 'danger';
        case 'medium': return 'warning';
        case 'low': 
        default: return 'success';
    }
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Confirmation de suppression
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (permissionToDelete) {
                fetch(`/admin/permissions/${permissionToDelete}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Erreur lors de la suppression');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de la suppression');
                });
                
                bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
            }
        });
    }
});
</script>
@endpush