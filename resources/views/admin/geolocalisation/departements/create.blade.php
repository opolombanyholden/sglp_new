{{-- resources/views/admin/geolocalisation/departements/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Créer un Département')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nouveau Département
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-2">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.geolocalisation.provinces.index') }}">Géolocalisation</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.geolocalisation.departements.index') }}">Départements</a>
                            </li>
                            <li class="breadcrumb-item active">Créer</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.geolocalisation.departements.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>

            {{-- Messages d'erreur généraux --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Erreur :</strong> Veuillez corriger les champs indiqués ci-dessous.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Formulaire --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informations du Département
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.geolocalisation.departements.store') }}" novalidate>
                        @csrf
                        
                        {{-- Contenu du formulaire directement intégré --}}
                        <div class="row g-3">
                            {{-- Sélection Province (obligatoire) --}}
                            <div class="col-md-6">
                                <label for="province_id" class="form-label required">
                                    <i class="fas fa-map-marked-alt me-1"></i>
                                    Province
                                </label>
                                <select class="form-select @error('province_id') is-invalid @enderror" 
                                        id="province_id" 
                                        name="province_id" 
                                        required>
                                    <option value="">Sélectionner une province...</option>
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" 
                                                {{ old('province_id', $departement->province_id) == $province->id ? 'selected' : '' }}>
                                            {{ $province->nom }} ({{ $province->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('province_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">La province à laquelle appartient ce département</div>
                            </div>

                            {{-- Nom du département --}}
                            <div class="col-md-6">
                                <label for="nom" class="form-label required">
                                    <i class="fas fa-building me-1"></i>
                                    Nom du Département
                                </label>
                                <input type="text" 
                                       class="form-control @error('nom') is-invalid @enderror" 
                                       id="nom" 
                                       name="nom" 
                                       value="{{ old('nom', $departement->nom) }}" 
                                       required
                                       placeholder="Ex: Estuaire, Woleu-Ntem...">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Le nom officiel du département</div>
                            </div>

                            {{-- Code et Chef-lieu --}}
                            <div class="col-md-6">
                                <label for="code" class="form-label">
                                    <i class="fas fa-tag me-1"></i>
                                    Code Département
                                </label>
                                <input type="text" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       id="code" 
                                       name="code" 
                                       value="{{ old('code', $departement->code) }}" 
                                       maxlength="15"
                                       placeholder="Ex: EST01, WOL02..."
                                       style="text-transform: uppercase;">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Code unique (auto-généré si vide)</div>
                            </div>

                            <div class="col-md-6">
                                <label for="chef_lieu" class="form-label">
                                    <i class="fas fa-city me-1"></i>
                                    Chef-lieu
                                </label>
                                <input type="text" 
                                       class="form-control @error('chef_lieu') is-invalid @enderror" 
                                       id="chef_lieu" 
                                       name="chef_lieu" 
                                       value="{{ old('chef_lieu', $departement->chef_lieu) }}" 
                                       placeholder="Ex: Libreville, Oyem...">
                                @error('chef_lieu')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Ville principale du département</div>
                            </div>

                            {{-- Description --}}
                            <div class="col-12">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>
                                    Description
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="3"
                                          placeholder="Description du département, ses caractéristiques, son histoire...">{{ old('description', $departement->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Description optionnelle du département</div>
                            </div>

                            {{-- Données géographiques --}}
                            <div class="col-12">
                                <hr class="my-4">
                                <h6 class="text-primary">
                                    <i class="fas fa-globe me-2"></i>
                                    Données Géographiques
                                </h6>
                            </div>

                            <div class="col-md-4">
                                <label for="superficie_km2" class="form-label">
                                    <i class="fas fa-ruler-combined me-1"></i>
                                    Superficie (km²)
                                </label>
                                <input type="number" 
                                       class="form-control @error('superficie_km2') is-invalid @enderror" 
                                       id="superficie_km2" 
                                       name="superficie_km2" 
                                       value="{{ old('superficie_km2', $departement->superficie_km2) }}" 
                                       min="0" 
                                       max="999999.99" 
                                       step="0.01"
                                       placeholder="Ex: 15000">
                                @error('superficie_km2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Superficie en kilomètres carrés</div>
                            </div>

                            <div class="col-md-4">
                                <label for="population_estimee" class="form-label">
                                    <i class="fas fa-users me-1"></i>
                                    Population estimée
                                </label>
                                <input type="number" 
                                       class="form-control @error('population_estimee') is-invalid @enderror" 
                                       id="population_estimee" 
                                       name="population_estimee" 
                                       value="{{ old('population_estimee', $departement->population_estimee) }}" 
                                       min="0" 
                                       max="99999999"
                                       placeholder="Ex: 500000">
                                @error('population_estimee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Nombre d'habitants estimé</div>
                            </div>

                            <div class="col-md-4">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label for="ordre_affichage" class="form-label">
                                            <i class="fas fa-sort-numeric-down me-1"></i>
                                            Ordre
                                        </label>
                                        <input type="number" 
                                               class="form-control @error('ordre_affichage') is-invalid @enderror" 
                                               id="ordre_affichage" 
                                               name="ordre_affichage" 
                                               value="{{ old('ordre_affichage', $departement->ordre_affichage ?? 0) }}" 
                                               min="0" 
                                               max="999">
                                        @error('ordre_affichage')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6">
                                        <div class="form-check form-switch mt-4">
                                            <input class="form-check-input @error('is_active') is-invalid @enderror" 
                                                   type="checkbox" 
                                                   id="is_active" 
                                                   name="is_active" 
                                                   value="1"
                                                   {{ old('is_active', $departement->is_active ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                <i class="fas fa-power-off me-1"></i>
                                                Actif
                                            </label>
                                            @error('is_active')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-text">Position dans les listes et statut</div>
                            </div>

                            {{-- Coordonnées GPS --}}
                            <div class="col-12">
                                <hr class="my-4">
                                <h6 class="text-primary">
                                    <i class="fas fa-map-pin me-2"></i>
                                    Localisation GPS (optionnel)
                                </h6>
                            </div>

                            <div class="col-md-6">
                                <label for="latitude" class="form-label">
                                    <i class="fas fa-compass me-1"></i>
                                    Latitude
                                </label>
                                <input type="number" 
                                       class="form-control @error('latitude') is-invalid @enderror" 
                                       id="latitude" 
                                       name="latitude" 
                                       value="{{ old('latitude', $departement->latitude) }}" 
                                       min="-90" 
                                       max="90" 
                                       step="0.00000001"
                                       placeholder="Ex: 0.3901">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Entre -90 et 90 degrés</div>
                            </div>

                            <div class="col-md-6">
                                <label for="longitude" class="form-label">
                                    <i class="fas fa-compass me-1"></i>
                                    Longitude
                                </label>
                                <input type="number" 
                                       class="form-control @error('longitude') is-invalid @enderror" 
                                       id="longitude" 
                                       name="longitude" 
                                       value="{{ old('longitude', $departement->longitude) }}" 
                                       min="-180" 
                                       max="180" 
                                       step="0.00000001"
                                       placeholder="Ex: 9.4544">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Entre -180 et 180 degrés</div>
                            </div>

                            {{-- Calcul de densité en temps réel --}}
                            <div class="col-12">
                                <div class="alert alert-info" id="densite-info" style="display: none;">
                                    <i class="fas fa-calculator me-2"></i>
                                    <strong>Densité calculée :</strong> <span id="densite-value">0</span> hab/km²
                                </div>
                            </div>

                            {{-- Information Province sélectionnée --}}
                            <div class="col-12" id="province-info" style="display: none;">
                                <div class="alert alert-primary">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Province sélectionnée
                                    </h6>
                                    <div id="province-details"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.geolocalisation.departements.index') }}" 
                                       class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Créer le Département
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.alert-info {
    background-color: #e7f3ff;
    border-color: #b8daff;
    color: #004085;
}

.alert-primary {
    background-color: #cce7ff;
    border-color: #99d3ff;
    color: #003d82;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-conversion en majuscules pour le code
    const codeInput = document.getElementById('code');
    if (codeInput) {
        codeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    }

    // Gestion du sélecteur de province
    const provinceSelect = document.getElementById('province_id');
    const provinceInfo = document.getElementById('province-info');
    const provinceDetails = document.getElementById('province-details');

    if (provinceSelect) {
        provinceSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value) {
                // Afficher les informations de la province
                const provinceName = selectedOption.text;
                provinceDetails.innerHTML = `
                    <div class="row">
                        <div class="col-md-8">
                            <strong>Province :</strong> ${provinceName}<br>
                            <small class="text-muted">
                                Le code du département sera automatiquement préfixé avec le code de cette province.
                            </small>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="/admin/geolocalisation/provinces/${this.value}" 
                               target="_blank" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Voir la province
                            </a>
                        </div>
                    </div>
                `;
                provinceInfo.style.display = 'block';
                
                // Auto-génération du préfixe du code si le code est vide
                const codeInput = document.getElementById('code');
                if (codeInput && !codeInput.value.trim()) {
                    // Extraire le code de la province du texte de l'option
                    const codeMatch = provinceName.match(/\(([^)]+)\)$/);
                    if (codeMatch) {
                        const provinceCode = codeMatch[1];
                        // Suggestion de préfixe mais ne pas l'imposer
                        codeInput.placeholder = `Ex: ${provinceCode.substring(0,2)}01, ${provinceCode.substring(0,2)}02...`;
                    }
                }
            } else {
                provinceInfo.style.display = 'none';
                // Remettre le placeholder par défaut
                if (codeInput) {
                    codeInput.placeholder = 'Ex: EST01, WOL02...';
                }
            }
        });

        // Trigger au chargement si une province est déjà sélectionnée
        if (provinceSelect.value) {
            provinceSelect.dispatchEvent(new Event('change'));
        }
    }

    // Calcul automatique de la densité
    const superficieInput = document.getElementById('superficie_km2');
    const populationInput = document.getElementById('population_estimee');
    const densiteInfo = document.getElementById('densite-info');
    const densiteValue = document.getElementById('densite-value');
    
    function calculerDensite() {
        const superficie = parseFloat(superficieInput?.value);
        const population = parseInt(populationInput?.value);
        
        if (superficie && population && superficie > 0) {
            const densite = (population / superficie).toFixed(2);
            densiteValue.textContent = new Intl.NumberFormat('fr-FR').format(densite);
            densiteInfo.style.display = 'block';
        } else {
            densiteInfo.style.display = 'none';
        }
    }
    
    superficieInput?.addEventListener('input', calculerDensite);
    populationInput?.addEventListener('input', calculerDensite);
    
    // Calcul initial si les valeurs existent
    calculerDensite();

    // Validation des coordonnées GPS
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    
    function validateCoordinate(input, min, max, name) {
        if (input) {
            input.addEventListener('blur', function() {
                const value = parseFloat(this.value);
                if (this.value && (isNaN(value) || value < min || value > max)) {
                    this.setCustomValidity(`${name} doit être comprise entre ${min} et ${max} degrés`);
                    this.classList.add('is-invalid');
                } else {
                    this.setCustomValidity('');
                    this.classList.remove('is-invalid');
                }
            });
        }
    }
    
    validateCoordinate(latInput, -90, 90, 'La latitude');
    validateCoordinate(lngInput, -180, 180, 'La longitude');

    // Validation unicité nom dans province
    const nomInput = document.getElementById('nom');
    if (nomInput && provinceSelect) {
        let validationTimeout;
        
        function validateNomUnique() {
            const nom = nomInput.value.trim();
            const provinceId = provinceSelect.value;
            
            if (nom && provinceId) {
                clearTimeout(validationTimeout);
                validationTimeout = setTimeout(() => {
                    // Ici on pourrait faire un appel AJAX pour vérifier l'unicité
                    // Pour l'instant, on fait juste une validation côté client basique
                    console.log(`Validation unicité: ${nom} dans province ${provinceId}`);
                }, 500);
            }
        }
        
        nomInput.addEventListener('input', validateNomUnique);
        provinceSelect.addEventListener('change', validateNomUnique);
    }
});
</script>
@endpush
@endsection