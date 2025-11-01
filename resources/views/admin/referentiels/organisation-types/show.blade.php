@extends('layouts.admin')

@section('title', $organisationType->nom . ' - Type d\'organisation')

@section('content')
<div class="container-fluid">
    
    {{-- EN-TÊTE DE PAGE --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">
                        <span class="badge me-2" style="background-color: {{ $organisationType->couleur }}; color: white;">
                            @if($organisationType->icone)
                                <i class="fas {{ $organisationType->icone }}"></i>
                            @endif
                        </span>
                        {{ $organisationType->nom }}
                        @if($organisationType->is_active)
                            <span class="badge bg-success">Actif</span>
                        @else
                            <span class="badge bg-secondary">Inactif</span>
                        @endif
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.referentiels.index') }}">Référentiels</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.referentiels.organisation-types.index') }}">Types d'organisations</a></li>
                            <li class="breadcrumb-item active">{{ $organisationType->nom }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.referentiels.organisation-types.edit', $organisationType->id) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <a href="{{ route('admin.referentiels.organisation-types.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
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

    {{-- STATISTIQUES --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Organisations</h6>
                            <h3 class="mb-0">{{ $statistics['nb_organisations'] }}</h3>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i> 
                                {{ $statistics['nb_organisations_actives'] }} actives
                            </small>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Documents requis</h6>
                            <h3 class="mb-0">{{ $statistics['nb_documents_requis'] }}</h3>
                            <small class="text-danger">
                                <i class="fas fa-exclamation-circle"></i> 
                                {{ $statistics['nb_documents_obligatoires'] }} obligatoires
                            </small>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-file-alt fa-2x"></i>
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
                            <h6 class="text-muted mb-1">Templates</h6>
                            <h3 class="mb-0">{{ $statistics['nb_templates'] }}</h3>
                            <small class="text-muted">
                                Documents à délivrer
                            </small>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-file-pdf fa-2x"></i>
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
                            <h6 class="text-muted mb-1">Fondateurs min.</h6>
                            <h3 class="mb-0">{{ $organisationType->nb_min_fondateurs_majeurs }}</h3>
                            <small class="text-muted">
                                {{ $organisationType->nb_min_adherents_creation }} adhérents min.
                            </small>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        
        {{-- COLONNE PRINCIPALE --}}
        <div class="col-lg-8">
            
            {{-- INFORMATIONS GÉNÉRALES --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informations générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Code :</strong>
                        </div>
                        <div class="col-md-8">
                            <code class="text-primary fs-6">{{ $organisationType->code }}</code>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Nom :</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $organisationType->nom }}
                        </div>
                    </div>
                    @if($organisationType->description)
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Description :</strong>
                            </div>
                            <div class="col-md-8">
                                {{ $organisationType->description }}
                            </div>
                        </div>
                    @endif
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>Couleur :</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="badge" style="background-color: {{ $organisationType->couleur }}; color: white; font-size: 1rem; padding: 0.5rem 1rem;">
                                {{ $organisationType->couleur }}
                            </span>
                        </div>
                    </div>
                    @if($organisationType->icone)
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Icône :</strong>
                            </div>
                            <div class="col-md-8">
                                <i class="fas {{ $organisationType->icone }} fa-2x" style="color: {{ $organisationType->couleur }};"></i>
                                <code class="ms-2">{{ $organisationType->icone }}</code>
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-4">
                            <strong>But :</strong>
                        </div>
                        <div class="col-md-8">
                            @if($organisationType->is_lucratif)
                                <span class="badge bg-warning">Lucratif</span>
                            @else
                                <span class="badge bg-info">Non lucratif</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- RÈGLES MÉTIER --}}
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-gavel"></i> Règles métier
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-users-cog fa-2x text-primary mb-2"></i>
                                <h4 class="mb-1">{{ $organisationType->nb_min_fondateurs_majeurs }}</h4>
                                <small class="text-muted">Fondateurs majeurs minimum</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-user-friends fa-2x text-success mb-2"></i>
                                <h4 class="mb-1">{{ $organisationType->nb_min_adherents_creation }}</h4>
                                <small class="text-muted">Adhérents minimum à la création</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GUIDES ET LÉGISLATION --}}
            @if($organisationType->guide_creation || $organisationType->texte_legislatif || $organisationType->loi_reference)
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-book"></i> Guides et législation
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($organisationType->loi_reference)
                            <div class="mb-3">
                                <strong>Référence législative :</strong>
                                <p class="mb-0 mt-1">
                                    <span class="badge bg-secondary">{{ $organisationType->loi_reference }}</span>
                                </p>
                            </div>
                        @endif
                        
                        @if($organisationType->guide_creation)
                            <div class="mb-3">
                                <strong>Guide de création :</strong>
                                <div class="alert alert-light mt-2">
                                    {!! nl2br(e($organisationType->guide_creation)) !!}
                                </div>
                            </div>
                        @endif
                        
                        @if($organisationType->texte_legislatif)
                            <div class="mb-0">
                                <strong>Texte législatif :</strong>
                                <div class="alert alert-light mt-2" style="max-height: 300px; overflow-y: auto;">
                                    {!! nl2br(e($organisationType->texte_legislatif)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- DOCUMENTS REQUIS --}}
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt"></i> Documents requis ({{ $organisationType->documentTypes->count() }})
                    </h5>
                    <a href="{{ route('admin.referentiels.organisation-types.edit', $organisationType->id) }}#documents" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-edit"></i> Gérer
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($organisationType->documentTypes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="60">#</th>
                                        <th>Nom du document</th>
                                        <th width="150" class="text-center">Type</th>
                                        <th width="100" class="text-center">Ordre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($organisationType->documentTypes->sortBy('pivot.ordre') as $index => $docType)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $docType->nom }}</strong>
                                                @if($docType->pivot->aide_texte)
                                                    <br>
                                                    <small class="text-muted">{{ $docType->pivot->aide_texte }}</small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($docType->pivot->is_obligatoire)
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-exclamation-circle"></i> Obligatoire
                                                    </span>
                                                @else
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-info-circle"></i> Facultatif
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-secondary">{{ $docType->pivot->ordre }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Aucun document requis pour ce type</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- TEMPLATES DE DOCUMENTS --}}
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-pdf"></i> Templates de documents ({{ $organisationType->documentTemplates->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($organisationType->documentTemplates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom du template</th>
                                        <th width="150">Type de document</th>
                                        <th width="100" class="text-center">Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($organisationType->documentTemplates as $template)
                                        <tr>
                                            <td>
                                                <strong>{{ $template->nom }}</strong>
                                                @if($template->description)
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($template->description, 80) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $template->type_document }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($template->is_active)
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactif</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Aucun template associé</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ORGANISATIONS RÉCENTES --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> Organisations récentes ({{ $organisationType->organisations->count() }} sur {{ $statistics['nb_organisations'] }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($organisationType->organisations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom de l'organisation</th>
                                        <th width="150">Statut</th>
                                        <th width="150">Date de création</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($organisationType->organisations->take(10) as $org)
                                        <tr>
                                            <td>
                                                <strong>{{ $org->nom_organisation ?? 'N/A' }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $org->is_active ? 'success' : 'secondary' }}">
                                                    {{ $org->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $org->created_at->format('d/m/Y') }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($statistics['nb_organisations'] > 10)
                            <div class="card-footer bg-light text-center">
                                <small class="text-muted">
                                    Affichage de 10 sur {{ $statistics['nb_organisations'] }} organisations
                                </small>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Aucune organisation de ce type</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- COLONNE LATÉRALE --}}
        <div class="col-lg-4">
            
            {{-- ACTIONS RAPIDES --}}
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-bolt"></i> Actions rapides
                    </h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.referentiels.organisation-types.edit', $organisationType->id) }}" class="btn btn-warning w-100 mb-2">
                        <i class="fas fa-edit"></i> Modifier ce type
                    </a>
                    <button type="button" class="btn btn-danger w-100" onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Supprimer ce type
                    </button>
                </div>
            </div>

            {{-- INFORMATIONS SYSTÈME --}}
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informations système
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>ID :</strong> {{ $organisationType->id }}
                    </p>
                    <p class="mb-2">
                        <strong>Ordre d'affichage :</strong> {{ $organisationType->ordre }}
                    </p>
                    <p class="mb-2">
                        <strong>Créé le :</strong><br>
                        <small>{{ $organisationType->created_at->format('d/m/Y à H:i') }}</small>
                    </p>
                    <p class="mb-0">
                        <strong>Dernière modification :</strong><br>
                        <small>{{ $organisationType->updated_at->format('d/m/Y à H:i') }}</small>
                    </p>
                    @if($organisationType->updated_at->diffInDays($organisationType->created_at) > 0)
                        <hr>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Modifié il y a {{ $organisationType->updated_at->diffForHumans() }}
                        </small>
                    @endif
                </div>
            </div>

            {{-- AIDE --}}
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-question-circle"></i> Besoin d'aide ?
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <strong>Modifier les règles métier :</strong> Les changements affecteront uniquement les nouvelles organisations.
                    </p>
                    <p class="small mb-2">
                        <strong>Désactiver le type :</strong> Les organisations existantes resteront actives, mais aucune nouvelle ne pourra être créée.
                    </p>
                    <p class="small mb-0">
                        <strong>Supprimer le type :</strong> Impossible si des organisations utilisent ce type.
                    </p>
                </div>
            </div>

        </div>

    </div>

</div>

{{-- FORMULAIRE DE SUPPRESSION CACHÉ --}}
<form id="delete-form" method="POST" action="{{ route('admin.referentiels.organisation-types.destroy', $organisationType->id) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
// Confirmation de suppression
function confirmDelete() {
    const nbOrganisations = {{ $statistics['nb_organisations'] }};
    
    if (nbOrganisations > 0) {
        alert(`Impossible de supprimer ce type :\n${nbOrganisations} organisation(s) l'utilisent actuellement.`);
        return;
    }
    
    if (confirm(`Êtes-vous sûr de vouloir supprimer le type "${{{ json_encode($organisationType->nom) }}}" ?\n\nCette action est irréversible.`)) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush