@extends('layouts.admin')

@section('title', 'Modifier le type d\'organisation')

@section('content')
<div class="container-fluid">
    
    {{-- EN-TÊTE DE PAGE --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-edit text-warning"></i>
                        Modifier : {{ $organisationType->nom }}
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.referentiels.index') }}">Référentiels</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.referentiels.organisation-types.index') }}">Types d'organisations</a></li>
                            <li class="breadcrumb-item active">Modifier</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.referentiels.organisation-types.show', $organisationType->id) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye"></i> Voir détails
                    </a>
                    <a href="{{ route('admin.referentiels.organisation-types.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- MESSAGES D'ERREUR GLOBAUX --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> Erreurs de validation</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ALERTE SI ORGANISATIONS LIÉES --}}
    @if($organisationType->organisations()->count() > 0)
        <div class="alert alert-warning" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Attention :</strong> Ce type est utilisé par <strong>{{ $organisationType->organisations()->count() }}</strong> organisation(s). 
            Les modifications peuvent impacter ces organisations existantes.
        </div>
    @endif

    {{-- FORMULAIRE DE MODIFICATION --}}
    <form method="POST" action="{{ route('admin.referentiels.organisation-types.update', $organisationType->id) }}" id="edit-form">
        @csrf
        @method('PUT')

        <div class="row">
            
            {{-- COLONNE PRINCIPALE --}}
            <div class="col-lg-8">
                
                {{-- INFORMATIONS GÉNÉRALES --}}
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle"></i> Informations générales
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        {{-- CODE --}}
                        <div class="mb-3">
                            <label for="code" class="form-label">
                                Code <span class="text-danger">*</span>
                                <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" 
                                   title="Code unique en minuscules et underscores"></i>
                            </label>
                            <input type="text" 
                                   class="form-control @error('code') is-invalid @enderror" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code', $organisationType->code) }}" 
                                   pattern="[a-z_]+"
                                   required>
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Lettres minuscules et underscores uniquement</small>
                        </div>

                        {{-- NOM --}}
                        <div class="mb-3">
                            <label for="nom" class="form-label">
                                Nom <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nom') is-invalid @enderror" 
                                   id="nom" 
                                   name="nom" 
                                   value="{{ old('nom', $organisationType->nom) }}" 
                                   required>
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- DESCRIPTION --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3">{{ old('description', $organisationType->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            {{-- COULEUR --}}
                            <div class="col-md-6 mb-3">
                                <label for="couleur" class="form-label">
                                    Couleur <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="color" 
                                           class="form-control form-control-color @error('couleur') is-invalid @enderror" 
                                           id="couleur" 
                                           name="couleur" 
                                           value="{{ old('couleur', $organisationType->couleur) }}"
                                           required>
                                    <select class="form-select" id="couleur-presets" style="max-width: 150px;">
                                        <option value="">Couleur personnalisée</option>
                                        @foreach($couleurs as $hex => $label)
                                            <option value="{{ $hex }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('couleur')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- ICÔNE --}}
                            <div class="col-md-6 mb-3">
                                <label for="icone" class="form-label">Icône Font Awesome</label>
                                <select class="form-select @error('icone') is-invalid @enderror" 
                                        id="icone" 
                                        name="icone">
                                    <option value="">Aucune icône</option>
                                    @foreach($icones as $class => $label)
                                        <option value="{{ $class }}" {{ old('icone', $organisationType->icone) === $class ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('icone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="icone-preview" class="mt-2"></div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- RÈGLES MÉTIER --}}
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-gavel"></i> Règles métier
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        {{-- BUT LUCRATIF --}}
                        <div class="mb-3">
                            <label class="form-label">But de l'organisation <span class="text-danger">*</span></label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input @error('is_lucratif') is-invalid @enderror" 
                                           type="radio" 
                                           name="is_lucratif" 
                                           id="non_lucratif" 
                                           value="0" 
                                           {{ old('is_lucratif', $organisationType->is_lucratif ? '1' : '0') == '0' ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label" for="non_lucratif">
                                        <span class="badge bg-info">Non lucratif</span>
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input @error('is_lucratif') is-invalid @enderror" 
                                           type="radio" 
                                           name="is_lucratif" 
                                           id="lucratif" 
                                           value="1"
                                           {{ old('is_lucratif', $organisationType->is_lucratif ? '1' : '0') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lucratif">
                                        <span class="badge bg-warning">Lucratif</span>
                                    </label>
                                </div>
                            </div>
                            @error('is_lucratif')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            {{-- NOMBRE MINIMUM DE FONDATEURS --}}
                            <div class="col-md-6 mb-3">
                                <label for="nb_min_fondateurs_majeurs" class="form-label">
                                    Nombre minimum de fondateurs majeurs <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('nb_min_fondateurs_majeurs') is-invalid @enderror" 
                                       id="nb_min_fondateurs_majeurs" 
                                       name="nb_min_fondateurs_majeurs" 
                                       value="{{ old('nb_min_fondateurs_majeurs', $organisationType->nb_min_fondateurs_majeurs) }}" 
                                       min="1"
                                       max="100"
                                       required>
                                @error('nb_min_fondateurs_majeurs')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- NOMBRE MINIMUM D'ADHÉRENTS --}}
                            <div class="col-md-6 mb-3">
                                <label for="nb_min_adherents_creation" class="form-label">
                                    Nombre minimum d'adhérents à la création <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('nb_min_adherents_creation') is-invalid @enderror" 
                                       id="nb_min_adherents_creation" 
                                       name="nb_min_adherents_creation" 
                                       value="{{ old('nb_min_adherents_creation', $organisationType->nb_min_adherents_creation) }}" 
                                       min="1"
                                       max="10000"
                                       required>
                                @error('nb_min_adherents_creation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                    </div>
                </div>

                {{-- GUIDES ET LÉGISLATION --}}
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-book"></i> Guides et législation
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        {{-- GUIDE DE CRÉATION --}}
                        <div class="mb-3">
                            <label for="guide_creation" class="form-label">
                                Guide de création
                                <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip" 
                                   title="Instructions détaillées pour créer ce type d'organisation"></i>
                            </label>
                            <textarea class="form-control @error('guide_creation') is-invalid @enderror" 
                                      id="guide_creation" 
                                      name="guide_creation" 
                                      rows="5">{{ old('guide_creation', $organisationType->guide_creation) }}</textarea>
                            @error('guide_creation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Supporte le format Markdown</small>
                        </div>

                        {{-- RÉFÉRENCE LÉGISLATIVE --}}
                        <div class="mb-3">
                            <label for="loi_reference" class="form-label">Référence de loi</label>
                            <input type="text" 
                                   class="form-control @error('loi_reference') is-invalid @enderror" 
                                   id="loi_reference" 
                                   name="loi_reference" 
                                   value="{{ old('loi_reference', $organisationType->loi_reference) }}">
                            @error('loi_reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- TEXTE LÉGISLATIF --}}
                        <div class="mb-3">
                            <label for="texte_legislatif" class="form-label">Texte législatif complet</label>
                            <textarea class="form-control @error('texte_legislatif') is-invalid @enderror" 
                                      id="texte_legislatif" 
                                      name="texte_legislatif" 
                                      rows="6">{{ old('texte_legislatif', $organisationType->texte_legislatif) }}</textarea>
                            @error('texte_legislatif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- DOCUMENTS REQUIS --}}
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt"></i> Documents requis
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            Gérez les documents que les organisations de ce type doivent fournir.
                        </p>

                        <div id="documents-container">
                            @php
                                $existingDocuments = old('documents') 
                                    ? old('documents') 
                                    : $organisationType->documentTypes->map(function($docType) {
                                        return [
                                            'document_type_id' => $docType->id,
                                            'is_obligatoire' => $docType->pivot->is_obligatoire,
                                            'ordre' => $docType->pivot->ordre,
                                            'aide_texte' => $docType->pivot->aide_texte,
                                        ];
                                    })->toArray();
                            @endphp

                            @foreach($existingDocuments as $index => $document)
                                <div class="document-item border rounded p-3 mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-5">
                                            <label class="form-label">Document</label>
                                            <select class="form-select" name="documents[{{ $index }}][document_type_id]" required>
                                                <option value="">Sélectionner un document</option>
                                                @foreach($documentTypes as $docType)
                                                    <option value="{{ $docType->id }}" 
                                                            {{ $document['document_type_id'] == $docType->id ? 'selected' : '' }}>
                                                        {{ $docType->nom }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Type</label>
                                            <select class="form-select" name="documents[{{ $index }}][is_obligatoire]" required>
                                                <option value="1" {{ $document['is_obligatoire'] == '1' ? 'selected' : '' }}>Obligatoire</option>
                                                <option value="0" {{ $document['is_obligatoire'] == '0' ? 'selected' : '' }}>Facultatif</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Ordre</label>
                                            <input type="number" class="form-control" name="documents[{{ $index }}][ordre]" 
                                                   value="{{ $document['ordre'] ?? $index + 1 }}" min="0" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger btn-sm w-100 remove-document">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label">Aide/Instructions</label>
                                            <textarea class="form-control" name="documents[{{ $index }}][aide_texte]" rows="2">{{ $document['aide_texte'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-outline-primary" id="add-document">
                            <i class="fas fa-plus"></i> Ajouter un document
                        </button>
                    </div>
                </div>

            </div>

            {{-- COLONNE LATÉRALE --}}
            <div class="col-lg-4">
                
                {{-- PARAMÈTRES --}}
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cog"></i> Paramètres
                        </h5>
                    </div>
                    <div class="card-body">
                        
                        {{-- STATUT ACTIF --}}
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1"
                                       {{ old('is_active', $organisationType->is_active) == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Type actif
                                </label>
                            </div>
                            <small class="text-muted">Si désactivé, ce type ne sera plus disponible pour les nouvelles organisations</small>
                        </div>

                        {{-- ORDRE D'AFFICHAGE --}}
                        <div class="mb-3">
                            <label for="ordre" class="form-label">Ordre d'affichage</label>
                            <input type="number" 
                                   class="form-control @error('ordre') is-invalid @enderror" 
                                   id="ordre" 
                                   name="ordre" 
                                   value="{{ old('ordre', $organisationType->ordre) }}" 
                                   min="0">
                            @error('ordre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">0 = premier dans la liste</small>
                        </div>

                    </div>
                </div>

                {{-- INFORMATIONS --}}
                <div class="card border-info mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle"></i> Informations
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">
                            <strong>Créé le :</strong><br>
                            {{ $organisationType->created_at->format('d/m/Y à H:i') }}
                        </p>
                        <p class="small mb-2">
                            <strong>Dernière modification :</strong><br>
                            {{ $organisationType->updated_at->format('d/m/Y à H:i') }}
                        </p>
                        <hr>
                        <p class="small mb-2">
                            <i class="fas fa-building text-primary"></i>
                            <strong>{{ $organisationType->organisations()->count() }}</strong> organisation(s)
                        </p>
                        <p class="small mb-2">
                            <i class="fas fa-file-alt text-success"></i>
                            <strong>{{ $organisationType->documentTypes()->count() }}</strong> document(s) requis
                        </p>
                        <p class="small mb-0">
                            <i class="fas fa-file-pdf text-danger"></i>
                            <strong>{{ $organisationType->documentTemplates()->count() }}</strong> template(s)
                        </p>
                    </div>
                </div>

                {{-- BOUTONS D'ACTION --}}
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-save"></i> Mettre à jour
                        </button>
                        <a href="{{ route('admin.referentiels.organisation-types.show', $organisationType->id) }}" class="btn btn-info w-100 mb-2">
                            <i class="fas fa-eye"></i> Voir détails
                        </a>
                        <a href="{{ route('admin.referentiels.organisation-types.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ========================================
    // TOOLTIPS
    // ========================================
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ========================================
    // SÉLECTEUR DE COULEUR PRÉDÉFINIE
    // ========================================
    const couleurPresets = document.getElementById('couleur-presets');
    const couleurInput = document.getElementById('couleur');
    
    if (couleurPresets && couleurInput) {
        couleurPresets.addEventListener('change', function() {
            if (this.value) {
                couleurInput.value = this.value;
            }
        });
    }

    // ========================================
    // PRÉVISUALISATION ICÔNE
    // ========================================
    const iconeSelect = document.getElementById('icone');
    const iconePreview = document.getElementById('icone-preview');
    
    if (iconeSelect && iconePreview) {
        function updateIconePreview() {
            const selectedIcon = iconeSelect.value;
            if (selectedIcon) {
                iconePreview.innerHTML = `
                    <div class="alert alert-info py-2">
                        <i class="fas ${selectedIcon} fa-2x"></i>
                        <span class="ms-2">Aperçu de l'icône</span>
                    </div>
                `;
            } else {
                iconePreview.innerHTML = '';
            }
        }
        
        iconeSelect.addEventListener('change', updateIconePreview);
        updateIconePreview(); // Initial
    }

    // ========================================
    // GESTION DES DOCUMENTS REQUIS
    // ========================================
    let documentIndex = {{ count($existingDocuments) }};
    const documentsContainer = document.getElementById('documents-container');
    const addDocumentBtn = document.getElementById('add-document');

    if (addDocumentBtn && documentsContainer) {
        addDocumentBtn.addEventListener('click', function() {
            const template = `
                <div class="document-item border rounded p-3 mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <label class="form-label">Document</label>
                            <select class="form-select" name="documents[${documentIndex}][document_type_id]" required>
                                <option value="">Sélectionner un document</option>
                                @foreach($documentTypes as $docType)
                                    <option value="{{ $docType->id }}">{{ $docType->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="documents[${documentIndex}][is_obligatoire]" required>
                                <option value="1" selected>Obligatoire</option>
                                <option value="0">Facultatif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Ordre</label>
                            <input type="number" class="form-control" name="documents[${documentIndex}][ordre]" 
                                   value="${documentIndex + 1}" min="0" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-danger btn-sm w-100 remove-document">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label">Aide/Instructions</label>
                            <textarea class="form-control" name="documents[${documentIndex}][aide_texte]" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            `;
            
            documentsContainer.insertAdjacentHTML('beforeend', template);
            documentIndex++;
        });

        // Suppression d'un document
        documentsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-document') || e.target.closest('.remove-document')) {
                const item = e.target.closest('.document-item');
                if (item && confirm('Supprimer ce document ?')) {
                    item.remove();
                }
            }
        });
    }

    // ========================================
    // VALIDATION DU CODE (format)
    // ========================================
    const codeInput = document.getElementById('code');
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.toLowerCase().replace(/[^a-z_]/g, '');
        });
    }

});
</script>
@endpush