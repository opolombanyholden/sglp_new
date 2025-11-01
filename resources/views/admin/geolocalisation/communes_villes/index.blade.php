@extends('layouts.admin')

@section('title', 'Gestion des Communes et Villes')

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
                        <li class="breadcrumb-item active">Communes & Villes</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-city"></i> Communes et Villes
                    <small class="text-muted">({{ $communesVilles->total() }} résultats)</small>
                </h4>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="mdi mdi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="mdi mdi-alert-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Actions et Filtres -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-filter-variant"></i> Filtres et Actions
                        </h5>
                        <a href="{{ route('admin.geolocalisation.communes.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus-circle"></i> Nouvelle Commune/Ville
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.geolocalisation.communes.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Département</label>
                            <select name="departement_id" class="form-select">
                                <option value="">-- Tous les départements --</option>
                                @foreach($departements as $departement)
                                    <option value="{{ $departement->id }}" 
                                        {{ request('departement_id') == $departement->id ? 'selected' : '' }}>
                                        {{ $departement->nom }} ({{ $departement->province->nom }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">-- Tous --</option>
                                <option value="commune" {{ request('type') == 'commune' ? 'selected' : '' }}>Commune</option>
                                <option value="ville" {{ request('type') == 'ville' ? 'selected' : '' }}>Ville</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Statut</label>
                            <select name="is_active" class="form-select">
                                <option value="">-- Tous --</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Recherche</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Nom, code, maire..." 
                                   value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-info">
                                    <i class="mdi mdi-magnify"></i> Filtrer
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(request()->hasAny(['departement_id', 'type', 'is_active', 'search']))
                        <div class="mt-2">
                            <a href="{{ route('admin.communes-villes.index') }}" class="btn btn-link text-muted p-0">
                                <i class="mdi mdi-filter-remove"></i> Effacer les filtres
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Communes/Villes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-view-list"></i> Liste des Communes et Villes
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($communesVilles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Type</th>
                                        <th>Code</th>
                                        <th>Département</th>
                                        <th>Province</th>
                                        <th>Maire</th>
                                        <th>Population</th>
                                        <th>Statut</th>
                                        <th width="120">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($communesVilles as $communeVille)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-soft-primary rounded me-2">
                                                        <i class="mdi {{ $communeVille->type == 'ville' ? 'mdi-city' : 'mdi-home-group' }} font-18 text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $communeVille->nom }}</h6>
                                                        @if($communeVille->description)
                                                            <small class="text-muted">{{ Str::limit($communeVille->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $communeVille->type == 'ville' ? 'primary' : 'secondary' }}">
                                                    {{ ucfirst($communeVille->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <code>{{ $communeVille->code }}</code>
                                            </td>
                                            <td>{{ $communeVille->departement->nom }}</td>
                                            <td>{{ $communeVille->departement->province->nom }}</td>
                                            <td>
                                                @if($communeVille->maire)
                                                    {{ $communeVille->maire }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($communeVille->population_estimee)
                                                    {{ number_format($communeVille->population_estimee, 0, ',', ' ') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle" 
                                                           type="checkbox" 
                                                           data-id="{{ $communeVille->id }}"
                                                           {{ $communeVille->is_active ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.geolocalisation.communes.show', $communeVille) }}" 
                                                       class="btn btn-soft-info" 
                                                       title="Voir les détails">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.geolocalisation.communes.edit', $communeVille) }}" 
                                                       class="btn btn-soft-warning" 
                                                       title="Modifier">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-soft-danger delete-btn" 
                                                            data-id="{{ $communeVille->id }}"
                                                            data-nom="{{ $communeVille->nom }}"
                                                            title="Supprimer">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Affichage de {{ $communesVilles->firstItem() ?? 0 }} à {{ $communesVilles->lastItem() ?? 0 }} 
                                    sur {{ $communesVilles->total() }} résultats
                                </div>
                                {{ $communesVilles->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-soft-info text-info rounded-circle">
                                    <i class="mdi mdi-city-variant font-24"></i>
                                </div>
                            </div>
                            <h5>Aucune commune/ville trouvée</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['departement_id', 'type', 'is_active', 'search']))
                                    Aucun résultat ne correspond à vos critères de recherche.
                                @else
                                    Commencez par ajouter votre première commune ou ville.
                                @endif
                            </p>
                            <a href="{{ route('admin.geolocalisation.communes.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus-circle"></i> Ajouter une Commune/Ville
                            </a>
                        </div>
                    @endif
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
                <p>Êtes-vous sûr de vouloir supprimer la commune/ville <strong id="delete-nom"></strong> ?</p>
                <p class="text-danger small">
                    <i class="mdi mdi-alert-triangle"></i> 
                    Cette action est irréversible et supprimera également tous les arrondissements liés.
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

@push('scripts')
<script>
$(document).ready(function() {
    // Gestion du changement de statut
    $('.status-toggle').change(function() {
        const id = $(this).data('id');
        const isChecked = $(this).is(':checked');
        
        $.ajax({
            url: `/admin/communes-villes/${id}/toggle-status`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                }
            },
            error: function() {
                // Rétablir l'ancien état en cas d'erreur
                $(this).prop('checked', !isChecked);
                toastr.error('Erreur lors du changement de statut');
            }
        });
    });

    // Gestion de la suppression
    $('.delete-btn').click(function() {
        const id = $(this).data('id');
        const nom = $(this).data('nom');
        
        $('#delete-nom').text(nom);
        $('#delete-form').attr('action', `/admin/communes-villes/${id}`);
        $('#deleteModal').modal('show');
    });
});
</script>
@endpush