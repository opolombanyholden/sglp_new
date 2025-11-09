@extends('layouts.admin')

@section('title', 'Détails du Template')

@section('content')
<div class="container-fluid py-4">
    
    {{-- En-tête --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.document-templates.index') }}">Templates</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $documentTemplate->code }}</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-file-alt text-primary"></i> {{ $documentTemplate->nom }}
                    </h2>
                    <p class="text-muted mb-0">
                        <code class="text-primary">{{ $documentTemplate->code }}</code>
                        @if($documentTemplate->is_active)
                            <span class="badge bg-success ms-2">Actif</span>
                        @else
                            <span class="badge bg-danger ms-2">Inactif</span>
                        @endif
                        @if($documentTemplate->auto_generate)
                            <span class="badge bg-info ms-2">
                                <i class="fas fa-magic"></i> Auto-génération
                            </span>
                        @endif
                    </p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.document-templates.preview', $documentTemplate) }}" 
                       class="btn btn-outline-secondary"
                       target="_blank">
                        <i class="fas fa-search"></i> Prévisualiser
                    </a>
                    <button type="button" 
                            class="btn btn-outline-info"
                            onclick="window.open('{{ route('admin.document-templates.preview', $documentTemplate) }}', '_blank'); setTimeout(() => window.frames[window.frames.length-1].print(), 500)">
                        <i class="fas fa-print"></i> Imprimer
                    </button>
                    <button type="button" 
                            class="btn btn-outline-success"
                            onclick="downloadPDF()">
                        <i class="fas fa-file-pdf"></i> Télécharger PDF
                    </button>
                    <a href="{{ route('admin.document-templates.edit', $documentTemplate) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <button type="button" 
                            class="btn btn-outline-danger" 
                            onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertes --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Colonne principale --}}
        <div class="col-lg-8">
            
            {{-- Statistiques --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-file-pdf fa-2x text-primary mb-2"></i>
                            <h3 class="mb-0">{{ $documentTemplate->generations->count() }}</h3>
                            <small class="text-muted">Documents générés</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-download fa-2x text-success mb-2"></i>
                            <h3 class="mb-0">{{ $documentTemplate->generations->sum('download_count') }}</h3>
                            <small class="text-muted">Téléchargements</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-info">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                            <h3 class="mb-0">{{ $documentTemplate->generations->where('is_valid', true)->count() }}</h3>
                            <small class="text-muted">Documents valides</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Informations de base --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informations de base
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 30%;">
                                    <i class="fas fa-barcode"></i> Code
                                </td>
                                <td>
                                    <code class="text-primary">{{ $documentTemplate->code }}</code>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-tag"></i> Nom
                                </td>
                                <td><strong>{{ $documentTemplate->nom }}</strong></td>
                            </tr>
                            @if($documentTemplate->description)
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-align-left"></i> Description
                                </td>
                                <td>{{ $documentTemplate->description }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-file"></i> Type de document
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $documentTemplate->type_document_label }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Contexte d'utilisation --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-sitemap"></i> Contexte d'utilisation
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 30%;">
                                    <i class="fas fa-building"></i> Type d'organisation
                                </td>
                                <td>
                                    @if($documentTemplate->organisationType)
                                        <span class="badge bg-info">
                                            {{ $documentTemplate->organisationType->nom }}
                                        </span>
                                    @else
                                        <span class="text-muted">Non spécifié</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-cogs"></i> Type d'opération
                                </td>
                                <td>
                                    @if($documentTemplate->operationType)
                                        <span class="badge bg-warning text-dark">
                                            {{ $documentTemplate->operationType->nom }}
                                        </span>
                                    @else
                                        <span class="text-muted">Tous les types</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-stream"></i> Étape du workflow
                                </td>
                                <td>
                                    @if($documentTemplate->workflowStep)
                                        <span class="badge bg-primary">
                                            Étape {{ $documentTemplate->workflowStep->numero_passage }} - 
                                            {{ $documentTemplate->workflowStep->libelle }}
                                        </span>
                                    @else
                                        <span class="text-muted">Aucune étape spécifique</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Fichiers et configuration --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-file-code"></i> Fichiers et configuration
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width: 30%;">
                                    <i class="fas fa-file"></i> Template Blade
                                </td>
                                <td>
                                    <code class="text-primary">{{ $documentTemplate->template_path }}</code>
                                    @if($documentTemplate->templateExists())
                                        <i class="fas fa-check-circle text-success ms-2" 
                                           title="Fichier existe"></i>
                                    @else
                                        <i class="fas fa-exclamation-triangle text-danger ms-2" 
                                           title="Fichier introuvable"></i>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-layer-group"></i> Layout
                                </td>
                                <td>
                                    <code>{{ $documentTemplate->layout_path ?? 'Défaut' }}</code>
                                </td>
                            </tr>
                            @if($documentTemplate->signature_image)
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-signature"></i> Signature
                                </td>
                                <td>
                                    <code>{{ $documentTemplate->signature_image }}</code>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-file-pdf"></i> Format PDF
                                </td>
                                <td>
                                    {{ strtoupper($documentTemplate->getPdfFormat()) }} - 
                                    {{ ucfirst($documentTemplate->getPdfOrientation()) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Documents générés récemment --}}
            @if($documentTemplate->generations->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Documents générés récemment
                    </h5>
                    <a href="{{ route('admin.documents.index', ['template_id' => $documentTemplate->id]) }}" 
                       class="btn btn-sm btn-outline-primary">
                        Voir tous les documents
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>N° Document</th>
                                    <th>Organisation</th>
                                    <th>Généré le</th>
                                    <th class="text-center">Téléchargements</th>
                                    <th class="text-center">Statut</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documentTemplate->generations->take(5) as $generation)
                                <tr>
                                    <td>
                                        <code class="text-primary">{{ $generation->numero_document }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ $generation->organisation->nom }}</strong>
                                    </td>
                                    <td>
                                        <small>
                                            {{ $generation->generated_at->format('d/m/Y H:i') }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            {{ $generation->download_count }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($generation->is_valid)
                                            <span class="badge bg-success">Valide</span>
                                        @else
                                            <span class="badge bg-danger">Invalidé</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.documents.show', $generation) }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- Colonne latérale --}}
        <div class="col-lg-4">
            
            {{-- Options actives --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs"></i> Options actives
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-qrcode"></i> QR Code
                            </span>
                            @if($documentTemplate->has_qr_code)
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-times-circle text-muted"></i>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-certificate"></i> Filigrane
                            </span>
                            @if($documentTemplate->has_watermark)
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-times-circle text-muted"></i>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-signature"></i> Signature
                            </span>
                            @if($documentTemplate->has_signature)
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-times-circle text-muted"></i>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-magic"></i> Auto-génération
                            </span>
                            @if($documentTemplate->auto_generate)
                                <i class="fas fa-check-circle text-success"></i>
                            @else
                                <i class="fas fa-times-circle text-muted"></i>
                            @endif
                        </div>
                    </div>

                    @if($documentTemplate->auto_generate && $documentTemplate->generation_delay_hours > 0)
                    <div class="mb-0">
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> 
                            Délai : {{ $documentTemplate->generation_delay_hours }}h
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Variables disponibles --}}
            @if($documentTemplate->variables)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-code"></i> Variables disponibles
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($documentTemplate->variables as $category => $fields)
                        <div class="mb-3">
                            <h6 class="text-primary mb-2">{{ ucfirst($category) }}</h6>
                            <div class="ms-3">
                                @foreach($fields as $field)
                                    <code class="d-block small mb-1">
                                        {{ $category }}.{{ $field }}
                                    </code>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Métadonnées --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-info"></i> Métadonnées
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-calendar-plus"></i> Créé le
                                </td>
                                <td>{{ $documentTemplate->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-calendar-check"></i> Modifié le
                                </td>
                                <td>{{ $documentTemplate->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">
                                    <i class="fas fa-clock"></i> Il y a
                                </td>
                                <td>{{ $documentTemplate->updated_at->diffForHumans() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Actions --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks"></i> Actions
                    </h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.document-templates.edit', $documentTemplate) }}" 
                       class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-edit"></i> Modifier
                    </a>
                    <a href="{{ route('admin.document-templates.preview', $documentTemplate) }}" 
                       class="btn btn-outline-secondary w-100 mb-2"
                       target="_blank">
                        <i class="fas fa-search"></i> Prévisualiser
                    </a>
                    @if($documentTemplate->generations->isNotEmpty())
                        <a href="{{ route('admin.documents.index', ['template_id' => $documentTemplate->id]) }}" 
                           class="btn btn-outline-info w-100 mb-2">
                            <i class="fas fa-file-alt"></i> Voir les documents
                        </a>
                    @endif
                    <button type="button" 
                            class="btn btn-outline-danger w-100" 
                            onclick="confirmDelete()">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- Formulaire de suppression caché --}}
    <form id="delete-form" 
          action="{{ route('admin.document-templates.destroy', $documentTemplate) }}" 
          method="POST" 
          class="d-none">
        @csrf
        @method('DELETE')
    </form>

</div>

@push('scripts')
<script>
function confirmDelete() {
    const documentsCount = {{ $documentTemplate->generations->count() }};
    
    let message = `Êtes-vous sûr de vouloir supprimer le template "${document.querySelector('h2').textContent.trim()}" ?\n\n`;
    
    if (documentsCount > 0) {
        message += `⚠️ ATTENTION : ${documentsCount} document(s) ont été générés avec ce template.\n\n`;
        message += `Cette action est irréversible.`;
    } else {
        message += `Cette action est irréversible.`;
    }
    
    if (confirm(message)) {
        document.getElementById('delete-form').submit();
    }
}

// Fonction pour télécharger le PDF
function downloadPDF() {
    // Afficher un loader
    const loader = document.createElement('div');
    loader.className = 'position-fixed top-50 start-50 translate-middle';
    loader.style.zIndex = '9999';
    loader.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Génération en cours...</span>
        </div>
        <p class="mt-2 text-center">Génération du PDF...</p>
    `;
    document.body.appendChild(loader);
    
    // Appel AJAX pour générer le PDF
    fetch('{{ route("admin.document-templates.preview.pdf", $documentTemplate) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur lors de la génération du PDF');
        }
        return response.blob();
    })
    .then(blob => {
        // Créer un lien de téléchargement
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'preview_{{ $documentTemplate->code }}_{{ date("YmdHis") }}.pdf';
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
        document.body.removeChild(loader);
    })
    .catch(error => {
        console.error('Erreur:', error);
        document.body.removeChild(loader);
        alert('Erreur lors de la génération du PDF. Veuillez réessayer.');
    });
}
</script>
@endpush

@endsection