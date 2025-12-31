@extends('layouts.admin')

@section('title', 'Cessation - ' . $organisation->nom)

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1">
                    <i class="fas fa-ban me-2 text-danger"></i>
                    Cessation d'activité
                </h2>
                <p class="text-muted mb-0">Déclarer la cessation des activités de l'organisation</p>
            </div>
            <a href="{{ route('admin.operations.select-operation', $organisation->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>

        <!-- Alerte -->
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Attention :</strong> La cessation d'activité est une opération irréversible.
            Assurez-vous que toutes les obligations légales ont été remplies avant de soumettre ce dossier.
        </div>

        <!-- Carte organisation -->
        <div class="card mb-4 border-danger">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar-circle bg-danger text-white">
                            {{ strtoupper(substr($organisation->sigle ?? $organisation->nom, 0, 2)) }}
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-0">{{ $organisation->nom }}</h5>
                        <small class="text-muted">{{ $organisation->organisationType->libelle ?? 'N/A' }} |
                            {{ $organisation->numero_recepisse ?? 'N/A' }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <form action="{{ route('admin.operations.store', [$organisation->id, $operationType->code]) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" id="formAction" value="brouillon">

            <!-- Informations de cessation -->
            <div class="card mb-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informations de cessation</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="date_effet" class="form-label">Date d'effet de la cessation <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_effet" name="date_effet"
                                value="{{ old('date_effet') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="type_cessation" class="form-label">Type de cessation <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="type_cessation" name="type_cessation" required>
                                <option value="">Sélectionnez...</option>
                                <option value="volontaire" {{ old('type_cessation') == 'volontaire' ? 'selected' : '' }}>
                                    Cessation volontaire</option>
                                <option value="judiciaire" {{ old('type_cessation') == 'judiciaire' ? 'selected' : '' }}>
                                    Décision judiciaire</option>
                                <option value="fusion" {{ old('type_cessation') == 'fusion' ? 'selected' : '' }}>Fusion avec
                                    une autre organisation</option>
                                <option value="absorption" {{ old('type_cessation') == 'absorption' ? 'selected' : '' }}>
                                    Absorption par une autre organisation</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="motif_cessation" class="form-label">Motif détaillé de la cessation <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="motif_cessation" name="motif_cessation" rows="4" required
                                placeholder="Décrivez les raisons de la cessation d'activité...">{{ old('motif_cessation') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="situation_financiere" class="form-label">Situation financière à la cessation</label>
                            <textarea class="form-control" id="situation_financiere" name="situation_financiere" rows="3"
                                placeholder="Décrivez la situation financière (solde de comptes, dettes éventuelles, etc.)">{{ old('situation_financiere') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="sort_biens" class="form-label">Sort des biens et patrimoine</label>
                            <textarea class="form-control" id="sort_biens" name="sort_biens" rows="3"
                                placeholder="Que deviendront les biens de l'organisation ?">{{ old('sort_biens') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents à fournir -->
            @if($documentTypes->isNotEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-paperclip me-2"></i> Documents à fournir</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($documentTypes as $docType)
                                <div class="col-md-6">
                                    <label for="doc_{{ $docType->id }}" class="form-label">
                                        {{ $docType->nom }}
                                        @if($docType->pivot->is_obligatoire)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    <input type="file" class="form-control" id="doc_{{ $docType->id }}"
                                        name="documents[{{ $docType->id }}]"
                                        {{ $docType->pivot->is_obligatoire ? 'required' : '' }}>
                                    @if($docType->description)
                                        <small class="form-text text-muted">{{ $docType->description }}</small>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Boutons d'action -->
            <div class="card">
                <div class="card-body d-flex justify-content-between">
                    <a href="{{ route('admin.operations.select-operation', $organisation->id) }}"
                        class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Annuler
                    </a>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-warning" onclick="submitFormWithAction('brouillon')">
                            <i class="fas fa-save me-1"></i> Enregistrer Brouillon
                        </button>
                        <button type="button" class="btn btn-danger" onclick="submitFormWithAction('soumettre')">
                            <i class="fas fa-paper-plane me-1"></i> Soumettre
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .avatar-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }
    </style>

    <script>
        function submitFormWithAction(action) {
            if (action === 'soumettre') {
                if (!confirm('Êtes-vous sûr de vouloir soumettre ce dossier de cessation ? Cette action ne peut pas être annulée une fois validée.')) {
                    return;
                }
            }
            document.getElementById('formAction').value = action;
            document.querySelector('form').submit();
        }
    </script>
@endsection