@extends('layouts.admin')

@section('title', 'Nouvelle Localité - ' . ($type === 'quartier' ? 'Quartier' : 'Village'))

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
                        <li class="breadcrumb-item active">Nouveau</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    @if($type === 'quartier')
                        <i class="mdi mdi-city"></i> Nouveau Quartier
                    @else
                        <i class="mdi mdi-tree"></i> Nouveau Village
                    @endif
                </h4>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.geolocalisation.localites.store') }}" id="localite-form">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">

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
                            @if($type === 'quartier')
                                <div class="col-md-12 mb-3">
                                    <label for="arrondissement_id" class="form-label">Arrondissement <span class="text-danger">*</span></label>
                                    <select name="arrondissement_id" id="arrondissement_id" class="form-select @error('arrondissement_id') is-invalid @enderror" required>
                                        <option value="">-- Sélectionnez un arrondissement --</option>
                                        @foreach($arrondissements as $arrondissement)
                                            <option value="{{ $arrondissement->id }}" {{ old('arrondissement_id') == $arrondissement->id ? 'selected' : '' }}>
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
                                            <option value="{{ $regroupement->id }}" {{ old('regroupement_id') == $regroupement->id ? 'selected' : '' }}>
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
                                <label for="nom" class="form-label">Nom {{ $type === 'quartier' ? 'du Quartier' : 'du Village' }} <span class="text-danger">*</span></label>
                                <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror" 
                                       value="{{ old('nom') }}" required placeholder="Ex: Centre-ville">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Code</label>
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" 
                                       value="{{ old('code') }}" placeholder="Ex: QT-CEN-001 (auto-généré si vide)">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Laissez vide pour génération automatique</small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Description de la localité...">{{ old('description') }}</textarea>
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
                                       value="{{ old('population_estimee') }}" min="0" placeholder="Ex: 5000">
                                @error('population_estimee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" name="latitude" id="latitude" 
                                       class="form-control @error('latitude') is-invalid @enderror" 
                                       value="{{ old('latitude') }}" step="0.00000001" min="-90" max="90"
                                       placeholder="Ex: -0.8037">
                                @error('latitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" name="longitude" id="longitude" 
                                       class="form-control @error('longitude') is-invalid @enderror" 
                                       value="{{ old('longitude') }}" step="0.00000001" min="-180" max="180"
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
                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactif</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="ordre_affichage" class="form-label">Ordre d'affichage</label>
                            <input type="number" name="ordre_affichage" id="ordre_affichage" 
                                   class="form-control @error('ordre_affichage') is-invalid @enderror" 
                                   value="{{ old('ordre_affichage', 0) }}" min="0">
                            @error('ordre_affichage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="mdi mdi-content-save"></i> Enregistrer
                            </button>
                            <a href="{{ route('admin.geolocalisation.localites.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Aide contextuelle -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-help-circle"></i> Aide
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info" role="alert">
                            <h6><i class="mdi mdi-information"></i> Type de Localité</h6>
                            @if($type === 'quartier')
                                <p class="mb-0">Un <strong>quartier</strong> est une zone urbaine située dans un arrondissement.</p>
                            @else
                                <p class="mb-0">Un <strong>village</strong> est une zone rurale située dans un regroupement.</p>
                            @endif
                        </div>

                        <div class="alert alert-warning" role="alert">
                            <h6><i class="mdi mdi-lightbulb"></i> Conseils</h6>
                            <ul class="mb-0 ps-3">
                                <li>Le nom doit être unique</li>
                                <li>Le code est généré automatiquement</li>
                                <li>Les coordonnées GPS sont optionnelles</li>
                            </ul>
                        </div>
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

.btn-success {
    background-color: #38a169;
    border-color: #38a169;
}

.btn-success:hover {
    background-color: #2f855a;
    border-color: #2f855a;
}

.alert-info {
    background-color: #ebf8ff;
    border-color: #bee3f8;
    color: #2c5282;
}

.alert-warning {
    background-color: #fffbeb;
    border-color: #fbd38d;
    color: #92400e;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-génération du code
    document.getElementById('nom').addEventListener('blur', function() {
        const nom = this.value.trim();
        const codeField = document.getElementById('code');
        
        if (nom && !codeField.value) {
            const prefix = '{{ $type }}' === 'quartier' ? 'QT' : 'VL';
            let code = nom.substring(0, 3).toUpperCase();
            code = code.replace(/[^A-Z]/g, '');
            if (code.length < 3) {
                code = code.padEnd(3, 'X');
            }
            code = prefix + '-' + code + '-001';
            
            codeField.value = code;
        }
    });

    // Validation du formulaire
    document.getElementById('localite-form').addEventListener('submit', function(e) {
        let valid = true;
        
        // Nettoyage des erreurs précédentes
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        // Vérifications obligatoires
        @if($type === 'quartier')
            const arrondissement = document.getElementById('arrondissement_id');
            if (!arrondissement.value) {
                valid = false;
                arrondissement.classList.add('is-invalid');
            }
        @else
            const regroupement = document.getElementById('regroupement_id');
            if (!regroupement.value) {
                valid = false;
                regroupement.classList.add('is-invalid');
            }
        @endif
        
        const nom = document.getElementById('nom');
        if (!nom.value.trim()) {
            valid = false;
            nom.classList.add('is-invalid');
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
});
</script>
@endpush