@extends('layouts.admin')

@section('title', 'Modifier une Étape de Workflow')

@section('content')
    @php
        // Conversion des dates string en objets Carbon
        $createdAt = \Carbon\Carbon::parse($step->created_at);
        $updatedAt = \Carbon\Carbon::parse($step->updated_at);
    @endphp
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-edit text-warning"></i> Modifier l'Étape
                </h1>
                <p class="text-muted mb-0">
                    <code>{{ $step->code }}</code> - {{ $step->libelle }}
                </p>
            </div>
            <div>
                <a href="{{ route('admin.workflow-steps.show', $step->id) }}" class="btn btn-info me-2">
                    <i class="fas fa-eye"></i> Voir Détails
                </a>
                <a href="{{ route('admin.workflow-steps.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>
        </div>

        <!-- Messages d'erreur globaux -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5><i class="fas fa-exclamation-triangle"></i> Erreurs de validation</h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Informations système -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle"></i>
            <strong>Dernière modification :</strong> {{ $updatedAt->format('d/m/Y à H:i') }}
            @if($createdAt->ne($updatedAt))
                | <strong>Créé le :</strong> {{ $createdAt->format('d/m/Y à H:i') }}
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Formulaire -->
        <form action="{{ route('admin.workflow-steps.update', $step->id) }}" method="POST" id="editStepForm">
            @csrf
            @method('PUT')

            <div class="row">
                <!-- Colonne gauche -->
                <div class="col-lg-8">
                    <!-- Informations de base -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-warning text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-info-circle"></i> Informations de Base
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label">
                                        Code <span class="text-danger">*</span>
                                        <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip"
                                            title="Code unique en majuscules (ex: STEP_RECEPTION)"></i>
                                    </label>
                                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                                        name="code" value="{{ old('code', $step->code) }}" placeholder="STEP_RECEPTION"
                                        required maxlength="255" pattern="[A-Z0-9_]+" style="text-transform: uppercase;">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Lettres majuscules, chiffres et underscores
                                        uniquement</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="numero_passage" class="form-label">
                                        Numéro de Passage <span class="text-danger">*</span>
                                        <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip"
                                            title="Ordre d'exécution dans le workflow (1, 2, 3...)"></i>
                                    </label>
                                    <input type="number" class="form-control @error('numero_passage') is-invalid @enderror"
                                        id="numero_passage" name="numero_passage"
                                        value="{{ old('numero_passage', $step->numero_passage) }}" min="1" required>
                                    @error('numero_passage')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="libelle" class="form-label">
                                    Libellé <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('libelle') is-invalid @enderror" id="libelle"
                                    name="libelle" value="{{ old('libelle', $step->libelle) }}"
                                    placeholder="Ex: Réception et vérification initiale" required maxlength="255">
                                @error('libelle')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                    name="description" rows="3"
                                    placeholder="Description détaillée de cette étape et de ses objectifs">{{ old('description', $step->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Type d'organisation et opération -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-info text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-filter"></i> Contexte d'Application
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type_organisation" class="form-label">
                                        Type d'Organisation <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('type_organisation') is-invalid @enderror"
                                        id="type_organisation" name="type_organisation" required>
                                        <option value="">-- Sélectionner --</option>
                                        <option value="association"
                                            {{ old('type_organisation', $step->type_organisation) == 'association' ? 'selected' : '' }}>
                                            Association</option>
                                        <option value="ong"
                                            {{ old('type_organisation', $step->type_organisation) == 'ong' ? 'selected' : '' }}>
                                            ONG</option>
                                        <option value="parti_politique"
                                            {{ old('type_organisation', $step->type_organisation) == 'parti_politique' ? 'selected' : '' }}>
                                            Parti Politique</option>
                                        <option value="confession_religieuse"
                                            {{ old('type_organisation', $step->type_organisation) == 'confession_religieuse' ? 'selected' : '' }}>
                                            Confession Religieuse</option>
                                    </select>
                                    @error('type_organisation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="type_operation" class="form-label">
                                        Type d'Opération <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('type_operation') is-invalid @enderror"
                                        id="type_operation" name="type_operation" required>
                                        <option value="">-- Sélectionner --</option>
                                        <option value="creation"
                                            {{ old('type_operation', $step->type_operation) == 'creation' ? 'selected' : '' }}>
                                            Création</option>
                                        <option value="modification"
                                            {{ old('type_operation', $step->type_operation) == 'modification' ? 'selected' : '' }}>
                                            Modification</option>
                                        <option value="cessation"
                                            {{ old('type_operation', $step->type_operation) == 'cessation' ? 'selected' : '' }}>
                                            Cessation</option>
                                        <option value="ajout_adherent"
                                            {{ old('type_operation', $step->type_operation) == 'ajout_adherent' ? 'selected' : '' }}>
                                            Ajout Adhérent</option>
                                        <option value="retrait_adherent"
                                            {{ old('type_operation', $step->type_operation) == 'retrait_adherent' ? 'selected' : '' }}>
                                            Retrait Adhérent</option>
                                        <option value="declaration_activite"
                                            {{ old('type_operation', $step->type_operation) == 'declaration_activite' ? 'selected' : '' }}>
                                            Déclaration d'Activité</option>
                                        <option value="changement_statutaire"
                                            {{ old('type_operation', $step->type_operation) == 'changement_statutaire' ? 'selected' : '' }}>
                                            Changement Statutaire</option>
                                    </select>
                                    @error('type_operation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paramètres avancés -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-secondary text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-cogs"></i> Paramètres Avancés
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="delai_traitement" class="form-label">
                                        Délai de Traitement (heures) <span class="text-danger">*</span>
                                        <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip"
                                            title="Délai maximum pour traiter un dossier à cette étape"></i>
                                    </label>
                                    <input type="number"
                                        class="form-control @error('delai_traitement') is-invalid @enderror"
                                        id="delai_traitement" name="delai_traitement"
                                        value="{{ old('delai_traitement', $step->delai_traitement) }}" min="1" required>
                                    @error('delai_traitement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Par défaut: 48 heures (2 jours)</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="template_document" class="form-label">
                                        Template de Document
                                        <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip"
                                            title="Sélectionnez un template si cette étape génère un document"></i>
                                    </label>
                                    <select class="form-select @error('template_document') is-invalid @enderror"
                                        id="template_document" name="template_document">
                                        <option value="">-- Aucun template --</option>
                                        @foreach($templates as $template)
                                            <option value="{{ $template->code }}"
                                                {{ old('template_document', $step->template_document) == $template->code ? 'selected' : '' }}>
                                                {{ $template->nom }} ({{ $template->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('template_document')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Sélectionnez un template ou laissez vide</small>
                                </div>
                            </div>

                            <!-- Options booléennes -->
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="permet_rejet"
                                            name="permet_rejet" value="1"
                                            {{ old('permet_rejet', $step->permet_rejet) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permet_rejet">
                                            <strong>Permet le Rejet</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Autoriser le rejet du dossier à cette étape
                                    </small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="permet_commentaire"
                                            name="permet_commentaire" value="1"
                                            {{ old('permet_commentaire', $step->permet_commentaire) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permet_commentaire">
                                            <strong>Permet les Commentaires</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Autoriser l'ajout de commentaires
                                    </small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="genere_document"
                                            name="genere_document" value="1"
                                            {{ old('genere_document', $step->genere_document) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="genere_document">
                                            <strong>Génère un Document</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Cette étape génère un document PDF
                                    </small>
                                </div>
                            </div>

                            <!-- Champs JSON -->
                            <div class="mb-3">
                                <label for="champs_requis" class="form-label">
                                    Champs Requis (JSON)
                                    <i class="fas fa-info-circle text-muted" data-bs-toggle="tooltip"
                                        title="Liste des champs obligatoires à cette étape (format JSON)"></i>
                                </label>
                                <textarea class="form-control font-monospace @error('champs_requis') is-invalid @enderror"
                                    id="champs_requis" name="champs_requis" rows="4"
                                    placeholder='["nom_organisation", "adresse_siege", "numero_agrement"]'>{{ old('champs_requis', $step->champs_requis ? json_encode(json_decode($step->champs_requis), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                                @error('champs_requis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-code"></i> Format JSON valide requis. Laissez vide si aucun champ
                                    spécifique.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne droite -->
                <div class="col-lg-4">
                    <!-- Statut -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold">
                                <i class="fas fa-toggle-on"></i> Statut
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                    {{ old('is_active', $step->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <strong>Étape Active</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i>
                                Une étape active est utilisée dans le workflow
                            </small>
                        </div>
                    </div>

                    <!-- Statistiques -->
                    <div class="card shadow mb-4 border-left-info">
                        <div class="card-body">
                            <h6 class="text-info">
                                <i class="fas fa-chart-line"></i> Statistiques
                            </h6>
                            @php
                                $stats = DB::table('dossier_validations')
                                    ->where('workflow_step_id', $step->id)
                                    ->selectRaw('
                                                                COUNT(*) as total,
                                                                SUM(CASE WHEN decision = "approuve" THEN 1 ELSE 0 END) as approuves,
                                                                SUM(CASE WHEN decision = "rejete" THEN 1 ELSE 0 END) as rejetes,
                                                                AVG(TIMESTAMPDIFF(HOUR, created_at, decided_at)) as delai_moyen
                                                            ')
                                    ->first();
                            @endphp
                            <ul class="small mb-0">
                                <li class="mb-2"><strong>Dossiers traités :</strong> {{ $stats->total ?? 0 }}</li>
                                <li class="mb-2"><strong>Approuvés :</strong> {{ $stats->approuves ?? 0 }}</li>
                                <li class="mb-2"><strong>Rejetés :</strong> {{ $stats->rejetes ?? 0 }}</li>
                                <li><strong>Délai moyen :</strong>
                                    {{ $stats->delai_moyen ? round($stats->delai_moyen, 1) . 'h' : 'N/A' }}</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Aide -->
                    <div class="card shadow mb-4 border-left-primary">
                        <div class="card-body">
                            <h6 class="text-primary">
                                <i class="fas fa-lightbulb"></i> Aide
                            </h6>
                            <ul class="small mb-0">
                                <li class="mb-2">Attention : modifier le <strong>code</strong> peut affecter les références
                                    existantes</li>
                                <li class="mb-2">Le <strong>numéro de passage</strong> détermine l'ordre d'exécution</li>
                                <li class="mb-2">Désactiver une étape la retire du workflow actif</li>
                                <li>Les modifications s'appliquent immédiatement aux nouveaux dossiers</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card shadow">
                        <div class="card-body">
                            <button type="submit" class="btn btn-warning btn-block w-100 mb-2">
                                <i class="fas fa-save"></i> Enregistrer les Modifications
                            </button>
                            <a href="{{ route('admin.workflow-steps.show', $step->id) }}"
                                class="btn btn-info btn-block w-100 mb-2">
                                <i class="fas fa-eye"></i> Voir Détails
                            </a>
                            <a href="{{ route('admin.workflow-steps.index') }}" class="btn btn-secondary btn-block w-100">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            // Initialiser les tooltips Bootstrap 5
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Forcer la conversion en majuscules du code
            $('#code').on('input', function () {
                this.value = this.value.toUpperCase();
            });

            // Validation JSON en temps réel pour champs_requis
            $('#champs_requis').on('blur', function () {
                const value = $(this).val().trim();
                if (value === '') {
                    $(this).removeClass('is-invalid');
                    return;
                }

                try {
                    JSON.parse(value);
                    $(this).removeClass('is-invalid');
                } catch (e) {
                    $(this).addClass('is-invalid');
                    if (!$(this).next('.invalid-feedback').length) {
                        $(this).after('<div class="invalid-feedback d-block">JSON invalide. Vérifiez la syntaxe.</div>');
                    }
                }
            });

            // Afficher/masquer le champ template_document selon genere_document
            $('#genere_document').on('change', function () {
                const templateField = $('#template_document').closest('.col-md-6');
                if ($(this).is(':checked')) {
                    templateField.show();
                    $('#template_document').attr('required', true);
                } else {
                    templateField.hide();
                    $('#template_document').attr('required', false);
                }
            }).trigger('change');

            // Validation avant soumission
            $('#editStepForm').on('submit', function (e) {
                const code = $('#code').val();
                const pattern = /^[A-Z0-9_]+$/;

                if (!pattern.test(code)) {
                    e.preventDefault();
                    alert('Le code doit contenir uniquement des lettres majuscules, chiffres et underscores (_)');
                    $('#code').focus();
                    return false;
                }

                // Validation JSON si rempli
                const champsRequis = $('#champs_requis').val().trim();
                if (champsRequis !== '') {
                    try {
                        JSON.parse(champsRequis);
                    } catch (e) {
                        e.preventDefault();
                        alert('Le champ "Champs Requis" contient du JSON invalide');
                        $('#champs_requis').focus();
                        return false;
                    }
                }

                // Confirmation si modification majeure
                const confirmMsg = 'Voulez-vous vraiment enregistrer ces modifications ? Elles affecteront les nouveaux dossiers.';
                if (!confirm(confirmMsg)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
@endpush