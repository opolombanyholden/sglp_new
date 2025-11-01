@extends('layouts.admin')

@section('title', 'Gestion des Localités')

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
                        <li class="breadcrumb-item active">Localités</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-map-marker"></i> Localités
                    <small class="text-muted">({{ $localites->total() }} résultats)</small>
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
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.geolocalisation.localites.create', ['type' => 'quartier']) }}" class="btn btn-primary">
                                <i class="mdi mdi-plus-circle"></i> Nouveau Quartier
                            </a>
                            <a href="{{ route('admin.geolocalisation.localites.create', ['type' => 'village']) }}" class="btn btn-success">
                                <i class="mdi mdi-plus-circle"></i> Nouveau Village
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.geolocalisation.localites.index') }}" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">-- Tous --</option>
                                <option value="quartier" {{ request('type') === 'quartier' ? 'selected' : '' }}>Quartiers</option>
                                <option value="village" {{ request('type') === 'village' ? 'selected' : '' }}>Villages</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Arrondissement</label>
                            <select name="arrondissement_id" class="form-select">
                                <option value="">-- Tous --</option>
                                @foreach($arrondissements as $arrondissement)
                                    <option value="{{ $arrondissement->id }}" 
                                        {{ request('arrondissement_id') == $arrondissement->id ? 'selected' : '' }}>
                                        {{ $arrondissement->nom }} ({{ $arrondissement->communeVille->nom }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Regroupement</label>
                            <select name="regroupement_id" class="form-select">
                                <option value="">-- Tous --</option>
                                @foreach($regroupements as $regroupement)
                                    <option value="{{ $regroupement->id }}" 
                                        {{ request('regroupement_id') == $regroupement->id ? 'selected' : '' }}>
                                        {{ $regroupement->nom }} ({{ $regroupement->canton->nom }})
                                    </option>
                                @endforeach
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

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-info d-block w-100">
                                <i class="mdi mdi-magnify"></i> Rechercher
                            </button>
                        </div>

                        <div class="col-md-10">
                            <label class="form-label">Recherche</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Nom, code ou description..." 
                                   value="{{ request('search') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            @if(request()->hasAny(['type', 'arrondissement_id', 'regroupement_id', 'is_active', 'search']))
                                <a href="{{ route('admin.geolocalisation.localites.index') }}" class="btn btn-secondary d-block w-100">
                                    <i class="mdi mdi-filter-remove"></i> Réinitialiser
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Localités -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-view-list"></i> Liste des Localités
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($localites->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Code</th>
                                        <th>Type</th>
                                        <th>Localisation</th>
                                        <th>Population</th>
                                        <th class="text-center">Statut</th>
                                        <th width="120" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($localites as $localite)
                                        <tr>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0">{{ $localite->nom }}</h6>
                                                    @if($localite->description)
                                                        <small class="text-muted">{{ Str::limit($localite->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if($localite->code)
                                                    <code>{{ $localite->code }}</code>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($localite->type === 'quartier')
                                                    <span class="badge bg-primary">
                                                        <i class="mdi mdi-city"></i> Quartier
                                                    </span>
                                                @else
                                                    <span class="badge bg-success">
                                                        <i class="mdi mdi-tree"></i> Village
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    @if($localite->arrondissement)
                                                        {{ $localite->arrondissement->nom }}<br>
                                                        {{ $localite->arrondissement->communeVille->nom }}
                                                    @elseif($localite->regroupement)
                                                        {{ $localite->regroupement->nom }}<br>
                                                        {{ $localite->regroupement->canton->nom }}
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                @if($localite->population_estimee)
                                                    {{ number_format($localite->population_estimee) }} hab.
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input status-toggle" 
                                                           type="checkbox" 
                                                           data-id="{{ $localite->id }}"
                                                           {{ $localite->is_active ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.geolocalisation.localites.show', $localite) }}" 
                                                       class="btn btn-outline-info" 
                                                       title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.geolocalisation.localites.edit', $localite) }}" 
                                                       class="btn btn-outline-primary" 
                                                       title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-btn" 
                                                            data-id="{{ $localite->id }}"
                                                            data-nom="{{ $localite->nom }}"
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
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
                                    Affichage de {{ $localites->firstItem() ?? 0 }} à {{ $localites->lastItem() ?? 0 }} 
                                    sur {{ $localites->total() }} résultats
                                </div>
                                {{ $localites->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-soft-primary text-primary rounded-circle">
                                    <i class="mdi mdi-map-marker font-24"></i>
                                </div>
                            </div>
                            <h5>Aucune localité trouvée</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['type', 'arrondissement_id', 'regroupement_id', 'is_active', 'search']))
                                    Aucun résultat ne correspond à vos critères de recherche.
                                @else
                                    Commencez par ajouter votre première localité.
                                @endif
                            </p>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.geolocalisation.localites.create', ['type' => 'quartier']) }}" class="btn btn-primary">
                                    <i class="mdi mdi-plus-circle"></i> Ajouter un Quartier
                                </a>
                                <a href="{{ route('admin.geolocalisation.localites.create', ['type' => 'village']) }}" class="btn btn-success">
                                    <i class="mdi mdi-plus-circle"></i> Ajouter un Village
                                </a>
                            </div>
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

.avatar-lg {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-soft-primary {
    background-color: #e3f2fd;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion du changement de statut
    document.querySelectorAll('.status-toggle').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const id = this.dataset.id;
            const isChecked = this.checked;
            const toggleElement = this;
            
            fetch(`/admin/geolocalisation/localites/${id}/toggle-status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    toggleElement.checked = !isChecked;
                    alert('Erreur lors du changement de statut');
                }
            })
            .catch(error => {
                toggleElement.checked = !isChecked;
                alert('Erreur lors du changement de statut');
            });
        });
    });

    // Gestion de la suppression
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const nom = this.dataset.nom;
            
            document.getElementById('delete-nom').textContent = nom;
            document.getElementById('delete-form').action = `/admin/geolocalisation/localites/${id}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
});
</script>
@endpush