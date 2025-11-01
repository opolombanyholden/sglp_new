@extends('layouts.admin')

@section('title', 'Modifier Regroupement - ' . $regroupement->nom)

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
                        <li class="breadcrumb-item"><a href="{{ route('admin.geolocalisation.regroupements.index') }}">Regroupements</a></li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-pencil"></i> Modifier : Regroupement {{ $regroupement->nom }}
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

    <form method="POST" action="{{ route('admin.geolocalisation.regroupements.update', $regroupement) }}" id="regroupement-form">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Formulaire principal -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-information"></i> Informations du Regroupement
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Sélection du Canton -->
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="canton_id" class="form-label">Canton <span class="text-danger">*</span></label>
                                <select name="canton_id" id="canton_id" class="form-select @error('canton_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionnez un canton --</option>
                                    @foreach($cantons as $canton)
                                        <option value="{{ $canton->id }}" 
                                            {{ old('canton_id', $regroupement->canton_id) == $canton->id ? 'selected' : '' }}>
                                            {{ $canton->nom }} ({{ $canton->departement->nom }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('canton_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Nom et Code -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom du Regroupement <span class="text-danger">*</span></label>
                                <input type="text" name="nom" id="nom" 
                                       class="form-control @error('nom') is-invalid @enderror" 
                                       value="{{ old('nom', $regroupement->nom) }}" 
                                       placeholder="Ex: Regroupement Achouka" required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Code Regroupement <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       value="{{ old('code', $regroupement->code) }}" 
                                       placeholder="Ex: REG-ACH-001" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Code unique du regroupement</small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="4" placeholder="Description du regroupement, ses particularités...">{{ old('description', $regroupement->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-cog"></i> Statut et Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Statut</label>
                            <select name="is_active" id="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                <option value="1" {{ old('is_active', $regroupement->is_active) == '1' ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ old('is_active', $regroupement->is_active) == '0' ? 'selected' : '' }}>Inactif</option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Mettre à jour
                            </button>
                            <a href="{{ route('admin.geolocalisation.regroupements.show', $regroupement) }}" class="btn btn-info">
                                <i class="mdi mdi-eye"></i> Voir les détails
                            </a>
                            <a href="{{ route('admin.geolocalisation.regroupements.index') }}" class="btn btn-secondary">
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
                            <strong>Créé le :</strong> {{ $regroupement->created_at->format('d/m/Y à H:i') }}<br>
                            <strong>Modifié le :</strong> {{ $regroupement->updated_at->format('d/m/Y à H:i') }}
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
    document.getElementById('regroupement-form').addEventListener('submit', function(e) {
        let isValid = true;
        
        // Vérification des champs obligatoires
        const required = ['canton_id', 'nom', 'code'];
        required.forEach(function(field) {
            const input = document.getElementById(field);
            if (!input.value || input.value.trim() === '') {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (!isValid) {
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

    // Confirmation des changements critiques
    const originalCanton = '{{ $regroupement->canton_id }}';
    document.getElementById('canton_id').addEventListener('change', function() {
        if (this.value !== originalCanton && originalCanton) {
            alert('Attention : Changer le canton peut avoir des impacts sur la hiérarchie géographique');
        }
    });
});
</script>
@endpush