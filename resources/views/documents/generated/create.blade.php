@extends('layouts.app')

@section('title', 'Générer un Document')

@section('content')
<div class="container-fluid py-4">
    
    {{-- En-tête --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.documents.index') }}">Documents</a>
                    </li>
                    <li class="breadcrumb-item active">Générer un document</li>
                </ol>
            </nav>
            <h2 class="mb-1">
                <i class="fas fa-plus-circle text-primary"></i> Générer un Document Manuellement
            </h2>
            <p class="text-muted">Créer un document officiel pour une organisation</p>
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
    <form action="{{ route('admin.documents.store') }}" method="POST" id="generateForm">
        @csrf

        <div class="row">
            {{-- Colonne principale --}}
            <div class="col-lg-8">
                
                {{-- Étape 1 : Sélection de l'organisation --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary me-2">1</span>
                            Sélectionner l'organisation
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="organisation_id" class="form-label">
                                Organisation <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg @error('organisation_id') is-invalid @enderror" 
                                    id="organisation_id" 
                                    name="organisation_id"
                                    required>
                                <option value="">Sélectionner une organisation...</option>
                                @foreach($organisations as $org)
                                    <option value="{{ $org->id }}" 
                                        data-type="{{ $org->organisation_type_id }}"
                                        data-nom="{{ $org->nom }}"
                                        data-sigle="{{ $org->sigle }}"
                                        data-siege="{{ $org->siege_social }}"
                                        {{ old('organisation_id') == $org->id ? 'selected' : '' }}>
                                        {{ $org->nom }}
                                        @if($org->sigle)
                                            ({{ $org->sigle }})
                                        @endif
                                        - {{ $org->organisationType->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('organisation_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Aperçu de l'organisation sélectionnée --}}
                        <div id="org-preview" class="alert alert-info" style="display: none;">
                            <h6 class="alert-heading">
                                <i class="fas fa-building me-2"></i>Organisation sélectionnée
                            </h6>
                            <div id="org-details"></div>
                        </div>
                    </div>
                </div>

                {{-- Étape 2 : Sélection du template --}}
                <div class="card mb-4" id="template-section" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary me-2">2</span>
                            Sélectionner le template de document
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="document_template_id" class="form-label">
                                Template <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-select-lg @error('document_template_id') is-invalid @enderror" 
                                    id="document_template_id" 
                                    name="document_template_id"
                                    required>
                                <option value="">Sélectionner un template...</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" 
                                        data-org-type="{{ $template->organisation_type_id }}"
                                        data-type="{{ $template->type_document }}"
                                        data-description="{{ $template->description }}"
                                        data-variables="{{ json_encode($template->variables ?? []) }}"
                                        {{ old('document_template_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->nom }} - {{ $template->type_document_label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('document_template_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted" id="template-count">
                                <i class="fas fa-info-circle"></i> Sélectionnez d'abord une organisation
                            </small>
                        </div>

                        {{-- Aperçu du template sélectionné --}}
                        <div id="template-preview" class="alert alert-secondary" style="display: none;">
                            <h6 class="alert-heading">
                                <i class="fas fa-file-alt me-2"></i>Template sélectionné
                            </h6>
                            <div id="template-details"></div>
                        </div>
                    </div>
                </div>

                {{-- Étape 3 : Variables optionnelles --}}
                <div class="card mb-4" id="variables-section" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <span class="badge bg-primary me-2">3</span>
                            Variables personnalisées (Optionnel)
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="fas fa-info-circle"></i>
                            Les variables par défaut seront utilisées automatiquement. 
                            Vous pouvez les personnaliser ci-dessous si nécessaire.
                        </p>

                        <div id="variables-list">
                            {{-- Chargé dynamiquement --}}
                        </div>

                        <div class="form-check mt-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="use_default_variables" 
                                   name="use_default_variables" 
                                   value="1"
                                   checked>
                            <label class="form-check-label" for="use_default_variables">
                                Utiliser les données par défaut de l'organisation
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Colonne latérale --}}
            <div class="col-lg-4">
                
                {{-- Progression --}}
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-tasks"></i> Progression
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar" 
                                 role="progressbar" 
                                 id="progress-bar"
                                 style="width: 0%;"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                0%
                            </div>
                        </div>

                        <ul class="list-unstyled mb-0">
                            <li class="mb-2" id="step-1-indicator">
                                <i class="fas fa-circle text-muted me-2"></i>
                                <span class="text-muted">Sélectionner l'organisation</span>
                            </li>
                            <li class="mb-2" id="step-2-indicator">
                                <i class="fas fa-circle text-muted me-2"></i>
                                <span class="text-muted">Choisir le template</span>
                            </li>
                            <li class="mb-0" id="step-3-indicator">
                                <i class="fas fa-circle text-muted me-2"></i>
                                <span class="text-muted">Paramètres optionnels</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Options de génération --}}
                <div class="card mb-4" id="options-section" style="display: none;">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">
                            <i class="fas fa-cogs"></i> Options de génération
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="generate_qr_code" 
                                   name="generate_qr_code" 
                                   value="1"
                                   checked>
                            <label class="form-check-label" for="generate_qr_code">
                                <i class="fas fa-qrcode"></i> Générer le QR Code
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="send_notification" 
                                   name="send_notification" 
                                   value="1">
                            <label class="form-check-label" for="send_notification">
                                <i class="fas fa-envelope"></i> Notifier l'organisation
                            </label>
                            <small class="form-text text-muted d-block">
                                Envoyer un email à l'organisation
                            </small>
                        </div>

                        <div class="form-check mb-0">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="auto_download" 
                                   name="auto_download" 
                                   value="1">
                            <label class="form-check-label" for="auto_download">
                                <i class="fas fa-download"></i> Télécharger automatiquement
                            </label>
                            <small class="form-text text-muted d-block">
                                Télécharger le PDF après génération
                            </small>
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
                            <li class="mb-2">
                                Sélectionnez l'organisation en premier pour filtrer les templates disponibles
                            </li>
                            <li class="mb-2">
                                Les variables sont pré-remplies automatiquement avec les données de l'organisation
                            </li>
                            <li class="mb-2">
                                Le QR Code permet la vérification publique du document
                            </li>
                            <li class="mb-0">
                                La génération peut prendre quelques secondes
                            </li>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.documents.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" 
                                    class="btn btn-primary btn-lg" 
                                    id="submit-btn"
                                    disabled>
                                <i class="fas fa-magic me-2"></i>
                                <span id="submit-text">Générer le document</span>
                                <span id="submit-loader" style="display: none;">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Génération en cours...
                                </span>
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
// Variables globales
let selectedOrgType = null;
let currentStep = 0;

// Éléments DOM
const orgSelect = document.getElementById('organisation_id');
const templateSelect = document.getElementById('document_template_id');
const orgPreview = document.getElementById('org-preview');
const orgDetails = document.getElementById('org-details');
const templateSection = document.getElementById('template-section');
const templatePreview = document.getElementById('template-preview');
const templateDetails = document.getElementById('template-details');
const variablesSection = document.getElementById('variables-section');
const variablesList = document.getElementById('variables-list');
const optionsSection = document.getElementById('options-section');
const submitBtn = document.getElementById('submit-btn');
const progressBar = document.getElementById('progress-bar');
const templateCount = document.getElementById('template-count');

// Étape 1 : Sélection de l'organisation
orgSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (this.value) {
        selectedOrgType = selectedOption.dataset.type;
        
        // Afficher l'aperçu de l'organisation
        orgDetails.innerHTML = `
            <p class="mb-1"><strong>Nom :</strong> ${selectedOption.dataset.nom}</p>
            ${selectedOption.dataset.sigle ? `<p class="mb-1"><strong>Sigle :</strong> ${selectedOption.dataset.sigle}</p>` : ''}
            ${selectedOption.dataset.siege ? `<p class="mb-0"><strong>Siège :</strong> ${selectedOption.dataset.siege}</p>` : ''}
        `;
        orgPreview.style.display = 'block';
        
        // Afficher la section template
        templateSection.style.display = 'block';
        
        // Filtrer les templates
        filterTemplates();
        
        // Mettre à jour la progression
        updateProgress(1);
    } else {
        orgPreview.style.display = 'none';
        templateSection.style.display = 'none';
        variablesSection.style.display = 'none';
        optionsSection.style.display = 'none';
        updateProgress(0);
    }
});

// Filtrer les templates selon le type d'organisation
function filterTemplates() {
    const options = templateSelect.querySelectorAll('option');
    let availableCount = 0;
    
    options.forEach(option => {
        if (option.value === '') return;
        
        const templateOrgType = option.dataset.orgType;
        if (templateOrgType == selectedOrgType || !templateOrgType) {
            option.style.display = 'block';
            availableCount++;
        } else {
            option.style.display = 'none';
        }
    });
    
    // Réinitialiser la sélection
    templateSelect.value = '';
    templatePreview.style.display = 'none';
    variablesSection.style.display = 'none';
    
    // Mettre à jour le compteur
    templateCount.innerHTML = `<i class="fas fa-check-circle text-success"></i> ${availableCount} template(s) disponible(s) pour cette organisation`;
}

// Étape 2 : Sélection du template
templateSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    
    if (this.value) {
        // Afficher l'aperçu du template
        templateDetails.innerHTML = `
            <p class="mb-1"><strong>Type :</strong> <span class="badge bg-secondary">${selectedOption.textContent.split(' - ')[1]}</span></p>
            ${selectedOption.dataset.description ? `<p class="mb-0"><strong>Description :</strong> ${selectedOption.dataset.description}</p>` : ''}
        `;
        templatePreview.style.display = 'block';
        
        // Afficher la section variables
        variablesSection.style.display = 'block';
        
        // Charger les variables
        loadVariables(selectedOption.dataset.variables);
        
        // Afficher les options
        optionsSection.style.display = 'block';
        
        // Activer le bouton de soumission
        submitBtn.disabled = false;
        
        // Mettre à jour la progression
        updateProgress(3);
    } else {
        templatePreview.style.display = 'none';
        variablesSection.style.display = 'none';
        optionsSection.style.display = 'none';
        submitBtn.disabled = true;
        updateProgress(2);
    }
});

// Charger les variables du template
function loadVariables(variablesJson) {
    const variables = JSON.parse(variablesJson);
    
    if (!variables || Object.keys(variables).length === 0) {
        variablesList.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Ce template n'a pas de variables personnalisables.
            </div>
        `;
        return;
    }
    
    let html = '<div class="accordion" id="variablesAccordion">';
    let index = 0;
    
    for (const [category, fields] of Object.entries(variables)) {
        html += `
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button ${index > 0 ? 'collapsed' : ''}" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#collapse${index}">
                        ${category.charAt(0).toUpperCase() + category.slice(1)}
                    </button>
                </h2>
                <div id="collapse${index}" 
                     class="accordion-collapse collapse ${index === 0 ? 'show' : ''}" 
                     data-bs-parent="#variablesAccordion">
                    <div class="accordion-body">
                        <p class="small text-muted mb-2">Variables disponibles :</p>
                        <ul class="small mb-0">
        `;
        
        fields.forEach(field => {
            html += `<li><code>${category}.${field}</code></li>`;
        });
        
        html += `
                        </ul>
                    </div>
                </div>
            </div>
        `;
        index++;
    }
    
    html += '</div>';
    variablesList.innerHTML = html;
}

// Mettre à jour la barre de progression
function updateProgress(step) {
    currentStep = step;
    const percentage = (step / 3) * 100;
    
    progressBar.style.width = percentage + '%';
    progressBar.textContent = Math.round(percentage) + '%';
    progressBar.setAttribute('aria-valuenow', percentage);
    
    // Mettre à jour les indicateurs
    for (let i = 1; i <= 3; i++) {
        const indicator = document.getElementById(`step-${i}-indicator`);
        const icon = indicator.querySelector('i');
        const text = indicator.querySelector('span');
        
        if (i <= step) {
            icon.classList.remove('fa-circle', 'text-muted');
            icon.classList.add('fa-check-circle', 'text-success');
            text.classList.remove('text-muted');
            text.classList.add('text-success');
        } else {
            icon.classList.remove('fa-check-circle', 'text-success');
            icon.classList.add('fa-circle', 'text-muted');
            text.classList.remove('text-success');
            text.classList.add('text-muted');
        }
    }
}

// Soumission du formulaire
document.getElementById('generateForm').addEventListener('submit', function(e) {
    // Afficher le loader
    submitBtn.disabled = true;
    document.getElementById('submit-text').style.display = 'none';
    document.getElementById('submit-loader').style.display = 'inline';
});

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Si des valeurs old existent (après erreur de validation)
    if (orgSelect.value) {
        orgSelect.dispatchEvent(new Event('change'));
    }
    if (templateSelect.value) {
        templateSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush

@endsection