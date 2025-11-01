@extends('layouts.app')

@section('title', 'Documents Générés')

@section('content')
<div class="container-fluid py-4">
    
    {{-- En-tête --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1">
                <i class="fas fa-file-pdf text-primary"></i> Documents Générés
            </h2>
            <p class="text-muted">Historique de tous les documents officiels générés</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.documents.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Générer un document
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

    {{-- Statistiques rapides --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-file-alt fa-2x text-primary mb-2"></i>
                    <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
                    <small class="text-muted">Total documents</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h3 class="mb-0">{{ $stats['valid'] ?? 0 }}</h3>
                    <small class="text-muted">Documents valides</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-download fa-2x text-warning mb-2"></i>
                    <h3 class="mb-0">{{ $stats['downloads'] ?? 0 }}</h3>
                    <small class="text-muted">Téléchargements</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-day fa-2x text-info mb-2"></i>
                    <h3 class="mb-0">{{ $stats['today'] ?? 0 }}</h3>
                    <small class="text-muted">Aujourd'hui</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.documents.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Template</label>
                        <select name="template_id" class="form-select">
                            <option value="">Tous les templates</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" 
                                    {{ request('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Organisation</label>
                        <select name="organisation_id" class="form-select">
                            <option value="">Toutes les organisations</option>
                            @foreach($organisations as $org)
                                <option value="{{ $org->id }}" 
                                    {{ request('organisation_id') == $org->id ? 'selected' : '' }}>
                                    {{ $org->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Statut</label>
                        <select name="is_valid" class="form-select">
                            <option value="">Tous</option>
                            <option value="1" {{ request('is_valid') == '1' ? 'selected' : '' }}>
                                Valide
                            </option>
                            <option value="0" {{ request('is_valid') == '0' ? 'selected' : '' }}>
                                Invalidé
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Date de</label>
                        <input type="date" name="date_from" class="form-control" 
                            value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Date à</label>
                        <input type="date" name="date_to" class="form-control" 
                            value="{{ request('date_to') }}">
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-4">
                        <label class="form-label">Recherche</label>
                        <input type="text" name="search" class="form-control" 
                            placeholder="N° document, organisation..." 
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Par page</label>
                        <select name="per_page" class="form-select">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 25) == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                        @if(request()->hasAny(['template_id', 'organisation_id', 'is_valid', 'date_from', 'date_to', 'search']))
                            <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Réinitialiser
                            </a>
                        @endif
                    </div>

                    <div class="col-md-3 text-end">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="button" class="btn btn-outline-success" onclick="exportData()">
                            <i class="fas fa-file-excel"></i> Exporter Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Liste des documents --}}
    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Liste des documents 
                    <span class="badge bg-primary">{{ $documents->total() }}</span>
                </h5>
                <div>
                    <small class="text-muted">
                        Affichage {{ $documents->firstItem() ?? 0 }} - {{ $documents->lastItem() ?? 0 }} 
                        sur {{ $documents->total() }}
                    </small>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" class="form-check-input" id="selectAll">
                            </th>
                            <th>N° Document</th>
                            <th>Template</th>
                            <th>Organisation</th>
                            <th>Généré le</th>
                            <th class="text-center">Téléchargements</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $document)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input document-checkbox" 
                                        value="{{ $document->id }}">
                                </td>
                                <td>
                                    <code class="text-primary">{{ $document->numero_document }}</code>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-fingerprint"></i> 
                                        {{ Str::limit($document->verification_token, 16) }}
                                    </small>
                                </td>
                                <td>
                                    <strong>{{ $document->documentTemplate->nom }}</strong>
                                    <br>
                                    <span class="badge bg-secondary">
                                        {{ $document->documentTemplate->type_document_label }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $document->organisation->nom }}</strong>
                                    @if($document->organisation->sigle)
                                        <br>
                                        <small class="text-muted">{{ $document->organisation->sigle }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $document->generated_at->format('d/m/Y') }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $document->generated_at->format('H:i') }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark">
                                        <i class="fas fa-download"></i> {{ $document->download_count }}
                                    </span>
                                    @if($document->last_downloaded_at)
                                        <br>
                                        <small class="text-muted">
                                            {{ $document->last_downloaded_at->diffForHumans() }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($document->is_valid)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle"></i> Valide
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle"></i> Invalidé
                                        </span>
                                        @if($document->invalidated_at)
                                            <br>
                                            <small class="text-muted">
                                                {{ $document->invalidated_at->format('d/m/Y') }}
                                            </small>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.documents.show', $document) }}" 
                                           class="btn btn-outline-info" 
                                           title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.documents.download', $document) }}" 
                                           class="btn btn-outline-primary" 
                                           title="Télécharger PDF"
                                           target="_blank">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="{{ route('document.verify', $document->verification_token) }}" 
                                           class="btn btn-outline-secondary" 
                                           title="Vérifier le document"
                                           target="_blank">
                                            <i class="fas fa-shield-alt"></i>
                                        </a>
                                        @if($document->is_valid)
                                            <button type="button" 
                                                    class="btn btn-outline-warning" 
                                                    title="Invalider"
                                                    onclick="invalidateDocument('{{ $document->id }}', '{{ $document->numero_document }}')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                title="Supprimer"
                                                onclick="deleteDocument('{{ $document->id }}', '{{ $document->numero_document }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun document trouvé</p>
                                    @if(request()->hasAny(['template_id', 'organisation_id', 'is_valid', 'date_from', 'date_to', 'search']))
                                        <a href="{{ route('admin.documents.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Réinitialiser les filtres
                                        </a>
                                    @else
                                        <a href="{{ route('admin.documents.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Générer le premier document
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($documents->hasPages())
            <div class="card-footer">
                {{ $documents->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

    {{-- Actions groupées --}}
    @if($documents->isNotEmpty())
        <div class="card mt-3 border-warning" id="bulkActionsCard" style="display: none;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong><span id="selectedCount">0</span> document(s) sélectionné(s)</strong>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" onclick="bulkDownload()">
                            <i class="fas fa-download"></i> Télécharger
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="bulkInvalidate()">
                            <i class="fas fa-ban"></i> Invalider
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="bulkDelete()">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Formulaires cachés pour actions --}}
    <form id="invalidate-form" method="POST" class="d-none">
        @csrf
        @method('PATCH')
    </form>

    <form id="delete-form" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>

</div>

@push('scripts')
<script>
// Gestion de la sélection multiple
const selectAllCheckbox = document.getElementById('selectAll');
const documentCheckboxes = document.querySelectorAll('.document-checkbox');
const bulkActionsCard = document.getElementById('bulkActionsCard');
const selectedCountSpan = document.getElementById('selectedCount');

// Sélectionner/désélectionner tout
if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
        documentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });
}

// Mettre à jour l'affichage des actions groupées
documentCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const selectedCount = document.querySelectorAll('.document-checkbox:checked').length;
    selectedCountSpan.textContent = selectedCount;
    bulkActionsCard.style.display = selectedCount > 0 ? 'block' : 'none';
}

// Fonction pour invalider un document
function invalidateDocument(id, numero) {
    if (confirm(`Voulez-vous vraiment invalider le document ${numero} ?\n\nLe document ne sera plus considéré comme valide.`)) {
        const form = document.getElementById('invalidate-form');
        form.action = `/admin/documents/${id}/invalidate`;
        form.submit();
    }
}

// Fonction pour supprimer un document
function deleteDocument(id, numero) {
    if (confirm(`Voulez-vous vraiment supprimer le document ${numero} ?\n\nCette action est irréversible.`)) {
        const form = document.getElementById('delete-form');
        form.action = `/admin/documents/${id}`;
        form.submit();
    }
}

// Actions groupées
function getSelectedIds() {
    return Array.from(document.querySelectorAll('.document-checkbox:checked')).map(cb => cb.value);
}

function bulkDownload() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    
    if (confirm(`Télécharger ${ids.length} document(s) ?`)) {
        window.location.href = `/admin/documents/bulk-download?ids=${ids.join(',')}`;
    }
}

function bulkInvalidate() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    
    if (confirm(`Invalider ${ids.length} document(s) ?\n\nCes documents ne seront plus considérés comme valides.`)) {
        fetch('/admin/documents/bulk-invalidate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de l\'invalidation');
            }
        });
    }
}

function bulkDelete() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    
    if (confirm(`ATTENTION : Supprimer ${ids.length} document(s) ?\n\nCette action est irréversible.`)) {
        fetch('/admin/documents/bulk-delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la suppression');
            }
        });
    }
}

// Export Excel
function exportData() {
    const form = document.getElementById('filterForm');
    const url = new URL(form.action);
    const formData = new FormData(form);
    
    // Ajouter les paramètres de filtre à l'URL d'export
    formData.forEach((value, key) => {
        if (value) url.searchParams.append(key, value);
    });
    
    window.location.href = `/admin/documents/export?${url.searchParams.toString()}`;
}
</script>
@endpush

@endsection