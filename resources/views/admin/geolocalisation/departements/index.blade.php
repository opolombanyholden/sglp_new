{{-- resources/views/admin/geolocalisation/departements/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Gestion des Départements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-building me-2"></i>
                        Départements du Gabon
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-2">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.geolocalisation.provinces.index') }}">Géolocalisation</a>
                            </li>
                            <li class="breadcrumb-item active">Départements</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.geolocalisation.departements.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouveau Département
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
                    <form method="GET" action="{{ route('admin.geolocalisation.departements.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Province</label>
                            <select name="province_id" class="form-select">
                                <option value="">Toutes les provinces</option>
                                @foreach($provinces as $province)
                                    <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>
                                        {{ $province->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Recherche</label>
                            <input type="text" name="recherche" class="form-control" 
                                   placeholder="Nom, code, chef-lieu..." 
                                   value="{{ request('recherche') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select name="type_subdivision" class="form-select">
                                <option value="">Tous types</option>
                                <option value="urbain" {{ request('type_subdivision') === 'urbain' ? 'selected' : '' }}>Urbain</option>
                                <option value="rural" {{ request('type_subdivision') === 'rural' ? 'selected' : '' }}>Rural</option>
                                <option value="mixte" {{ request('type_subdivision') === 'mixte' ? 'selected' : '' }}>Mixte</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Statut</label>
                            <select name="statut" class="form-select">
                                <option value="">Tous</option>
                                <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
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
            @if($departements->count() > 0)
            <div class="card mb-4">
                <div class="card-body">
                    <form id="bulk-action-form" method="POST" action="{{ route('admin.geolocalisation.departements.bulk-action') }}">
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
                                <a href="{{ route('admin.geolocalisation.departements.export', ['format' => 'csv']) }}" 
                                   class="btn btn-outline-success me-2">
                                    <i class="fas fa-file-csv me-2"></i>Exporter CSV
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- Liste des départements --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Liste des Départements ({{ $departements->total() }})
                    </h5>
                    <small class="text-muted">
                        Page {{ $departements->currentPage() }} sur {{ $departements->lastPage() }}
                    </small>
                </div>
                <div class="card-body p-0">
                    @if($departements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="30">
                                            <input type="checkbox" id="select-all" class="form-check-input">
                                        </th>
                                        <th>Département</th>
                                        <th>Province</th>
                                        <th>Chef-lieu</th>
                                        <th>Subdivisions</th>
                                        <th>Type</th>
                                        <th>Statut</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($departements as $departement)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="departements[]" 
                                                   value="{{ $departement->id }}" 
                                                   class="form-check-input departement-checkbox">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('admin.geolocalisation.departements.show', $departement) }}" 
                                                           class="text-decoration-none">
                                                            {{ $departement->nom }}
                                                        </a>
                                                    </h6>
                                                    <small class="text-muted">Code: {{ $departement->code }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.geolocalisation.provinces.show', $departement->province) }}" 
                                               class="text-decoration-none">
                                                {{ $departement->province->nom }}
                                            </a>
                                        </td>
                                        <td>{{ $departement->chef_lieu ?: 'Non renseigné' }}</td>
                                        <td>
                                            <div class="small">
                                                @if($departement->communes_villes_count > 0)
                                                    <div class="text-primary">
                                                        <i class="fas fa-city me-1"></i>
                                                        {{ $departement->communes_villes_count }} commune(s)/ville(s)
                                                    </div>
                                                @endif
                                                @if($departement->cantons_count > 0)
                                                    <div class="text-success">
                                                        <i class="fas fa-tree me-1"></i>
                                                        {{ $departement->cantons_count }} canton(s)
                                                    </div>
                                                @endif
                                                @if($departement->organisations_count > 0)
                                                    <div class="text-info">
                                                        <i class="fas fa-sitemap me-1"></i>
                                                        {{ $departement->organisations_count }} organisation(s)
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $typeConfig = [
                                                    'urbain' => ['class' => 'primary', 'icon' => 'city'],
                                                    'rural' => ['class' => 'success', 'icon' => 'tree'],
                                                    'mixte' => ['class' => 'warning', 'icon' => 'exchange-alt'],
                                                    'non défini' => ['class' => 'secondary', 'icon' => 'question']
                                                ];
                                                $config = $typeConfig[$departement->type_subdivision] ?? $typeConfig['non défini'];
                                            @endphp
                                            <span class="badge bg-{{ $config['class'] }}">
                                                <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                                {{ ucfirst($departement->type_subdivision) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $departement->is_active ? 'success' : 'secondary' }}">
                                                {{ $departement->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.geolocalisation.departements.show', $departement) }}" 
                                                   class="btn btn-outline-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.geolocalisation.departements.edit', $departement) }}" 
                                                   class="btn btn-outline-primary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.geolocalisation.departements.toggle-status', $departement) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" 
                                                            class="btn btn-outline-{{ $departement->is_active ? 'warning' : 'success' }}"
                                                            title="{{ $departement->is_active ? 'Désactiver' : 'Activer' }}"
                                                            onclick="return confirm('Confirmer le changement de statut ?')">
                                                        <i class="fas fa-{{ $departement->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" 
                                                      action="{{ route('admin.geolocalisation.departements.destroy', $departement) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger"
                                                            title="Supprimer"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce département ? Cette action est irréversible.')">
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
                            {{ $departements->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun département trouvé</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['recherche', 'statut', 'province_id']))
                                    Aucun résultat ne correspond à vos critères de recherche.
                                    <br>
                                    <a href="{{ route('admin.geolocalisation.departements.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                                        Réinitialiser les filtres
                                    </a>
                                @else
                                    Commencez par créer votre premier département.
                                    <br>
                                    <a href="{{ route('admin.geolocalisation.departements.create') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="fas fa-plus me-2"></i>Créer un département
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
    const checkboxes = document.querySelectorAll('.departement-checkbox');
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
        const checkedBoxes = document.querySelectorAll('.departement-checkbox:checked');
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
        const checkedBoxes = document.querySelectorAll('.departement-checkbox:checked');
        const action = document.querySelector('select[name="action"]').value;
        
        let message = '';
        switch(action) {
            case 'delete':
                message = `Êtes-vous sûr de vouloir supprimer ${checkedBoxes.length} département(s) ? Cette action est irréversible.`;
                break;
            case 'activate':
                message = `Confirmer l'activation de ${checkedBoxes.length} département(s) ?`;
                break;
            case 'deactivate':
                message = `Confirmer la désactivation de ${checkedBoxes.length} département(s) ?`;
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