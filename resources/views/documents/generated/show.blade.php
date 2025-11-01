@extends('layouts.app')

@section('title', 'Détails du Document')

@section('content')
<div class="container-fluid py-4">
    
    {{-- En-tête --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.documents.index') }}">Documents</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $documentGeneration->numero_document }}</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-file-pdf text-primary"></i> Document Officiel
                    </h2>
                    <p class="text-muted mb-0">
                        <code class="text-primary">{{ $documentGeneration->numero_document }}</code>
                        @if($documentGeneration->is_valid)
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-check-circle"></i> Valide
                            </span>
                        @else
                            <span class="badge bg-danger ms-2">
                                <i class="fas fa-times-circle"></i> Invalidé
                            </span>
                        @endif
                    </p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.documents.download', $documentGeneration) }}" 
                       class="btn btn-primary"
                       target="_blank">
                        <i class="fas fa-download"></i> Télécharger
                    </a>
                    <a href="{{ route('document.verify', $documentGeneration->verification_token) }}" 
                       class="btn btn-outline-secondary"
                       target="_blank">
                        <i class="fas fa-shield-alt"></i> Vérifier
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($documentGeneration->is_valid)
                                <li>
                                    <a class="dropdown-item text-warning" 
                                       href="#" 
                                       onclick="invalidateDocument(); return false;">
                                        <i class="fas fa-ban"></i> Invalider
                                    </a>
                                </li>
                            @else
                                <li>
                                    <a class="dropdown-item text-success" 
                                       href="#" 
                                       onclick="revalidateDocument(); return false;">
                                        <i class="fas fa-check-circle"></i> Réactiver
                                    </a>
                                </li>
                            @endif
                            <li>
                                <a class="dropdown-item" 
                                   href="#" 
                                   onclick="regenerateDocument(); return false;">
                                    <i class="fas fa-redo"></i> Régénérer
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" 
                                   href="#" 
                                   onclick="deleteDocument(); return false;">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </li>
                        </ul>
                    </div>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Colonne principale --}}
        <div class="col-lg-8">
            
            {{-- Informations du document --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informations du document
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">
                                            <i class="fas fa-barcode"></i> Numéro
                                        </td>
                                        <td>
                                            <code class="text-primary">{{ $documentGeneration->numero_document }}</code>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">
                                            <i class="fas fa-file"></i> Template
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.document-templates.show', $documentGeneration->documentTemplate) }}">
                                                {{ $documentGeneration->documentTemplate->nom }}
                                            </a>
                                            <br>
                                            <span class="badge bg-secondary">
                                                {{ $documentGeneration->documentTemplate->type_document_label }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">
                                            <i class="fas fa-calendar"></i> Généré le
                                        </td>
                                        <td>
                                            {{ $documentGeneration->generated_at->format('d/m/Y à H:i') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $documentGeneration->generated_at->diffForHumans() }}
                                            </small>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">
                                            <i class="fas fa-user"></i> Généré par
                                        </td>
                                        <td>
                                            @if($documentGeneration->generatedBy)
                                                {{ $documentGeneration->generatedBy->name }}
                                            @else
                                                <span class="text-muted">Système (automatique)</span>
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">
                                            <i class="fas fa-download"></i> Téléchargements
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $documentGeneration->download_count }}
                                            </span>
                                            @if($documentGeneration->last_downloaded_at)
                                                <br>
                                                <small class="text-muted">
                                                    Dernier : {{ $documentGeneration->last_downloaded_at->format('d/m/Y H:i') }}
                                                </small>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">
                                            <i class="fas fa-shield-alt"></i> Vérifications
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ $documentGeneration->verifications->count() }}
                                            </span>
                                            @if($documentGeneration->verifications->isNotEmpty())
                                                <br>
                                                <small class="text-muted">
                                                    Dernière : {{ $documentGeneration->verifications->first()->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">
                                            <i class="fas fa-toggle-on"></i> Statut
                                        </td>
                                        <td>
                                            @if($documentGeneration->is_valid)
                                                <span class="badge bg-success">Valide</span>
                                            @else
                                                <span class="badge bg-danger">Invalidé</span>
                                                @if($documentGeneration->invalidated_at)
                                                    <br>
                                                    <small class="text-muted">
                                                        Le {{ $documentGeneration->invalidated_at->format('d/m/Y à H:i') }}
                                                    </small>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    @if(!$documentGeneration->is_valid && $documentGeneration->invalidation_reason)
                                    <tr>
                                        <td class="text-muted">
                                            <i class="fas fa-comment"></i> Motif
                                        </td>
                                        <td>
                                            <span class="text-danger">{{ $documentGeneration->invalidation_reason }}</span>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Organisation --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-building"></i> Organisation concernée
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mb-3">{{ $documentGeneration->organisation->nom }}</h6>
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    @if($documentGeneration->organisation->sigle)
                                    <tr>
                                        <td class="text-muted" style="width: 30%;">Sigle</td>
                                        <td><strong>{{ $documentGeneration->organisation->sigle }}</strong></td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="text-muted">Type</td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $documentGeneration->organisation->organisationType->nom }}
                                            </span>
                                        </td>
                                    </tr>
                                    @if($documentGeneration->organisation->numero_recepisse)
                                    <tr>
                                        <td class="text-muted">N° Récépissé</td>
                                        <td>
                                            <code>{{ $documentGeneration->organisation->numero_recepisse }}</code>
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tbody>
                                    @if($documentGeneration->organisation->siege_social)
                                    <tr>
                                        <td class="text-muted" style="width: 30%;">Siège</td>
                                        <td>{{ $documentGeneration->organisation->siege_social }}</td>
                                    </tr>
                                    @endif
                                    @if($documentGeneration->organisation->telephone)
                                    <tr>
                                        <td class="text-muted">Téléphone</td>
                                        <td>{{ $documentGeneration->organisation->telephone }}</td>
                                    </tr>
                                    @endif
                                    @if($documentGeneration->organisation->email)
                                    <tr>
                                        <td class="text-muted">Email</td>
                                        <td>{{ $documentGeneration->organisation->email }}</td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.organisations.show', $documentGeneration->organisation) }}" 
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> Voir l'organisation
                        </a>
                    </div>
                </div>
            </div>

            {{-- Métadonnées du document --}}
            @if($documentGeneration->metadata)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-database"></i> Données du document
                    </h5>
                </div>
                <div class="card-body">
                    <div class="accordion" id="metadataAccordion">
                        @foreach($documentGeneration->metadata as $category => $data)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#metadata{{ $loop->index }}">
                                        <i class="fas fa-folder-open me-2"></i>
                                        <strong>{{ ucfirst($category) }}</strong>
                                    </button>
                                </h2>
                                <div id="metadata{{ $loop->index }}" 
                                     class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" 
                                     data-bs-parent="#metadataAccordion">
                                    <div class="accordion-body">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tbody>
                                                @foreach($data as $key => $value)
                                                    <tr>
                                                        <td class="text-muted" style="width: 40%;">
                                                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                        </td>
                                                        <td>
                                                            @if(is_array($value))
                                                                <code>{{ json_encode($value) }}</code>
                                                            @elseif(is_bool($value))
                                                                <span class="badge bg-{{ $value ? 'success' : 'secondary' }}">
                                                                    {{ $value ? 'Oui' : 'Non' }}
                                                                </span>
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- Historique des vérifications --}}
            @if($documentGeneration->verifications->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Historique des vérifications
                    </h5>
                    <span class="badge bg-primary">
                        {{ $documentGeneration->verifications->count() }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>IP</th>
                                    <th>Navigateur</th>
                                    <th>Résultat</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documentGeneration->verifications->take(10) as $verification)
                                <tr>
                                    <td>
                                        {{ $verification->created_at->format('d/m/Y H:i:s') }}
                                        <br>
                                        <small class="text-muted">
                                            {{ $verification->created_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        <code>{{ $verification->ip_address }}</code>
                                    </td>
                                    <td>
                                        <small>{{ Str::limit($verification->user_agent, 50) }}</small>
                                    </td>
                                    <td>
                                        @if($verification->is_valid)
                                            <span class="badge bg-success">Valide</span>
                                        @else
                                            <span class="badge bg-danger">Invalide</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($documentGeneration->verifications->count() > 10)
                    <div class="card-footer text-center">
                        <small class="text-muted">
                            Affichage des 10 dernières vérifications sur {{ $documentGeneration->verifications->count() }}
                        </small>
                    </div>
                @endif
            </div>
            @endif

        </div>

        {{-- Colonne latérale --}}
        <div class="col-lg-4">
            
            {{-- QR Code --}}
            @if($documentGeneration->qr_code_path || $documentGeneration->qr_code_url)
            <div class="card mb-4 text-center">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-qrcode"></i> QR Code de vérification
                    </h6>
                </div>
                <div class="card-body">
                    @if($documentGeneration->qr_code_path && file_exists(public_path($documentGeneration->qr_code_path)))
                        <img src="{{ asset($documentGeneration->qr_code_path) }}" 
                             alt="QR Code" 
                             class="img-fluid mb-3"
                             style="max-width: 200px;">
                    @else
                        <div class="bg-light p-4 mb-3">
                            <i class="fas fa-qrcode fa-5x text-muted"></i>
                        </div>
                    @endif
                    <p class="small text-muted mb-2">Scannez pour vérifier le document</p>
                    <a href="{{ route('document.verify', $documentGeneration->verification_token) }}" 
                       class="btn btn-sm btn-outline-primary"
                       target="_blank">
                        <i class="fas fa-external-link-alt"></i> Page de vérification
                    </a>
                </div>
            </div>
            @endif

            {{-- Token de vérification --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-key"></i> Token de vérification
                    </h6>
                </div>
                <div class="card-body">
                    <div class="input-group input-group-sm">
                        <input type="text" 
                               class="form-control font-monospace small" 
                               id="verificationToken"
                               value="{{ $documentGeneration->verification_token }}" 
                               readonly>
                        <button class="btn btn-outline-secondary" 
                                type="button"
                                onclick="copyToken()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        Identifiant unique pour vérification
                    </small>
                </div>
            </div>

            {{-- Fichiers --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-file"></i> Fichiers
                    </h6>
                </div>
                <div class="card-body">
                    @if($documentGeneration->pdf_path && file_exists(storage_path('app/' . $documentGeneration->pdf_path)))
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>
                                <i class="fas fa-file-pdf text-danger"></i> 
                                PDF
                            </span>
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i>
                            </span>
                        </div>
                        <small class="text-muted d-block">
                            {{ round(filesize(storage_path('app/' . $documentGeneration->pdf_path)) / 1024, 2) }} Ko
                        </small>
                    @else
                        <div class="alert alert-warning small mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            Fichier PDF introuvable
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-tasks"></i> Actions
                    </h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.documents.download', $documentGeneration) }}" 
                       class="btn btn-primary w-100 mb-2"
                       target="_blank">
                        <i class="fas fa-download"></i> Télécharger le PDF
                    </a>
                    <a href="{{ route('document.verify', $documentGeneration->verification_token) }}" 
                       class="btn btn-outline-secondary w-100 mb-2"
                       target="_blank">
                        <i class="fas fa-shield-alt"></i> Vérifier le document
                    </a>
                    @if($documentGeneration->is_valid)
                        <button type="button" 
                                class="btn btn-outline-warning w-100 mb-2"
                                onclick="invalidateDocument()">
                            <i class="fas fa-ban"></i> Invalider
                        </button>
                    @else
                        <button type="button" 
                                class="btn btn-outline-success w-100 mb-2"
                                onclick="revalidateDocument()">
                            <i class="fas fa-check-circle"></i> Réactiver
                        </button>
                    @endif
                    <button type="button" 
                            class="btn btn-outline-primary w-100 mb-2"
                            onclick="regenerateDocument()">
                        <i class="fas fa-redo"></i> Régénérer
                    </button>
                    <hr>
                    <button type="button" 
                            class="btn btn-outline-danger w-100"
                            onclick="deleteDocument()">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
            </div>

            {{-- Informations techniques --}}
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info"></i> Informations techniques
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">ID</td>
                                <td><code>{{ $documentGeneration->id }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Créé le</td>
                                <td>
                                    <small>{{ $documentGeneration->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Modifié le</td>
                                <td>
                                    <small>{{ $documentGeneration->updated_at->format('d/m/Y H:i') }}</small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Formulaires cachés --}}
    <form id="invalidate-form" 
          action="{{ route('admin.documents.invalidate', $documentGeneration) }}" 
          method="POST" 
          class="d-none">
        @csrf
        @method('PATCH')
    </form>

    <form id="revalidate-form" 
          action="{{ route('admin.documents.revalidate', $documentGeneration) }}" 
          method="POST" 
          class="d-none">
        @csrf
        @method('PATCH')
    </form>

    <form id="regenerate-form" 
          action="{{ route('admin.documents.regenerate', $documentGeneration) }}" 
          method="POST" 
          class="d-none">
        @csrf
    </form>

    <form id="delete-form" 
          action="{{ route('admin.documents.destroy', $documentGeneration) }}" 
          method="POST" 
          class="d-none">
        @csrf
        @method('DELETE')
    </form>

</div>

@push('scripts')
<script>
// Copier le token
function copyToken() {
    const tokenInput = document.getElementById('verificationToken');
    tokenInput.select();
    document.execCommand('copy');
    
    // Feedback visuel
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}

// Invalider le document
function invalidateDocument() {
    const reason = prompt('Motif d\'invalidation (optionnel) :');
    if (reason !== null) { // null si annulé
        const form = document.getElementById('invalidate-form');
        
        // Ajouter le motif si fourni
        if (reason.trim()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'reason';
            input.value = reason;
            form.appendChild(input);
        }
        
        if (confirm('Confirmer l\'invalidation du document {{ $documentGeneration->numero_document }} ?')) {
            form.submit();
        }
    }
}

// Réactiver le document
function revalidateDocument() {
    if (confirm('Réactiver le document {{ $documentGeneration->numero_document }} ?\n\nLe document redeviendra valide.')) {
        document.getElementById('revalidate-form').submit();
    }
}

// Régénérer le document
function regenerateDocument() {
    if (confirm('Régénérer le document {{ $documentGeneration->numero_document }} ?\n\nUn nouveau PDF sera créé avec les données actuelles.')) {
        document.getElementById('regenerate-form').submit();
    }
}

// Supprimer le document
function deleteDocument() {
    if (confirm('ATTENTION : Supprimer définitivement le document {{ $documentGeneration->numero_document }} ?\n\nCette action est irréversible.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>
@endpush

@endsection