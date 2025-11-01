@extends('layouts.app')

@section('title', 'Créer un Template de Document')

@section('content')
<div class="container-fluid py-4">
    
    {{-- En-tête --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.document-templates.index') }}">Templates</a>
                    </li>
                    <li class="breadcrumb-item active">Nouveau template</li>
                </ol>
            </nav>
            <h2 class="mb-1">
                <i class="fas fa-plus-circle text-primary"></i> Créer un Template de Document
            </h2>
            <p class="text-muted">Définir un nouveau modèle de document officiel</p>
        </div>
    </div>

    {{-- Alertes d'erreurs --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-triangle"></i> Erreurs de validation
            </h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Formulaire --}}
    <form action="{{ route('admin.document-templates.store') }}" method="POST" id="templateForm">
        @csrf

        <div class="row">
            {{-- Colonne principale --}}
            <div class="col-lg-8">
                
                {{-- Informations de base --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> Informations de base
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">
                                    Code du template <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code') }}"
                                       placeholder="Ex: ASSOC_CREATION_DEPOT"
                                       required>
                                <small class="form-text text-muted">
                                    Identifiant unique (lettres majuscules, chiffres et underscores)
                                </small>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">
                                    Nom du template <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" 
                                       name="nom" 
                                       value="{{ old('nom') }}"
                                       placeholder="Ex: Récépissé de dépôt - Association"
                                       required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Description détaillée du template...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Contexte d'utilisation --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-sitemap"></i> Contexte d'utilisation
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="organisation_type_id" class="form-label">
                                    Type d'organisation <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('organisation_type_id') is-invalid @enderror" 
                                        id="organisation_type_id" 
                                        name="organisation_type_id"
                                        required>
                                    <option value="">Sélectionner...</option>
                                    @foreach($organisationTypes as $type)
                                        <option value="{{ $type->id }}" 
                                            {{ old('organisation_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('organisation_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="operation_type_id" class="form-label">
                                    Type d'opération
                                </label>
                                <select class="form-select @error('operation_type_id') is-invalid @enderror" 
                                        id="operation_type_id" 
                                        name="operation_type_id">
                                    <option value="">Tous les types</option>
                                    @foreach($operationTypes as $type)
                                        <option value="{{ $type->id }}" 
                                            {{ old('operation_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Optionnel</small>
                                @error('operation_type_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="workflow_step_id" class="form-label">
                                    Étape du workflow
                                </label>
                                <select class="form-select @error('workflow_step_id') is-invalid @enderror" 
                                        id="workflow_step_id" 
                                        name="workflow_step_id">
                                    <option value="">Aucune étape</option>
                                    {{-- Les options seront chargées dynamiquement --}}
                                </select>
                                <small class="form-text text-muted">Optionnel</small>
                                @error('workflow_step_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="type_document" class="form-label">
                                Type de document <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('type_document') is-invalid @enderror" 
                                    id="type_document" 
                                    name="type_document"
                                    required>
                                <option value="">Sélectionner...</option>
                                @foreach($typesDocument as $key => $label)
                                    <option value="{{ $key }}" 
                                        {{ old('type_document') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type_document')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Fichiers template --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-file-code"></i> Fichiers template
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="template_path" class="form-label">
                                Chemin du template Blade <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control font-monospace @error('template_path') is-invalid @enderror" 
                                   id="template_path" 
                                   name="template_path" 
                                   value="{{ old('template_path') }}"
                                   placeholder="documents.templates.association.creation.step-1-recepisse-depot"
                                   required>
                            <small class="form-text text-muted">
                                Notation avec points (ex: documents.templates.xxx.yyy)
                            </small>
                            @error('template_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="layout_path" class="form-label">
                                Chemin du layout
                            </label>
                            <input type="text" 
                                   class="form-control font-monospace @error('layout_path') is-invalid @enderror" 
                                   id="layout_path" 
                                   name="layout_path" 
                                   value="{{ old('layout_path', 'documents.layouts.official') }}"
                                   placeholder="documents.layouts.official">
                            <small class="form-text text-muted">Layout par défaut si vide</small>
                            @error('layout_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-0">
                            <label for="signature_image" class="form-label">
                                Chemin de l'image de signature
                            </label>
                            <input type="text" 
                                   class="form-control @error('signature_image') is-invalid @enderror" 
                                   id="signature_image" 
                                   name="signature_image" 
                                   value="{{ old('signature_image') }}"
                                   placeholder="images/signatures/directeur.png">
                            <small class="form-text text-muted">
                                Relatif à public/ (ex: images/signatures/xxx.png)
                            </small>
                            @error('signature_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            {{-- Colonne latérale --}}
            <div class="col-lg-4">
                
                {{-- Options --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs"></i> Options
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="has_qr_code" 
                                   name="has_qr_code" 
                                   value="1"
                                   {{ old('has_qr_code', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_qr_code">
                                <i class="fas fa-qrcode"></i> Inclure un QR Code
                            </label>
                            <small class="form-text text-muted d-block">
                                Pour la vérification publique du document
                            </small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="has_watermark" 
                                   name="has_watermark" 
                                   value="1"
                                   {{ old('has_watermark') ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_watermark">
                                <i class="fas fa-certificate"></i> Filigrane
                            </label>
                            <small class="form-text text-muted d-block">
                                Ajouter un filigrane de sécurité
                            </small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="has_signature" 
                                   name="has_signature" 
                                   value="1"
                                   {{ old('has_signature', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="has_signature">
                                <i class="fas fa-signature"></i> Signature
                            </label>
                            <small class="form-text text-muted d-block">
                                Inclure une zone de signature
                            </small>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="auto_generate" 
                                   name="auto_generate" 
                                   value="1"
                                   {{ old('auto_generate', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_generate">
                                <i class="fas fa-magic"></i> Génération automatique
                            </label>
                            <small class="form-text text-muted d-block">
                                Générer automatiquement lors de l'étape du workflow
                            </small>
                        </div>

                        <div class="mb-3" id="delay_field" style="{{ old('auto_generate', true) ? '' : 'display:none;' }}">
                            <label for="generation_delay_hours" class="form-label">
                                Délai de génération (heures)
                            </label>
                            <input type="number" 
                                   class="form-control @error('generation_delay_hours') is-invalid @enderror" 
                                   id="generation_delay_hours" 
                                   name="generation_delay_hours" 
                                   value="{{ old('generation_delay_hours', 0) }}"
                                   min="0"
                                   max="720">
                            <small class="form-text text-muted">
                                0 = immédiat, sinon délai en heures
                            </small>
                            @error('generation_delay_hours')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_active" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="fas fa-toggle-on"></i> Activer le template
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Aide --}}
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-lightbulb text-warning"></i> Conseils
                        </h6>
                        <ul class="small mb-0">
                            <li class="mb-2">Le <strong>code</strong> doit être unique et explicite</li>
                            <li class="mb-2">Le <strong>template Blade</strong> doit exister dans resources/views/</li>
                            <li class="mb-2">La <strong>génération automatique</strong> se déclenche lors du workflow</li>
                            <li class="mb-0">Les <strong>QR codes</strong> permettent la vérification publique</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

        {{-- Actions --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.document-templates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer le template
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>

@push('scripts')
<script>
// Afficher/masquer le champ délai selon auto_generate
document.getElementById('auto_generate').addEventListener('change', function() {
    const delayField = document.getElementById('delay_field');
    delayField.style.display = this.checked ? 'block' : 'none';
});

// Charger dynamiquement les workflow steps
const orgTypeSelect = document.getElementById('organisation_type_id');
const opTypeSelect = document.getElementById('operation_type_id');
const stepSelect = document.getElementById('workflow_step_id');

function loadWorkflowSteps() {
    const orgTypeId = orgTypeSelect.value;
    const opTypeId = opTypeSelect.value;

    if (!orgTypeId) {
        stepSelect.innerHTML = '<option value="">Aucune étape</option>';
        return;
    }

    // Appel AJAX pour charger les steps
    fetch(`{{ route('admin.document-templates.ajax.workflow-steps') }}?organisation_type_id=${orgTypeId}&operation_type_id=${opTypeId}`)
        .then(response => response.json())
        .then(steps => {
            stepSelect.innerHTML = '<option value="">Aucune étape</option>';
            steps.forEach(step => {
                const option = document.createElement('option');
                option.value = step.id;
                option.textContent = `Étape ${step.numero_passage} - ${step.libelle}`;
                stepSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Erreur chargement workflow steps:', error);
        });
}

orgTypeSelect.addEventListener('change', loadWorkflowSteps);
opTypeSelect.addEventListener('change', loadWorkflowSteps);

// Validation côté client
document.getElementById('templateForm').addEventListener('submit', function(e) {
    const code = document.getElementById('code').value;
    const pattern = /^[A-Z0-9_]+$/;
    
    if (!pattern.test(code)) {
        e.preventDefault();
        alert('Le code doit contenir uniquement des lettres majuscules, chiffres et underscores (_)');
        document.getElementById('code').focus();
        return false;
    }
});
</script>
@endpush

@endsection