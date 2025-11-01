@extends('layouts.admin')

@section('title', 'Détails Localité - ' . $localite->nom)

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
                        <li class="breadcrumb-item active">{{ $localite->nom }}</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    @if($localite->type === 'quartier')
                        <i class="mdi mdi-city"></i> Quartier {{ $localite->nom }}
                    @else
                        <i class="mdi mdi-tree"></i> Village {{ $localite->nom }}
                    @endif
                    
                    @if(!$localite->is_active)
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
                <a href="{{ route('admin.geolocalisation.localites.edit', $localite) }}" class="btn btn-warning">
                    <i class="mdi mdi-pencil"></i> Modifier
                </a>
                <a href="{{ route('admin.geolocalisation.localites.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Retour à la liste
                </a>
                <button type="button" class="btn btn-soft-danger delete-btn" 
                        data-id="{{ $localite->id }}"
                        data-nom="{{ $localite->nom }}">
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
                                    <td><strong>{{ $localite->nom }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Code :</th>
                                    <td>
                                        @if($localite->code)
                                            <code>{{ $localite->code }}</code>
                                        @else
                                            <span class="text-muted">Non défini</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Type :</th>
                                    <td>
                                        @if($localite->type === 'quartier')
                                            <span class="badge bg-primary">Quartier urbain</span>
                                        @else
                                            <span class="badge bg-success">Village rural</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Statut :</th>
                                    <td>
                                        @if($localite->is_active)
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
                                    <th width="40%" class="text-muted">Population :</th>
                                    <td>
                                        @if($localite->population_estimee)
                                            {{ number_format($localite->population_estimee) }} habitants
                                        @else
                                            <span class="text-muted">Non définie</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Latitude :</th>
                                    <td>
                                        @if($localite->latitude)
                                            {{ $localite->latitude }}°
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Longitude :</th>
                                    <td>
                                        @if($localite->longitude)
                                            {{ $localite->longitude }}°
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Ordre :</th>
                                    <td>{{ $localite->ordre_affichage }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($localite->description)
                        <hr>
                        <div>
                            <h6 class="text-muted">Description :</h6>
                            <p class="mb-0">{{ $localite->description }}</p>
                        </div>
                    @endif

                    @if($localite->latitude && $localite->longitude)
                        <hr>
                        <div>
                            <h6 class="text-muted">Localisation GPS :</h6>
                            <a href="https://www.google.com/maps?q={{ $localite->latitude }},{{ $localite->longitude }}" 
                               target="_blank" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="mdi mdi-map-marker"></i> Voir sur Google Maps
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Hiérarchie géographique -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-map-marker-path"></i> Hiérarchie géographique
                    </h5>
                </div>
                <div class="card-body">
                    <nav aria-label="Hiérarchie géographique">
                        <ol class="breadcrumb bg-light mb-0">
                            @if($localite->type === 'quartier' && $localite->arrondissement)
                                <li class="breadcrumb-item">{{ $localite->arrondissement->communeVille->departement->province->nom }}</li>
                                <li class="breadcrumb-item">{{ $localite->arrondissement->communeVille->departement->nom }}</li>
                                <li class="breadcrumb-item">{{ $localite->arrondissement->communeVille->nom }}</li>
                                <li class="breadcrumb-item">{{ $localite->arrondissement->nom }}</li>
                                <li class="breadcrumb-item active">{{ $localite->nom }}</li>
                            @elseif($localite->type === 'village' && $localite->regroupement)
                                <li class="breadcrumb-item">{{ $localite->regroupement->canton->departement->province->nom }}</li>
                                <li class="breadcrumb-item">{{ $localite->regroupement->canton->departement->nom }}</li>
                                <li class="breadcrumb-item">{{ $localite->regroupement->canton->nom }}</li>
                                <li class="breadcrumb-item">{{ $localite->regroupement->nom }}</li>
                                <li class="breadcrumb-item active">{{ $localite->nom }}</li>
                            @else
                                <li class="breadcrumb-item active">{{ $localite->nom }}</li>
                            @endif
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
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
                                    {{ $localite->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>
                        
                        @if($localite->updated_at != $localite->created_at)
                            <div class="timeline-sm-item">
                                <div class="timeline-sm-marker bg-info"></div>
                                <div class="timeline-sm-content">
                                    <p class="mb-0 text-muted">
                                        <strong>Dernière modification</strong><br>
                                        {{ $localite->updated_at->format('d/m/Y à H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-lightning-bolt"></i> Actions rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.geolocalisation.localites.edit', $localite) }}" class="btn btn-warning btn-sm">
                            <i class="mdi mdi-pencil"></i> Modifier
                        </a>
                        
                        @if($localite->type === 'quartier')
                            <a href="{{ route('admin.geolocalisation.localites.create', ['type' => 'quartier']) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="mdi mdi-plus-circle"></i> Créer un autre quartier
                            </a>
                        @else
                            <a href="{{ route('admin.geolocalisation.localites.create', ['type' => 'village']) }}" 
                               class="btn btn-outline-success btn-sm">
                                <i class="mdi mdi-plus-circle"></i> Créer un autre village
                            </a>
                        @endif
                        
                        <a href="{{ route('admin.geolocalisation.localites.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Retour à la liste
                        </a>
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
                <p>Êtes-vous sûr de vouloir supprimer la localité <strong id="delete-nom"></strong> ?</p>
                <p class="text-danger small">
                    <i class="mdi mdi-alert-triangle"></i> 
                    Cette action est irréversible.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="mdi mdi-delete"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
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

.btn-soft-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.btn-soft-danger:hover {
    background-color: #f1b0b7;
    color: #721c24;
}

.timeline-sm {
    position: relative;
    padding-left: 30px;
}

.timeline-sm-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-sm-marker {
    position: absolute;
    left: -30px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-sm-marker.bg-success {
    background-color: #38a169;
}

.timeline-sm-marker.bg-info {
    background-color: #4299e1;
}

.table-borderless td,
.table-borderless th {
    border: none;
    padding: 0.5rem 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la suppression
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const nom = this.dataset.nom;
            
            document.getElementById('delete-nom').textContent = nom;
            document.getElementById('delete-form').action = `/admin/localites/${id}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
});
</script>
@endpush