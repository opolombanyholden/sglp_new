@extends('layouts.admin')

@section('title', 'Modifier Commune/Ville - ' . $communeVille->nom)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item"><a href="#">Géolocalisation</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.geolocalisation.communes.index') }}">Communes & Villes</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-pencil"></i> Modifier : {{ $communeVille->nom }}
                    <small class="text-muted">({{ ucfirst($communeVille->type) }})</small>
                </h4>
            </div>
        </div>
    </div>

    <!-- Alertes d'erreurs -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle me-1"></i>
            <strong>Erreurs détectées :</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.geolocalisation.communes.update', $communeVille) }}" id="commune-ville-form">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Informations principales -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-information"></i> Informations principales
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="departement_id" class="form-label">Département <span class="text-danger">*</span></label>
                                <select name="departement_id" id="departement_id" class="form-select @error('departement_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionnez un département --</option>
                                    @foreach($departements as $departement)
                                        <option value="{{ $departement->id }}" 
                                            {{ (old('departement_id', $communeVille->departement_id) == $departement->id) ? 'selected' : '' }}>
                                            {{ $departement->nom }} ({{ $departement->province->nom }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('departement_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">-- Sélectionnez le type --</option>
                                    <option value="commune" {{ old('type', $communeVille->type) == 'commune' ? 'selected' : '' }}>Commune</option>
                                    <option value="ville" {{ old('type', $communeVille->type) == 'ville' ? 'selected' : '' }}>Ville</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="nom" id="nom" 
                                       class="form-control @error('nom') is-invalid @enderror" 
                                       value="{{ old('nom', $communeVille->nom) }}" 
                                       placeholder="Ex: Libreville, Port-Gentil..." required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       value="{{ old('code', $communeVille->code) }}" 
                                       placeholder="Ex: ESLBV, OGOGG..." required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Code unique de la commune/ville</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" placeholder="Description de la commune/ville...">{{ old('description', $communeVille->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="statut" class="form-label">Statut administratif</label>
                                <input type="text" name="statut" id="statut" 
                                       class="form-control @error('statut') is-invalid @enderror" 
                                       value="{{ old('statut', $communeVille->statut) }}" 
                                       placeholder="Ex: Commune urbaine, Ville...">
                                @error('statut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="date_creation" class="form-label">Date de création</label>
                                <input type="date" name="date_creation" id="date_creation" 
                                       class="form-control @error('date_creation') is-invalid @enderror" 
                                       value="{{ old('date_creation', $communeVille->date_creation?->format('Y-m-d')) }}">
                                @error('date_creation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations géographiques -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-map-marker"></i> Informations géographiques
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="superficie_km2" class="form-label">Superficie (km²)</label>
                                <input type="number" name="superficie_km2" id="superficie_km2" 
                                       class="form-control @error('superficie_km2') is-invalid @enderror" 
                                       value="{{ old('superficie_km2', $communeVille->superficie_km2) }}" 
                                       step="0.01" min="0" placeholder="0.00">
                                @error('superficie_km2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="population_estimee" class="form-label">Population estimée</label>
                                <input type="number" name="population_estimee" id="population_estimee" 
                                       class="form-control @error('population_estimee') is-invalid @enderror" 
                                       value="{{ old('population_estimee', $communeVille->population_estimee) }}" 
                                       min="0" placeholder="0">
                                @error('population_estimee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="ordre_affichage" class="form-label">Ordre d'affichage</label>
                                <input type="number" name="ordre_affichage" id="ordre_affichage" 
                                       class="form-control @error('ordre_affichage') is-invalid @enderror" 
                                       value="{{ old('ordre_affichage', $communeVille->ordre_affichage) }}" 
                                       min="0" placeholder="0">
                                @error('ordre_affichage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" name="latitude" id="latitude" 
                                       class="form-control @error('latitude') is-invalid @enderror" 
                                       value="{{ old('latitude', $communeVille->latitude) }}" 
                                       step="0.00000001" min="-90" max="90" 
                                       placeholder="0.00000000">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Entre -90 et 90</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" name="longitude" id="longitude" 
                                       class="form-control @error('longitude') is-invalid @enderror" 
                                       value="{{ old('longitude', $communeVille->longitude) }}" 
                                       step="0.00000001" min="-180" max="180" 
                                       placeholder="0.00000000">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Entre -180 et 180</small>
                            </div>
                        </div>

                        @if($communeVille->latitude && $communeVille->longitude)
                            <div class="alert alert-info">
                                <i class="mdi mdi-map-marker-check"></i>
                                <strong>Coordonnées actuelles :</strong> 
                                {{ $communeVille->latitude }}, {{ $communeVille->longitude }}
                                <a href="https://www.google.com/maps?q={{ $communeVille->latitude }},{{ $communeVille->longitude }}" 
                                   target="_blank" class="btn btn-sm btn-outline-info ms-2">
                                    <i class="mdi mdi-map"></i> Voir sur Google Maps
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informations de contact -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-account-tie"></i> Informations administratives
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="maire" class="form-label">Maire</label>
                                <input type="text" name="maire" id="maire" 
                                       class="form-control @error('maire') is-invalid @enderror" 
                                       value="{{ old('maire', $communeVille->maire) }}" 
                                       placeholder="Nom du maire en exercice">
                                @error('maire')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" name="telephone" id="telephone" 
                                       class="form-control @error('telephone') is-invalid @enderror" 
                                       value="{{ old('telephone', $communeVille->telephone) }}" 
                                       placeholder="+241 XX XX XX XX">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $communeVille->email) }}" 
                                       placeholder="mairie@exemple.ga">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="site_web" class="form-label">Site web</label>
                                <input type="url" name="site_web" id="site_web" 
                                       class="form-control @error('site_web') is-invalid @enderror" 
                                       value="{{ old('site_web', $communeVille->site_web) }}" 
                                       placeholder="https://www.exemple.ga">
                                @error('site_web')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Mettre à jour
                            </button>
                            <a href="{{ route('admin.geolocalisation.communes.show', $communeVille) }}" class="btn btn-info">
                                <i class="mdi mdi-eye"></i> Voir les détails
                            </a>
                            <a href="{{ route('admin.geolocalisation.communes.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Retour à la liste
                            </a>
                        </div>

                        <hr>

                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $communeVille->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Commune/Ville active
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-chart-pie"></i> Statistiques
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="mb-2">
                                    <span class="h3 text-primary">{{ $communeVille->arrondissements()->count() }}</span>
                                </div>
                                <small class="text-muted">Arrondissements</small>
                            </div>
                            <div class="col-6">
                                <div class="mb-2">
                                    <span class="h3 text-success">{{ $communeVille->countOrganisations() }}</span>
                                </div>
                                <small class="text-muted">Organisations</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Informations supplémentaires -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-information-outline"></i> Informations supplémentaires
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="services_publics" class="form-label">Services publics</label>
                            <input type="text" name="services_publics" id="services_publics" 
                                   class="form-control @error('services_publics') is-invalid @enderror" 
                                   value="{{ old('services_publics', implode(', ', $communeVille->metadata['services_publics'] ?? [])) }}" 
                                   placeholder="Ex: Hôpital, École, Poste...">
                            <small class="form-text text-muted">Séparez par des virgules</small>
                        </div>

                        <div class="mb-3">
                            <label for="equipements" class="form-label">Équipements</label>
                            <input type="text" name="equipements" id="equipements" 
                                   class="form-control @error('equipements') is-invalid @enderror" 
                                   value="{{ old('equipements', implode(', ', $communeVille->metadata['equipements'] ?? [])) }}" 
                                   placeholder="Ex: Stade, Marché, Bibliothèque...">
                            <small class="form-text text-muted">Séparez par des virgules</small>
                        </div>

                        <div class="mb-3">
                            <label for="autres_infos" class="form-label">Autres informations</label>
                            <textarea name="autres_infos" id="autres_infos" 
                                      class="form-control @error('autres_infos') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Informations complémentaires...">{{ old('autres_infos', $communeVille->metadata['autres_infos'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Historique -->
                <div class="card border-light">
                    <div class="card-header bg-light">
                        <h6 class="card-title text-muted mb-0">
                            <i class="mdi mdi-clock-outline"></i> Historique
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Créé le :</strong> {{ $communeVille->created_at->format('d/m/Y à H:i') }}<br>
                            <strong>Modifié le :</strong> {{ $communeVille->updated_at->format('d/m/Y à H:i') }}
                        </small>
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
    // Validation du formulaire
    $('#commune-ville-form').on('submit', function(e) {
        let isValid = true;
        
        // Vérification des champs obligatoires
        const required = ['departement_id', 'nom', 'code', 'type'];
        required.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val().trim()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });

        // Validation des coordonnées GPS
        const lat = parseFloat($('#latitude').val());
        const lng = parseFloat($('#longitude').val());
        
        if ($('#latitude').val() && (lat < -90 || lat > 90)) {
            $('#latitude').addClass('is-invalid');
            isValid = false;
        } else {
            $('#latitude').removeClass('is-invalid');
        }

        if ($('#longitude').val() && (lng < -180 || lng > 180)) {
            $('#longitude').addClass('is-invalid');
            isValid = false;
        } else {
            $('#longitude').removeClass('is-invalid');
        }

        if (!isValid) {
            e.preventDefault();
            toastr.error('Veuillez corriger les erreurs dans le formulaire');
        }
    });

    // Confirmation des changements importants
    $('#departement_id').on('change', function() {
        const originalValue = '{{ $communeVille->departement_id }}';
        if ($(this).val() !== originalValue && originalValue) {
            toastr.warning('Attention : Changer le département peut affecter les arrondissements liés');
        }
    });
});
</script>
@endpush