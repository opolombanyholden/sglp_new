@extends('layouts.admin')

@section('title', 'Détails Arrondissement - ' . $arrondissement->nom)

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
                        <li class="breadcrumb-item active">{{ $arrondissement->nom }}</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-office-building"></i> {{ $arrondissement->nom_complet }}
                    @if(!$arrondissement->is_active)
                        <span class="badge bg-danger ms-2">Inactif</span>
                    @endif
                </h4>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.geolocalisation.arrondissements.edit', $arrondissement) }}" class="btn btn-warning">
                    <i class="mdi mdi-pencil"></i> Modifier
                </a>
                <a href="{{ route('admin.geolocalisation.arrondissements.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Retour à la liste
                </a>
                @if($arrondissement->hasValidCoordinates())
                    <a href="https://www.google.com/maps?q={{ $arrondissement->latitude }},{{ $arrondissement->longitude }}" 
                       target="_blank" class="btn btn-info">
                        <i class="mdi mdi-map"></i> Voir sur la carte
                    </a>
                @endif
                <button type="button" class="btn btn-soft-danger delete-btn" 
                        data-id="{{ $arrondissement->id }}"
                        data-nom="{{ $arrondissement->nom }}"
                        data-can-delete="{{ $arrondissement->canBeDeleted() ? 'true' : 'false' }}"
                        data-blockers="{{ implode(', ', $arrondissement->deletion_blockers) }}">
                    <i class="mdi mdi-delete"></i> Supprimer
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <!-- Informations générales -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-information"></i> Informations générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="40%" class="text-muted">Nom :</th>
                                    <td><strong>{{ $arrondissement->nom }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Code :</th>
                                    <td><code>{{ $arrondissement->code }}</code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Numéro :</th>
                                    <td>
                                        @if($arrondissement->numero_arrondissement)
                                            <span class="badge bg-primary">
                                                {{ $arrondissement->numero_arrondissement === 1 ? '1er' : $arrondissement->numero_arrondissement . 'ème' }}
                                            </span>
                                        @else
                                            <span class="text-muted">Non défini</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Statut :</th>
                                    <td>
                                        @if($arrondissement->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="40%" class="text-muted">Commune/Ville :</th>
                                    <td>
                                        <a href="{{ route('admin.geolocalisation.communes.show', $arrondissement->communeVille) }}">
                                            {{ $arrondissement->communeVille->nom }}
                                        </a>
                                        <small class="text-muted d-block">{{ ucfirst($arrondissement->communeVille->type) }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Département :</th>
                                    <td>{{ $arrondissement->communeVille->departement->nom }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Province :</th>
                                    <td>{{ $arrondissement->communeVille->departement->province->nom }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Ordre affichage :</th>
                                    <td>{{ $arrondissement->ordre_affichage ?? 0 }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($arrondissement->description)
                        <hr>
                        <div>
                            <h6 class="text-muted">Description :</h6>
                            <p class="mb-0">{{ $arrondissement->description }}</p>
                        </div>
                    @endif

                    @if($arrondissement->limites_geographiques)
                        <hr>
                        <div>
                            <h6 class="text-muted">Limites géographiques :</h6>
                            <p class="mb-0">{{ $arrondissement->limites_geographiques }}</p>
                        </div>
                    @endif
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
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="40%" class="text-muted">Superficie :</th>
                                    <td>
                                        @if($arrondissement->superficie_km2)
                                            {{ number_format($arrondissement->superficie_km2, 2, ',', ' ') }} km²
                                        @else
                                            <span class="text-muted">Non renseignée</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Population :</th>
                                    <td>
                                        @if($arrondissement->population_estimee)
                                            {{ number_format($arrondissement->population_estimee, 0, ',', ' ') }} habitants
                                        @else
                                            <span class="text-muted">Non estimée</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($arrondissement->population_estimee && $arrondissement->superficie_km2)
                                    <tr>
                                        <th class="text-muted">Densité :</th>
                                        <td>
                                            {{ number_format($arrondissement->population_estimee / $arrondissement->superficie_km2, 2, ',', ' ') }} hab/km²
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="40%" class="text-muted">Latitude :</th>
                                    <td>
                                        @if($arrondissement->latitude)
                                            {{ $arrondissement->latitude }}°
                                        @else
                                            <span class="text-muted">Non définie</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Longitude :</th>
                                    <td>
                                        @if($arrondissement->longitude)
                                            {{ $arrondissement->longitude }}°
                                        @else
                                            <span class="text-muted">Non définie</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($arrondissement->hasValidCoordinates())
                                    <tr>
                                        <td colspan="2" class="pt-2">
                                            <a href="https://www.google.com/maps?q={{ $arrondissement->latitude }},{{ $arrondissement->longitude }}" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-map"></i> Voir sur Google Maps
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </table>
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
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="40%" class="text-muted">Délégué :</th>
                                    <td>
                                        @if($arrondissement->delegue)
                                            <strong>{{ $arrondissement->delegue }}</strong>
                                        @else
                                            <span class="text-muted">Non assigné</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Téléphone :</th>
                                    <td>
                                        @if($arrondissement->telephone)
                                            <a href="tel:{{ $arrondissement->telephone }}">{{ $arrondissement->telephone }}</a>
                                        @else
                                            <span class="text-muted">Non renseigné</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Email :</th>
                                    <td>
                                        @if($arrondissement->email)
                                            <a href="mailto:{{ $arrondissement->email }}">{{ $arrondissement->email }}</a>
                                        @else
                                            <span class="text-muted">Non renseigné</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Services et équipements -->
            @if($arrondissement->services_publics || $arrondissement->equipements)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-city-variant"></i> Services et Équipements
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @if($arrondissement->services_publics && count($arrondissement->services_publics) > 0)
                                <div class="col-md-6">
                                    <h6 class="text-muted">Services publics :</h6>
                                    <ul class="list-unstyled mb-3">
                                        @foreach($arrondissement->services_publics as $service)
                                            <li><i class="mdi mdi-check-circle text-success me-1"></i> {{ $service }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($arrondissement->equipements && count($arrondissement->equipements) > 0)
                                <div class="col-md-6">
                                    <h6 class="text-muted">Équipements :</h6>
                                    <ul class="list-unstyled mb-3">
                                        @foreach($arrondissement->equipements as $equipement)
                                            <li><i class="mdi mdi-home-city text-info me-1"></i> {{ $equipement }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quartiers/Localités -->
            @if($arrondissement->localites->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-home-group"></i> Quartiers ({{ $arrondissement->localites->count() }})
                            </h5>
                            <a href="#" class="btn btn-sm btn-primary">
                                <i class="mdi mdi-plus"></i> Ajouter un quartier
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Code</th>
                                        <th>Population</th>
                                        <th>Statut</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($arrondissement->localites as $localite)
                                        <tr>
                                            <td>
                                                <strong>{{ $localite->nom }}</strong>
                                                @if($localite->description)
                                                    <br><small class="text-muted">{{ Str::limit($localite->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td><code>{{ $localite->code }}</code></td>
                                            <td>
                                                @if($localite->population_estimee)
                                                    {{ number_format($localite->population_estimee, 0, ',', ' ') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($localite->is_active)
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="#" class="btn btn-soft-info" title="Voir">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-soft-warning" title="Modifier">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-home-group"></i> Quartiers
                        </h5>
                    </div>
                    <div class="card-body text-center py-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-soft-info text-info rounded-circle">
                                <i class="mdi mdi-home-group font-24"></i>
                            </div>
                        </div>
                        <h5>Aucun quartier</h5>
                        <p class="text-muted">Cet arrondissement ne contient encore aucun quartier.</p>
                        <a href="#" class="btn btn-primary">
                            <i class="mdi mdi-plus-circle"></i> Ajouter le premier quartier
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistiques -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-chart-pie"></i> Statistiques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h3 class="text-primary mb-1">{{ $stats['quartiers_count'] }}</h3>
                                <small class="text-muted">Quartiers</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h3 class="text-success mb-1">{{ $stats['organisations_count'] }}</h3>
                            <small class="text-muted">Organisations</small>
                        </div>
                        <div class="col-6">
                            <div class="border-end">
                                <h3 class="text-info mb-1">{{ $stats['adherents_count'] }}</h3>
                                <small class="text-muted">Adhérents</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h3 class="text-warning mb-1">
                                @if($arrondissement->population_estimee && $stats['quartiers_count'] > 0)
                                    {{ number_format($arrondissement->population_estimee / $stats['quartiers_count'], 0, ',', ' ') }}
                                @else
                                    -
                                @endif
                            </h3>
                            <small class="text-muted">Hab/Quartier</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Métadonnées -->
            @if($arrondissement->metadata && count($arrondissement->metadata) > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-information-outline"></i> Informations complémentaires
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(isset($arrondissement->metadata['transport_public']))
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Transport public :</span>
                                <span class="badge bg-{{ $arrondissement->metadata['transport_public'] ? 'success' : 'danger' }}">
                                    {{ $arrondissement->metadata['transport_public'] ? 'Disponible' : 'Non disponible' }}
                                </span>
                            </div>
                        @endif

                        @if(isset($arrondissement->metadata['niveau_securite']))
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Sécurité :</span>
                                <span class="badge bg-info">{{ ucfirst($arrondissement->metadata['niveau_securite']) }}</span>
                            </div>
                        @endif

                        @if(isset($arrondissement->metadata['densite_population']))
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Densité :</span>
                                <span>{{ number_format($arrondissement->metadata['densite_population'], 2, ',', ' ') }} hab/km²</span>
                            </div>
                        @endif

                        @if(isset($arrondissement->metadata['autres_infos']))
                            <hr>
                            <div>
                                <h6 class="text-muted">Notes :</h6>
                                <p class="small mb-0">{{ $arrondissement->metadata['autres_infos'] }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Historique -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-clock-outline"></i> Historique
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline-sm">
                        <div class="timeline-sm-item">
                            <div class="timeline-sm-marker bg-success"></div>
                            <div class="timeline-sm-content">
                                <p class="mb-0 text-muted">
                                    <strong>Créé</strong><br>
                                    {{ $arrondissement->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>
                        
                        @if($arrondissement->updated_at != $arrondissement->created_at)
                            <div class="timeline-sm-item">
                                <div class="timeline-sm-marker bg-info"></div>
                                <div class="timeline-sm-content">
                                    <p class="mb-0 text-muted">
                                        <strong>Dernière modification</strong><br>
                                        {{ $arrondissement->updated_at->format('d/m/Y à H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer l'arrondissement <strong id="delete-nom"></strong> ?</p>
                <div id="delete-blockers" class="alert alert-warning" style="display: none;">
                    <i class="mdi mdi-alert-triangle"></i> 
                    <strong>Impossible de supprimer :</strong>
                    <span id="delete-blockers-text"></span>
                </div>
                <p class="text-danger small" id="delete-warning">
                    <i class="mdi mdi-alert-triangle"></i> 
                    Cette action est irréversible et supprimera également tous les quartiers liés.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirm-delete-btn">
                        <i class="mdi mdi-delete"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Gestion de la suppression
    $('.delete-btn').click(function() {
        const id = $(this).data('id');
        const nom = $(this).data('nom');
        const canDelete = $(this).data('can-delete') === 'true';
        const blockers = $(this).data('blockers');
        
        $('#delete-nom').text(nom);
        $('#delete-form').attr('action', `/admin/arrondissements/${id}`);
        
        if (!canDelete && blockers) {
            $('#delete-blockers').show();
            $('#delete-blockers-text').text(blockers);
            $('#delete-warning').hide();
            $('#confirm-delete-btn').prop('disabled', true).addClass('disabled');
        } else {
            $('#delete-blockers').hide();
            $('#delete-warning').show();
            $('#confirm-delete-btn').prop('disabled', false).removeClass('disabled');
        }
        
        $('#deleteModal').modal('show');
    });
});
</script>
@endpush