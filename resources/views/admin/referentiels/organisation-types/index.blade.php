@extends('layouts.admin')

@section('title', 'Types d\'organisations - Référentiels')

@section('content')
<div class="container-fluid">
    
    {{-- EN-TÊTE DE PAGE --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <i class="fas fa-building text-primary"></i>
                        Types d'organisations
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.referentiels.index') }}">Référentiels</a></li>
                            <li class="breadcrumb-item active">Types d'organisations</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.referentiels.organisation-types.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau type
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- MESSAGES FLASH --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- STATISTIQUES GLOBALES --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Actifs</h6>
                            <h3 class="mb-0 text-success">{{ $stats['actifs'] }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Inactifs</h6>
                            <h3 class="mb-0 text-secondary">{{ $stats['inactifs'] }}</h3>
                        </div>
                        <div class="text-secondary">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Non lucratifs</h6>
                            <h3 class="mb-0 text-info">{{ $stats['non_lucratifs'] }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-hands-helping fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- FILTRES ET RECHERCHE --}}
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-filter"></i> Filtres et recherche
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.referentiels.organisation-types.index') }}">
                <div class="row g-3">
                    
                    {{-- RECHERCHE --}}
                    <div class="col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" 
                               class="form-control" 
                               id="search" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Code, nom, description...">
                    </div>

                    {{-- FILTRE STATUT --}}
                    <div class="col-md-3">
                        <label for="statut" class="form-label">Statut</label>
                        <select class="form-select" id="statut" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>

                    {{-- FILTRE BUT --}}
                    <div class="col-md-3">
                        <label for="but" class="form-label">But</label>
                        <select class="form-select" id="but" name="but">
                            <option value="">Tous les types</option>
                            <option value="lucratif" {{ request('but') === 'lucratif' ? 'selected' : '' }}>Lucratif</option>
                            <option value="non_lucratif" {{ request('but') === 'non_lucratif' ? 'selected' : '' }}>Non lucratif</option>
                        </select>
                    </div>

                    {{-- BOUTONS --}}
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>

                    @if(request()->hasAny(['search', 'statut', 'but']))
                        <div class="col-12">
                            <a href="{{ route('admin.referentiels.organisation-types.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times"></i> Réinitialiser les filtres
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- TABLEAU DES TYPES --}}
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                Liste des types d'organisations ({{ $organisationTypes->total() }})
            </h5>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Réorganiser">
                    <i class="fas fa-sort"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="tooltip" title="Exporter">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($organisationTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th width="60">Ordre</th>
                                <th>Code</th>
                                <th>Nom</th>
                                <th width="100" class="text-center">But</th>
                                <th width="120" class="text-center">Fondateurs min.</th>
                                <th width="120" class="text-center">Adhérents min.</th>
                                <th width="100" class="text-center">Documents</th>
                                <th width="100" class="text-center">Organisations</th>
                                <th width="80" class="text-center">Statut</th>
                                <th width="150" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($organisationTypes as $type)
                                <tr>
                                    <td>
                                        <span class="badge" style="background-color: {{ $type->couleur }}; color: white;">
                                            @if($type->icone)
                                                <i class="fas {{ $type->icone }}"></i>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $type->ordre }}</span>
                                    </td>
                                    <td>
                                        <code class="text-primary">{{ $type->code }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ $type->nom }}</strong>
                                        @if($type->description)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($type->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($type->is_lucratif)
                                            <span class="badge bg-warning">Lucratif</span>
                                        @else
                                            <span class="badge bg-info">Non lucratif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ $type->nb_min_fondateurs_majeurs }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ $type->nb_min_adherents_creation }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary" data-bs-toggle="tooltip" 
                                              title="{{ $type->document_types_count }} document(s) requis">
                                            <i class="fas fa-file-alt"></i> {{ $type->document_types_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success" data-bs-toggle="tooltip" 
                                              title="{{ $type->organisations_count }} organisation(s)">
                                            <i class="fas fa-building"></i> {{ $type->organisations_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($type->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-secondary">Inactif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.referentiels.organisation-types.show', $type->id) }}" 
                                               class="btn btn-info btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Voir détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.referentiels.organisation-types.edit', $type->id) }}" 
                                               class="btn btn-warning btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger btn-sm" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Supprimer"
                                                    onclick="confirmDelete({{ $type->id }}, '{{ $type->nom }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Affichage de {{ $organisationTypes->firstItem() }} à {{ $organisationTypes->lastItem() }} 
                            sur {{ $organisationTypes->total() }} résultats
                        </div>
                        <div>
                            {{ $organisationTypes->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aucun type d'organisation trouvé.</p>
                    @if(request()->hasAny(['search', 'statut', 'but']))
                        <a href="{{ route('admin.referentiels.organisation-types.index') }}" class="btn btn-sm btn-primary">
                            Réinitialiser les filtres
                        </a>
                    @else
                        <a href="{{ route('admin.referentiels.organisation-types.create') }}" class="btn btn-sm btn-primary">
                            Créer le premier type
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

</div>

{{-- FORMULAIRE DE SUPPRESSION CACHÉ --}}
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
// Initialiser les tooltips Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Confirmation de suppression
function confirmDelete(id, nom) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le type "${nom}" ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('delete-form');
        form.action = `/admin/referentiels/organisation-types/${id}`;
        form.submit();
    }
}
</script>
@endpush