@extends('layouts.admin')

@section('title', 'Nouveau Type de Document')

@section('content')
<div class="container-fluid">
    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Nouveau Type de Document</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Tableau de bord</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.referentiels.index') }}">Référentiels</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.referentiels.document-types.index') }}">Types de documents</a>
                    </li>
                    <li class="breadcrumb-item active">Nouveau</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.referentiels.document-types.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    {{-- Formulaire --}}
    <div class="row">
        <div class="col-lg-9">
            <form method="POST" action="{{ route('admin.referentiels.document-types.store') }}" id="documentTypeForm">
                @csrf

                {{-- INFORMATIONS GÉNÉRALES --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations générales</h5>
                    </div>
                    <div class="card-body">
                        {{-- Code --}}
                        <div class="mb-3">
                            <label for="code" class="form-label">
                                Code <span class="text-danger">*</span>
                                <i class="fas fa-info-circle text-muted" 
                                   title="Identifiant unique technique (minuscules, chiffres et underscores)"
                                   data-bs-toggle="tooltip"></i>
                            </label>
                            <input type="text" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code') }}"
                                   placeholder="Ex: piece_identite"
                                   pattern="[a-z0-9_]+"
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Utilisez uniquement des lettres minuscules, chiffres et underscores</small>
                        </div>

                        {{-- Libellé --}}
                        <div class="mb-3">
                            <label for="libelle" class="form-label">
                                Libellé <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('libelle') is-invalid @enderror" 
                                   id="libelle" 
                                   name="libelle" 
                                   value="{{ old('libelle') }}"
                                   placeholder="Ex: Pièce d'identité du président"
                                   required>
                            @error('libelle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Description détaillée du document...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- APPLICABILITÉ --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-network-wired"></i> Applicabilité
                            <small class="ms-2">(Types d'organisation et Types d'opération)</small>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Important :</strong> Ce document peut être requis pour <strong>plusieurs types d'organisation</strong> ET <strong>plusieurs types d'opération</strong>. 
                            Sélectionnez toutes les combinaisons applicables.
                        </div>

                        {{-- TYPES D'ORGANISATION --}}
                        <div class="mb-4">
                            <h6 class="text-success mb-3">
                                <i class="fas fa-sitemap"></i> Types d'organisation concernés 
                                <span class="text-danger">*</span>
                            </h6>
                            
                            @error('organisation_types')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div id="organisation-types-container">
                                @foreach($typesOrganisation as $key => $label)
                                    <div class="card mb-2 organisation-type-card">
                                        <div class="card-body py-2">
                                            <div class="row align-items-center">
                                                {{-- Checkbox Type Organisation --}}
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input organisation-type-checkbox" 
                                                               type="checkbox" 
                                                               name="organisation_types[]" 
                                                               value="{{ $key }}" 
                                                               id="org_{{ $key }}"
                                                               {{ (is_array(old('organisation_types')) && in_array($key, old('organisation_types'))) ? 'checked' : '' }}
                                                               onchange="toggleOrganisationOptions('{{ $key }}')">
                                                        <label class="form-check-label fw-bold" for="org_{{ $key }}">
                                                            {{ $label }}
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Options --}}
                                                <div class="col-md-8">
                                                    <div class="row g-2 org-options" id="options_org_{{ $key }}" style="display: none;">
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" 
                                                                       type="checkbox" 
                                                                       name="org_obligatoire[{{ $key }}]" 
                                                                       value="1"
                                                                       id="oblig_org_{{ $key }}"
                                                                       {{ old("org_obligatoire.{$key}") ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="oblig_org_{{ $key }}">
                                                                    Obligatoire
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">Ordre</span>
                                                                <input type="number" 
                                                                       class="form-control" 
                                                                       name="org_ordre[{{ $key }}]" 
                                                                       value="{{ old("org_ordre.{$key}", 0) }}" 
                                                                       min="0"
                                                                       placeholder="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <hr>

                        {{-- TYPES D'OPÉRATION --}}
                        <div class="mb-3">
                            <h6 class="text-success mb-3">
                                <i class="fas fa-tasks"></i> Types d'opération concernés 
                                <span class="text-danger">*</span>
                            </h6>

                            @error('operation_types')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            <div id="operation-types-container">
                                @foreach($typesOperation as $key => $label)
                                    <div class="card mb-2 operation-type-card">
                                        <div class="card-body py-2">
                                            <div class="row align-items-center">
                                                {{-- Checkbox Type Opération --}}
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input operation-type-checkbox" 
                                                               type="checkbox" 
                                                               name="operation_types[]" 
                                                               value="{{ $key }}" 
                                                               id="op_{{ $key }}"
                                                               {{ (is_array(old('operation_types')) && in_array($key, old('operation_types'))) ? 'checked' : '' }}
                                                               onchange="toggleOperationOptions('{{ $key }}')">
                                                        <label class="form-check-label fw-bold" for="op_{{ $key }}">
                                                            {{ $label }}
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Options --}}
                                                <div class="col-md-8">
                                                    <div class="row g-2 op-options" id="options_op_{{ $key }}" style="display: none;">
                                                        <div class="col-md-6">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" 
                                                                       type="checkbox" 
                                                                       name="op_obligatoire[{{ $key }}]" 
                                                                       value="1"
                                                                       id="oblig_op_{{ $key }}"
                                                                       {{ old("op_obligatoire.{$key}") ? 'checked' : '' }}>
                                                                <label class="form-check-label small" for="oblig_op_{{ $key }}">
                                                                    Obligatoire
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text">Ordre</span>
                                                                <input type="number" 
                                                                       class="form-control" 
                                                                       name="op_ordre[{{ $key }}]" 
                                                                       value="{{ old("op_ordre.{$key}", 0) }}" 
                                                                       min="0"
                                                                       placeholder="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CONFIGURATION TECHNIQUE --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> Configuration technique</h5>
                    </div>
                    <div class="card-body">
                        {{-- Formats acceptés (Multiple) --}}
                        <div class="mb-3">
                            <label class="form-label">
                                Formats de fichiers acceptés <span class="text-danger">*</span>
                            </label>
                            <div class="border rounded p-3 bg-light">
                                @php
                                    $formats = [
                                        'pdf' => 'PDF',
                                        'jpg' => 'JPG',
                                        'jpeg' => 'JPEG',
                                        'png' => 'PNG',
                                        'doc' => 'DOC',
                                        'docx' => 'DOCX',
                                        'xls' => 'XLS',
                                        'xlsx' => 'XLSX',
                                    ];
                                    $oldFormats = old('formats', ['pdf', 'jpg', 'png']);
                                @endphp

                                <div class="row">
                                    @foreach($formats as $ext => $label)
                                        <div class="col-md-3 col-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input format-checkbox" 
                                                       type="checkbox" 
                                                       name="formats[]" 
                                                       value="{{ $ext }}" 
                                                       id="format_{{ $ext }}"
                                                       {{ in_array($ext, $oldFormats) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="format_{{ $ext }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @error('formats')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Raccourcis formats --}}
                        <div class="mb-3">
                            <label class="form-label small text-muted">Sélection rapide :</label>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="selectFormats(['pdf'])">
                                    PDF seul
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="selectFormats(['pdf','jpg','jpeg','png'])">
                                    PDF + Images
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="selectFormats(['pdf','doc','docx'])">
                                    Documents
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="selectAllFormats()">
                                    Tout
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearFormats()">
                                    Aucun
                                </button>
                            </div>
                        </div>

                        {{-- Taille max --}}
                        <div class="mb-3">
                            <label for="taille_max" class="form-label">
                                Taille maximale (Mo) <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control @error('taille_max') is-invalid @enderror" 
                                   id="taille_max" 
                                   name="taille_max" 
                                   value="{{ old('taille_max', 5) }}"
                                   min="1"
                                   max="50"
                                   step="1"
                                   required>
                            @error('taille_max')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Taille maximale du fichier (1 à 50 Mo)</small>
                        </div>

                        {{-- Ordre global --}}
                        <div class="mb-3">
                            <label for="ordre" class="form-label">Ordre d'affichage global</label>
                            <input type="number" 
                                   class="form-control @error('ordre') is-invalid @enderror" 
                                   id="ordre" 
                                   name="ordre" 
                                   value="{{ old('ordre', 0) }}"
                                   min="0">
                            @error('ordre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Ordre d'affichage global (0 = premier)</small>
                        </div>
                    </div>
                </div>

                {{-- STATUT --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-toggle-on"></i> Statut</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Type de document actif</strong>
                                <br>
                                <small class="text-muted">
                                    Si désactivé, ce type ne sera plus proposé
                                </small>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Boutons d'action --}}
                <div class="d-flex justify-content-between mb-4">
                    <a href="{{ route('admin.referentiels.document-types.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Enregistrer le type de document
                    </button>
                </div>
            </form>
        </div>

        {{-- Aide latérale --}}
        <div class="col-lg-3">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Guide</h6>
                </div>
                <div class="card-body">
                    <h6 class="text-primary small">🔗 Modèle flexible</h6>
                    <p class="small text-muted mb-3">
                        Ce document peut être requis pour <strong>plusieurs combinaisons</strong> de types d'organisation et d'opération.
                    </p>

                    <h6 class="text-primary small">📋 Exemple</h6>
                    <p class="small text-muted mb-3">
                        "Pièce d'identité" peut être requis pour :
                    </p>
                    <ul class="small text-muted mb-3">
                        <li>Associations → Création (Obligatoire)</li>
                        <li>ONG → Création (Obligatoire)</li>
                        <li>Associations → Modification (Facultatif)</li>
                    </ul>

                    <h6 class="text-primary small">✅ Obligatoire vs Facultatif</h6>
                    <p class="small text-muted mb-3">
                        Vous pouvez définir différentes règles pour chaque type d'organisation ET chaque type d'opération.
                    </p>

                    <h6 class="text-primary small">📄 Formats multiples</h6>
                    <p class="small text-muted mb-0">
                        Sélectionnez tous les formats que vous acceptez. L'utilisateur pourra fournir le document dans n'importe lequel de ces formats.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Afficher options pour éléments cochés
    document.querySelectorAll('.organisation-type-checkbox:checked').forEach(function(cb) {
        toggleOrganisationOptions(cb.value);
    });
    document.querySelectorAll('.operation-type-checkbox:checked').forEach(function(cb) {
        toggleOperationOptions(cb.value);
    });
});

// Auto-génération du code
document.getElementById('libelle').addEventListener('input', function(e) {
    const codeInput = document.getElementById('code');
    if (!codeInput.value || codeInput.dataset.autoGenerated === 'true') {
        const code = e.target.value
            .toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s]/g, '')
            .replace(/\s+/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');
        
        codeInput.value = code;
        codeInput.dataset.autoGenerated = 'true';
    }
});

document.getElementById('code').addEventListener('input', function() {
    this.dataset.autoGenerated = 'false';
});

// Toggle options organisation
function toggleOrganisationOptions(orgKey) {
    const checkbox = document.getElementById('org_' + orgKey);
    const options = document.getElementById('options_org_' + orgKey);
    options.style.display = checkbox.checked ? 'block' : 'none';
}

// Toggle options opération
function toggleOperationOptions(opKey) {
    const checkbox = document.getElementById('op_' + opKey);
    const options = document.getElementById('options_op_' + opKey);
    options.style.display = checkbox.checked ? 'block' : 'none';
}

// Gestion formats
function selectFormats(formats) {
    document.querySelectorAll('.format-checkbox').forEach(function(checkbox) {
        checkbox.checked = formats.includes(checkbox.value);
    });
}

function selectAllFormats() {
    document.querySelectorAll('.format-checkbox').forEach(function(cb) {
        cb.checked = true;
    });
}

function clearFormats() {
    document.querySelectorAll('.format-checkbox').forEach(function(cb) {
        cb.checked = false;
    });
}

// Validation
document.getElementById('documentTypeForm').addEventListener('submit', function(e) {
    const errors = [];
    
    // Vérifier types d'organisation
    if (document.querySelectorAll('.organisation-type-checkbox:checked').length === 0) {
        errors.push('Sélectionnez au moins un type d\'organisation');
    }
    
    // Vérifier types d'opération
    if (document.querySelectorAll('.operation-type-checkbox:checked').length === 0) {
        errors.push('Sélectionnez au moins un type d\'opération');
    }
    
    // Vérifier formats
    if (document.querySelectorAll('.format-checkbox:checked').length === 0) {
        errors.push('Sélectionnez au moins un format de fichier');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('⚠️ Erreurs de validation :\n\n' + errors.join('\n'));
        return false;
    }
});
</script>
@endpush
@endsection