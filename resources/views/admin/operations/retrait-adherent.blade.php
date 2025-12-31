@extends('layouts.admin')

@section('title', 'Retrait d\'adhérents - ' . $organisation->nom)

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1">
                    <i class="fas fa-user-minus me-2 text-warning"></i>
                    Retrait d'adhérents
                </h2>
                <p class="text-muted mb-0">Retirer des adhérents de l'organisation</p>
            </div>
            <a href="{{ route('admin.operations.select-operation', $organisation->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>

        <!-- Carte organisation -->
        <div class="card mb-4 border-warning">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar-circle bg-warning text-dark">
                            {{ strtoupper(substr($organisation->sigle ?? $organisation->nom, 0, 2)) }}
                        </div>
                    </div>
                    <div class="col">
                        <h5 class="mb-0">{{ $organisation->nom }}</h5>
                        <small class="text-muted">
                            {{ $organisation->organisationType->libelle ?? 'N/A' }} |
                            {{ $organisation->adherents->count() }} adhérents actuels
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <form action="{{ route('admin.operations.store', [$organisation->id, $operationType->code]) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="action" id="formAction" value="brouillon">

            <!-- Liste des adhérents -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i> Sélectionner les adhérents à retirer</h5>
                </div>
                <div class="card-body">
                    @if($organisation->adherents->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucun adhérent enregistré pour cette organisation.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Nom complet</th>
                                        <th>Date de naissance</th>
                                        <th>Profession</th>
                                        <th>Téléphone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($organisation->adherents as $adherent)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input adherent-checkbox"
                                                    name="adherents_ids[]" value="{{ $adherent->id }}">
                                            </td>
                                            <td>
                                                <strong>{{ $adherent->civilite }} {{ $adherent->nom }}
                                                    {{ $adherent->prenoms }}</strong>
                                            </td>
                                            <td>{{ $adherent->date_naissance ? date('d/m/Y', strtotime($adherent->date_naissance)) : 'N/A' }}
                                            </td>
                                            <td>{{ $adherent->profession ?? 'N/A' }}</td>
                                            <td>{{ $adherent->telephone ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <span id="selectedCount">0</span> adhérent(s) sélectionné(s) sur
                                {{ $organisation->adherents->count() }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Motif du retrait -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comment me-2"></i> Motif du retrait</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="type_retrait" class="form-label">Type de retrait <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="type_retrait" name="type_retrait" required>
                                <option value="">Sélectionnez...</option>
                                <option value="demission">Démission volontaire</option>
                                <option value="exclusion">Exclusion</option>
                                <option value="deces">Décès</option>
                                <option value="radiation">Radiation pour non-paiement</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date_effet" class="form-label">Date d'effet</label>
                            <input type="date" class="form-control" id="date_effet" name="date_effet"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label for="motif_retrait" class="form-label">Motif détaillé <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="motif_retrait" name="motif_retrait" rows="3" required
                                placeholder="Expliquez les raisons du retrait de ces adhérents...">{{ old('motif_retrait') }}</textarea>
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
                                        name="documents[{{ $docType->id }}]">
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
                        <button type="button" class="btn btn-secondary" onclick="submitFormWithAction('brouillon')">
                            <i class="fas fa-save me-1"></i> Enregistrer Brouillon
                        </button>
                        <button type="button" class="btn btn-warning" onclick="submitFormWithAction('soumettre')">
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
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.adherent-checkbox');
            const countSpan = document.getElementById('selectedCount');

            selectAll.addEventListener('change', function () {
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateCount();
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateCount);
            });

            function updateCount() {
                const checked = document.querySelectorAll('.adherent-checkbox:checked').length;
                countSpan.textContent = checked;
            }
        });

        function submitFormWithAction(action) {
            const selected = document.querySelectorAll('.adherent-checkbox:checked').length;
            if (selected === 0) {
                alert('Veuillez sélectionner au moins un adhérent à retirer.');
                return;
            }
            document.getElementById('formAction').value = action;
            document.querySelector('form').submit();
        }
    </script>
@endsection