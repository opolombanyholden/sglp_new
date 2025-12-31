@extends('layouts.admin')

@section('title', 'Déclaration d\'activité - ' . $organisation->nom)

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1">
                    <i class="fas fa-file-alt me-2 text-primary"></i>
                    Déclaration d'activité
                </h2>
                <p class="text-muted mb-0">Déclarer les activités réalisées par l'organisation</p>
            </div>
            <a href="{{ route('admin.operations.select-operation', $organisation->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>

        <!-- Carte organisation -->
        <div class="card mb-4 border-primary">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar-circle bg-primary text-white">
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

            <!-- Période de déclaration -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-calendar me-2"></i> Période de déclaration</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="type_periode" class="form-label">Type de période <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="type_periode" name="periode[type]" required>
                                <option value="">Sélectionnez...</option>
                                <option value="annuelle">Déclaration annuelle</option>
                                <option value="semestrielle">Déclaration semestrielle</option>
                                <option value="trimestrielle">Déclaration trimestrielle</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="annee" class="form-label">Année <span class="text-danger">*</span></label>
                            <select class="form-select" id="annee" name="periode[annee]" required>
                                @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="periode" class="form-label">Période spécifique</label>
                            <select class="form-select" id="periode" name="periode[numero]">
                                <option value="">Année complète</option>
                                <option value="S1">1er semestre</option>
                                <option value="S2">2ème semestre</option>
                                <option value="T1">1er trimestre</option>
                                <option value="T2">2ème trimestre</option>
                                <option value="T3">3ème trimestre</option>
                                <option value="T4">4ème trimestre</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activités réalisées -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i> Activités réalisées</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="resume_activites" class="form-label">Résumé des activités <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="resume_activites" name="activites[resume]" rows="4" required
                                placeholder="Décrivez les principales activités réalisées durant cette période...">{{ old('activites.resume') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="nb_reunions" class="form-label">Nombre de réunions</label>
                            <input type="number" class="form-control" id="nb_reunions" name="activites[nb_reunions]" min="0"
                                value="0">
                        </div>
                        <div class="col-md-6">
                            <label for="nb_evenements" class="form-label">Nombre d'événements organisés</label>
                            <input type="number" class="form-control" id="nb_evenements" name="activites[nb_evenements]"
                                min="0" value="0">
                        </div>
                        <div class="col-12">
                            <label for="evenements_details" class="form-label">Détails des événements majeurs</label>
                            <textarea class="form-control" id="evenements_details" name="activites[evenements_details]"
                                rows="3"
                                placeholder="Listez les événements importants (conférences, formations, cérémonies, etc.)">{{ old('activites.evenements_details') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="projets" class="form-label">Projets en cours ou réalisés</label>
                            <textarea class="form-control" id="projets" name="activites[projets]" rows="3"
                                placeholder="Décrivez les projets en cours ou réalisés durant cette période...">{{ old('activites.projets') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bilan financier -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-coins me-2"></i> Bilan financier (optionnel)</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="recettes" class="form-label">Recettes totales (FCFA)</label>
                            <input type="number" class="form-control" id="recettes" name="bilan[recettes]" min="0"
                                value="0">
                        </div>
                        <div class="col-md-4">
                            <label for="depenses" class="form-label">Dépenses totales (FCFA)</label>
                            <input type="number" class="form-control" id="depenses" name="bilan[depenses]" min="0"
                                value="0">
                        </div>
                        <div class="col-md-4">
                            <label for="solde" class="form-label">Solde (FCFA)</label>
                            <input type="number" class="form-control" id="solde" name="bilan[solde]" readonly>
                        </div>
                        <div class="col-12">
                            <label for="observations_financieres" class="form-label">Observations financières</label>
                            <textarea class="form-control" id="observations_financieres" name="bilan[observations]" rows="2"
                                placeholder="Commentaires sur la situation financière...">{{ old('bilan.observations') }}</textarea>
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
                        <button type="button" class="btn btn-warning" onclick="submitFormWithAction('brouillon')">
                            <i class="fas fa-save me-1"></i> Enregistrer Brouillon
                        </button>
                        <button type="button" class="btn btn-primary" onclick="submitFormWithAction('soumettre')">
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
            const recettes = document.getElementById('recettes');
            const depenses = document.getElementById('depenses');
            const solde = document.getElementById('solde');

            function updateSolde() {
                const r = parseInt(recettes.value) || 0;
                const d = parseInt(depenses.value) || 0;
                solde.value = r - d;
            }

            recettes.addEventListener('input', updateSolde);
            depenses.addEventListener('input', updateSolde);
        });

        function submitFormWithAction(action) {
            document.getElementById('formAction').value = action;
            document.querySelector('form').submit();
        }
    </script>
@endsection