@extends('layouts.admin')

@section('title', $documentType->libelle)

@section('content')
<div class="container-fluid">
    {{-- En-tête --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">{{ $documentType->libelle }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}">Tableau de bord</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.referentiels.index') }}">Référentiels</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.referentiels.document-types.index') }}">Types de documents</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $documentType->libelle }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.referentiels.document-types.edit', $documentType->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="{{ route('admin.referentiels.document-types.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Statistiques --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-building fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Types d'organisations</h6>
                            <h3 class="mb-0">{{ $statistics['nb_types_orga'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-tasks fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Types d'opérations</h6>
                            <h3 class="mb-0">{{ $statistics['nb_types_operation'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Informations principales --}}
        <div class="col-lg-8">
            {{-- Informations générales --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations générales</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Code technique</label>
                            <div>
                                <code class="fs-6">{{ $documentType->code }}</code>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Libellé</label>
                            <div>
                                <strong>{{ $documentType->libelle }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Formats acceptés</label>
                            <div>
                                <span class="badge bg-secondary">{{ $documentType->extensions_string }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Taille maximale</label>
                            <div>
                                <span class="badge bg-light text-dark">{{ $documentType->taille_max }} Mo</span>
                            </div>
                        </div>
                    </div>

                    @if($documentType->description)
                        <div class="mb-3">
                            <label class="text-muted small">Description</label>
                            <p class="mb-0">{{ $documentType->description }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <label class="text-muted small">Ordre d'affichage</label>
                            <div>
                                <span class="badge bg-secondary">{{ $documentType->ordre }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Statut</label>
                            <div>
                                @if($documentType->is_active)
                                    <span class="badge bg-success">Actif</span>
                                @else
                                    <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Types d'organisations --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> Types d'organisations 
                        <span class="badge bg-primary">{{ $documentType->organisationTypes->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($documentType->organisationTypes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type d'organisation</th>
                                        <th style="width: 100px" class="text-center">Obligatoire</th>
                                        <th style="width: 100px" class="text-center">Ordre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documentType->organisationTypes as $orgType)
                                        <tr>
                                            <td>
                                                <strong>{{ $orgType->nom }}</strong>
                                            </td>
                                            <td class="text-center">
                                                @if($orgType->pivot->is_obligatoire)
                                                    <span class="badge bg-warning">Obligatoire</span>
                                                @else
                                                    <span class="badge bg-light text-dark">Facultatif</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ $orgType->pivot->ordre }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">Ce document n'est utilisé par aucun type d'organisation</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Types d'opérations --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks"></i> Types d'opérations 
                        <span class="badge bg-success">{{ $documentType->operationTypes->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($documentType->operationTypes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type d'opération</th>
                                        <th style="width: 100px" class="text-center">Obligatoire</th>
                                        <th style="width: 100px" class="text-center">Ordre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documentType->operationTypes as $opType)
                                        <tr>
                                            <td>
                                                <strong>{{ $opType->libelle }}</strong>
                                            </td>
                                            <td class="text-center">
                                                @if($opType->pivot->is_obligatoire)
                                                    <span class="badge bg-warning">Obligatoire</span>
                                                @else
                                                    <span class="badge bg-light text-dark">Facultatif</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ $opType->pivot->ordre }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">Ce document n'est utilisé par aucun type d'opération</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Panneau latéral --}}
        <div class="col-lg-4">
            {{-- Métadonnées --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-calendar"></i> Métadonnées</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">ID</label>
                        <div><code>#{{ $documentType->id }}</code></div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Créé le</label>
                        <div>{{ $documentType->created_at->format('d/m/Y à H:i') }}</div>
                    </div>

                    <div class="mb-0">
                        <label class="text-muted small">Dernière modification</label>
                        <div>{{ $documentType->updated_at->format('d/m/Y à H:i') }}</div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-cog"></i> Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.referentiels.document-types.edit', $documentType->id) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-edit"></i> Modifier
                        </a>

                        @if($documentType->organisationTypes->count() == 0 && $documentType->operationTypes->count() == 0)
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        @else
                            <button type="button" 
                                    class="btn btn-danger" 
                                    disabled
                                    title="Impossible de supprimer : document utilisé">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Document lié à {{ $documentType->organisationTypes->count() }} type(s) d'organisation et {{ $documentType->operationTypes->count() }} type(s) d'opération
                            </small>
                        @endif

                        <a href="{{ route('admin.referentiels.document-types.index') }}" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour à la liste
                        </a>
                    </div>
                </div>
            </div>

            {{-- Informations techniques --}}
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations techniques</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Extensions</label>
                        <div>
                            @if(is_array($documentType->extensions_autorisees) && count($documentType->extensions_autorisees) > 0)
                                @foreach($documentType->extensions_autorisees as $ext)
                                    <span class="badge bg-secondary me-1">{{ strtoupper($ext) }}</span>
                                @endforeach
                            @else
                                <span class="text-muted small">Aucune extension définie</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="text-muted small">Taille maximale en octets</label>
                        <div>
                            <code>{{ number_format($documentType->taille_max_octets, 0, '', ' ') }} bytes</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmation de suppression --}}
<form id="delete-form" 
      method="POST" 
      action="{{ route('admin.referentiels.document-types.destroy', $documentType->id) }}"
      style="display: none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script>
function confirmDelete() {
    if (confirm('Voulez-vous vraiment supprimer le type de document "{{ $documentType->libelle }}" ?\n\nCette action est irréversible.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush
@endsection