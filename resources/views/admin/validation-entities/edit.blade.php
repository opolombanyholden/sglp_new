@extends('layouts.admin')

@section('title', 'Modifier une Entité de Validation')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit text-warning"></i> Modifier l'Entité
            </h1>
            <p class="text-muted mb-0">
                <code>{{ $entity->code }}</code> - {{ $entity->nom }}
            </p>
        </div>
        <div>
            <a href="{{ route('admin.validation-entities.show', $entity->id) }}" class="btn btn-info me-2">
                <i class="fas fa-eye"></i> Voir Détails
            </a>
            <a href="{{ route('admin.validation-entities.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
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
    <form action="{{ route('admin.validation-entities.update', $entity->id) }}" method="POST" id="editEntityForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Colonne gauche -->
            <div class="col-lg-8">
                <!-- Informations de base -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-warning text-white">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-info-circle"></i> Informations de Base
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">
                                    Code <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code', $entity->code) }}"
                                       required
                                       maxlength="255">
                                @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                    <option value="direction" {{ old('type', $entity->type) == 'direction' ? 'selected' : '' }}>Direction</option>
                                    <option value="service" {{ old('type', $entity->type) == 'service' ? 'selected' : '' }}>Service</option>
                                    <option value="departement" {{ old('type', $entity->type) == 'departement' ? 'selected' : '' }}>Département</option>
                                    <option value="commission" {{ old('type', $entity->type) == 'commission' ? 'selected' : '' }}>Commission</option>
                                    <option value="externe" {{ old('type', $entity->type) == 'externe' ? 'selected' : '' }}>Externe</option>
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
                                   value="{{ old('nom', $entity->nom) }}"
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
                                      rows="3">{{ old('description', $entity->description) }}</textarea>
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
                                       value="{{ old('email_notification', $entity->email_notification) }}"
                                       maxlength="255">
                                @error('email_notification')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="capacite_traitement" class="form-label">
                                    Capacité de Traitement <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('capacite_traitement') is-invalid @enderror" 
                                       id="capacite_traitement" 
                                       name="capacite_traitement" 
                                       value="{{ old('capacite_traitement', $entity->capacite_traitement) }}"
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
                        @php
                        $jours = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
                        $horaires = json_decode($entity->horaires_travail, true) ?? [
                            'lundi' => ['08:00', '17:00'],
                            'mardi' => ['08:00', '17:00'],
                            'mercredi' => ['08:00', '17:00'],
                            'jeudi' => ['08:00', '17:00'],
                            'vendredi' => ['08:00', '15:00']
                        ];
                        @endphp

                        <div id="horairesContainer">
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
                                           value="{{ $horaires[$jour][0] ?? '08:00' }}">
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
                                           value="{{ $horaires[$jour][1] ?? '17:00' }}">
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <input type="hidden" 
                               id="horaires_travail" 
                               name="horaires_travail" 
                               value='{{ json_encode($horaires) }}'>
                    </div>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="col-lg-4">
                <!-- Statut -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-{{ $entity->is_active ? 'success' : 'danger' }} text-white">
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
                                   {{ old('is_active', $entity->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Entité Active</strong>
                            </label>
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            <i class="fas fa-info-circle"></i> 
                            Une entité active peut recevoir des dossiers
                        </small>
                    </div>
                </div>

                <!-- Informations système -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-secondary">
                            <i class="fas fa-info-circle"></i> Informations Système
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">
                            <strong>ID :</strong> {{ $entity->id }}
                        </p>
                        <p class="small mb-2">
                            <strong>Créé le :</strong><br>
                            {{ \Carbon\Carbon::parse($entity->created_at)->format('d/m/Y à H:i') }}
                        </p>
                        <p class="small mb-0">
                            <strong>Modifié le :</strong><br>
                            {{ \Carbon\Carbon::parse($entity->updated_at)->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-save"></i> Enregistrer les Modifications
                        </button>
                        <a href="{{ route('admin.validation-entities.show', $entity->id) }}" 
                           class="btn btn-info w-100 mb-2">
                            <i class="fas fa-eye"></i> Voir Détails
                        </a>
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
    // Convertir le code en majuscules
    $('#code').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });
    
    // Mettre à jour le JSON des horaires
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
    $('#editEntityForm').on('submit', function(e) {
        updateHorairesJSON();
        return true;
    });
});
</script>
@endpush