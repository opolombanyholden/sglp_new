@extends('layouts.admin')

@section('title', 'Modifier Localité - ' . $localite->nom)

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
                        <li class="breadcrumb-item"><a href="{{ route('admin.geolocalisation.localites.index') }}">Localités</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    @if($localite->type === 'quartier')
                        <i class="mdi mdi-city"></i> Modifier Quartier: {{ $localite->nom }}
                    @else
                        <i class="mdi mdi-tree"></i> Modifier Village: {{ $localite->nom }}
                    @endif
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

    <form method="POST" action="{{ route('admin.geolocalisation.localites.update', $localite) }}" id="localite-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="type" value="{{ $localite->type }}">

        <div class="row">
            <!-- Formulaire principal -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-information"></i> Informations de la Localité
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Localisation -->
                        <div class="row">
                            @if($localite->type === 'quartier')
                                <div class="col-md-12 mb-3">
                                    <label for="arrondissement_id" class="form-label">Arrondissement <span class="text-danger">*</span></label>
                                    <select name="arrondissement_id" id="arrondissement_id" class="form-select @error('arrondissement_id') is-invalid @enderror" required>
                                        <option value="">-- Sélectionnez un arrondissement --</option>
                                        @foreach($arrondissements as $arrondissement)
                                            <option value="{{ $arrondissement->id }}" 
                                                {{ old('arrondissement_id', $localite->arrondissement_id) == $arrondissement->id ? 'selected' : '' }}>
                                                {{ $arrondissement->nom }} - {{ $arrondissement->communeVille->nom }} ({{ $arrondissement->communeVille->departement->nom }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('arrondissement_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @else
                                <div class="col-md-12 mb-3">
                                    <label for="regroupement_id" class="form-label">Regroupement <span class="text-danger">*</span></label>
                                    <select name="regroupement_id" id="regroupement_id" class="form-select @error('regroupement_id') is-invalid @enderror" required>
                                        <option value="">-- Sélectionnez un regroupement --</option>
                                        @foreach($regroupements as $regroupement)
                                            <option value="{{ $regroupement->id }}" 
                                                {{ old('regroupement_id', $localite->regroupement_id) == $regroupement->id ? 'selected' : '' }}>
                                                {{ $regroupement->nom }} - {{ $regroupement->canton->nom }} ({{ $regroupement->canton->departement->nom }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('regroupement_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        <!-- Nom et Code -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom {{ $localite->type === 'quartier' ? 'du Quartier' : 'du Village' }} <span class="text-danger">*</span></label>
                                <input type="text" name="nom" id="nom" 
                                       class="form-control @error('nom') is-invalid @enderror" 
                                       value="{{ old('nom', $localite->nom) }}" 
                                       required placeholder="Ex: Centre-ville">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Code</label>
                                <input type="text" name="code" id="code" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       value="{{ old('code', $localite->code) }}" 
                                       placeholder="Ex: QT-CEN-001">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Code unique de la localité</small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="4" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Description de la localité...">{{ old('description', $localite->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Population et Coordonnées -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="population_estimee" class="form-label">Population estimée</label>
                                <input type="number" name="population_estimee" id="population_estimee" 
                                       class="form-control @error('population_estimee') is-invalid @enderror" 
                                       value="{{ old('population_estimee', $localite->population_estimee) }}" 
                                       min="0" placeholder="Ex: 5000">
                                @error('population_estimee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" name="latitude" id="latitude" 
                                       class="form-control @error('latitude') is-invalid @enderror" 
                                       value="{{ old('latitude', $localite->latitude) }}" 
                                       step="0.00000001" min="-90" max="90"
                                       placeholder="Ex: -0.8037">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" name="longitude" id="longitude" 
                                       class="form-control @error('longitude') is-invalid @enderror" 
                                       value="{{ old('longitude', $localite->longitude) }}" 
                                       step="0.00000001" min="-180" max="180"
                                       placeholder="Ex: 11.6094">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Statut et actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-cog"></i> Paramètres
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Statut</label>
                            <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active', $localite->is_active) == '1' ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ old('is_active', $localite->is_active) == '0' ? 'selected' : '' }}>Inactif</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ordre_affichage" class="form-label">Ordre d'affichage</label>
                            <input type="number" name="ordre_affichage" id="ordre_affichage" 
                                   class="form-control @error('ordre_affichage') is-invalid @enderror" 
                                   value="{{ old('ordre_affichage', $localite->ordre_affichage) }}" min="0">
                            @error('ordre_affichage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Mettre à jour
                            </button>
                            <a href="{{ route('admin.geolocalisation.localites.show', $localite) }}" class="btn btn-info">
                                <i class="mdi mdi-eye"></i> Voir les détails
                            </a>
                            <a href="{{ route('admin.geolocalisation.localites.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Informations système -->
                <div class="card border-light">
                    <div class="card-header bg-light">
                        <h6 class="card-title text-muted mb-0">
                            <i class="mdi mdi-clock-outline"></i> Historique
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Créé le :</strong> {{ $localite->created_at->format('d/m/Y à H:i') }}<br>
                            <strong>Modifié le :</strong> {{ $localite->updated_at->format('d/m/Y à H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.page-title {
    color: #2c5282;
    font-weight: 600;
}

.breadcrumb-item a {
    color: #4299e1;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #2b77c7;
    text-decoration: underline;
}

.form-label {
    font-weight: 500;
    color: #2d3748;
}

.text-danger {
    color: #e53e3e !important;
}

.btn-primary {
    background-color: #4299e1;
    border-color: #4299e1;
}

.btn-primary:hover {
    background-color: #2b77c7;
    border-color: #2b77c7;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire
    document.getElementById('localite-form').addEventListener('submit', function(e) {
        let valid = true;
        
        // Vérification des champs obligatoires
        @if($localite->type === 'quartier')
            const arrondissement = document.getElementById('arrondissement_id');
            if (!arrondissement.value) {
                valid = false;
                arrondissement.classList.add('is-invalid');
            } else {
                arrondissement.classList.remove('is-invalid');
            }
        @else
            const regroupement = document.getElementById('regroupement_id');
            if (!regroupement.value) {
                valid = false;
                regroupement.classList.add('is-invalid');
            } else {
                regroupement.classList.remove('is-invalid');
            }
        @endif
        
        const nom = document.getElementById('nom');
        if (!nom.value.trim()) {
            valid = false;
            nom.classList.add('is-invalid');
        } else {
            nom.classList.remove('is-invalid');
        }

        if (!valid) {
            e.preventDefault();
            
            // Scroll vers la première erreur
            const firstError = document.querySelector('.is-invalid');
            if (firstError) {
                firstError.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                firstError.focus();
            }
        }
    });

    // Validation des coordonnées GPS
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    
    if (latitudeInput) {
        latitudeInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (this.value && (value < -90 || value > 90)) {
                this.setCustomValidity('La latitude doit être comprise entre -90 et 90');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    if (longitudeInput) {
        longitudeInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (this.value && (value < -180 || value > 180)) {
                this.setCustomValidity('La longitude doit être comprise entre -180 et 180');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Confirmation des changements critiques
    @if($localite->type === 'quartier')
        const originalArrondissement = '{{ $localite->arrondissement_id }}';
        document.getElementById('arrondissement_id').addEventListener('change', function() {
            if (this.value !== originalArrondissement && originalArrondissement) {
                alert('Attention : Changer l\'arrondissement peut avoir des impacts sur la hiérarchie géographique');
            }
        });
    @else
        const originalRegroupement = '{{ $localite->regroupement_id }}';
        document.getElementById('regroupement_id').addEventListener('change', function() {
            if (this.value !== originalRegroupement && originalRegroupement) {
                alert('Attention : Changer le regroupement peut avoir des impacts sur la hiérarchie géographique');
            }
        });
    @endif
});
</script>
@endpush