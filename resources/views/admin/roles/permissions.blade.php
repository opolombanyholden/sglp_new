{{-- resources/views/admin/roles/permissions.blade.php --}}
@extends('layouts.admin')
@section('title', 'Gestion des Permissions')

@section('content')
<div class="container-fluid">
    <!-- Header avec couleur gabonaise jaune pour "Permissions" -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #ffcd00 0%, #ffd700 100%);">
                <div class="card-body" style="color: #212529;">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-key me-2"></i>
                                Gestion des Permissions
                            </h2>
                            <p class="mb-0" style="opacity: 0.9;">
                                Configurez les permissions pour le rôle "{{ $role->display_name ?? $role->name }}"
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span style="background: rgba(33, 37, 41, 0.2); padding: 8px 16px; border-radius: 20px; font-weight: 600; margin-right: 1rem;">
                                Niveau {{ $role->level }}/10
                            </span>
                            <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-outline-dark btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour au rôle
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Résumé des sélections (Sticky) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 20px;">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); border-radius: 15px 15px 0 0;">
                    <h5 class="mb-0">
                        <i class="fas fa-list-check me-2"></i>
                        Permissions Sélectionnées
                        <span style="background: rgba(255, 255, 255, 0.2); padding: 4px 12px; border-radius: 15px; font-size: 0.9rem; margin-left: 1rem;" id="selectedCount">{{ $rolePermissions->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div id="selectedPermissions" style="max-height: 300px; overflow-y: auto;">
                        @if($rolePermissions->count() > 0)
                            @foreach($rolePermissions as $permission)
                            <div class="selected-permission">
                                {{ $permission->display_name }}
                                <button type="button" class="remove-permission" onclick="removePermission({{ $permission->id }})">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center">Aucune permission sélectionnée</p>
                        @endif
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="clearAllSelections()" style="border-radius: 20px;">
                            <i class="fas fa-times me-2"></i>
                            Tout désélectionner
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Filtres et recherche -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="position-relative">
                                        <i class="fas fa-search position-absolute" style="left: 20px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
                                        <input type="text" 
                                               class="form-control" 
                                               id="searchPermissions" 
                                               placeholder="Rechercher une permission..."
                                               style="border-radius: 25px; padding: 12px 20px 12px 50px; border: 2px solid #e3e6f0;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button class="btn btn-sm active" data-category="all" style="border-radius: 20px; border: 2px solid #009e3f; background: #009e3f; color: white;">Toutes</button>
                                        @foreach($categories as $categoryKey => $categoryInfo)
                                        <button class="btn btn-sm btn-outline-secondary" data-category="{{ $categoryKey }}" style="border-radius: 20px;">
                                            <i class="fas fa-{{ $categoryInfo['icon'] }} me-1"></i>{{ $categoryInfo['label'] }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catégories de permissions -->
            <form id="permissionsForm" action="{{ route('admin.roles.permissions.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    @foreach($permissionsByCategory as $category => $permissions)
                    @php
                        $categoryInfo = $categories[$category] ?? ['icon' => 'cog', 'label' => ucfirst($category), 'description' => 'Gestion ' . $category, 'color' => '#6c757d'];
                        $selectedInCategory = $permissions->whereIn('id', $rolePermissionIds)->count();
                    @endphp
                    
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm category-card" data-category="{{ $category }}">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, {{ $categoryInfo['color'] }}, {{ $categoryInfo['color'] }}dd);">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1rem;">
                                            <i class="fas fa-{{ $categoryInfo['icon'] }} fa-lg"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold">{{ $categoryInfo['label'] }}</h6>
                                            <small style="opacity: 0.9;">{{ $categoryInfo['description'] }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <span style="background: rgba(255, 255, 255, 0.2); padding: 4px 12px; border-radius: 15px; font-size: 0.85rem;">
                                            <span class="selected-in-category" data-category="{{ $category }}">{{ $selectedInCategory }}</span> / {{ $permissions->count() }}
                                        </span>
                                        <button type="button" 
                                                class="btn btn-sm select-all-category" 
                                                data-category="{{ $category }}"
                                                onclick="toggleCategorySelection('{{ $category }}')"
                                                style="background: rgba(255, 255, 255, 0.2); border: none; color: inherit; border-radius: 15px; padding: 6px 12px;">
                                            @if($selectedInCategory == $permissions->count())
                                                <i class="fas fa-check-square me-1"></i>Tout désélectionner
                                            @else
                                                <i class="fas fa-square me-1"></i>Tout sélectionner
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @foreach($permissions as $permission)
                                @php
                                    $isSelected = in_array($permission->id, $rolePermissionIds);
                                @endphp
                                <div class="permission-item d-flex align-items-start gap-3 p-3 mb-2 {{ $isSelected ? 'selected' : '' }}" 
                                     data-permission="{{ $permission->id }}"
                                     data-category="{{ $category }}"
                                     onclick="togglePermission({{ $permission->id }})"
                                     style="background: {{ $isSelected ? 'rgba(0, 158, 63, 0.1)' : '#f8f9fc' }}; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; border: 2px solid {{ $isSelected ? '#009e3f' : 'transparent' }};">
                                    <div class="permission-checkbox {{ $isSelected ? 'checked' : '' }}" id="checkbox-{{ $permission->id }}" style="width: 20px; height: 20px; border: 2px solid {{ $isSelected ? '#009e3f' : '#ced4da' }}; border-radius: 4px; background: {{ $isSelected ? '#009e3f' : 'white' }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                                        <i class="fas fa-check" style="display: {{ $isSelected ? 'block' : 'none' }}; color: white; font-size: 0.8rem;"></i>
                                    </div>
                                    <div class="permission-details flex-grow-1">
                                        <div class="permission-name fw-bold mb-1" style="color: #003f7f;">
                                            {{ $permission->display_name }}
                                        </div>
                                        @if($permission->description)
                                        <div class="permission-description text-muted small mb-2">
                                            {{ $permission->description }}
                                        </div>
                                        @endif
                                        <div>
                                            <span class="permission-category badge" style="background: rgba({{ $categoryInfo['color'] }}, 0.1); color: {{ $categoryInfo['color'] }}; font-size: 0.75rem; padding: 4px 8px;">
                                                <i class="fas fa-{{ $categoryInfo['icon'] }} me-1"></i>
                                                {{ $categoryInfo['label'] }}
                                            </span>
                                        </div>
                                    </div>
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $permission->id }}"
                                           id="permission-{{ $permission->id }}"
                                           style="display: none;"
                                           {{ $isSelected ? 'checked' : '' }}>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Actions en bas (Sticky) -->
                <div class="card border-0 shadow-lg" style="position: sticky; bottom: 20px; z-index: 100;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('admin.roles.show', $role->id) }}" class="btn btn-secondary btn-lg" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Retour au rôle
                                </a>
                            </div>
                            
                            <div>
                                <button type="button" class="btn btn-outline-info btn-lg me-3" onclick="previewPermissions()" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-eye me-2"></i>
                                    Aperçu
                                </button>
                                
                                <button type="button" class="btn btn-outline-warning btn-lg me-3" onclick="resetToDefault()" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-undo me-2"></i>
                                    Reset
                                </button>
                                
                                <button type="submit" class="btn btn-lg" id="saveBtn" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-save me-2"></i>
                                    <span>Enregistrer les permissions</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) tricolore spécialisé permissions -->
<div style="position: fixed; bottom: 2rem; right: 2rem; z-index: 1000;">
    <div id="fabMenu">
        <div onclick="toggleFAB()" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.3s ease;">
            <i class="fas fa-key" style="color: white; font-size: 1.5rem;"></i>
        </div>
        <div class="fab-options" style="position: absolute; bottom: 70px; right: 0; display: flex; flex-direction: column; gap: 10px; opacity: 0; visibility: hidden; transition: all 0.3s ease;">
            <button onclick="previewPermissions()" style="width: 45px; height: 45px; border-radius: 50%; border: none; background: #009e3f; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;" title="Aperçu">
                <i class="fas fa-eye"></i>
            </button>
            <button onclick="resetToDefault()" style="width: 45px; height: 45px; border-radius: 50%; border: none; background: #ffcd00; color: #000; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;" title="Reset">
                <i class="fas fa-undo"></i>
            </button>
            <button onclick="clearAllSelections()" style="width: 45px; height: 45px; border-radius: 50%; border: none; background: #003f7f; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;" title="Tout désélectionner">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<style>
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

.permission-item:hover {
    background: rgba(0, 158, 63, 0.05) !important;
    border-color: rgba(0, 158, 63, 0.2) !important;
}

.permission-item.selected {
    background: rgba(0, 158, 63, 0.1) !important;
    border-color: #009e3f !important;
}

.permission-checkbox.checked {
    background: #009e3f !important;
    border-color: #009e3f !important;
}

.form-control:focus {
    border-color: #009e3f;
    box-shadow: 0 0 0 3px rgba(0, 158, 63, 0.1);
}

.selected-permission {
    background: rgba(0, 158, 63, 0.1);
    border: 1px solid rgba(0, 158, 63, 0.2);
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    margin: 0.25rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.85rem;
}

.remove-permission {
    background: none;
    border: none;
    color: #8b1538;
    cursor: pointer;
    padding: 0;
    margin-left: 0.25rem;
}

#fabMenu.active .fab-options {
    opacity: 1;
    visibility: visible;
}

.fab-options button:hover {
    transform: scale(1.1);
}
</style>

<script>
let selectedPermissions = new Set(@json($rolePermissionIds));
let allPermissions = @json($allPermissions->pluck('id')->toArray());
let originalPermissions = new Set(@json($rolePermissionIds)); // Pour le reset

// Initialiser les permissions
document.addEventListener('DOMContentLoaded', function() {
    console.log('Module Gestion Permissions SGLP chargé');
    initializeSearch();
    initializeFilters();
    updateSelectionSummary();
});

/**
 * Basculer une permission
 */
function togglePermission(permissionId) {
    if (selectedPermissions.has(permissionId)) {
        selectedPermissions.delete(permissionId);
        updatePermissionVisual(permissionId, false);
    } else {
        selectedPermissions.add(permissionId);
        updatePermissionVisual(permissionId, true);
    }
    
    updateSelectionSummary();
    updateCategoryCounters();
}

/**
 * Mettre à jour l'affichage visuel d'une permission
 */
function updatePermissionVisual(permissionId, selected) {
    const item = document.querySelector(`[data-permission="${permissionId}"]`);
    const checkbox = document.getElementById(`checkbox-${permissionId}`);
    const input = document.getElementById(`permission-${permissionId}`);
    
    if (selected) {
        item.classList.add('selected');
        item.style.background = 'rgba(0, 158, 63, 0.1)';
        item.style.borderColor = '#009e3f';
        checkbox.classList.add('checked');
        checkbox.style.background = '#009e3f';
        checkbox.style.borderColor = '#009e3f';
        checkbox.querySelector('i').style.display = 'block';
        input.checked = true;
    } else {
        item.classList.remove('selected');
        item.style.background = '#f8f9fc';
        item.style.borderColor = 'transparent';
        checkbox.classList.remove('checked');
        checkbox.style.background = 'white';
        checkbox.style.borderColor = '#ced4da';
        checkbox.querySelector('i').style.display = 'none';
        input.checked = false;
    }
}

/**
 * Basculer toute une catégorie
 */
function toggleCategorySelection(category) {
    const categoryPermissions = document.querySelectorAll(`[data-category="${category}"]`);
    const button = document.querySelector(`[data-category="${category}"].select-all-category`);
    
    let allSelected = true;
    categoryPermissions.forEach(function(item) {
        const permissionId = parseInt(item.dataset.permission);
        if (!selectedPermissions.has(permissionId)) {
            allSelected = false;
        }
    });
    
    categoryPermissions.forEach(function(item) {
        const permissionId = parseInt(item.dataset.permission);
        if (allSelected) {
            selectedPermissions.delete(permissionId);
            updatePermissionVisual(permissionId, false);
        } else {
            selectedPermissions.add(permissionId);
            updatePermissionVisual(permissionId, true);
        }
    });
    
    // Mettre à jour le texte du bouton
    if (allSelected) {
        button.innerHTML = '<i class="fas fa-square me-1"></i>Tout sélectionner';
    } else {
        button.innerHTML = '<i class="fas fa-check-square me-1"></i>Tout désélectionner';
    }
    
    updateSelectionSummary();
    updateCategoryCounters();
}

/**
 * Mettre à jour les compteurs de catégorie
 */
function updateCategoryCounters() {
    document.querySelectorAll('.selected-in-category').forEach(function(counter) {
        const category = counter.dataset.category;
        const categoryPermissions = document.querySelectorAll(`[data-category="${category}"]`);
        let selectedCount = 0;
        
        categoryPermissions.forEach(function(item) {
            const permissionId = parseInt(item.dataset.permission);
            if (selectedPermissions.has(permissionId)) {
                selectedCount++;
            }
        });
        
        counter.textContent = selectedCount;
        
        // Mettre à jour le bouton de sélection de catégorie
        const button = document.querySelector(`[data-category="${category}"].select-all-category`);
        if (selectedCount === categoryPermissions.length) {
            button.innerHTML = '<i class="fas fa-check-square me-1"></i>Tout désélectionner';
        } else {
            button.innerHTML = '<i class="fas fa-square me-1"></i>Tout sélectionner';
        }
    });
}

/**
 * Mettre à jour le résumé des sélections
 */
function updateSelectionSummary() {
    const container = document.getElementById('selectedPermissions');
    const counter = document.getElementById('selectedCount');
    
    counter.textContent = selectedPermissions.size;
    
    if (selectedPermissions.size === 0) {
        container.innerHTML = '<p class="text-muted text-center">Aucune permission sélectionnée</p>';
        return;
    }
    
    let html = '';
    selectedPermissions.forEach(permissionId => {
        const item = document.querySelector(`[data-permission="${permissionId}"]`);
        if (item) {
            const name = item.querySelector('.permission-name').textContent;
            
            html += `
                <div class="selected-permission">
                    ${name}
                    <button type="button" class="remove-permission" onclick="removePermission(${permissionId})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }
    });
    
    container.innerHTML = html;
}

/**
 * Supprimer une permission sélectionnée
 */
function removePermission(permissionId) {
    selectedPermissions.delete(permissionId);
    updatePermissionVisual(permissionId, false);
    updateSelectionSummary();
    updateCategoryCounters();
}

/**
 * Tout désélectionner
 */
function clearAllSelections() {
    if (confirm('Êtes-vous sûr de vouloir désélectionner toutes les permissions ?')) {
        selectedPermissions.clear();
        document.querySelectorAll('.permission-item').forEach(function(item) {
            const permissionId = parseInt(item.dataset.permission);
            updatePermissionVisual(permissionId, false);
        });
        updateSelectionSummary();
        updateCategoryCounters();
    }
}

/**
 * Initialiser la recherche
 */
function initializeSearch() {
    document.getElementById('searchPermissions').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        document.querySelectorAll('.permission-item').forEach(function(item) {
            const name = item.querySelector('.permission-name').textContent.toLowerCase();
            const description = item.querySelector('.permission-description');
            const descText = description ? description.textContent.toLowerCase() : '';
            
            if (name.includes(searchTerm) || descText.includes(searchTerm)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    });
}

/**
 * Initialiser les filtres
 */
function initializeFilters() {
    document.querySelectorAll('[data-category]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const category = this.dataset.category;
            
            // Mettre à jour les boutons actifs
            document.querySelectorAll('[data-category]').forEach(b => {
                if (b.classList.contains('select-all-category')) return; // Ignorer les boutons de sélection
                b.classList.remove('active');
                b.style.background = '';
                b.style.color = '';
            });
            
            this.classList.add('active');
            this.style.background = '#009e3f';
            this.style.color = 'white';
            
            // Filtrer les catégories de cartes
            if (category === 'all') {
                document.querySelectorAll('.category-card').forEach(card => card.style.display = '');
            } else {
                document.querySelectorAll('.category-card').forEach(function(card) {
                    if (card.dataset.category === category) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
        });
    });
}

/**
 * Aperçu des permissions
 */
function previewPermissions() {
    if (selectedPermissions.size === 0) {
        alert('Aucune permission sélectionnée.');
        return;
    }
    
    let preview = `Aperçu des permissions (${selectedPermissions.size}) :\n\n`;
    
    selectedPermissions.forEach(permissionId => {
        const item = document.querySelector(`[data-permission="${permissionId}"]`);
        if (item) {
            const name = item.querySelector('.permission-name').textContent;
            const category = item.dataset.category;
            preview += `• [${category.toUpperCase()}] ${name}\n`;
        }
    });
    
    alert(preview);
}

/**
 * Reset aux permissions par défaut
 */
function resetToDefault() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser aux permissions par défaut du rôle ?')) {
        selectedPermissions.clear();
        
        // Recharger les permissions originales du rôle
        originalPermissions.forEach(permissionId => {
            selectedPermissions.add(permissionId);
        });
        
        // Mettre à jour l'affichage
        document.querySelectorAll('.permission-item').forEach(function(item) {
            const permissionId = parseInt(item.dataset.permission);
            updatePermissionVisual(permissionId, selectedPermissions.has(permissionId));
        });
        
        updateSelectionSummary();
        updateCategoryCounters();
    }
}

/**
 * FAB toggle
 */
function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    fabMenu.classList.toggle('active');
}

// Fermer FAB en cliquant ailleurs
document.addEventListener('click', function(event) {
    const fabMenu = document.getElementById('fabMenu');
    if (!fabMenu.contains(event.target)) {
        fabMenu.classList.remove('active');
    }
});

/**
 * Soumission du formulaire
 */
document.getElementById('permissionsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (selectedPermissions.size === 0) {
        if (!confirm('Aucune permission n\'est sélectionnée. Le rôle n\'aura aucun accès. Continuer ?')) {
            return;
        }
    }
    
    if (confirm(`Enregistrer ${selectedPermissions.size} permission(s) pour ce rôle ?`)) {
        // Afficher loading
        const saveBtn = document.getElementById('saveBtn');
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement...';
        saveBtn.disabled = true;
        
        // Soumettre le formulaire
        this.submit();
    }
});
</script>
@endsection