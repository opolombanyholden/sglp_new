@extends('layouts.admin')

@section('title', 'Ajout d\'adhérents - ' . $organisation->nom)

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1">
                    <i class="fas fa-user-plus me-2 text-success"></i>
                    Ajout d'adhérents
                </h2>
                <p class="text-muted mb-0">Ajouter de nouveaux adhérents à l'organisation</p>
            </div>
            <a href="{{ route('admin.operations.select-operation', $organisation->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>

        <!-- Carte organisation -->
        <div class="card mb-4 border-success">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar-circle bg-success text-white">
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

            <!-- Liste des adhérents à ajouter -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-users me-2"></i> Nouveaux adhérents</h5>
                    <button type="button" class="btn btn-light btn-sm" onclick="addAdherent()">
                        <i class="fas fa-plus me-1"></i> Ajouter un adhérent
                    </button>
                </div>
                <div class="card-body">
                    <div id="adherents-container">
                        <!-- Template adhérent -->
                        <div class="adherent-item border rounded p-3 mb-3" data-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Adhérent #1</h6>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeAdherent(this)"
                                    style="display: none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-2">
                                    <label class="form-label">Civilité <span class="text-danger">*</span></label>
                                    <select class="form-select" name="adherents[0][civilite]" required>
                                        <option value="">...</option>
                                        <option value="M.">M.</option>
                                        <option value="Mme">Mme</option>
                                        <option value="Mlle">Mlle</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="adherents[0][nom]" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">Prénoms <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="adherents[0][prenoms]" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Date de naissance</label>
                                    <input type="date" class="form-control" name="adherents[0][date_naissance]">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Lieu de naissance</label>
                                    <input type="text" class="form-control" name="adherents[0][lieu_naissance]">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Nationalité</label>
                                    <input type="text" class="form-control" name="adherents[0][nationalite]"
                                        value="Gabonaise">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">N° CNI / Passeport</label>
                                    <input type="text" class="form-control" name="adherents[0][numero_identite]">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Profession</label>
                                    <input type="text" class="form-control" name="adherents[0][profession]">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" name="adherents[0][telephone]">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="adherents[0][email]">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Adresse</label>
                                    <input type="text" class="form-control" name="adherents[0][adresse]">
                                </div>
                            </div>
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
                        <button type="button" class="btn btn-success" onclick="submitFormWithAction('soumettre')">
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

        .adherent-item {
            background: #f8f9fa;
        }
    </style>

    <script>
        let adherentIndex = 0;

        function addAdherent() {
            adherentIndex++;
            const container = document.getElementById('adherents-container');
            const template = container.querySelector('.adherent-item').cloneNode(true);

            // Mettre à jour l'index
            template.dataset.index = adherentIndex;
            template.querySelector('h6').textContent = `Adhérent #${adherentIndex + 1}`;

            // Mettre à jour les noms des champs
            template.querySelectorAll('input, select').forEach(input => {
                const name = input.name.replace(/\[0\]/g, `[${adherentIndex}]`);
                input.name = name;
                input.value = '';
            });

            // Afficher le bouton supprimer
            template.querySelector('button[onclick*="removeAdherent"]').style.display = 'inline-block';

            container.appendChild(template);
            updateRemoveButtons();
        }

        function removeAdherent(button) {
            const item = button.closest('.adherent-item');
            item.remove();
            updateNumbers();
            updateRemoveButtons();
        }

        function updateNumbers() {
            const items = document.querySelectorAll('.adherent-item');
            items.forEach((item, index) => {
                item.querySelector('h6').textContent = `Adhérent #${index + 1}`;
            });
        }

        function updateRemoveButtons() {
            const items = document.querySelectorAll('.adherent-item');
            items.forEach((item, index) => {
                const btn = item.querySelector('button[onclick*="removeAdherent"]');
                btn.style.display = items.length > 1 ? 'inline-block' : 'none';
            });
        }

        function submitFormWithAction(action) {
            document.getElementById('formAction').value = action;
            document.querySelector('form').submit();
        }
    </script>
@endsection