@extends('layouts.app')

@section('title', 'Gestion des Templates de Documents')

@section('content')
<div class="container-fluid py-4">
    
    {{-- En-tête --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1">
                <i class="fas fa-file-alt text-primary"></i> Templates de Documents
            </h2>
            <p class="text-muted">Gérer les modèles de documents officiels</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.document-templates.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nouveau Template
            </a>
        </div>
    </div>

    {{-- Alertes --}}
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

    {{-- Filtres --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.document-templates.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Type d'organisation</label>
                        <select name="organisation_type_id" class="form-select">
                            <option value="">Tous les types</option>
                            @foreach($organisationTypes as $type)
                                <option value="{{ $type->id }}" 
                                    {{ request('organisation_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Type de document</label>
                        <select name="type_document" class="form-select">
                            <option value="">Tous les types</option>
                            @foreach($typesDocument as $key => $label)
                                <option value="{{ $key }}" 
                                    {{ request('type_document') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Auto-génération</label>
                        <select name="auto_generate" class="form-select">
                            <option value="">Tous</option>
                            <option value="1" {{ request('auto_generate') == '1' ? 'selected' : '' }}>
                                Oui
                            </option>
                            <option value="0" {{ request('auto_generate') == '0' ? 'selected' : '' }}>
                                Non
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" class="form-control" 
                            placeholder="Code, nom..." 
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                    </div>
                </div>

                @if(request()->hasAny(['organisation_type_id', 'type_document', 'auto_generate', 'search']))
                    <div class="mt-3">
                        <a href="{{ route('admin.document-templates.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-times"></i> Réinitialiser les filtres
                        </a>
                    </div>
                @endif
            </form>
        </div>
    </div>

    {{-- Liste des templates --}}
    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Liste des templates 
                    <span class="badge bg-primary">{{ $templates->total() }}</span>
                </h5>
                <div>
                    <small class="text-muted">
                        Affichage {{ $templates->firstItem() ?? 0 }} - {{ $templates->lastItem() ?? 0 }} 
                        sur {{ $templates->total() }}
                    </small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Type Org.</th>
                            <th>Type Doc.</th>
                            <th class="text-center">Auto</th>
                            <th class="text-center">QR Code</th>
                            <th class="text-center">Statut</th>
                            <th class="text-center">Documents</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>
                                    <code class="text-primary">{{ $template->code }}</code>
                                </td>
                                <td>
                                    <strong>{{ $template->nom }}</strong>
                                    @if($template->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($template->organisationType)
                                        <span class="badge bg-info">
                                            {{ $template->organisationType->nom }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $template->type_document_label }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($template->auto_generate)
                                        <i class="fas fa-check-circle text-success" title="Auto-génération activée"></i>
                                    @else
                                        <i class="fas fa-times-circle text-muted" title="Manuel"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($template->has_qr_code)
                                        <i class="fas fa-qrcode text-primary" title="QR Code inclus"></i>
                                    @else
                                        <i class="fas fa-minus text-muted"></i>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($template->is_active)
                                        <span class="badge bg-success">Actif</span>
                                    @else
                                        <span class="badge bg-danger">Inactif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">
                                        {{ $template->generations_count ?? 0 }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.document-templates.show', $template) }}" 
                                           class="btn btn-outline-info" 
                                           title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.document-templates.preview', $template) }}" 
                                           class="btn btn-outline-secondary" 
                                           title="Prévisualiser"
                                           target="_blank">
                                            <i class="fas fa-search"></i>
                                        </a>
                                        <a href="{{ route('admin.document-templates.edit', $template) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Supprimer"
                                                onclick="confirmDelete('{{ $template->id }}', '{{ $template->nom }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <form id="delete-form-{{ $template->id }}" 
                                          action="{{ route('admin.document-templates.destroy', $template) }}" 
                                          method="POST" 
                                          class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun template trouvé</p>
                                    <a href="{{ route('admin.document-templates.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Créer le premier template
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($templates->hasPages())
            <div class="card-footer">
                {{ $templates->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
function confirmDelete(id, name) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le template "${name}" ?\n\nCette action est irréversible.`)) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush

@endsection