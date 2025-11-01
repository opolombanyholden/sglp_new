{{-- resources/views/admin/provinces/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Gestion des Provinces')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-map-marked-alt me-2"></i>
                    Provinces du Gabon
                </h1>
                <a href="{{ route('admin.geolocalisation.provinces.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouvelle Province
                </a>
            </div>

            {{-- Messages flash --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Filtres et recherche --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres et Recherche
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.geolocalisation.provinces.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Recherche</label>
                            <input type="text" name="recherche" class="form-control" 
                                   placeholder="Nom, code, chef-lieu..." 
                                   value="{{ request('recherche') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Statut</label>
                            <select name="statut" class="form-select">
                                <option value="">Tous</option>
                                <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Trier par</label>
                            <select name="sort" class="form-select">
                                <option value="ordre_affichage" {{ request('sort') === 'ordre_affichage' ? 'selected' : '' }}>Ordre d'affichage</option>
                                <option value="nom" {{ request('sort') === 'nom' ? 'selected' : '' }}>Nom</option>
                                <option value="code" {{ request('sort') === 'code' ? 'selected' : '' }}>Code</option>
                                <option value="population_estimee" {{ request('sort') === 'population_estimee' ? 'selected' : '' }}>Population</option>
                                <option value="superficie_km2" {{ request('sort') === 'superficie_km2' ? 'selected' : '' }}>Superficie</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Actions groupées --}}
            @if($provinces->count() > 0)
            <div class="card mb-4">
                <div class="card-body">
                    <form id="bulk-action-form" method="POST" action="{{ route('admin.geolocalisation.provinces.bulk-action') }}">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label">Action groupée</label>
                                <select name="action" class="form-select" required>
                                    <option value="">Choisir une action...</option>
                                    <option value="activate">Activer</option>
                                    <option value="deactivate">Désactiver</option>
                                    <option value="delete">Supprimer</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-warning" disabled id="bulk-action-btn">
                                    <i class="fas fa-cogs me-2"></i>Exécuter
                                </button>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="{{ route('admin.geolocalisation.provinces.export', ['format' => 'csv']) }}" 
                                   class="btn btn-outline-success me-2">
                                    <i class="fas fa-file-csv me-2"></i>Exporter CSV
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Liste des provinces --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Liste des Provinces ({{ $provinces->total() }})
                    </h5>
                    <small class="text-muted">
                        Page {{ $provinces->currentPage() }} sur {{ $provinces->lastPage() }}
                    </small>
                </div>
                <div class="card-body p-0">
                    @if($provinces->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
                                        <th>Province</th>
                                        <th>Chef-lieu</th>
                                        <th>Statistiques</th>
                                        <th>Géographie</th>
                                        <th>Statut</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($provinces as $province)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="provinces[]" 
                                                   value="{{ $province->id }}" 
                                                   class="form-check-input province-checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('admin.geolocalisation.provinces.show', $province) }}" 
                                                           class="text-decoration-none">
                                                            {{ $province->nom }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">Code: {{ $province->code }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">
                                                {{ $province->chef_lieu ?: 'Non renseigné' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div class="text-primary">
                                                    <i class="fas fa-building me-1"></i>
                                                    {{ $province->departements_count }} département(s)
                                                </div>
                                                <div class="text-success">
                                                    <i class="fas fa-users me-1"></i>
                                                    {{ $province->organisations_count }} organisation(s)
                                                </div>
                                                <div class="text-info">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $province->adherents_count }} adhérent(s)
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                @if($province->population_estimee)
                                                    <div>{{ $province->population_formattee }}</div>
                                                @endif
                                                @if($province->superficie_km2)
                                                    <div>{{ $province->superficie_formattee }}</div>
                                                @endif
                                                @if($province->densite)
                                                    <div class="text-muted">{{ $province->densite_formattee }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $province->is_active ? 'success' : 'secondary' }}">
                                                {{ $province->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.geolocalisation.provinces.show', $province) }}" 
                                                   class="btn btn-outline-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.geolocalisation.provinces.edit', $province) }}" 
                                                   class="btn btn-outline-primary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.geolocalisation.provinces.toggle-status', $province) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="btn btn-outline-{{ $province->is_active ? 'warning' : 'success' }}"
                                                            title="{{ $province->is_active ? 'Désactiver' : 'Activer' }}"
                                                            onclick="return confirm('Confirmer le changement de statut ?')">
                                                        <i class="fas fa-{{ $province->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" 
                                                      action="{{ route('admin.geolocalisation.provinces.destroy', $province) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger"
                                                            title="Supprimer"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette province ? Cette action est irréversible.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="card-footer">
                            {{ $provinces->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune province trouvée</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['recherche', 'statut']))
                                    Aucun résultat ne correspond à vos critères de recherche.
                                    <br>
                                    <a href="{{ route('admin.geolocalisation.provinces.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                                        Réinitialiser les filtres
                                    </a>
                                @else
                                    Commencez par créer votre première province.
                                    <br>
                                    <a href="{{ route('admin.geolocalisation.provinces.create') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="fas fa-plus me-2"></i>Créer une province
                                    </a>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript pour les actions groupées --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.province-checkbox');
    const bulkActionBtn = document.getElementById('bulk-action-btn');
    const bulkActionForm = document.getElementById('bulk-action-form');

    // Sélectionner tout
    selectAll?.addEventListener('change', function() {
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionButton();
    });

    // Mise à jour du bouton d'action groupée
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionButton);
    });

    function updateBulkActionButton() {
        const checkedBoxes = document.querySelectorAll('.province-checkbox:checked');
        const actionSelect = document.querySelector('select[name="action"]');
        
        if (bulkActionBtn) {
            bulkActionBtn.disabled = checkedBoxes.length === 0 || !actionSelect?.value;
        }

        // Mise à jour du select-all
        if (selectAll) {
            selectAll.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < checkboxes.length;
            selectAll.checked = checkboxes.length > 0 && checkedBoxes.length === checkboxes.length;
        }
    }

    // Activation du bouton quand une action est sélectionnée
    document.querySelector('select[name="action"]')?.addEventListener('change', updateBulkActionButton);

    // Confirmation pour les actions groupées
    bulkActionForm?.addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.province-checkbox:checked');
        const action = document.querySelector('select[name="action"]').value;
        
        let message = '';
        switch(action) {
            case 'delete':
                message = `Êtes-vous sûr de vouloir supprimer ${checkedBoxes.length} province(s) ? Cette action est irréversible.`;
                break;
            case 'activate':
                message = `Confirmer l'activation de ${checkedBoxes.length} province(s) ?`;
                break;
            case 'deactivate':
                message = `Confirmer la désactivation de ${checkedBoxes.length} province(s) ?`;
                break;
        }

        if (message && !confirm(message)) {
            e.preventDefault();
        }
    });

    // Initialisation
    updateBulkActionButton();
});
</script>
@endpush
@endsection
