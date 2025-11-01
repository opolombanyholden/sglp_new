@extends('layouts.app')

@section('title', 'Prévisualisation du Template')

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
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.document-templates.show', $documentTemplate) }}">
                            {{ $documentTemplate->code }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">Prévisualisation</li>
                </ol>
            </nav>
            
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-search text-primary"></i> Prévisualisation
                    </h2>
                    <p class="text-muted mb-0">
                        <code class="text-primary">{{ $documentTemplate->code }}</code> - {{ $documentTemplate->nom }}
                    </p>
                </div>
                <div class="btn-group">
                    <button type="button" 
                            class="btn btn-outline-secondary"
                            onclick="window.print()">
                        <i class="fas fa-print"></i> Imprimer
                    </button>
                    <button type="button" 
                            class="btn btn-outline-primary"
                            onclick="generatePDF()">
                        <i class="fas fa-file-pdf"></i> Générer PDF
                    </button>
                    <a href="{{ route('admin.document-templates.show', $documentTemplate) }}" 
                       class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Alertes --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-exclamation-triangle"></i> Erreur de prévisualisation
            </h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        {{-- Colonne prévisualisation --}}
        <div class="col-lg-9">
            
            {{-- Zone de prévisualisation --}}
            <div class="card shadow-sm mb-4" id="preview-container">
                <div class="card-body p-0">
                    <div class="preview-document bg-white p-5" style="min-height: 800px;">
                        @if($previewContent)
                            {!! $previewContent !!}
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                <h4>Template introuvable</h4>
                                <p class="text-muted">
                                    Le fichier template 
                                    <code>{{ $documentTemplate->template_path }}</code> 
                                    n'existe pas ou contient des erreurs.
                                </p>
                                <a href="{{ route('admin.document-templates.edit', $documentTemplate) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Corriger le template
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- Colonne paramètres --}}
        <div class="col-lg-3">
            
            {{-- Informations template --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informations
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Template</td>
                                <td><code class="small">{{ $documentTemplate->code }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Type</td>
                                <td>
                                    <span class="badge bg-secondary small">
                                        {{ $documentTemplate->type_document_label }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Format</td>
                                <td>{{ strtoupper($documentTemplate->getPdfFormat()) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Orientation</td>
                                <td>{{ ucfirst($documentTemplate->getPdfOrientation()) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Options actives --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-cogs"></i> Options
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        @if($documentTemplate->has_qr_code)
                            <i class="fas fa-check-circle text-success"></i>
                        @else
                            <i class="fas fa-times-circle text-muted"></i>
                        @endif
                        <small>QR Code</small>
                    </div>
                    <div class="mb-2">
                        @if($documentTemplate->has_watermark)
                            <i class="fas fa-check-circle text-success"></i>
                        @else
                            <i class="fas fa-times-circle text-muted"></i>
                        @endif
                        <small>Filigrane</small>
                    </div>
                    <div class="mb-0">
                        @if($documentTemplate->has_signature)
                            <i class="fas fa-check-circle text-success"></i>
                        @else
                            <i class="fas fa-times-circle text-muted"></i>
                        @endif
                        <small>Signature</small>
                    </div>
                </div>
            </div>

            {{-- Données de test --}}
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-database"></i> Données de test
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted mb-2">
                        Cette prévisualisation utilise des données d'exemple pour illustrer le rendu du document.
                    </p>
                    <button type="button" 
                            class="btn btn-sm btn-outline-primary w-100"
                            data-bs-toggle="collapse" 
                            data-bs-target="#testDataCollapse">
                        <i class="fas fa-code"></i> Voir les données
                    </button>
                    
                    <div class="collapse mt-3" id="testDataCollapse">
                        <pre class="bg-light p-2 small" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode($testData ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</code></pre>
                    </div>
                </div>
            </div>

            {{-- Aide --}}
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="fas fa-lightbulb text-warning"></i> Astuce
                    </h6>
                    <p class="small mb-2">
                        Cette prévisualisation vous permet de vérifier le rendu du template avant de le mettre en production.
                    </p>
                    <ul class="small mb-0 ps-3">
                        <li>Vérifiez les espacements</li>
                        <li>Validez les variables</li>
                        <li>Testez l'impression</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

</div>

@push('styles')
<style>
/* Styles pour la prévisualisation */
.preview-document {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    font-family: 'Times New Roman', Times, serif;
    line-height: 1.6;
}

.preview-document h1,
.preview-document h2,
.preview-document h3 {
    color: #2c3e50;
}

.preview-document table {
    width: 100%;
    border-collapse: collapse;
}

.preview-document table td,
.preview-document table th {
    padding: 8px;
    border: 1px solid #ddd;
}

/* Styles pour l'impression */
@media print {
    .container-fluid,
    .breadcrumb,
    .btn-group,
    .col-lg-3,
    .card-header,
    nav,
    .alert {
        display: none !important;
    }
    
    .col-lg-9 {
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .preview-document {
        box-shadow: none !important;
        padding: 0 !important;
    }
    
    #preview-container {
        border: none !important;
        box-shadow: none !important;
    }
}

/* Animation de chargement */
.loading-spinner {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
}
</style>
@endpush

@push('scripts')
<script>
// Fonction pour générer le PDF
function generatePDF() {
    // Afficher un loader
    const loader = document.createElement('div');
    loader.className = 'loading-spinner';
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

// Mise en évidence des variables manquantes
document.addEventListener('DOMContentLoaded', function() {
    const previewContent = document.querySelector('.preview-document');
    if (previewContent) {
        // Détecter les variables non remplacées (format {{ variable }})
        const content = previewContent.innerHTML;
        const missingVars = content.match(/\{\{[^}]+\}\}/g);
        
        if (missingVars && missingVars.length > 0) {
            const alert = document.createElement('div');
            alert.className = 'alert alert-warning alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Variables non remplacées détectées :</strong>
                <ul class="mb-0 mt-2">
                    ${missingVars.map(v => `<li><code>${v}</code></li>`).join('')}
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.col-lg-9');
            container.insertBefore(alert, container.firstChild);
        }
    }
});
</script>
@endpush

@endsection