@extends('layouts.admin')

@section('title', 'Détails - ' . $communeVille->nom)

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
                        <li class="breadcrumb-item active">{{ $communeVille->nom }}</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-{{ $communeVille->type == 'ville' ? 'city' : 'home-group' }}"></i> 
                    {{ $communeVille->nom_complet }}
                </h4>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-{{ $communeVille->type == 'ville' ? 'primary' : 'secondary' }} me-2">
                                {{ ucfirst($communeVille->type) }}
                            </span>
                            <span class="badge bg-{{ $communeVille->is_active ? 'success' : 'danger' }}">
                                {{ $communeVille->statut_affichage }}
                            </span>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('admin.geolocalisation.communes.edit', $communeVille) }}" class="btn btn-warning">
                                <i class="mdi mdi-pencil"></i> Modifier
                            </a>
                            <a href="{{ route('admin.geolocalisation.communes.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <!-- Informations générales -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-information"></i> Informations générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                <tr>
                                    <th width="200">Nom complet :</th>
                                    <td>{{ $communeVille->nom_complet }}</td>
                                </tr>
                                <tr>
                                    <th>Code :</th>
                                    <td><code>{{ $communeVille->code }}</code></td>
                                </tr>
                                <tr>
                                    <th>Type :</th>
                                    <td>
                                        <span class="badge bg-{{ $communeVille->type == 'ville' ? 'primary' : 'secondary' }}">
                                            {{ ucfirst($communeVille->type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Département :</th>
                                    <td>
                                        <a href="{{ route('admin.geolocalisation.departements.show', $communeVille->departement) }}">
                                            {{ $communeVille->departement->nom }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Province :</th>
                                    <td>
                                        <a href="{{ route('admin.geolocalisation.provinces.show', $communeVille->departement->province) }}">
                                            {{ $communeVille->departement->province->nom }}
                                        </a>
                                    </td>
                                </tr>
                                @if($communeVille->statut)
                                <tr>
                                    <th>Statut administratif :</th>
                                    <td>{{ $communeVille->statut }}</td>
                                </tr>
                                @endif
                                @if($communeVille->description)
                                <tr>
                                    <th>Description :</th>
                                    <td>{{ $communeVille->description }}</td>
                                </tr>
                                @endif
                                @if($communeVille->date_creation)
                                <tr>
                                    <th>Date de création :</th>
                                    <td>{{ $communeVille->date_creation->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
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
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Superficie</label>
                                <div class="h5">
                                    @if($communeVille->superficie_km2)
                                        {{ number_format($communeVille->superficie_km2, 2, ',', ' ') }} km²
                                    @else
                                        <span class="text-muted">Non renseignée</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Population estimée</label>
                                <div class="h5">
                                    @if($communeVille->population_estimee)
                                        {{ number_format($communeVille->population_estimee, 0, ',', ' ') }} hab.
                                    @else
                                        <span class="text-muted">Non renseignée</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($communeVille->hasValidCoordinates())
                    <div class="alert alert-info mt-3">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-map-marker-radius font-20 me-2"></i>
                            <div class="flex-grow-1">
                                <strong>Coordonnées GPS :</strong><br>
                                Latitude : {{ $communeVille->latitude }} | Longitude : {{ $communeVille->longitude }}
                            </div>
                            <a href="https://www.google.com/maps?q={{ $communeVille->latitude }},{{ $communeVille->longitude }}" 
                               target="_blank" 
                               class="btn btn-sm btn-outline-info">
                                <i class="mdi mdi-map"></i> Voir sur Google Maps
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations administratives -->
            @if($communeVille->maire || $communeVille->telephone || $communeVille->email || $communeVille->site_web)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-account-tie"></i> Informations administratives
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <tbody>
                                @if($communeVille->maire)
                                <tr>
                                    <th width="200">Maire :</th>
                                    <td>{{ $communeVille->maire }}</td>
                                </tr>
                                @endif
                                @if($communeVille->telephone)
                                <tr>
                                    <th>Téléphone :</th>
                                    <td>
                                        <i class="mdi mdi-phone"></i> 
                                        <a href="tel:{{ $communeVille->telephone }}">{{ $communeVille->telephone }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($communeVille->email)
                                <tr>
                                    <th>Email :</th>
                                    <td>
                                        <i class="mdi mdi-email"></i> 
                                        <a href="mailto:{{ $communeVille->email }}">{{ $communeVille->email }}</a>
                                    </td>
                                </tr>
                                @endif
                                @if($communeVille->site_web)
                                <tr>
                                    <th>Site web :</th>
                                    <td>
                                        <i class="mdi mdi-web"></i> 
                                        <a href="{{ $communeVille->site_web }}" target="_blank">{{ $communeVille->site_web }}</a>
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Liste des arrondissements -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-view-grid"></i> Arrondissements
                            <span class="badge bg-primary ms-2">{{ $communeVille->arrondissements->count() }}</span>
                        </h5>
                        <a href="{{ route('admin.geolocalisation.arrondissements.create', ['commune_ville_id' => $communeVille->id]) }}" 
                           class="btn btn-sm btn-primary">
                            <i class="mdi mdi-plus"></i> Ajouter
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($communeVille->arrondissements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Code</th>
                                        <th>Statut</th>
                                        <th width="100">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($communeVille->arrondissements as $arrondissement)
                                    <tr>
                                        <td>
                                            <i class="mdi mdi-view-grid text-muted me-1"></i>
                                            {{ $arrondissement->nom }}
                                        </td>
                                        <td><code>{{ $arrondissement->code }}</code></td>
                                        <td>
                                            <span class="badge bg-{{ $arrondissement->is_active ? 'success' : 'danger' }}">
                                                {{ $arrondissement->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.geolocalisation.arrondissements.show', $arrondissement) }}" 
                                                   class="btn btn-soft-info" title="Voir">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.geolocalisation.arrondissements.edit', $arrondissement) }}" 
                                                   class="btn btn-soft-warning" title="Modifier">
                                                    <i class="mdi mdi-pencil"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="avatar-md mx-auto mb-3">
                                <div class="avatar-title bg-soft-info text-info rounded-circle">
                                    <i class="mdi mdi-view-grid font-20"></i>
                                </div>
                            </div>
                            <p class="text-muted">Aucun arrondissement pour le moment</p>
                            <a href="{{ route('admin.geolocalisation.arrondissements.create', ['commune_ville_id' => $communeVille->id]) }}" 
                               class="btn btn-sm btn-primary">
                                <i class="mdi mdi-plus"></i> Ajouter le premier arrondissement
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistiques -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-chart-bar"></i> Statistiques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="mb-2">
                                <i class="mdi mdi-view-grid text-primary font-24"></i>
                            </div>
                            <h4 class="mb-0">{{ $stats['arrondissements_count'] }}</h4>
                            <small class="text-muted">Arrondissements</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="mb-2">
                                <i class="mdi mdi-office-building text-success font-24"></i>
                            </div>
                            <h4 class="mb-0">{{ $stats['organisations_count'] }}</h4>
                            <small class="text-muted">Organisations</small>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <i class="mdi mdi-account-group text-info font-24"></i>
                            </div>
                            <h4 class="mb-0">{{ $stats['adherents_count'] }}</h4>
                            <small class="text-muted">Adhérents</small>
                        </div>
                        <div class="col-6">
                            <div class="mb-2">
                                <i class="mdi mdi-shape text-warning font-24"></i>
                            </div>
                            <h4 class="mb-0">{{ $communeVille->ordre_affichage }}</h4>
                            <small class="text-muted">Ordre d'affichage</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Métadonnées -->
            @if($communeVille->metadata)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-information-outline"></i> Informations complémentaires
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($communeVille->metadata['services_publics']) && count($communeVille->metadata['services_publics']) > 0)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Services publics</h6>
                        @foreach($communeVille->metadata['services_publics'] as $service)
                            <span class="badge bg-soft-primary text-primary me-1 mb-1">{{ trim($service) }}</span>
                        @endforeach
                    </div>
                    @endif

                    @if(isset($communeVille->metadata['equipements']) && count($communeVille->metadata['equipements']) > 0)
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Équipements</h6>
                        @foreach($communeVille->metadata['equipements'] as $equipement)
                            <span class="badge bg-soft-success text-success me-1 mb-1">{{ trim($equipement) }}</span>
                        @endforeach
                    </div>
                    @endif

                    @if(isset($communeVille->metadata['autres_infos']))
                    <div>
                        <h6 class="text-muted mb-2">Autres informations</h6>
                        <p class="mb-0">{{ $communeVille->metadata['autres_infos'] }}</p>
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
                    <div class="mb-2">
                        <small class="text-muted d-block">Créé le</small>
                        <strong>{{ $communeVille->created_at->format('d/m/Y à H:i') }}</strong>
                    </div>
                    <div>
                        <small class="text-muted d-block">Dernière modification</small>
                        <strong>{{ $communeVille->updated_at->format('d/m/Y à H:i') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Actions supplémentaires -->
            <div class="card border-danger">
                <div class="card-header bg-soft-danger">
                    <h6 class="card-title text-danger mb-0">
                        <i class="mdi mdi-alert-circle"></i> Zone de danger
                    </h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        La suppression de cette commune/ville entraînera la suppression de tous les arrondissements associés.
                    </p>
                    <button type="button" class="btn btn-danger btn-sm w-100" 
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteModal">
                        <i class="mdi mdi-delete"></i> Supprimer cette commune/ville
                    </button>
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
                <p>Êtes-vous sûr de vouloir supprimer la commune/ville <strong>{{ $communeVille->nom }}</strong> ?</p>
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-triangle me-1"></i>
                    <strong>Attention :</strong> Cette action est irréversible et supprimera :
                    <ul class="mb-0 mt-2">
                        <li>{{ $stats['arrondissements_count'] }} arrondissement(s)</li>
                        <li>Toutes les données associées</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('admin.geolocalisation.communes.destroy', $communeVille) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="mdi mdi-delete"></i> Confirmer la suppression
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection