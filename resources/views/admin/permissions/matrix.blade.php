{{-- resources/views/admin/permissions/matrix.blade.php --}}
@extends('layouts.admin')

@section('title', 'Matrice des Permissions')

@section('content')
<div class="container-fluid">
    <!-- Header avec couleur gabonaise rouge pour "Analyse" -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-table me-2"></i>
                                Matrice des Permissions
                            </h2>
                            <p class="mb-0 opacity-90">Analyse globale des droits d'accès et vue d'ensemble du système de permissions SGLP</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex gap-2 justify-content-end flex-wrap">
                                <button onclick="exportMatrix()" class="btn btn-light btn-lg">
                                    <i class="fas fa-download me-2"></i>
                                    Exporter
                                </button>
                                <button onclick="auditPermissions()" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-search me-2"></i>
                                    Audit
                                </button>
                                <button onclick="refreshMatrix()" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-sync me-2"></i>
                                    Actualiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques analytiques -->
    <div class="row mb-4">
        <div class="col-md-2 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white text-center">
                    <div class="mb-2">
                        <i class="fas fa-user-shield fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="totalRoles">-</h4>
                    <small class="opacity-90">Rôles actifs</small>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white text-center">
                    <div class="mb-2">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="totalPermissions">-</h4>
                    <small class="opacity-90">Permissions</small>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark text-center">
                    <div class="mb-2">
                        <i class="fas fa-link fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="totalAssignments">-</h4>
                    <small>Associations</small>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                <div class="card-body text-white text-center">
                    <div class="mb-2">
                        <i class="fas fa-percentage fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="coverageRate">-%</h4>
                    <small class="opacity-90">Couverture</small>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #6c757d 0%, #adb5bd 100%);">
                <div class="card-body text-white text-center">
                    <div class="mb-2">
                        <i class="fas fa-eye-slash fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="unusedPermissions">-</h4>
                    <small class="opacity-90">Non utilisées</small>
                </div>
            </div>
        </div>

        <div class="col-md-2 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white text-center">
                    <div class="mb-2">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                    <h4 class="mb-1" id="riskAnalysis">-</h4>
                    <small class="opacity-90">Haut risque</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Contrôles et filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <h6 class="mb-0">
                                <i class="fas fa-filter me-2 text-primary"></i>
                                Contrôles d'affichage
                            </h6>
                        </div>
                        <div class="col-md-9">
                            <div class="d-flex gap-2 flex-wrap">
                                <select class="form-select form-select-sm" id="categoryFilter" style="width: auto;">
                                    <option value="">Toutes catégories</option>
                                    <option value="users">Utilisateurs</option>
                                    <option value="organizations">Organisations</option>
                                    <option value="workflow">Workflow</option>
                                    <option value="system">Système</option>
                                    <option value="content">Contenu</option>
                                    <option value="reports">Rapports</option>
                                    <option value="api">API</option>
                                </select>
                                
                                <select class="form-select form-select-sm" id="riskFilter" style="width: auto;">
                                    <option value="">Tous niveaux</option>
                                    <option value="high">Haut risque</option>
                                    <option value="medium">Moyen risque</option>
                                    <option value="low">Faible risque</option>
                                </select>
                                
                                <select class="form-select form-select-sm" id="roleTypeFilter" style="width: auto;">
                                    <option value="">Tous rôles</option>
                                    <option value="system">Système</option>
                                    <option value="custom">Personnalisé</option>
                                </select>
                                
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="showOnlyAssigned" checked>
                                    <label class="form-check-label" for="showOnlyAssigned">
                                        Assignées uniquement
                                    </label>
                                </div>
                                
                                <button class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mode d'affichage -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" id="matrixViewBtn" onclick="switchView('matrix')">
                    <i class="fas fa-table me-2"></i>Vue Matrice
                </button>
                <button type="button" class="btn btn-outline-primary" id="listViewBtn" onclick="switchView('list')">
                    <i class="fas fa-list me-2"></i>Vue Liste
                </button>
                <button type="button" class="btn btn-outline-primary" id="analyticsViewBtn" onclick="switchView('analytics')">
                    <i class="fas fa-chart-bar me-2"></i>Vue Analytics
                </button>
            </div>
        </div>
    </div>

    <!-- Vue Matrice (par défaut) -->
    <div id="matrixView" class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2 text-primary"></i>
                            Matrice Rôles × Permissions
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-success" onclick="selectAllVisible()">
                                <i class="fas fa-check me-1"></i>Tout sélectionner
                            </button>
                            <button class="btn btn-sm btn-outline-warning" onclick="clearAllSelections()">
                                <i class="fas fa-times me-1"></i>Tout désélectionner
                            </button>
                            <button class="btn btn-sm btn-success" onclick="applyBulkChanges()" id="applyChangesBtn" disabled>
                                <i class="fas fa-save me-1"></i>Appliquer (<span id="changeCounter">0</span>)
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-bordered table-hover mb-0" id="permissionMatrix">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th class="sticky-column bg-dark text-white" style="min-width: 200px; z-index: 10;">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-shield me-2"></i>
                                            Rôles / Permissions
                                        </div>
                                    </th>
                                    <!-- Colonnes permissions générées dynamiquement -->
                                </tr>
                            </thead>
                            <tbody id="matrixBody">
                                <!-- Lignes générées par JavaScript -->
                                <tr>
                                    <td colspan="100%" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Chargement de la matrice...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue Liste (masquée par défaut) -->
    <div id="listView" class="row" style="display: none;">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2 text-success"></i>
                        Vue Liste des Associations
                    </h5>
                </div>
                <div class="card-body">
                    <div id="listContent">
                        <!-- Contenu liste généré dynamiquement -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vue Analytics (masquée par défaut) -->
    <div id="analyticsView" class="row" style="display: none;">
        <!-- Graphiques d'analyse -->
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-pie-chart me-2"></i>
                        Répartition par Catégorie
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-bar-chart me-2"></i>
                        Permissions par Rôle
                    </h6>
                </div>
                <div class="card-body">
                    <canvas id="roleChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Analyses détaillées -->
        <div class="col-12">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Alertes Sécurité
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="securityAlerts">
                                <!-- Généré dynamiquement -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0">
                                <i class="fas fa-lightbulb me-2"></i>
                                Recommandations
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="recommendations">
                                <!-- Généré dynamiquement -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>
                                Tendances
                            </h6>
                        </div>
                        <div class="card-body">
                            <div id="trends">
                                <!-- Généré dynamiquement -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAB spécialisé matrice -->
<div class="fab-container">
    <div class="fab-menu" id="fabMenu">
        <div class="fab-main" onclick="toggleFAB()">
            <i class="fas fa-tools fab-icon"></i>
        </div>
        <div class="fab-options">
            <button class="fab-option" style="background: #009e3f;" title="Exporter matrice" onclick="exportMatrix()">
                <i class="fas fa-download"></i>
            </button>
            <button class="fab-option" style="background: #ffcd00; color: #000;" title="Audit sécurité" onclick="auditPermissions()">
                <i class="fas fa-search"></i>
            </button>
            <button class="fab-option" style="background: #003f7f;" title="Rapport complet" onclick="generateReport()">
                <i class="fas fa-file-pdf"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal d'audit -->
<div class="modal fade" id="auditModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-search me-2"></i>
                    Audit des Permissions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="auditResults">
                    <!-- Résultats d'audit -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="button" class="btn btn-success" onclick="exportAudit()">
                    <i class="fas fa-download me-2"></i>Exporter Audit
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles pour la matrice */
.sticky-column {
    position: sticky;
    left: 0;
    background: #212529 !important;
    z-index: 5;
}

.permission-cell {
    width: 60px;
    text-align: center;
    vertical-align: middle;
    position: relative;
    padding: 8px !important;
}

.permission-toggle {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
    background: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    margin: 0 auto;
}

.permission-toggle.active {
    background: #009e3f;
    border-color: #009e3f;
    color: white;
}

.permission-toggle.pending {
    background: #ffcd00;
    border-color: #ffa500;
    color: #212529;
}

.permission-toggle:hover {
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.role-row {
    background: #f8f9fc;
}

.role-row:nth-child(even) {
    background: white;
}

.role-cell {
    background: inherit !important;
    font-weight: 600;
    border-right: 3px solid #dee2e6;
}

.permission-header {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    min-height: 150px;
    white-space: nowrap;
    font-size: 0.8rem;
    font-weight: 600;
}

/* Légende des couleurs */
.legend {
    position: fixed;
    bottom: 100px;
    right: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 15px;
    font-size: 0.85rem;
    z-index: 999;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 5px;
}

.legend-color {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #dee2e6;
}

/* FAB et autres styles */
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
    background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);
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

/* Animations */
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

.loading {
    opacity: 0.6;
    pointer-events: none;
}

/* Responsif */
@media (max-width: 768px) {
    .permission-header {
        writing-mode: horizontal-tb;
        text-orientation: mixed;
        min-height: auto;
        padding: 5px;
    }
    
    .sticky-column {
        min-width: 150px !important;
    }
    
    .legend {
        position: relative;
        bottom: auto;
        right: auto;
        margin-top: 20px;
    }
}
</style>

<script>
// Configuration globale
const MATRIX_CONFIG = {
    routes: {
        data: '{{ route("admin.permissions.matrix.data") }}',
        update: '{{ route("admin.permissions.matrix.update") }}',
        audit: '{{ route("admin.permissions.matrix.audit") }}',
        export: '{{ route("admin.permissions.matrix.export") }}'
    },
    csrf: '{{ csrf_token() }}'
};

// Variables globales
let matrixData = null;
let pendingChanges = new Map();
let currentView = 'matrix';
let filters = {
    category: '',
    risk: '',
    roleType: '',
    onlyAssigned: true
};

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔐 Matrice Permission SGLP - Chargement...');
    
    initEventListeners();
    loadMatrixData();
    setupLegend();
});

// Charger les données de la matrice
async function loadMatrixData() {
    try {
        showLoading('Chargement de la matrice...');
        
        const response = await fetch(MATRIX_CONFIG.routes.data, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': MATRIX_CONFIG.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ filters: filters })
        });
        
        if (!response.ok) throw new Error('Erreur serveur');
        
        const data = await response.json();
        
        if (data.success) {
            matrixData = data.data;
            buildMatrix();
            updateStatistics(data.stats);
        } else {
            throw new Error(data.message || 'Erreur inconnue');
        }
        
    } catch (error) {
        console.error('Erreur chargement matrice:', error);
        showError('Erreur lors du chargement de la matrice: ' + error.message);
    } finally {
        hideLoading();
    }
}

// Construire la matrice
function buildMatrix() {
    if (!matrixData) return;
    
    const table = document.getElementById('permissionMatrix');
    const thead = table.querySelector('thead tr');
    const tbody = document.getElementById('matrixBody');
    
    // Vider le contenu existant
    thead.innerHTML = '<th class="sticky-column bg-dark text-white" style="min-width: 200px; z-index: 10;"><div class="d-flex align-items-center"><i class="fas fa-user-shield me-2"></i>Rôles / Permissions</div></th>';
    tbody.innerHTML = '';
    
    // Construire l'en-tête des permissions
    matrixData.permissions.forEach(permission => {
        if (shouldShowPermission(permission)) {
            const th = document.createElement('th');
            th.className = 'permission-header bg-dark text-white';
            th.style.minWidth = '80px';
            th.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <div class="mb-2">
                        <i class="${getCategoryIcon(permission.category)} fa-lg"></i>
                    </div>
                    <div class="permission-name">${permission.display_name || permission.name}</div>
                    <small class="text-muted">${permission.category}</small>
                    <div class="risk-indicator risk-${permission.risk_level}"></div>
                </div>
            `;
            thead.appendChild(th);
        }
    });
    
    // Construire les lignes des rôles
    matrixData.roles.forEach(role => {
        if (shouldShowRole(role)) {
            const tr = document.createElement('tr');
            tr.className = 'role-row';
            tr.dataset.roleId = role.id;
            
            // Cellule du rôle
            const roleCell = document.createElement('td');
            roleCell.className = 'sticky-column role-cell';
            roleCell.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="role-icon me-3">
                        <div class="icon-circle ${role.is_system ? 'bg-secondary' : 'bg-primary'}" style="width: 40px; height: 40px;">
                            <i class="fas ${role.is_system ? 'fa-shield-alt' : 'fa-user-cog'} text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="fw-bold">${role.display_name || role.name}</div>
                        <small class="text-muted">Niveau ${role.level}</small>
                        <div>
                            <span class="badge ${role.is_system ? 'bg-secondary' : 'bg-info'} badge-sm">
                                ${role.is_system ? 'Système' : 'Personnalisé'}
                            </span>
                        </div>
                    </div>
                </div>
            `;
            
            tr.appendChild(roleCell);
            
            // Cellules des permissions
            matrixData.permissions.forEach(permission => {
                if (shouldShowPermission(permission)) {
                    const cell = document.createElement('td');
                    cell.className = 'permission-cell';
                    cell.dataset.roleId = role.id;
                    cell.dataset.permissionId = permission.id;
                    
                    const hasPermission = role.permissions && role.permissions.includes(permission.id);
                    const isPending = pendingChanges.has(`${role.id}-${permission.id}`);
                    
                    cell.innerHTML = `
                        <div class="permission-toggle ${hasPermission ? 'active' : ''} ${isPending ? 'pending' : ''}" 
                             onclick="togglePermission(${role.id}, ${permission.id})"
                             title="${role.display_name} - ${permission.display_name}">
                            <i class="fas ${hasPermission ? 'fa-check' : 'fa-times'}"></i>
                        </div>
                    `;
                    
                    tr.appendChild(cell);
                }
            });
            
            tbody.appendChild(tr);
        }
    });
    
    console.log(`✅ Matrice construite: ${matrixData.roles.length} rôles × ${matrixData.permissions.length} permissions`);
}

// Basculer une permission
function togglePermission(roleId, permissionId) {
    const key = `${roleId}-${permissionId}`;
    const cell = document.querySelector(`[data-role-id="${roleId}"][data-permission-id="${permissionId}"]`);
    const toggle = cell.querySelector('.permission-toggle');
    
    if (pendingChanges.has(key)) {
        // Annuler le changement
        pendingChanges.delete(key);
        toggle.classList.remove('pending');
    } else {
        // Ajouter le changement
        const currentState = toggle.classList.contains('active');
        pendingChanges.set(key, {
            roleId: roleId,
            permissionId: permissionId,
            action: currentState ? 'remove' : 'add',
            currentState: currentState
        });
        toggle.classList.add('pending');
    }
    
    updateChangeCounter();
}

// Mettre à jour le compteur de changements
function updateChangeCounter() {
    const count = pendingChanges.size;
    document.getElementById('changeCounter').textContent = count;
    document.getElementById('applyChangesBtn').disabled = count === 0;
}

// Appliquer les changements en lot
async function applyBulkChanges() {
    if (pendingChanges.size === 0) return;
    
    try {
        showLoading('Application des modifications...');
        
        const changes = Array.from(pendingChanges.values());
        
        const response = await fetch(MATRIX_CONFIG.routes.update, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': MATRIX_CONFIG.csrf,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ changes: changes })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(`${changes.length} modification(s) appliquée(s) avec succès`);
            
            // Mettre à jour l'interface
            changes.forEach(change => {
                const cell = document.querySelector(`[data-role-id="${change.roleId}"][data-permission-id="${change.permissionId}"]`);
                const toggle = cell.querySelector('.permission-toggle');
                
                toggle.classList.remove('pending');
                
                if (change.action === 'add') {
                    toggle.classList.add('active');
                    toggle.querySelector('i').className = 'fas fa-check';
                } else {
                    toggle.classList.remove('active');
                    toggle.querySelector('i').className = 'fas fa-times';
                }
            });
            
            pendingChanges.clear();
            updateChangeCounter();
            updateStatistics(data.stats);
            
        } else {
            throw new Error(data.message || 'Erreur lors de la mise à jour');
        }
        
    } catch (error) {
        console.error('Erreur application changements:', error);
        showError('Erreur: ' + error.message);
    } finally {
        hideLoading();
    }
}

// Fonctions d'affichage
function switchView(viewType) {
    // Masquer toutes les vues
    document.getElementById('matrixView').style.display = 'none';
    document.getElementById('listView').style.display = 'none';
    document.getElementById('analyticsView').style.display = 'none';
    
    // Réinitialiser les boutons
    document.querySelectorAll('.btn-group .btn').forEach(btn => btn.classList.remove('active'));
    
    // Afficher la vue sélectionnée
    document.getElementById(viewType + 'View').style.display = 'block';
    document.getElementById(viewType + 'ViewBtn').classList.add('active');
    
    currentView = viewType;
    
    // Charger le contenu spécifique
    if (viewType === 'list') {
        buildListView();
    } else if (viewType === 'analytics') {
        buildAnalyticsView();
    }
}

// Construire la vue liste
function buildListView() {
    const container = document.getElementById('listContent');
    container.innerHTML = '';
    
    if (!matrixData) return;
    
    let html = '<div class="row">';
    
    matrixData.roles.forEach(role => {
        if (!shouldShowRole(role)) return;
        
        const rolePermissions = matrixData.permissions.filter(p => 
            role.permissions && role.permissions.includes(p.id) && shouldShowPermission(p)
        );
        
        html += `
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header ${role.is_system ? 'bg-secondary' : 'bg-primary'} text-white">
                        <div class="d-flex align-items-center">
                            <i class="fas ${role.is_system ? 'fa-shield-alt' : 'fa-user-cog'} me-2"></i>
                            <div>
                                <h6 class="mb-0">${role.display_name || role.name}</h6>
                                <small>Niveau ${role.level}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-info">${rolePermissions.length} permission(s)</span>
                        </div>
                        <div style="max-height: 200px; overflow-y: auto;">
        `;
        
        rolePermissions.forEach(permission => {
            html += `
                <div class="d-flex align-items-center mb-2">
                    <div class="permission-icon me-2">
                        <i class="${getCategoryIcon(permission.category)} text-${getCategoryColor(permission.category)}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${permission.display_name || permission.name}</div>
                        <small class="text-muted">${permission.category}</small>
                    </div>
                    <span class="badge risk-${permission.risk_level}">${permission.risk_level}</span>
                </div>
            `;
        });
        
        html += `
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Construire la vue analytics
function buildAnalyticsView() {
    if (!matrixData) return;
    
    // Graphiques avec Chart.js (si disponible)
    buildCategoryChart();
    buildRoleChart();
    buildSecurityAlerts();
    buildRecommendations();
    buildTrends();
}

// Fonctions utilitaires
function shouldShowRole(role) {
    if (filters.roleType && filters.roleType !== (role.is_system ? 'system' : 'custom')) {
        return false;
    }
    return true;
}

function shouldShowPermission(permission) {
    if (filters.category && permission.category !== filters.category) {
        return false;
    }
    if (filters.risk && permission.risk_level !== filters.risk) {
        return false;
    }
    if (filters.onlyAssigned) {
        // Vérifier si la permission est assignée à au moins un rôle visible
        const isAssigned = matrixData.roles.some(role => 
            shouldShowRole(role) && role.permissions && role.permissions.includes(permission.id)
        );
        if (!isAssigned) return false;
    }
    return true;
}

function getCategoryIcon(category) {
    const icons = {
        users: 'fas fa-users',
        organizations: 'fas fa-building',
        workflow: 'fas fa-project-diagram',
        system: 'fas fa-cogs',
        content: 'fas fa-file-alt',
        reports: 'fas fa-chart-bar'
    };
    return icons[category] || 'fas fa-key';
}

function getCategoryColor(category) {
    const colors = {
        users: 'primary',
        organizations: 'success',
        workflow: 'warning',
        system: 'danger',
        content: 'secondary',
        reports: 'info'
    };
    return colors[category] || 'primary';
}

// Event listeners
function initEventListeners() {
    // Filtres
    document.getElementById('categoryFilter').addEventListener('change', function() {
        filters.category = this.value;
        applyFilters();
    });
    
    document.getElementById('riskFilter').addEventListener('change', function() {
        filters.risk = this.value;
        applyFilters();
    });
    
    document.getElementById('roleTypeFilter').addEventListener('change', function() {
        filters.roleType = this.value;
        applyFilters();
    });
    
    document.getElementById('showOnlyAssigned').addEventListener('change', function() {
        filters.onlyAssigned = this.checked;
        applyFilters();
    });
    
    // FAB
    document.addEventListener('click', function(event) {
        const fabMenu = document.getElementById('fabMenu');
        if (!fabMenu.contains(event.target)) {
            fabMenu.classList.remove('active');
        }
    });
}

function applyFilters() {
    buildMatrix();
}

function resetFilters() {
    filters = {
        category: '',
        risk: '',
        roleType: '',
        onlyAssigned: true
    };
    
    document.getElementById('categoryFilter').value = '';
    document.getElementById('riskFilter').value = '';
    document.getElementById('roleTypeFilter').value = '';
    document.getElementById('showOnlyAssigned').checked = true;
    
    buildMatrix();
}

// Actions principales
function toggleFAB() {
    document.getElementById('fabMenu').classList.toggle('active');
}

function refreshMatrix() {
    loadMatrixData();
}

async function exportMatrix() {
    try {
        const response = await fetch(MATRIX_CONFIG.routes.export, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': MATRIX_CONFIG.csrf,
            },
            body: JSON.stringify({ 
                filters: filters,
                view: currentView
            })
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `matrice-permissions-${new Date().toISOString().split('T')[0]}.xlsx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showSuccess('Export de la matrice lancé avec succès');
        } else {
            throw new Error('Erreur lors de l\'export');
        }
    } catch (error) {
        console.error('Erreur export:', error);
        showError('Erreur lors de l\'export: ' + error.message);
    }
}

async function auditPermissions() {
    try {
        showLoading('Audit en cours...');
        
        const response = await fetch(MATRIX_CONFIG.routes.audit, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': MATRIX_CONFIG.csrf,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            displayAuditResults(data.data);
            const modal = new bootstrap.Modal(document.getElementById('auditModal'));
            modal.show();
        } else {
            throw new Error(data.message || 'Erreur d\'audit');
        }
        
    } catch (error) {
        console.error('Erreur audit:', error);
        showError('Erreur lors de l\'audit: ' + error.message);
    } finally {
        hideLoading();
    }
}

// Fonctions d'assistance
function setupLegend() {
    const legend = document.createElement('div');
    legend.className = 'legend';
    legend.innerHTML = `
        <h6 class="mb-3">Légende</h6>
        <div class="legend-item">
            <div class="legend-color" style="background: #009e3f; border-color: #009e3f;"></div>
            <span>Permission accordée</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: white;"></div>
            <span>Permission refusée</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background: #ffcd00; border-color: #ffa500;"></div>
            <span>Modification en attente</span>
        </div>
    `;
    
    document.body.appendChild(legend);
}

function updateStatistics(stats) {
    if (!stats) return;
    
    document.getElementById('totalRoles').textContent = stats.total_roles || 0;
    document.getElementById('totalPermissions').textContent = stats.total_permissions || 0;
    document.getElementById('totalAssignments').textContent = stats.total_assignments || 0;
    document.getElementById('coverageRate').textContent = (stats.coverage_rate || 0).toFixed(1);
    document.getElementById('unusedPermissions').textContent = stats.unused_permissions || 0;
    document.getElementById('riskAnalysis').textContent = stats.high_risk_count || 0;
}

// Fonctions de notification
function showLoading(message = 'Chargement...') {
    // Implémenter loading overlay
    console.log('Loading:', message);
}

function hideLoading() {
    // Masquer loading overlay
}

function showSuccess(message) {
    // Notification de succès
    console.log('Success:', message);
}

function showError(message) {
    // Notification d'erreur
    console.log('Error:', message);
}

console.log('🔐 Matrice Permission SGLP - Initialisé');
</script>
@endsection