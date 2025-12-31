@extends('layouts.admin')

@section('title', 'Sélection des champs à modifier')

@section('styles')
    <style>
        .field-selection-card {
            transition: all 0.3s ease;
            border: 2px solid transparent;
            cursor: pointer;
        }

        .field-selection-card:hover {
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .field-selection-card.selected {
            border-color: var(--primary);
            background: rgba(var(--primary-rgb), 0.05);
        }

        .field-category {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px 8px 0 0;
            padding: 1rem;
        }

        .field-category i {
            font-size: 1.5rem;
            margin-right: 0.5rem;
        }

        .field-list {
            padding: 1rem;
        }

        .field-item {
            padding: 0.75rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            transition: all 0.2s ease;
            border: 1px solid #e0e0e0;
        }

        .field-item:hover {
            background: #f8f9fa;
        }

        .field-item.selected {
            background: rgba(102, 126, 234, 0.1);
            border-color: #667eea;
        }

        .field-item label {
            margin: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .field-item input[type="checkbox"] {
            margin-right: 0.75rem;
            width: 18px;
            height: 18px;
        }

        .current-value {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.25rem;
            padding-left: 2rem;
        }

        .organisation-info-card {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
        }

        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .step.active .step-number {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .step.completed .step-number {
            background: #28a745;
            color: white;
        }

        .step-connector {
            width: 60px;
            height: 2px;
            background: #e0e0e0;
        }

        .selection-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            position: sticky;
            top: 1rem;
        }

        .selection-count {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="fas fa-edit text-primary me-2"></i>
                            Modification de l'organisation
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Tableau de bord</a>
                                </li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('admin.operations.select-organisation') }}">Opérations</a></li>
                                <li class="breadcrumb-item active">Sélection des champs</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="{{ route('admin.operations.select-operation', $organisation) }}"
                        class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Indicateur d'étapes -->
        <div class="step-indicator">
            <div class="step completed">
                <span class="step-number"><i class="fas fa-check"></i></span>
                <span>Organisation</span>
            </div>
            <div class="step-connector"></div>
            <div class="step active">
                <span class="step-number">2</span>
                <span>Champs à modifier</span>
            </div>
            <div class="step-connector"></div>
            <div class="step">
                <span class="step-number">3</span>
                <span>Formulaire</span>
            </div>
            <div class="step-connector"></div>
            <div class="step">
                <span class="step-number">4</span>
                <span>Validation</span>
            </div>
        </div>

        <div class="row">
            <!-- Informations organisation -->
            <div class="col-lg-4 mb-4">
                <div class="organisation-info-card">
                    <h5 class="mb-3">
                        <i class="fas fa-building me-2"></i>
                        Organisation sélectionnée
                    </h5>
                    <h4 class="mb-2">{{ $organisation->nom }}</h4>
                    @if($organisation->sigle)
                        <p class="mb-2"><strong>Sigle:</strong> {{ $organisation->sigle }}</p>
                    @endif
                    <p class="mb-2">
                        <strong>Type:</strong>
                        {{ $organisation->organisationType->libelle ?? 'N/A' }}
                    </p>
                    @if($organisation->numero_recepisse)
                        <p class="mb-0">
                            <strong>N° Récépissé:</strong> {{ $organisation->numero_recepisse }}
                        </p>
                    @endif
                </div>

                @if($dossierActuel)
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <i class="fas fa-info-circle me-2"></i>
                            Dossier actuel
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>N°:</strong> {{ $dossierActuel->numero_dossier }}</p>
                            <p class="mb-1"><strong>Version:</strong> {{ $dossierActuel->version ?? 1 }}</p>
                            <p class="mb-0"><strong>Statut:</strong>
                                <span class="badge bg-{{ $dossierActuel->statut_color }}">
                                    {{ $dossierActuel->statut_label }}
                                </span>
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Résumé de la sélection -->
                <div class="selection-summary mt-3">
                    <h6 class="mb-3"><i class="fas fa-check-square me-2"></i>Sélection</h6>
                    <div class="text-center mb-3">
                        <span class="selection-count" id="selectionCount">0</span>
                        <span class="d-block text-muted">champs sélectionnés</span>
                    </div>
                    <div id="selectedFieldsList" class="small">
                        <em class="text-muted">Aucun champ sélectionné</em>
                    </div>
                </div>
            </div>

            <!-- Formulaire de sélection -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>
                            Sélectionnez les informations à modifier
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Important:</strong> Sélectionnez uniquement les champs que vous souhaitez modifier.
                            Cette sélection sera enregistrée dans l'historique du dossier pour traçabilité.
                        </div>

                        <form action="{{ route('admin.operations.create', [$organisation, 'modification']) }}" method="GET"
                            id="fieldSelectionForm">
                            <div class="row">
                                @foreach($champsModifiables as $categoryKey => $category)
                                    <div class="col-md-6 mb-4">
                                        <div class="card h-100 field-selection-card">
                                            <div class="field-category">
                                                <i class="{{ $category['icon'] }}"></i>
                                                {{ $category['label'] }}
                                            </div>
                                            <div class="field-list">
                                                @foreach($category['champs'] as $fieldKey => $fieldLabel)
                                                    <div class="field-item" data-field="{{ $fieldKey }}">
                                                        <label>
                                                            <input type="checkbox" name="champs_modifies[]" value="{{ $fieldKey }}"
                                                                class="field-checkbox">
                                                            {{ $fieldLabel }}
                                                        </label>
                                                        @php
                                                            $currentValue = $organisation->{$fieldKey} ?? null;
                                                        @endphp
                                                        @if($currentValue)
                                                            <div class="current-value">
                                                                <i class="fas fa-arrow-right me-1"></i>
                                                                Actuel:
                                                                <em>{{ is_array($currentValue) ? json_encode($currentValue) : Str::limit($currentValue, 50) }}</em>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-outline-secondary me-2" id="selectAllBtn">
                                    <i class="fas fa-check-double me-2"></i>Tout sélectionner
                                </button>
                                <button type="button" class="btn btn-outline-secondary me-2" id="deselectAllBtn">
                                    <i class="fas fa-times me-2"></i>Tout désélectionner
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg" id="continueBtn" disabled>
                                    <i class="fas fa-arrow-right me-2"></i>
                                    Continuer vers le formulaire
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const checkboxes = document.querySelectorAll('.field-checkbox');
            const continueBtn = document.getElementById('continueBtn');
            const selectAllBtn = document.getElementById('selectAllBtn');
            const deselectAllBtn = document.getElementById('deselectAllBtn');
            const selectionCount = document.getElementById('selectionCount');
            const selectedFieldsList = document.getElementById('selectedFieldsList');

            function updateSelection() {
                const checked = document.querySelectorAll('.field-checkbox:checked');
                const count = checked.length;

                selectionCount.textContent = count;
                continueBtn.disabled = count === 0;

                // Mettre à jour la liste des champs sélectionnés
                if (count === 0) {
                    selectedFieldsList.innerHTML = '<em class="text-muted">Aucun champ sélectionné</em>';
                } else {
                    let html = '<ul class="list-unstyled mb-0">';
                    checked.forEach(cb => {
                        const label = cb.closest('label').textContent.trim();
                        html += `<li><i class="fas fa-check text-success me-1"></i>${label}</li>`;
                    });
                    html += '</ul>';
                    selectedFieldsList.innerHTML = html;
                }

                // Mettre à jour le style des items
                document.querySelectorAll('.field-item').forEach(item => {
                    const checkbox = item.querySelector('.field-checkbox');
                    item.classList.toggle('selected', checkbox.checked);
                });
            }

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelection);
            });

            selectAllBtn.addEventListener('click', function () {
                checkboxes.forEach(cb => cb.checked = true);
                updateSelection();
            });

            deselectAllBtn.addEventListener('click', function () {
                checkboxes.forEach(cb => cb.checked = false);
                updateSelection();
            });

            // Permettre de cliquer sur l'item entier
            document.querySelectorAll('.field-item').forEach(item => {
                item.addEventListener('click', function (e) {
                    if (e.target.tagName !== 'INPUT') {
                        const checkbox = this.querySelector('.field-checkbox');
                        checkbox.checked = !checkbox.checked;
                        updateSelection();
                    }
                });
            });
        });
    </script>
@endsection