@extends('layouts.admin')

@section('title', 'Types de Documents')

@section('content')
<div class="container-fluid">
    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Types de Documents</h1>
            <p class="text-muted mb-0">Gestion des types de documents requis pour les organisations</p>
        </div>
        <a href="{{ route('admin.referentiels.document-types.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouveau type
        </a>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Statistiques --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-alt fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Actifs</h6>
                            <h3 class="mb-0">{{ $stats['actifs'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-times-circle fa-2x text-secondary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Inactifs</h6>
                            <h3 class="mb-0">{{ $stats['inactifs'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres et recherche --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.referentiels.document-types.index') }}">
                <div class="row g-3">
                    {{-- Recherche --}}
                    <div class="col-md-4">
                        <label class="form-label">Recherche</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Nom, code, description..."
                               value="{{ request('search') }}">
                    </div>

                    {{-- Type d'organisation --}}
                    <div class="col-md-3">
                        <label class="form-label">Type d'organisation</label>
                        <select name="organisation_type" class="form-select">
                            <option value="">Tous</option>
                            @foreach($typesOrganisation as $key => $label)
                                <option value="{{ $key }}" {{ request('organisation_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Type d'opération --}}
                    <div class="col-md-2">
                        <label class="form-label">Type d'opération</label>
                        <select name="operation_type" class="form-select">
                            <option value="">Tous</option>
                            @foreach($typesOperation as $key => $label)
                                <option value="{{ $key }}" {{ request('operation_type') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Statut --}}
                    <div class="col-md-2">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="">Tous</option>
                            <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>

                    {{-- Boutons --}}
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <a href="{{ route('admin.referentiels.document-types.index') }}" class="btn btn-sm btn-link">
                            <i class="fas fa-redo"></i> Réinitialiser les filtres
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des types --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    Liste des types de documents 
                    <span class="badge bg-secondary">{{ $documentTypes->total() }}</span>
                </h5>
                <div>
                    <select class="form-select form-select-sm" onchange="window.location.href='?per_page='+this.value+'{{ request()->except('per_page') ? '&'.http_build_query(request()->except('per_page')) : '' }}'">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 par page</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 par page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 par page</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($documentTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px">Ordre</th>
                                <th>Libellé</th>
                                <th>Code</th>
                                <th>Types organisation</th>
                                <th>Types opération</th>
                                <th>Formats</th>
                                <th style="width: 80px">Taille max</th>
                                <th style="width: 80px" class="text-center">Statut</th>
                                <th style="width: 150px" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documentTypes as $type)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $type->ordre }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $type->libelle }}</strong>
                                        @if($type->description)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($type->description, 60) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <code class="text-primary">{{ $type->code }}</code>
                                    </td>
                                    <td>
                                        @if($type->organisationTypes->count() > 0)
                                            @foreach($type->organisationTypes->take(2) as $orgType)
                                                <span class="badge bg-info mb-1">
                                                    {{ $orgType->nom }}
                                                    @if($orgType->pivot->is_obligatoire)
                                                        <i class="fas fa-star text-warning" title="Obligatoire"></i>
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if($type->organisationTypes->count() > 2)
                                                <span class="badge bg-light text-dark">
                                                    +{{ $type->organisationTypes->count() - 2 }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted small">Aucun</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($type->operationTypes->count() > 0)
                                            @foreach($type->operationTypes->take(2) as $opType)
                                                <span class="badge bg-secondary mb-1">
                                                    {{ $opType->libelle }}
                                                    @if($opType->pivot->is_obligatoire)
                                                        <i class="fas fa-star text-warning" title="Obligatoire"></i>
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if($type->operationTypes->count() > 2)
                                                <span class="badge bg-light text-dark">
                                                    +{{ $type->operationTypes->count() - 2 }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted small">Aucun</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $type->extensions_string }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $type->taille_max }} Mo</span>
                                    </td>
                                    <td class="text-center">
                                        @if($type->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-secondary">Inactif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.referentiels.document-types.show', $type->id) }}" 
                                               class="btn btn-outline-info" 
                                               title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.referentiels.document-types.edit', $type->id) }}" 
                                               class="btn btn-outline-primary" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    onclick="confirmDelete({{ $type->id }}, '{{ addslashes($type->libelle) }}')"
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

                {{-- Pagination --}}
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Affichage de {{ $documentTypes->firstItem() ?? 0 }} à {{ $documentTypes->lastItem() ?? 0 }} 
                            sur {{ $documentTypes->total() }} résultats
                        </div>
                        {{ $documentTypes->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">
                        @if(request()->hasAny(['search', 'organisation_type', 'operation_type', 'statut']))
                            Aucun type de document trouvé avec ces critères.
                        @else
                            Aucun type de document disponible.
                        @endif
                    </p>
                    @if(!request()->hasAny(['search', 'organisation_type', 'operation_type', 'statut']))
                        <a href="{{ route('admin.referentiels.document-types.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Créer le premier type
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal de confirmation de suppression --}}
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete(id, libelle) {
    if (confirm(`Voulez-vous vraiment supprimer le type de document "${libelle}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('delete-form');
        form.action = `/admin/referentiels/document-types/${id}`;
        form.submit();
    }
}

// Auto-dismiss alerts après 5 secondes
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>
@endpush
@endsection