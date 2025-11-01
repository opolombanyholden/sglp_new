@extends('layouts.admin')

@section('title', 'Nouveau Regroupement')

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
                        <li class="breadcrumb-item active">Nouveau</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-home-group"></i> Nouveau Regroupement
                </h4>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.geolocalisation.regroupements.store') }}" id="regroupement-form">
        @csrf

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
                                        <option value="{{ $canton->id }}" {{ old('canton_id') == $canton->id ? 'selected' : '' }}>
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
                                <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror" 
                                       value="{{ old('nom') }}" required placeholder="Ex: Regroupement Achouka">
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="code" class="form-label">Code Regroupement <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" 
                                       value="{{ old('code') }}" required placeholder="Ex: REG-ACH-001">
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Code unique du regroupement</small>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control @error('description') is-invalid @enderror" 
                                      placeholder="Description du regroupement, ses particularités...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                            <i class="mdi mdi-cog"></i> Statut et Actions
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

                        <hr>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="mdi mdi-content-save"></i> Enregistrer le Regroupement
                            </button>
                            <a href="{{ route('admin.geolocalisation.regroupements.index') }}" class="btn btn-secondary">
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
                            <h6><i class="mdi mdi-information"></i> Regroupement</h6>
                            <p class="mb-2"><strong>Définition :</strong> Ensemble de villages regroupés au sein d'un canton.</p>
                            <p class="mb-2"><strong>Niveau :</strong> 4ème niveau de la hiérarchie administrative.</p>
                            <p class="mb-0"><strong>Composants :</strong> Villages et localités.</p>
                        </div>

                        <div class="alert alert-warning" role="alert">
                            <h6><i class="mdi mdi-lightbulb"></i> Conseils</h6>
                            <ul class="mb-0 ps-3">
                                <li>Le nom doit être unique dans le canton</li>
                                <li>Le code doit être unique</li>
                                <li>Utilisez une description claire</li>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-génération du code regroupement
    document.getElementById('nom').addEventListener('blur', function() {
        const nom = this.value.trim();
        const codeField = document.getElementById('code');
        
        if (nom && !codeField.value) {
            // Génération de code
            let code = nom.substring(0, 3).toUpperCase();
            // Nettoyage des caractères spéciaux
            code = code.replace(/[^A-Z]/g, '');
            if (code.length < 3) {
                code = code.padEnd(3, 'X');
            }
            code = 'REG-' + code + '-001';
            
            codeField.value = code;
        }
    });

    // Validation du formulaire
    document.getElementById('regroupement-form').addEventListener('submit', function(e) {
        let valid = true;
        
        // Nettoyage des erreurs précédentes
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        
        // Vérifications obligatoires
        const canton = document.getElementById('canton_id');
        const nom = document.getElementById('nom');
        const code = document.getElementById('code');
        
        if (!canton.value) {
            valid = false;
            canton.classList.add('is-invalid');
        }
        
        if (!nom.value.trim()) {
            valid = false;
            nom.classList.add('is-invalid');
        }
        
        if (!code.value.trim()) {
            valid = false;
            code.classList.add('is-invalid');
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
});
</script>
@endpush