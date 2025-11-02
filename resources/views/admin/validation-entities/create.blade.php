@extends('layouts.admin')

@section('title', 'Créer une Entité de Validation')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus-circle text-primary"></i> Nouvelle Entité de Validation
            </h1>
            <p class="text-muted mb-0">Créer une nouvelle entité (service, direction, commission...)</p>
        </div>
        <a href="{{ route('admin.validation-entities.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <!-- Messages d'erreur globaux -->
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <h5><i class="fas fa-exclamation-triangle"></i> Erreurs de validation</h5>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Formulaire -->
    <form action="{{ route('admin.validation-entities.store') }}" method="POST" id="createEntityForm">
        @csrf
        
        <div class="row">
            <!-- Colonne gauche -->
            <div class="col-lg-8">
                <!-- Informations de base -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-info-circle"></i> Informations de Base
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">
                                    Code <span class="text-danger">*</span>
                                    <i class="fas fa-info-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       title="Code unique en majuscules (ex: DIR_INTERIEUR)"></i>
                                </label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code') }}"
                                       placeholder="Ex: SRV_JURIDIQUE"
                                       required
                                       maxlength="255">
                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Sera automatiquement converti en majuscules</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">
                                    Type <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" 
                                        name="type" 
                                        required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="direction" {{ old('type') == 'direction' ? 'selected' : '' }}>Direction</option>
                                    <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>Service</option>
                                    <option value="departement" {{ old('type') == 'departement' ? 'selected' : '' }}>Département</option>
                                    <option value="commission" {{ old('type') == 'commission' ? 'selected' : '' }}>Commission</option>
                                    <option value="externe" {{ old('type') == 'externe' ? 'selected' : '' }}>Externe</option>
                                </select>
                                @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nom" class="form-label">
                                Nom de l'Entité <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control @error('nom') is-invalid @enderror" 
                                   id="nom" 
                                   name="nom" 
                                   value="{{ old('nom') }}"
                                   placeholder="Ex: Direction de l'Intérieur"
                                   required
                                   maxlength="255">
                            @error('nom')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      placeholder="Description détaillée de l'entité et de ses responsabilités">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email_notification" class="form-label">
                                    Email de Notification
                                </label>
                                <input type="email" 
                                       class="form-control @error('email_notification') is-invalid @enderror" 
                                       id="email_notification" 
                                       name="email_notification" 
                                       value="{{ old('email_notification') }}"
                                       placeholder="exemple@interieur.gouv.ga"
                                       maxlength="255">
                                @error('email_notification')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Email pour recevoir les notifications</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="capacite_traitement" class="form-label">
                                    Capacité de Traitement <span class="text-danger">*</span>
                                    <i class="fas fa-info-circle text-muted" 
                                       data-bs-toggle="tooltip" 
                                       title="Nombre de dossiers pouvant être traités par jour"></i>
                                </label>
                                <input type="number" 
                                       class="form-control @error('capacite_traitement') is-invalid @enderror" 
                                       id="capacite_traitement" 
                                       name="capacite_traitement" 
                                       value="{{ old('capacite_traitement', 10) }}"
                                       min="1"
                                       max="1000"
                                       required>
                                @error('capacite_traitement')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Dossiers / jour (1-1000)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Horaires de travail -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-info text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-clock"></i> Horaires de Travail
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">
                            <i class="fas fa-info-circle"></i> 
                            Définissez les horaires de disponibilité de l'entité pour le traitement des dossiers.
                        </p>

                        <div id="horairesContainer">
                            @php
                            $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
                            $defaultHoraires = [
                                'lundi' => ['08:00', '17:00'],
                                'mardi' => ['08:00', '17:00'],
                                'mercredi' => ['08:00', '17:00'],
                                'jeudi' => ['08:00', '17:00'],
                                'vendredi' => ['08:00', '15:00']
                            ];
                            @endphp

                            @foreach($jours as $jour)
                            <div class="row mb-2 align-items-center">
                                <div class="col-md-3">
                                    <label class="form-label mb-0">
                                        <i class="fas fa-calendar-day"></i> {{ ucfirst($jour) }}
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" 
                                           class="form-control form-control-sm horaire-input" 
                                           id="horaire_{{ $jour }}_debut"
                                           data-jour="{{ $jour }}"
                                           data-type="debut"
                                           value="{{ $defaultHoraires[$jour][0] }}">
                                </div>
                                <div class="col-md-1 text-center">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                                <div class="col-md-4">
                                    <input type="time" 
                                           class="form-control form-control-sm horaire-input" 
                                           id="horaire_{{ $jour }}_fin"
                                           data-jour="{{ $jour }}"
                                           data-type="fin"
                                           value="{{ $defaultHoraires[$jour][1] }}">
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Champ caché pour stocker le JSON -->
                        <input type="hidden" 
                               id="horaires_travail" 
                               name="horaires_travail" 
                               value='{{ json_encode($defaultHoraires) }}'>
                    </div>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="col-lg-4">
                <!-- Statut -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-success text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-toggle-on"></i> Statut
                        </h6>
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
                                <strong>Entité Active</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            <i class="fas fa-info-circle"></i> 
                            Une entité active peut recevoir des dossiers à valider
                        </small>
                    </div>
                </div>

                <!-- Aide -->
                <div class="card shadow mb-4 border-left-primary">
                    <div class="card-body">
                        <h6 class="text-primary">
                            <i class="fas fa-lightbulb"></i> Aide
                        </h6>
                        <ul class="small mb-0">
                            <li class="mb-2">Le <strong>code</strong> doit être unique</li>
                            <li class="mb-2">Le <strong>type</strong> détermine la catégorie</li>
                            <li class="mb-2">La <strong>capacité</strong> limite les dossiers quotidiens</li>
                            <li>Les <strong>horaires</strong> sont utilisés pour la planification</li>
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save"></i> Enregistrer l'Entité
                        </button>
                        <a href="{{ route('admin.validation-entities.index') }}" 
                           class="btn btn-secondary w-100">
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
$(document).ready(function() {
    // Initialiser les tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Convertir le code en majuscules automatiquement
    $('#code').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
    
    // Mettre à jour le JSON des horaires quand on change les inputs
    $('.horaire-input').on('change', function() {
        updateHorairesJSON();
    });
    
    function updateHorairesJSON() {
        const horaires = {};
        const jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
        
        jours.forEach(jour => {
            const debut = $(`#horaire_${jour}_debut`).val();
            const fin = $(`#horaire_${jour}_fin`).val();
            
            if (debut && fin) {
                horaires[jour] = [debut, fin];
            }
        });
        
        $('#horaires_travail').val(JSON.stringify(horaires));
    }
    
    // Validation du formulaire
    $('#createEntityForm').on('submit', function(e) {
        let isValid = true;
        
        // Vérifier que le code est rempli
        if ($('#code').val().trim() === '') {
            alert('Le code est obligatoire');
            $('#code').focus();
            isValid = false;
        }
        
        // Vérifier que le nom est rempli
        if ($('#nom').val().trim() === '') {
            alert('Le nom est obligatoire');
            $('#nom').focus();
            isValid = false;
        }
        
        // Vérifier que le type est sélectionné
        if ($('#type').val() === '') {
            alert('Le type est obligatoire');
            $('#type').focus();
            isValid = false;
        }
        
        // Vérifier la capacité
        const capacite = parseInt($('#capacite_traitement').val());
        if (isNaN(capacite) || capacite < 1 || capacite > 1000) {
            alert('La capacité de traitement doit être entre 1 et 1000');
            $('#capacite_traitement').focus();
            isValid = false;
        }
        
        // Mettre à jour les horaires une dernière fois
        if (isValid) {
            updateHorairesJSON();
        }
        
        return isValid;
    });
    
    // Initialiser le JSON des horaires au chargement
    updateHorairesJSON();
});
</script>
@endpush