@extends('layouts.admin')

@section('title', 'Nouvel Arrondissement')

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
                        <li class="breadcrumb-item"><a href="{{ route('admin.geolocalisation.arrondissements.index') }}">Arrondissements</a></li>
                        <li class="breadcrumb-item active">Nouveau</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-office-building-outline"></i> Nouvel Arrondissement
                </h4>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.geolocalisation.arrondissements.store') }}" id="arrondissement-form">
        @csrf

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
                                <select name="departement_id" id="departement_id" class="form-select @error('departement_id') is-invalid @enderror">
                                    <option value="">-- Sélectionnez un département --</option>
                                    @foreach($departements as $departement)
                                        <option value="{{ $departement->id }}" {{ old('departement_id') == $departement->id ? 'selected' : '' }}>
                                            {{ $departement->nom }} ({{ $departement->province->nom }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('departement_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="commune_ville_id" class="form-label">Commune/Ville <span class="text-danger">*</span></label>
                                <select name="commune_ville_id" id="commune_ville_id" class="form-select @error('commune_ville_id') is-invalid @enderror" required disabled>
                                    <option value="">-- Sélectionnez d'abord un département --</option>
                                </select>
                                @error('commune_ville_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text" name="nom" id="nom" 
                                       class="form-control @error('nom') is-invalid @enderror" 
                                       value="{{ old('nom') }}" 
                                       placeholder="Ex: Centre-ville, Cocotiers..." required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="numero_arrondissement" class="form-label">Numéro <span class="text-danger">*</span></label>
                                <input type="number" name="numero_arrondissement" id="numero_arrondissement" 
                                       class="form-control @error('numero_arrondissement') is-invalid @enderror" 
                                       value="{{ old('numero_arrondissement') }}" 
                                       min="1" max="20" placeholder="1" required>
                                @error('numero_arrondissement')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Numéro d'ordre (1er, 2ème, etc.)</small>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" id="code" 
                                       class="form-control @error('code') is-invalid @enderror" 
                                       value="{{ old('code') }}" 
                                       placeholder="Auto-généré" required readonly>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Code unique généré automatiquement</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" placeholder="Description de l'arrondissement...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="limites_geographiques" class="form-label">Limites géographiques</label>
                            <textarea name="limites_geographiques" id="limites_geographiques" 
                                      class="form-control @error('limites_geographiques') is-invalid @enderror" 
                                      rows="3" placeholder="Description des limites géographiques...">{{ old('limites_geographiques') }}</textarea>
                            @error('limites_geographiques')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                       value="{{ old('superficie_km2') }}" 
                                       step="0.01" min="0" placeholder="0.00">
                                @error('superficie_km2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="population_estimee" class="form-label">Population estimée</label>
                                <input type="number" name="population_estimee" id="population_estimee" 
                                       class="form-control @error('population_estimee') is-invalid @enderror" 
                                       value="{{ old('population_estimee') }}" 
                                       min="0" placeholder="0">
                                @error('population_estimee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="ordre_affichage" class="form-label">Ordre d'affichage</label>
                                <input type="number" name="ordre_affichage" id="ordre_affichage" 
                                       class="form-control @error('ordre_affichage') is-invalid @enderror" 
                                       value="{{ old('ordre_affichage', 0) }}" 
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
                                       value="{{ old('latitude') }}" 
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
                                       value="{{ old('longitude') }}" 
                                       step="0.00000001" min="-180" max="180" 
                                       placeholder="0.00000000">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Entre -180 et 180</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Administration et contact -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-account-tie"></i> Administration et Contact
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="delegue" class="form-label">Délégué d'arrondissement</label>
                                <input type="text" name="delegue" id="delegue" 
                                       class="form-control @error('delegue') is-invalid @enderror" 
                                       value="{{ old('delegue') }}" 
                                       placeholder="Nom du délégué">
                                @error('delegue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" name="telephone" id="telephone" 
                                       class="form-control @error('telephone') is-invalid @enderror" 
                                       value="{{ old('telephone') }}" 
                                       placeholder="+241 XX XX XX XX">
                                @error('telephone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" 
                                       placeholder="delegation@exemple.ga">
                                @error('email')
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
                                <i class="mdi mdi-content-save"></i> Enregistrer
                            </button>
                            <a href="{{ route('admin.geolocalisation.arrondissements.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Retour à la liste
                            </a>
                        </div>

                        <hr>

                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Arrondissement actif
                            </label>
                        </div>

                    </div>
                </div>

                <!-- Informations complémentaires -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-information-outline"></i> Services et équipements
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="services_publics" class="form-label">Services publics</label>
                            <input type="text" name="services_publics" id="services_publics" 
                                   class="form-control @error('services_publics') is-invalid @enderror" 
                                   value="{{ old('services_publics') }}" 
                                   placeholder="Ex: Mairie, Poste, Commissariat...">
                            <small class="form-text text-muted">Séparez par des virgules</small>
                            @error('services_publics')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="equipements" class="form-label">Équipements</label>
                            <input type="text" name="equipements" id="equipements" 
                                   class="form-control @error('equipements') is-invalid @enderror" 
                                   value="{{ old('equipements') }}" 
                                   placeholder="Ex: École, Hôpital, Marché...">
                            <small class="form-text text-muted">Séparez par des virgules</small>
                            @error('equipements')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="transport_public" class="form-label">Transport public</label>
                            <select name="transport_public" id="transport_public" class="form-select">
                                <option value="">-- Non spécifié --</option>
                                <option value="1" {{ old('transport_public') === '1' ? 'selected' : '' }}>Disponible</option>
                                <option value="0" {{ old('transport_public') === '0' ? 'selected' : '' }}>Non disponible</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="niveau_securite" class="form-label">Niveau de sécurité</label>
                            <select name="niveau_securite" id="niveau_securite" class="form-select">
                                <option value="">-- Non évalué --</option>
                                <option value="très faible" {{ old('niveau_securite') === 'très faible' ? 'selected' : '' }}>Très faible</option>
                                <option value="faible" {{ old('niveau_securite') === 'faible' ? 'selected' : '' }}>Faible</option>
                                <option value="moyen" {{ old('niveau_securite') === 'moyen' ? 'selected' : '' }}>Moyen</option>
                                <option value="bon" {{ old('niveau_securite') === 'bon' ? 'selected' : '' }}>Bon</option>
                                <option value="excellent" {{ old('niveau_securite') === 'excellent' ? 'selected' : '' }}>Excellent</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="densite_population" class="form-label">Densité population (hab/km²)</label>
                            <input type="number" name="densite_population" id="densite_population" 
                                   class="form-control @error('densite_population') is-invalid @enderror" 
                                   value="{{ old('densite_population') }}" 
                                   min="0" step="0.01" placeholder="0.00">
                            @error('densite_population')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="autres_infos" class="form-label">Autres informations</label>
                            <textarea name="autres_infos" id="autres_infos" 
                                      class="form-control @error('autres_infos') is-invalid @enderror" 
                                      rows="3" 
                                      placeholder="Informations complémentaires...">{{ old('autres_infos') }}</textarea>
                            @error('autres_infos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Aide -->
                <div class="card border-info">
                    <div class="card-header bg-soft-info">
                        <h6 class="card-title text-info mb-0">
                            <i class="mdi mdi-lightbulb-outline"></i> Aide
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Arrondissement :</strong> Division administrative urbaine d'une commune ou ville.<br><br>
                            
                            <strong>Numérotation :</strong> Les arrondissements sont généralement numérotés dans l'ordre (1er, 2ème, 3ème, etc.)<br><br>
                            
                            <strong>Code :</strong> Sera généré automatiquement selon le format : [CODE_COMMUNE][A][NUMERO]<br><br>
                            
                            <strong>Délégué :</strong> Responsable administratif nommé pour gérer l'arrondissement
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
    // Chargement en cascade : Département → Communes/Villes
    $('#departement_id').on('change', function() {
        const departementId = $(this).val();
        const $communeVille = $('#commune_ville_id');
        
        if (departementId) {
            $communeVille.prop('disabled', true).html('<option value="">Chargement...</option>');
            
            $.ajax({
                url: `/admin/geolocalisation/communes/by-departement/${departementId}`,
                method: 'GET',
                success: function(data) {
                    let options = '<option value="">-- Sélectionnez une commune/ville --</option>';
                    
                    data.forEach(function(item) {
                        const typeLabel = item.type === 'ville' ? 'Ville' : 'Commune';
                        options += `<option value="${item.id}">${item.nom} (${typeLabel})</option>`;
                    });
                    
                    $communeVille.html(options).prop('disabled', false);
                },
                error: function() {
                    $communeVille.html('<option value="">Erreur de chargement</option>');
                    toastr.error('Erreur lors du chargement des communes/villes');
                }
            });
        } else {
            $communeVille.html('<option value="">-- Sélectionnez d\'abord un département --</option>').prop('disabled', true);
        }
        
        // Réinitialiser les champs dépendants
        resetDependentFields();
    });

    // Auto-suggestion du numéro d'arrondissement suivant
    $('#commune_ville_id').on('change', function() {
        const communeVilleId = $(this).val();
        
        if (communeVilleId) {
            // Récupérer le prochain numéro disponible
            $.ajax({
                url: `/admin/arrondissements/next-numero/${communeVilleId}`,
                method: 'GET',
                success: function(data) {
                    if (data.next_numero && !$('#numero_arrondissement').val()) {
                        $('#numero_arrondissement').val(data.next_numero);
                        generateCode();
                    }
                },
                error: function() {
                    // Si erreur, suggérer 1 par défaut
                    if (!$('#numero_arrondissement').val()) {
                        $('#numero_arrondissement').val(1);
                        generateCode();
                    }
                }
            });
        }
        
        generateCode();
    });

    // Auto-génération du code
    $('#nom, #numero_arrondissement').on('input', function() {
        generateCode();
    });

    function generateCode() {
        const communeVilleId = $('#commune_ville_id').val();
        const nom = $('#nom').val().trim();
        const numero = $('#numero_arrondissement').val();
        
        if (communeVilleId && nom && numero) {
            // Récupérer le code de la commune/ville sélectionnée
            const communeVilleText = $('#commune_ville_id option:selected').text();
            const communeCode = communeVilleText.split('(')[0].trim().substring(0, 3).toUpperCase();
            
            // Générer le code
            const numeroFormatted = numero.toString().padStart(2, '0');
            const codeGenere = communeCode + 'A' + numeroFormatted;
            
            $('#code').val(codeGenere);
        }
    }

    function resetDependentFields() {
        $('#numero_arrondissement').val('');
        $('#code').val('');
    }

    // Calcul automatique de la densité de population
    $('#population_estimee, #superficie_km2').on('input', function() {
        const population = parseFloat($('#population_estimee').val()) || 0;
        const superficie = parseFloat($('#superficie_km2').val()) || 0;
        
        if (population > 0 && superficie > 0) {
            const densite = (population / superficie).toFixed(2);
            if (!$('#densite_population').val()) {
                $('#densite_population').val(densite);
            }
        }
    });

    // Validation du formulaire
    $('#arrondissement-form').on('submit', function(e) {
        let isValid = true;
        
        // Vérification des champs obligatoires
        const required = ['commune_ville_id', 'nom', 'numero_arrondissement'];
        required.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val() || input.val().trim() === '') {
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

        // Validation du numéro d'arrondissement
        const numero = parseInt($('#numero_arrondissement').val());
        if ($('#numero_arrondissement').val() && (numero < 1 || numero > 20)) {
            $('#numero_arrondissement').addClass('is-invalid');
            isValid = false;
        } else {
            $('#numero_arrondissement').removeClass('is-invalid');
        }

        if (!isValid) {
            e.preventDefault();
            toastr.error('Veuillez corriger les erreurs dans le formulaire');
        }
    });

    // Suggestions de coordonnées GPS pour les principales villes
    $('#commune_ville_id').on('change', function() {
        const nom = $(this).find('option:selected').text().toLowerCase();
        
        // Coordonnées de quelques arrondissements connus
        const coordonnees = {
            'libreville': [
                { nom: 'centre-ville', lat: 0.4162, lng: 9.4673 },
                { nom: 'cocotiers', lat: 0.3925, lng: 9.4536 },
                { nom: 'montagne sainte', lat: 0.4289, lng: 9.4521 }
            ]
        };

        // Auto-remplissage si correspondance trouvée et champs vides
        Object.keys(coordonnees).forEach(function(ville) {
            if (nom.includes(ville) && !$('#latitude').val() && !$('#longitude').val()) {
                const arrondissementNom = $('#nom').val().toLowerCase();
                const coords = coordonnees[ville].find(a => arrondissementNom.includes(a.nom));
                
                if (coords) {
                    $('#latitude').val(coords.lat);
                    $('#longitude').val(coords.lng);
                    toastr.info('Coordonnées GPS suggérées remplies automatiquement');
                }
            }
        });
    });
});
</script>
@endpush