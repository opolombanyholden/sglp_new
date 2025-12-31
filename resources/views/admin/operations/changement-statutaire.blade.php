@extends('layouts.admin')

@section('title', 'Changement statutaire - ' . $organisation->nom)

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="page-title mb-1">
                    <i class="fas fa-gavel me-2 text-purple"></i>
                    Changement statutaire
                </h2>
                <p class="text-muted mb-0">Modifier les statuts de l'organisation</p>
            </div>
            <a href="{{ route('admin.operations.select-operation', $organisation->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour
            </a>
        </div>

        <!-- Carte organisation -->
        <div class="card mb-4" style="border-color: #7c3aed;">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="avatar-circle text-white" style="background: #7c3aed;">
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

            <!-- Informations sur le changement -->
            <div class="card mb-4">
                <div class="card-header text-white" style="background: #7c3aed;">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Description du changement</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="type_changement" class="form-label">Type de changement <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="type_changement" name="type_changement" required>
                                <option value="">Sélectionnez...</option>
                                <option value="modification_objet">Modification de l'objet social</option>
                                <option value="modification_denomination">Changement de dénomination</option>
                                <option value="modification_siege">Transfert de siège social</option>
                                <option value="modification_organes">Modification des organes dirigeants</option>
                                <option value="modification_conditions">Modification des conditions d'adhésion</option>
                                <option value="modification_multiple">Modifications multiples</option>
                                <option value="refonte_totale">Refonte totale des statuts</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date_ag" class="form-label">Date de l'Assemblée Générale <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_ag" name="date_ag" required>
                            <small class="form-text text-muted">Date à laquelle les modifications ont été votées</small>
                        </div>
                        <div class="col-12">
                            <label for="description_changements" class="form-label">Description détaillée des modifications
                                <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="description_changements" name="description_changements"
                                rows="5" required
                                placeholder="Décrivez précisément les articles modifiés et les changements apportés...">{{ old('description_changements') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Articles modifiés - Section dynamique -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i> Articles modifiés <span
                            class="text-danger">*</span></h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Important :</strong> Précisez chaque article modifié avec son contenu avant et après
                        modification.
                        Cela permet une traçabilité complète des changements apportés aux statuts ou au règlement intérieur.
                    </div>

                    <!-- Document concerné -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Document concerné <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="doc_statuts"
                                    name="documents_concernes[]" value="statuts" checked>
                                <label class="form-check-label" for="doc_statuts">
                                    <i class="fas fa-book me-1"></i> Statuts
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="doc_reglement"
                                    name="documents_concernes[]" value="reglement_interieur">
                                <label class="form-check-label" for="doc_reglement">
                                    <i class="fas fa-list-alt me-1"></i> Règlement intérieur
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Conteneur des articles modifiés -->
                    <div id="articles-container">
                        <!-- Premier article (modèle) -->
                        <div class="article-modification-item border rounded p-3 mb-3 bg-light" data-index="0">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-paragraph me-2"></i>
                                    <span class="article-number">Article #1</span>
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-article"
                                    style="display: none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Document</label>
                                    <select class="form-select form-select-sm" name="articles[0][document]">
                                        <option value="statuts">Statuts</option>
                                        <option value="reglement_interieur">Règlement intérieur</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Numéro/Référence de l'article <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="articles[0][numero]"
                                        placeholder="Ex: Article 5, Titre II - Art. 3" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Titre/Intitulé</label>
                                    <input type="text" class="form-control form-control-sm" name="articles[0][titre]"
                                        placeholder="Ex: Conditions d'adhésion">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-arrow-left text-danger me-1"></i>
                                        Ancienne rédaction <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" name="articles[0][ancien_contenu]" rows="4" required
                                        placeholder="Copiez ici le texte exact de l'article AVANT modification..."></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-arrow-right text-success me-1"></i>
                                        Nouvelle rédaction <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" name="articles[0][nouveau_contenu]" rows="4" required
                                        placeholder="Copiez ici le texte exact de l'article APRÈS modification..."></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Motif de cette modification</label>
                                    <input type="text" class="form-control form-control-sm" name="articles[0][motif]"
                                        placeholder="Expliquez brièvement pourquoi cet article a été modifié...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton pour ajouter un article -->
                    <div class="text-center mt-3">
                        <button type="button" class="btn btn-outline-primary" id="add-article-btn">
                            <i class="fas fa-plus me-2"></i> Ajouter un autre article modifié
                        </button>
                    </div>
                </div>
            </div>

            <!-- Synthèse comparative (optionnel) -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i> Synthèse générale (optionnel)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-lightbulb me-1"></i>
                        Si vous avez des modifications générales qui ne concernent pas d'articles spécifiques, vous pouvez
                        les résumer ici.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ancien_texte" class="form-label">Notes sur l'ancienne version</label>
                            <textarea class="form-control" id="ancien_texte" name="ancien_texte" rows="3"
                                placeholder="Remarques générales sur la version précédente...">{{ old('ancien_texte') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="nouveau_texte" class="form-label">Notes sur la nouvelle version</label>
                            <textarea class="form-control" id="nouveau_texte" name="nouveau_texte" rows="3"
                                placeholder="Remarques générales sur la nouvelle version...">{{ old('nouveau_texte') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Justification -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-comment me-2"></i> Justification</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="justification" class="form-label">Raisons du changement statutaire <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="justification" name="justification" rows="3" required
                                placeholder="Expliquez pourquoi ces modifications sont nécessaires...">{{ old('justification') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="quorum" class="form-label">Quorum de l'AG</label>
                            <input type="text" class="form-control" id="quorum" name="quorum"
                                placeholder="Ex: 2/3 des membres présents" value="{{ old('quorum') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="vote" class="form-label">Résultat du vote</label>
                            <input type="text" class="form-control" id="vote" name="vote"
                                placeholder="Ex: Adopté à l'unanimité" value="{{ old('vote') }}">
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
                        <p class="text-muted mb-3">
                            <i class="fas fa-info-circle me-1"></i>
                            Les nouveaux statuts modifiés doivent obligatoirement être joints à ce dossier.
                        </p>
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
                        <button type="button" class="btn text-white" style="background: #7c3aed;"
                            onclick="submitFormWithAction('soumettre')">
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

        .text-purple {
            color: #7c3aed;
        }

        .article-modification-item {
            transition: all 0.3s ease;
            border-color: #dee2e6 !important;
        }

        .article-modification-item:hover {
            border-color: #7c3aed !important;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.15);
        }

        .article-modification-item .remove-article {
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .article-modification-item:hover .remove-article {
            opacity: 1;
        }

        #add-article-btn {
            border-style: dashed;
            border-width: 2px;
        }

        #add-article-btn:hover {
            background-color: rgba(124, 58, 237, 0.1);
            border-color: #7c3aed;
            color: #7c3aed;
        }
    </style>

    <script>
        let articleIndex = 1;

        function submitFormWithAction(action) {
            document.getElementById('formAction').value = action;
            document.querySelector('form').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('articles-container');
            const addBtn = document.getElementById('add-article-btn');

            // Ajouter un nouvel article
            addBtn.addEventListener('click', function() {
                const newArticle = document.createElement('div');
                newArticle.className = 'article-modification-item border rounded p-3 mb-3 bg-light';
                newArticle.setAttribute('data-index', articleIndex);
                
                newArticle.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 text-primary">
                            <i class="fas fa-paragraph me-2"></i>
                            <span class="article-number">Article #${articleIndex + 1}</span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-article">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Document</label>
                            <select class="form-select form-select-sm" name="articles[${articleIndex}][document]">
                                <option value="statuts">Statuts</option>
                                <option value="reglement_interieur">Règlement intérieur</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Numéro/Référence de l'article <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" name="articles[${articleIndex}][numero]" 
                                   placeholder="Ex: Article 5, Titre II - Art. 3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Titre/Intitulé</label>
                            <input type="text" class="form-control form-control-sm" name="articles[${articleIndex}][titre]" 
                                   placeholder="Ex: Conditions d'adhésion">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-arrow-left text-danger me-1"></i>
                                Ancienne rédaction <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="articles[${articleIndex}][ancien_contenu]" rows="4" required
                                      placeholder="Copiez ici le texte exact de l'article AVANT modification..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-arrow-right text-success me-1"></i>
                                Nouvelle rédaction <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="articles[${articleIndex}][nouveau_contenu]" rows="4" required
                                      placeholder="Copiez ici le texte exact de l'article APRÈS modification..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Motif de cette modification</label>
                            <input type="text" class="form-control form-control-sm" name="articles[${articleIndex}][motif]" 
                                   placeholder="Expliquez brièvement pourquoi cet article a été modifié...">
                        </div>
                    </div>
                `;
                
                container.appendChild(newArticle);
                articleIndex++;
                updateArticleNumbers();
                updateRemoveButtons();
            });

            // Supprimer un article (délégation d'événement)
            container.addEventListener('click', function(e) {
                if (e.target.closest('.remove-article')) {
                    const item = e.target.closest('.article-modification-item');
                    if (item) {
                        item.remove();
                        updateArticleNumbers();
                        updateRemoveButtons();
                    }
                }
            });

            function updateArticleNumbers() {
                const items = container.querySelectorAll('.article-modification-item');
                items.forEach((item, index) => {
                    const numberSpan = item.querySelector('.article-number');
                    if (numberSpan) {
                        numberSpan.textContent = `Article #${index + 1}`;
                    }
                });
            }

            function updateRemoveButtons() {
                const items = container.querySelectorAll('.article-modification-item');
                items.forEach((item, index) => {
                    const removeBtn = item.querySelector('.remove-article');
                    if (removeBtn) {
                        // Cacher le bouton supprimer s'il n'y a qu'un seul article
                        removeBtn.style.display = items.length > 1 ? 'inline-block' : 'none';
                    }
                });
            }

            // Initialiser l'état des boutons supprimer
            updateRemoveButtons();
        });
    </script>
@endsection