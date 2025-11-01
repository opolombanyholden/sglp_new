@extends('layouts.admin')

@section('title', 'Modifier la Permission')

@section('content')
<div class="container-fluid">
    <!-- Header avec couleur gabonaise jaune pour édition -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                <div class="card-body text-dark">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-edit me-2"></i>
                                Modifier la Permission
                            </h2>
                            <p class="mb-0 opacity-90">
                                Édition de la permission: <strong>{{ $permission->display_name ?? $permission->name }}</strong>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-dark btn-lg me-2">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour à la liste
                            </a>
                            <a href="{{ route('admin.permissions.show', $permission->id) }}" class="btn btn-outline-dark btn-lg">
                                <i class="fas fa-eye me-2"></i>
                                Voir détails
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations système (si permission système) -->
    @if($permission->isSystemPermission ?? false)
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="alert alert-warning border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h6 class="mb-1">Permission Système</h6>
                        <p class="mb-0">Cette permission fait partie du système. Certaines modifications peuvent être limitées pour préserver l'intégrité du système.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Formulaire d'édition -->
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <form id="editPermissionForm" action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Card principale -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-key me-2 text-warning"></i>
                                Informations de la Permission
                            </h5>
                            <div class="d-flex gap-2">
                                <span class="badge {{ $permission->risk_level == 'high' ? 'bg-danger' : ($permission->risk_level == 'medium' ? 'bg-warning text-dark' : 'bg-success') }}">
                                    Risque: {{ ucfirst($permission->risk_level ?? 'Faible') }}
                                </span>
                                @if($permission->isSystemPermission ?? false)
                                    <span class="badge bg-secondary">Système</span>
                                @else
                                    <span class="badge bg-info">Personnalisé</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Nom de la permission -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-bold">
                                    Nom de la permission <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="fas fa-code text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-0 bg-light @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $permission->name) }}"
                                           placeholder="ex: users.create"
                                           pattern="^[a-z]+\.[a-z_]+$"
                                           {{ ($permission->isSystemPermission ?? false) ? 'readonly' : '' }}
                                           required>
                                </div>
                                @if($permission->isSystemPermission ?? false)
                                    <small class="text-muted">Le nom des permissions système ne peut pas être modifié</small>
                                @else
                                    <small class="text-muted">Format: catégorie.action (ex: users.create, orgs.validate)</small>
                                @endif
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                                <div id="nameValidation" class="mt-1"></div>
                            </div>

                            <!-- Nom d'affichage -->
                            <div class="col-md-6 mb-3">
                                <label for="display_name" class="form-label fw-bold">
                                    Nom d'affichage <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="fas fa-tag text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-0 bg-light @error('display_name') is-invalid @enderror" 
                                           id="display_name" 
                                           name="display_name" 
                                           value="{{ old('display_name', $permission->display_name) }}"
                                           placeholder="ex: Créer des utilisateurs"
                                           maxlength="150"
                                           required>
                                </div>
                                @error('display_name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <!-- Catégorie -->
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label fw-bold">
                                    Catégorie <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="fas fa-folder text-muted"></i>
                                    </span>
                                    <select class="form-select border-0 bg-light @error('category') is-invalid @enderror" 
                                            id="category" 
                                            name="category" 
                                            {{ ($permission->isSystemPermission ?? false) ? 'disabled' : '' }}
                                            required>
                                        <option value="">Sélectionnez une catégorie</option>
                                        @foreach($categories ?? [] as $key => $label)
                                            <option value="{{ $key }}" {{ old('category', $permission->category) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($permission->isSystemPermission ?? false)
                                        <input type="hidden" name="category" value="{{ $permission->category }}">
                                    @endif
                                </div>
                                @if($permission->isSystemPermission ?? false)
                                    <small class="text-muted">La catégorie des permissions système ne peut pas être modifiée</small>
                                @endif
                                @error('category')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Niveau de risque (calculé automatiquement) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">
                                    Niveau de risque estimé
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0">
                                        <i class="fas fa-shield-alt text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-0 bg-light text-center fw-bold" 
                                           id="risk_level_display" 
                                           value="{{ ucfirst($permission->risk_level ?? 'Faible') }}"
                                           readonly>
                                </div>
                                <small class="text-muted">Calculé automatiquement selon le nom de la permission</small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">
                                Description
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0 align-items-start pt-3">
                                    <i class="fas fa-align-left text-muted"></i>
                                </span>
                                <textarea class="form-control border-0 bg-light @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="4"
                                          maxlength="500"
                                          placeholder="Description détaillée de ce que permet cette permission...">{{ old('description', $permission->description) }}</textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Description optionnelle mais recommandée</small>
                                <small class="text-muted"><span id="charCount">{{ strlen($permission->description ?? '') }}</span>/500 caractères</small>
                            </div>
                            @error('description')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Informations de modification -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations
                            </h6>
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <strong>ID:</strong> 
                                                <span class="text-muted">#{{ $permission->id }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <strong>Rôles assignés:</strong> 
                                                <span class="badge bg-primary">{{ $permission->roles()->count() }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <strong>Créé le:</strong> 
                                                <span class="text-muted">{{ $permission->created_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-2">
                                                <strong>Modifié le:</strong> 
                                                <span class="text-muted">{{ $permission->updated_at->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="card-footer bg-white border-0 pt-0">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning" id="submitBtn">
                                        <i class="fas fa-save me-2"></i>
                                        Enregistrer les modifications
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetChanges()">
                                        <i class="fas fa-undo me-2"></i>
                                        Annuler les modifications
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('admin.permissions.show', $permission->id) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye me-2"></i>
                                        Voir détails
                                    </a>
                                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-list me-2"></i>
                                        Retour à la liste
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card des rôles associés -->
    @if($permission->roles()->count() > 0)
    <div class="row mt-4">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2 text-info"></i>
                        Rôles ayant cette permission ({{ $permission->roles()->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($permission->roles as $role)
                        <div class="col-md-6 col-lg-4 mb-2">
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    <div class="avatar-circle bg-primary text-white" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;">
                                        {{ substr($role->display_name ?? $role->name, 0, 1) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">{{ $role->display_name ?? $role->name }}</div>
                                    <small class="text-muted">{{ $role->name }}</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Card d'aide pour modifications -->
    <div class="row mt-4">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Conseils pour la modification
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Bonnes pratiques</h6>
                            <ul class="mb-3">
                                <li>Utilisez un nom d'affichage clair et explicite</li>
                                <li>La description aide les administrateurs à comprendre l'usage</li>
                                <li>Évitez de modifier le nom sur une permission utilisée</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Restrictions</h6>
                            <ul class="mb-3">
                                <li>Les permissions système ont des limitations</li>
                                <li>Le nom doit correspondre à la catégorie</li>
                                <li>Les permissions utilisées nécessitent des précautions</li>
                            </ul>
                        </div>
                    </div>
                    @if($permission->roles()->count() > 0)
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Attention:</strong> Cette permission est utilisée par {{ $permission->roles()->count() }} rôle(s). Les modifications affecteront tous les utilisateurs ayant ces rôles.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Styles conformes au design termines.blade.php */
.card {
    animation: fadeInUp 0.6s ease-out;
}

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

.form-control:focus,
.form-select:focus {
    box-shadow: 0 0 0 3px rgba(255, 205, 0, 0.1);
    border-color: #ffcd00;
}

.form-control[readonly] {
    background-color: #f8f9fa !important;
    opacity: 0.7;
}

.badge.risk-low {
    background: #d4edda;
    color: #155724;
}

.badge.risk-medium {
    background: #fff3cd;
    color: #856404;
}

.badge.risk-high {
    background: #f8d7da;
    color: #721c24;
}

.avatar-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: bold;
}

.is-invalid {
    border-color: #dc3545;
}

.text-danger {
    color: #dc3545 !important;
}

#nameValidation.text-success {
    color: #28a745 !important;
}

#nameValidation.text-danger {
    color: #dc3545 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const displayNameInput = document.getElementById('display_name');
    const categorySelect = document.getElementById('category');
    const descriptionTextarea = document.getElementById('description');
    const charCountSpan = document.getElementById('charCount');
    const riskLevelDisplay = document.getElementById('risk_level_display');
    const submitBtn = document.getElementById('submitBtn');
    const isSystemPermission = {{ ($permission->isSystemPermission ?? false) ? 'true' : 'false' }};
    const originalValues = {
        name: nameInput.value,
        display_name: displayNameInput.value,
        category: categorySelect.value,
        description: descriptionTextarea.value
    };

    let nameValidationTimeout;

    // Validation en temps réel du nom (seulement pour permissions non-système)
    if (!isSystemPermission) {
        nameInput.addEventListener('input', function() {
            clearTimeout(nameValidationTimeout);
            const value = this.value.trim();
            
            if (value.length > 0 && value !== originalValues.name) {
                nameValidationTimeout = setTimeout(() => {
                    validatePermissionName(value);
                }, 500);
            }
            
            updateRiskLevel(value);
            checkForChanges();
        });
    }

    // Calcul du niveau de risque
    function calculateRiskLevel(permissionName) {
        const name = permissionName.toLowerCase();
        
        const highRiskPatterns = ['delete', 'destroy', 'system', 'config', 'admin', 'manage', 'permissions'];
        const mediumRiskPatterns = ['create', 'edit', 'update', 'validate', 'assign', 'reject'];
        
        for (const pattern of highRiskPatterns) {
            if (name.includes(pattern)) {
                return { label: 'Élevé', class: 'risk-high bg-danger' };
            }
        }
        
        for (const pattern of mediumRiskPatterns) {
            if (name.includes(pattern)) {
                return { label: 'Moyen', class: 'risk-medium bg-warning text-dark' };
            }
        }
        
        return { label: 'Faible', class: 'risk-low bg-success' };
    }

    // Mise à jour du niveau de risque
    function updateRiskLevel(permissionName) {
        const risk = calculateRiskLevel(permissionName);
        riskLevelDisplay.value = risk.label;
        riskLevelDisplay.className = `form-control border-0 bg-light text-center fw-bold`;
    }

    // Validation du nom de permission
    async function validatePermissionName(name) {
        const validationDiv = document.getElementById('nameValidation');
        
        if (!name.match(/^[a-z]+\.[a-z_]+$/)) {
            showValidationMessage('Format invalide. Utilisez: catégorie.action', 'danger');
            return;
        }

        try {
            const response = await fetch('{{ route("admin.permissions.validate-name") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ 
                    name: name,
                    current_id: {{ $permission->id }}
                })
            });

            const data = await response.json();
            
            if (data.available) {
                showValidationMessage('✓ Nom de permission disponible', 'success');
                submitBtn.disabled = false;
            } else {
                showValidationMessage('✗ Ce nom de permission est déjà utilisé', 'danger');
                submitBtn.disabled = true;
            }
        } catch (error) {
            console.error('Erreur validation:', error);
            showValidationMessage('Erreur de validation', 'warning');
        }
    }

    function showValidationMessage(message, type) {
        const validationDiv = document.getElementById('nameValidation');
        validationDiv.innerHTML = `<small class="text-${type}">${message}</small>`;
        validationDiv.className = `mt-1 text-${type}`;
    }

    // Compteur de caractères pour description
    descriptionTextarea.addEventListener('input', function() {
        const count = this.value.length;
        charCountSpan.textContent = count;
        
        if (count > 450) {
            charCountSpan.className = 'text-warning';
        } else if (count > 480) {
            charCountSpan.className = 'text-danger';
        } else {
            charCountSpan.className = 'text-muted';
        }
        
        checkForChanges();
    });

    // Vérification des modifications
    function checkForChanges() {
        const hasChanges = 
            nameInput.value !== originalValues.name ||
            displayNameInput.value !== originalValues.display_name ||
            categorySelect.value !== originalValues.category ||
            descriptionTextarea.value !== originalValues.description;

        if (hasChanges) {
            submitBtn.classList.remove('btn-outline-warning');
            submitBtn.classList.add('btn-warning');
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer les modifications';
        } else {
            submitBtn.classList.remove('btn-warning');
            submitBtn.classList.add('btn-outline-warning');
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Aucune modification';
        }
    }

    // Event listeners pour détecter les changements
    displayNameInput.addEventListener('input', checkForChanges);
    categorySelect.addEventListener('change', checkForChanges);

    // Soumission du formulaire
    document.getElementById('editPermissionForm').addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enregistrement en cours...';
        submitBtn.disabled = true;
    });

    // Initialisation
    checkForChanges();
});

// Fonction de réinitialisation des modifications
function resetChanges() {
    if (confirm('Êtes-vous sûr de vouloir annuler toutes les modifications ?')) {
        location.reload();
    }
}

console.log('🔑 Formulaire édition permission - Style conforme chargé');
</script>
@endsection