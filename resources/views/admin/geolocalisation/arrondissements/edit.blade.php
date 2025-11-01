@extends('layouts.admin')

@section('title', 'Modifier Arrondissement - ' . $arrondissement->nom)

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
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-pencil"></i> Modifier : {{ $arrondissement->nom }}
                    @if($arrondissement->numero_arrondissement)
                        <small class="text-muted">({{ $arrondissement->numero_arrondissement === 1 ? '1er' : $arrondissement->numero_arrondissement . 'ème' }} Arrondissement)</small>
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

    <form method="POST" action="{{ route('admin.geolocalisation.arrondissements.update', $arrondissement) }}" id="arrondissement-form">
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
                                <select name="departement_id" id="departement_id" class="form-select @error('departement_id') is-invalid @enderror">
                                    <option value="">-- Sélectionnez un département --</option>
                                    @foreach($departements as $departement)
                                        <option value="{{ $departement->id }}" 
                                            {{ old('departement_id', $arrondissement->communeVille->departement_id) == $departement->id ? 'selected' : '' }}>
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
                                <select name="commune_ville_id" id="commune_ville_id" class="form-select @error('commune_ville_id') is-invalid @enderror" required>
                                    <option value="">-- Sélectionnez une commune/ville --</option>
                                    @foreach($communesVilles as $communeVille)
                                        <option value="{{ $communeVille->id }}" 
                                            data-departement="{{ $communeVille->departement_id }}"
                                            {{ old('commune_ville_id', $arrondissement->commune_ville_id) == $communeVille->id ? 'selected' : '' }}>
                                            {{ $communeVille->nom }} ({{ ucfirst($communeVille->type) }})
                                        </option>
                                    @endforeach
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
                                       value="{{ old('nom', $arrondissement->nom) }}" 
                                       placeholder="Ex: Centre-ville, Cocotiers..." required>
                                @error('nom')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-3">
                                <label for="numero_arrondissement" class="form-label">Numéro <span class="text-danger">*</span></label>
                                <input type="number" name="numero_arrondissement" id="numero_arrondissement" 
                                       class="form-control @error('numero_arrondissement') is-invalid @enderror" 
                                       value="{{ old('numero_arrondissement', $arrondissement->numero_arrondissement) }}" 
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
                                       value="{{ old('code', $arrondissement->code) }}" 
                                       placeholder="Auto-généré" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Code unique</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" placeholder="Description de l'arrondissement...">{{ old('description', $arrondissement->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="limites_geographiques" class="form-label">Limites géographiques</label>
                            <textarea name="limites_geographiques" id="limites_geographiques" 
                                      class="form-control @error('limites_geographiques') is-invalid @enderror" 
                                      rows="3" placeholder="Description des limites géographiques...">{{ old('limites_geographiques', $arrondissement->limites_geographiques) }}</textarea>
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
                                       value="{{ old('superficie_km2', $arrondissement->superficie_km2) }}" 
                                       step="0.01" min="0" placeholder="0.00">
                                @error('superficie_km2')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="population_estimee" class="form-label">Population estimée</label>
                                <input type="number" name="population_estimee" id="population_estimee" 
                                       class="form-control @error('population_estimee') is-invalid @enderror" 
                                       value="{{ old('population_estimee', $arrondissement->population_estimee) }}" 
                                       min="0" placeholder="0">
                                @error('population_estimee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="ordre_affichage" class="form-label">Ordre d'affichage</label>
                                <input type="number" name="ordre_affichage" id="ordre_affichage" 
                                       class="form-control @error('ordre_affichage') is-invalid @enderror" 
                                       value="{{ old('ordre_affichage', $arrondissement->ordre_affichage) }}" 
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
                                       value="{{ old('latitude', $arrondissement->latitude) }}" 
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
                                       value="{{ old('longitude', $arrondissement->longitude) }}" 
                                       step="0.00000001" min="-180" max="180" 
                                       placeholder="0.00000000">
                                @error('longitude')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Entre -180 et 180</small>
                            </div>
                        </div>

                        @if($arrondissement->latitude && $arrondissement->longitude)
                            <div class="alert alert-info">
                                <i class="mdi mdi-map-marker-check"></i>
                                <strong>Coordonnées actuelles :</strong> 
                                {{ $arrondissement->latitude }}, {{ $arrondissement->longitude }}
                                <a href="https://www.google.com/maps?q={{ $arrondissement->latitude }},{{ $arrondissement->longitude }}" 
                                   target="_blank" class="btn btn-sm btn-outline-info ms-2">
                                    <i class="mdi mdi-map"></i> Voir sur Google Maps
                                </a>
                            </div>
                        @endif
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
                                       value="{{ old('delegue', $arrondissement->delegue) }}" 
                                       placeholder="Nom du délégué">
                                @error('delegue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="text" name="telephone" id="telephone" 
                                       class="form-control @error('telephone') is-invalid @enderror" 
                                       value="{{ old('telephone', $arrondissement->telephone) }}" 
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
                                       value="{{ old('email', $arrondissement->email) }}" 
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
                                <i class="mdi mdi-content-save"></i> Mettre à jour
                            </button>
                            <a href="{{ route('admin.geolocalisation.arrondissements.show', $arrondissement) }}" class="btn btn-info">
                                <i class="mdi mdi-eye"></i> Voir les détails
                            </a>
                            <a href="{{ route('admin.geolocalisation.arrondissements.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Retour à la liste
                            </a>
                        </div>

                        <hr>

                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                                {{ old('is_active', $arrondissement->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Arrondissement actif
                            </label>
                        </div>

                    </div>
                </div>

                <!-- Statistiques actuelles -->
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
                                    <span class="h3 text-primary">{{ $arrondissement->countQuartiers() }}</span>
                                </div>
                                <small class="text-muted">Quartiers</small>
                            </div>
                            <div class="col-6">
                                <div class="mb-2">
                                    <span class="h3 text-success">{{ $arrondissement->countOrganisations() }}</span>
                                </div>
                                <small class="text-muted">Organisations</small>
                            </div>
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
                                   value="{{ old('services_publics', $arrondissement->services_publics_list) }}" 
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
                                   value="{{ old('equipements', $arrondissement->equipements_list) }}" 
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
                                <option value="1" {{ old('transport_public', $arrondissement->metadata['transport_public'] ?? '') === '1' ? 'selected' : '' }}>Disponible</option>
                                <option value="0" {{ old('transport_public', $arrondissement->metadata['transport_public'] ?? '') === '0' ? 'selected' : '' }}>Non disponible</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="niveau_securite" class="form-label">Niveau de sécurité</label>
                            <select name="niveau_securite" id="niveau_securite" class="form-select">
                                <option value="">-- Non évalué --</option>
                                <option value="très faible" {{ old('niveau_securite', $arrondissement->metadata['niveau_securite'] ?? '') === 'très faible' ? 'selected' : '' }}>Très faible</option>
                                <option value="faible" {{ old('niveau_securite', $arrondissement->metadata['niveau_securite'] ?? '') === 'faible' ? 'selected' : '' }}>Faible</option>
                                <option value="moyen" {{ old('niveau_securite', $arrondissement->metadata['niveau_securite'] ?? '') === 'moyen' ? 'selected' : '' }}>Moyen</option>
                                <option value="bon" {{ old('niveau_securite', $arrondissement->metadata['niveau_securite'] ?? '') === 'bon' ? 'selected' : '' }}>Bon</option>
                                <option value="excellent" {{ old('niveau_securite', $arrondissement->metadata['niveau_securite'] ?? '') === 'excellent' ? 'selected' : '' }}>Excellent</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="densite_population" class="form-label">Densité population (hab/km²)</label>
                            <input type="number" name="densite_population" id="densite_population" 
                                   class="form-control @error('densite_population') is-invalid @enderror" 
                                   value="{{ old('densite_population', $arrondissement->metadata['densite_population'] ?? '') }}" 
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
                                      placeholder="Informations complémentaires...">{{ old('autres_infos', $arrondissement->metadata['autres_infos'] ?? '') }}</textarea>
                            @error('autres_infos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Alertes de modification -->
                @if($arrondissement->countQuartiers() > 0 || $arrondissement->countOrganisations() > 0)
                    <div class="card border-warning">
                        <div class="card-header bg-soft-warning">
                            <h6 class="card-title text-warning mb-0">
                                <i class="mdi mdi-alert-triangle"></i> Attention
                            </h6>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">
                                <strong>Éléments liés :</strong><br>
                                @if($arrondissement->countQuartiers() > 0)
                                    • {{ $arrondissement->countQuartiers() }} quartier(s)<br>
                                @endif
                                @if($arrondissement->countOrganisations() > 0)
                                    • {{ $arrondissement->countOrganisations() }} organisation(s)<br>
                                @endif
                                <br>
                                Certaines modifications peuvent affecter les éléments liés.
                            </small>
                        </div>
                    </div>
                @endif

                <!-- Historique -->
                <div class="card border-light">
                    <div class="card-header bg-light">
                        <h6 class="card-title text-muted mb-0">
                            <i class="mdi mdi-clock-outline"></i> Historique
                        </h6>
                    </div>
                    <div class="card-body">
                        <small class="text-muted">
                            <strong>Créé le :</strong> {{ $arrondissement->created_at->format('d/m/Y à H:i') }}<br>
                            <strong>Modifié le :</strong> {{ $arrondissement->updated_at->format('d/m/Y à H:i') }}
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
    // Chargement en cascade : Département → Communes/Villes (pour modification)
    $('#departement_id').on('change', function() {
        const departementId = $(this).val();
        const $communeVille = $('#commune_ville_id');
        const currentCommuneVille = '{{ $arrondissement->commune_ville_id }}';
        
        if (departementId) {
            $communeVille.prop('disabled', true).html('<option value="">Chargement...</option>');
            
            $.ajax({
                url: `/admin/geolocalisation/communes/by-departement/${departementId}`,
                method: 'GET',
                success: function(data) {
                    let options = '<option value="">-- Sélectionnez une commune/ville --</option>';
                    
                    data.forEach(function(item) {
                        const typeLabel = item.type === 'ville' ? 'Ville' : 'Commune';
                        const selected = item.id == currentCommuneVille ? 'selected' : '';
                        options += `<option value="${item.id}" ${selected}>${item.nom} (${typeLabel})</option>`;
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
    });

    // Déclencher le filtrage au chargement si un département est sélectionné
    if ($('#departement_id').val()) {
        $('#departement_id').trigger('change');
    }

    // Auto-génération du code lors des modifications
    $('#nom, #numero_arrondissement, #commune_ville_id').on('input change', function() {
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
            
            // Ne pas écraser si l'utilisateur a modifié manuellement
            if (!$('#code').data('user-modified')) {
                $('#code').val(codeGenere);
            }
        }
    }

    // Marquer le code comme modifié par l'utilisateur
    $('#code').on('input', function() {
        $(this).data('user-modified', true);
    });

    // Calcul automatique de la densité de population
    $('#population_estimee, #superficie_km2').on('input', function() {
        const population = parseFloat($('#population_estimee').val()) || 0;
        const superficie = parseFloat($('#superficie_km2').val()) || 0;
        
        if (population > 0 && superficie > 0) {
            const densite = (population / superficie).toFixed(2);
            if (!$('#densite_population').data('user-modified')) {
                $('#densite_population').val(densite);
            }
        }
    });

    // Marquer la densité comme modifiée par l'utilisateur
    $('#densite_population').on('input', function() {
        $(this).data('user-modified', true);
    });

    // Validation du formulaire
    $('#arrondissement-form').on('submit', function(e) {
        let isValid = true;
        
        // Vérification des champs obligatoires
        const required = ['commune_ville_id', 'nom', 'numero_arrondissement', 'code'];
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

    // Confirmation des changements importants
    $('#commune_ville_id').on('change', function() {
        const originalValue = '{{ $arrondissement->commune_ville_id }}';
        if ($(this).val() !== originalValue && originalValue) {
            toastr.warning('Attention : Changer la commune/ville peut affecter les quartiers et organisations liés');
        }
    });

    $('#numero_arrondissement').on('change', function() {
        const originalValue = '{{ $arrondissement->numero_arrondissement }}';
        if ($(this).val() != originalValue && originalValue) {
            toastr.warning('Attention : Changer le numéro d\'arrondissement peut créer de la confusion');
        }
    });
});
</script>
@endpush