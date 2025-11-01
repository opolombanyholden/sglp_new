@extends('layouts.admin')

@section('title', 'Gestion des Cantons')

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
                        <li class="breadcrumb-item active">Cantons</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-pine-tree"></i> Cantons
                    <small class="text-muted">({{ $cantons->total() }} résultats)</small>
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
                        <a href="{{ route('admin.geolocalisation.cantons.create') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus-circle"></i> Nouveau Canton
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.geolocalisation.cantons.index') }}" class="row g-3">
                        <div class="col-md-4">
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

                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select name="is_active" class="form-select">
                                <option value="">-- Tous --</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Recherche</label>
                            <div class="input-group">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Nom ou code du canton..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-info">
                                    <i class="mdi mdi-magnify"></i> Rechercher
                                </button>
                            </div>
                        </div>
                    </form>

                    @if(request()->hasAny(['departement_id', 'is_active', 'search']))
                        <div class="mt-2">
                            <a href="{{ route('admin.geolocalisation.cantons.index') }}" class="btn btn-link text-muted p-0">
                                <i class="mdi mdi-filter-remove"></i> Effacer les filtres
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des Cantons -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-view-list"></i> Liste des Cantons
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($cantons->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Canton</th>
                                        <th>Code</th>
                                        <th>Département</th>
                                        <th>Province</th>
                                        <th class="text-center">Regroupements</th>
                                        <th class="text-center">Statut</th>
                                        <th width="120" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cantons as $canton)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-soft-success rounded me-2">
                                                        <i class="mdi mdi-pine-tree font-18 text-success"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $canton->nom }}</h6>
                                                        @if($canton->description)
                                                            <small class="text-muted">{{ Str::limit($canton->description, 50) }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <code>{{ $canton->code }}</code>
                                            </td>
                                            <td>{{ $canton->departement->nom }}</td>
                                            <td>{{ $canton->departement->province->nom }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-soft-success text-success">
                                                    {{ $canton->countRegroupements() }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input status-toggle" 
                                                           type="checkbox" 
                                                           data-id="{{ $canton->id }}"
                                                           {{ $canton->is_active ? 'checked' : '' }}>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.geolocalisation.cantons.show', $canton) }}" 
                                                       class="btn btn-outline-info" 
                                                       title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.geolocalisation.cantons.edit', $canton) }}" 
                                                       class="btn btn-outline-primary" 
                                                       title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-btn" 
                                                            data-id="{{ $canton->id }}"
                                                            data-nom="{{ $canton->nom }}"
                                                            data-can-delete="{{ $canton->canBeDeleted() ? 'true' : 'false' }}"
                                                            data-blockers="{{ implode(', ', $canton->deletion_blockers) }}"
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
                                    Affichage de {{ $cantons->firstItem() ?? 0 }} à {{ $cantons->lastItem() ?? 0 }} 
                                    sur {{ $cantons->total() }} résultats
                                </div>
                                {{ $cantons->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar-lg mx-auto mb-3">
                                <div class="avatar-title bg-soft-success text-success rounded-circle">
                                    <i class="mdi mdi-pine-tree font-24"></i>
                                </div>
                            </div>
                            <h5>Aucun canton trouvé</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['departement_id', 'is_active', 'search']))
                                    Aucun résultat ne correspond à vos critères de recherche.
                                @else
                                    Commencez par ajouter votre premier canton.
                                @endif
                            </p>
                            <a href="{{ route('admin.geolocalisation.cantons.create') }}" class="btn btn-primary">
                                <i class="mdi mdi-plus-circle"></i> Ajouter un Canton
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
                <p>Êtes-vous sûr de vouloir supprimer le canton <strong id="delete-nom"></strong> ?</p>
                <div id="delete-blockers" class="alert alert-warning" style="display: none;">
                    <i class="mdi mdi-alert-triangle"></i> 
                    <strong>Impossible de supprimer :</strong>
                    <span id="delete-blockers-text"></span>
                </div>
                <p class="text-danger small" id="delete-warning">
                    <i class="mdi mdi-alert-triangle"></i> 
                    Cette action est irréversible et supprimera également tous les regroupements et villages liés.
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

.avatar-sm {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-soft-success {
    background-color: #d4edda;
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
            
            fetch(`/admin/geolocalisation/cantons/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Afficher un message de succès
                    showToast('Succès', data.message, 'success');
                } else {
                    // Rétablir l'ancien état en cas d'erreur
                    toggleElement.checked = !isChecked;
                    showToast('Erreur', 'Erreur lors du changement de statut', 'error');
                }
            })
            .catch(error => {
                // Rétablir l'ancien état en cas d'erreur
                toggleElement.checked = !isChecked;
                showToast('Erreur', 'Erreur lors du changement de statut', 'error');
            });
        });
    });

    // Gestion de la suppression
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const nom = this.dataset.nom;
            const canDelete = this.dataset.canDelete === 'true';
            const blockers = this.dataset.blockers;
            
            document.getElementById('delete-nom').textContent = nom;
            document.getElementById('delete-form').action = `/admin/geolocalisation/cantons/${id}`;
            
            if (!canDelete && blockers) {
                document.getElementById('delete-blockers').style.display = 'block';
                document.getElementById('delete-blockers-text').textContent = blockers;
                document.getElementById('delete-warning').style.display = 'none';
                document.getElementById('confirm-delete-btn').disabled = true;
                document.getElementById('confirm-delete-btn').classList.add('disabled');
            } else {
                document.getElementById('delete-blockers').style.display = 'none';
                document.getElementById('delete-warning').style.display = 'block';
                document.getElementById('confirm-delete-btn').disabled = false;
                document.getElementById('confirm-delete-btn').classList.remove('disabled');
            }
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });

    // Soumission automatique lors des changements de filtre
    document.querySelectorAll('select[name="departement_id"], select[name="is_active"]').forEach(function(select) {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });

    // Fonction utilitaire pour les toasts
    function showToast(title, message, type = 'info') {
        // Vous pouvez utiliser votre système de notification préféré
        alert(`${title}: ${message}`);
    }
});
</script>
@endpush