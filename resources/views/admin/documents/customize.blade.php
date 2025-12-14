@extends('layouts.admin')

@section('title', 'Personnaliser le Document')

@section('content')
    <div class="container-fluid py-4">

        {{-- En-tête --}}
        <div class="row mb-4">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dossiers.index') }}">Dossiers</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dossiers.show', $dossier) }}">
                                {{ $dossier->numero_dossier }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Personnaliser {{ $template->nom }}</li>
                    </ol>
                </nav>
                <h2 class="mb-1">
                    <i class="fas fa-edit text-primary"></i> Personnaliser le Document
                </h2>
                <p class="text-muted">
                    Template : <code>{{ $template->nom }}</code> pour le dossier
                    <strong>{{ $dossier->numero_dossier }}</strong>
                </p>
            </div>
        </div>

        {{-- Formulaire --}}
        <form action="{{ route('admin.documents.save-customization', $dossier) }}" method="POST" id="customizationForm">
            @csrf
            <input type="hidden" name="template_id" value="{{ $template->id }}">

            <div class="row">
                {{-- Colonne principale --}}
                <div class="col-lg-8">

                    {{-- En-tête du document --}}
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-heading"></i> En-tête du Document
                            </h5>
                        </div>
                        <div class="card-body">
                            <textarea name="header_text" id="header_text" class="form-control wysiwyg-editor"
                                rows="6">{!! old('header_text', $headerText) !!}</textarea>
                            <small class="form-text text-muted mt-2 d-block">
                                Ce texte sera affiché en haut du document généré. Vous pouvez le modifier selon vos besoins.
                            </small>
                        </div>
                    </div>

                    {{-- Signature --}}
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-signature"></i> Signature du Document
                            </h5>
                        </div>
                        <div class="card-body">
                            <textarea name="signature_text" id="signature_text" class="form-control wysiwyg-editor"
                                rows="6">{!! old('signature_text', $signatureText) !!}</textarea>
                            <small class="form-text text-muted mt-2 d-block">
                                Texte de signature (nom, titre, fonction) qui apparaîtra en bas du document.
                            </small>
                        </div>
                    </div>

                </div>

                {{-- Colonne latérale --}}
                <div class="col-lg-4">

                    {{-- Informations --}}
                    <div class="card mb-4 bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-info-circle text-info"></i> Informations
                            </h6>
                            <ul class="small mb-0">
                                <li class="mb-2">
                                    <strong>Dossier</strong> : {{ $dossier->numero_dossier }}
                                </li>
                                <li class="mb-2">
                                    <strong>Organisation</strong> : {{ $dossier->organisation->nom ?? 'N/A' }}
                                </li>
                                <li class="mb-2">
                                    <strong>Type</strong> : {{ $template->type_document_label }}
                                </li>
                                <li class="mb-0">
                                    <strong>Template</strong> : {{ $template->code }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Aide --}}
                    <div class="card bg-warning bg-opacity-10 border-warning">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-lightbulb text-warning"></i> Conseils
                            </h6>
                            <ul class="small mb-0">
                                <li class="mb-2">Utilisez l'éditeur pour formater votre texte</li>
                                <li class="mb-2">Les modifications s'appliquent uniquement à ce document</li>
                                <li class="mb-0">Vous pouvez revenir aux valeurs par défaut du template</li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Actions --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.dossiers.show', $dossier) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-file-pdf"></i> Générer le Document
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </form>

    </div>

    @push('scripts')
        <script>
            // Initialize CKEditor for WYSIWYG fields
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.wysiwyg-editor').forEach(function (textarea) {
                    CKEDITOR.replace(textarea.id, {
                        height: 250,
                        language: 'fr',
                        toolbar: [
                            { name: 'document', items: ['Source'] },
                            { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', '-', 'Undo', 'Redo'] },
                            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
                            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight'] },
                            { name: 'links', items: ['Link', 'Unlink'] },
                            { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                            { name: 'colors', items: ['TextColor', 'BGColor'] }
                        ],
                        removePlugins: 'image,flash,iframe',
                        allowedContent: true
                    });
                });
            });
        </script>
    @endpush

@endsection