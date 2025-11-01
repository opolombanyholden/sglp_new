@extends('layouts.admin')

@section('title', 'Créer une Permission')

@section('content')
<div class="container-fluid">
    <!-- Header avec couleur gabonaise verte pour création -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                <i class="fas fa-plus-circle me-2"></i>
                                Créer une Permission
                            </h2>
                            <p class="mb-0 opacity-90">Ajouter une nouvelle permission au système SGLP</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-light btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>
                                Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire de création -->
    <div class="row">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <form id="createPermissionForm" action="{{ route('admin.permissions.store') }}" method="POST">
                @csrf
                
                <!-- Card principale -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-key me-2 text-success"></i>
                            Informations de la Permission
                        </h5>
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
                                           value="{{ old('name') }}"
                                           placeholder="ex: users.create"
                                           pattern="^[a-z]+\.[a-z_]+$"
                                           required>
                                </div>
                                <small class="text-muted">Format: catégorie.action (ex: users.create, orgs.validate)</small>
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
                                           value="{{ old('display_name') }}"
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
                                            required>
                                        <option value="">Sélectionnez une catégorie</option>
                                        @foreach($categories ?? [] as $key => $label)
                                            <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
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
                                           class="form-control border-0 bg-light" 
                                           id="risk_level_display" 
                                           value="Non déterminé"
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
                                          placeholder="Description détaillée de ce que permet cette permission...">{{ old('description') }}</textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Description optionnelle mais recommandée</small>
                                <small class="text-muted"><span id="charCount">0</span>/500 caractères</small>
                            </div>
                            @error('description')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Informations prévisualisation -->
                        <div id="previewSection" class="mb-4 d-none">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-eye me-2"></i>
                                Prévisualisation
                            </h6>
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <strong>Code:</strong> 
                                                <code id="previewName" class="text-primary">-</code>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Affichage:</strong> 
                                                <span id="previewDisplayName">-</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <strong>Catégorie:</strong> 
                                                <span id="previewCategory">-</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Risque:</strong> 
                                                <span id="previewRisk" class="badge">-</span>
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
                                    <button type="submit" class="btn btn-success" id="submitBtn">
                                        <i class="fas fa-save me-2"></i>
                                        Créer la Permission
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                        <i class="fas fa-undo me-2"></i>
                                        Réinitialiser
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-times me-2"></i>
                                    Annuler
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Card d'aide -->
    <div class="row mt-4">
        <div class="col-xl-8 col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Guide de création des permissions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">Format du nom</h6>
                            <ul class="mb-3">
                                <li><code>users.create</code> - Créer des utilisateurs</li>
                                <li><code>orgs.validate</code> - Valider des organisations</li>
                                <li><code>workflow.assign</code> - Assigner des tâches</li>
                                <li><code>reports.export</code> - Exporter des rapports</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Niveaux de risque</h6>
                            <ul class="mb-3">
                                <li><span class="badge bg-danger">Élevé</span> - delete, system, config, admin</li>
                                <li><span class="badge bg-warning text-dark">Moyen</span> - create, edit, validate, assign</li>
                                <li><span class="badge bg-success">Faible</span> - view, list, read</li>
                            </ul>
                        </div>
                    </div>
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Le nom de la permission doit correspondre à la catégorie sélectionnée et suivre la convention de nommage du système.
                    </div>
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
    box-shadow: 0 0 0 3px rgba(0, 158, 63, 0.1);
    border-color: #009e3f;
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

.is-invalid {
    border-color: #dc3545;
}

.text-danger {
    color: #dc3545 !important;
}

.input-group .is-invalid ~ .invalid-feedback {
    display: block;
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
    const previewSection = document.getElementById('previewSection');
    const submitBtn = document.getElementById('submitBtn');

    let nameValidationTimeout;

    // Validation en temps réel du nom
    nameInput.addEventListener('input', function() {
        clearTimeout(nameValidationTimeout);
        const value = this.value.trim();
        
        if (value.length > 0) {
            nameValidationTimeout = setTimeout(() => {
                validatePermissionName(value);
            }, 500);
        }
        
        updateRiskLevel(value);
        updatePreview();
    });

    // Mise à jour de la prévisualisation
    function updatePreview() {
        const name = nameInput.value.trim();
        const displayName = displayNameInput.value.trim();
        const category = categorySelect.options[categorySelect.selectedIndex]?.text || '-';
        const risk = calculateRiskLevel(name);

        if (name || displayName) {
            previewSection.classList.remove('d-none');
            document.getElementById('previewName').textContent = name || '-';
            document.getElementById('previewDisplayName').textContent = displayName || '-';
            document.getElementById('previewCategory').textContent = category;
            
            const riskBadge = document.getElementById('previewRisk');
            riskBadge.textContent = risk.label;
            riskBadge.className = `badge ${risk.class}`;
        } else {
            previewSection.classList.add('d-none');
        }
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
                body: JSON.stringify({ name: name })
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
    });

    // Suggestions automatiques
    categorySelect.addEventListener('change', function() {
        const category = this.value;
        if (category && !nameInput.value.includes('.')) {
            nameInput.value = category + '.';
            nameInput.focus();
        }
        updatePreview();
    });

    // Event listeners pour prévisualisation
    displayNameInput.addEventListener('input', updatePreview);
    categorySelect.addEventListener('change', updatePreview);
    descriptionTextarea.addEventListener('input', updatePreview);

    // Soumission du formulaire
    document.getElementById('createPermissionForm').addEventListener('submit', function(e) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';
        submitBtn.disabled = true;
    });
});

// Fonction de réinitialisation
function resetForm() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ?')) {
        document.getElementById('createPermissionForm').reset();
        document.getElementById('previewSection').classList.add('d-none');
        document.getElementById('nameValidation').innerHTML = '';
        document.getElementById('charCount').textContent = '0';
        document.getElementById('risk_level_display').value = 'Non déterminé';
        document.getElementById('submitBtn').disabled = false;
    }
}

console.log('🔑 Formulaire création permission - Style conforme chargé');
</script>
@endsection