{{-- resources/views/admin/dossiers/modals/assign.blade.php --}}
{{-- ✅ MODAL D'ASSIGNATION - AVEC GESTION PRIORITÉ FIFO --}}

<!-- Modal d'assignation -->
<div class="modal fade" id="assignModal" tabindex="-1" aria-labelledby="assignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="assignModalLabel">
                    <i class="fas fa-user-check mr-2"></i>Assigner le Dossier
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="assignForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Dossier:</strong> {{ $dossier->numero_dossier ?? 'N/A' }}<br>
                                <strong>Organisation:</strong> {{ $dossier->organisation->nom ?? 'N/A' }}<br>
                                <strong>Type:</strong>
                                {{ ucfirst(str_replace('_', ' ', $dossier->organisation->type ?? 'N/A')) }}<br>
                                <strong>Position actuelle:</strong>
                                <span id="currentPosition" class="badge badge-secondary">
                                    {{ $dossier->ordre_traitement ?? 'Non défini' }}
                                    ({{ ucfirst($dossier->priorite_niveau ?? 'normale') }})
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Colonne gauche : Agent et instructions -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="agent_id" class="form-label">
                                        <i class="fas fa-user mr-1"></i>Sélectionner un Agent <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select name="agent_id" id="agent_id" class="form-control" required>
                                        <option value="">-- Choisir un agent --</option>
                                        @if(isset($agents) && $agents->count() > 0)
                                            @foreach($agents as $agent)
                                                <option value="{{ $agent->id }}" data-email="{{ $agent->email }}"
                                                    data-phone="{{ $agent->phone ?? '' }}"
                                                    data-role="{{ $agent->role ?? 'Agent' }}"
                                                    data-workload="{{ $agent->dossiers_en_cours ?? 0 }}">
                                                    {{ $agent->name }} - {{ $agent->email }}
                                                    @if($agent->phone)
                                                        ({{ $agent->phone }})
                                                    @endif
                                                    <span class="text-muted">- {{ $agent->dossiers_en_cours ?? 0 }}
                                                        dossier(s)</span>
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>Aucun agent disponible</option>
                                        @endif
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        L'agent sélectionné recevra une notification et le dossier passera en statut "En
                                        cours"
                                    </small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="instructions_agent" class="form-label">
                                        <i class="fas fa-clipboard-list mr-1"></i>Instructions pour l'agent
                                        <span class="badge badge-secondary">Optionnel</span>
                                    </label>
                                    <textarea name="instructions_agent" id="instructions_agent" class="form-control"
                                        rows="4"
                                        placeholder="Exemple : Vérifier particulièrement les documents d'identité des dirigeants et s'assurer que l'objet social respecte la réglementation en vigueur. Délai de traitement souhaité : 5 jours ouvrables."
                                        maxlength="1000"></textarea>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-lightbulb mr-1"></i>
                                        Instructions spécifiques, points d'attention, délais particuliers...
                                        <span class="float-right">
                                            <span id="charCount">0</span>/1000 caractères
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Colonne droite : Gestion de la priorité FIFO -->
                        <div class="col-md-4">
                            {{-- ✅ NOUVEAU : GESTION PRIORITÉ FIFO --}}
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="fas fa-sort-amount-up mr-2"></i>Gestion de la Priorité
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label for="priorite_niveau" class="form-label">
                                            <strong>Niveau de priorité</strong>
                                        </label>
                                        <select name="priorite_niveau" id="priorite_niveau" class="form-control">
                                            <option value="normale" selected>📋 Normale (FIFO)</option>
                                            <option value="moyenne">⚠️ Moyenne</option>
                                            <option value="haute">🔥 Haute</option>
                                            <option value="urgente" class="text-danger font-weight-bold">🚨 URGENTE
                                                (Tête de liste)</option>
                                        </select>
                                    </div>

                                    {{-- Zone d'information sur la priorité --}}
                                    <div id="priorityInfo" class="alert alert-info">
                                        <small>
                                            <i class="fas fa-info-circle mr-1"></i>
                                            <strong>Normale :</strong> Traitement selon l'ordre FIFO (premier arrivé,
                                            premier servi)
                                        </small>
                                    </div>

                                    {{-- Justification obligatoire pour urgente --}}
                                    <div id="justificationGroup" style="display: none;">
                                        <div class="form-group">
                                            <label for="priorite_justification" class="form-label">
                                                <strong class="text-danger">Justification obligatoire *</strong>
                                            </label>
                                            <textarea name="priorite_justification" id="priorite_justification"
                                                class="form-control" rows="3"
                                                placeholder="Expliquez pourquoi ce dossier nécessite un traitement en urgence..."
                                                maxlength="500"></textarea>
                                            <small class="form-text text-muted">
                                                Maximum 500 caractères
                                            </small>
                                        </div>
                                    </div>

                                    {{-- Impact sur l'ordre --}}
                                    <div id="orderImpact" class="mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-calculator mr-1"></i>
                                            <strong>Position estimée :</strong>
                                            <span
                                                id="estimatedPosition">{{ $dossier->ordre_traitement ?? 'À calculer' }}</span>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Queue FIFO actuelle --}}
                            <div class="card border-info mt-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list-ol mr-2"></i>Queue de Traitement
                                    </h6>
                                </div>
                                <div class="card-body p-2">
                                    <div id="queuePreview" class="small">
                                        {{-- Chargé dynamiquement via AJAX --}}
                                        <div class="text-center text-muted">
                                            <i class="fas fa-spinner fa-spin"></i> Chargement...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Options de notification --}}
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">
                                        <i class="fas fa-bell mr-2"></i>Options de Notification
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="notifier_agent_email"
                                            name="notifier_agent_email" checked>
                                        <label class="form-check-label" for="notifier_agent_email">
                                            <i class="fas fa-envelope text-primary mr-1"></i>
                                            <strong>Notifier l'agent par email</strong>
                                        </label>
                                        <small class="form-text text-muted ml-4">
                                            L'agent recevra un email avec les détails du dossier et les instructions
                                        </small>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="notification_immediate"
                                            name="notification_immediate" checked>
                                        <label class="form-check-label" for="notification_immediate">
                                            <i class="fas fa-bolt text-warning mr-1"></i>
                                            <strong>Notification immédiate</strong>
                                        </label>
                                        <small class="form-text text-muted ml-4">
                                            Envoyer la notification dès l'assignation (recommandé)
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Aperçu de l'agent sélectionné --}}
                    <div class="row" id="agentPreview" style="display: none;">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-success">
                                <h6 class="alert-heading">
                                    <i class="fas fa-user-check mr-2"></i>Agent sélectionné
                                </h6>
                                <div id="agentDetails">
                                    <!-- Détails remplis dynamiquement -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-check mr-1"></i> Assigner le Dossier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript pour la gestion de la priorité FIFO -->
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ✅ GESTION DE LA PRIORITÉ
        const prioriteSelect = document.getElementById('priorite_niveau');
        const priorityInfo = document.getElementById('priorityInfo');
        const justificationGroup = document.getElementById('justificationGroup');
        const justificationTextarea = document.getElementById('priorite_justification');
        const estimatedPosition = document.getElementById('estimatedPosition');

        // Messages d'information par priorité
        const priorityMessages = {
            'normale': '<i class="fas fa-info-circle mr-1"></i><strong>Normale :</strong> Traitement selon l\'ordre FIFO (premier arrivé, premier servi)',
            'moyenne': '<i class="fas fa-exclamation-circle mr-1"></i><strong>Moyenne :</strong> Traitement prioritaire, mais après les dossiers urgents',
            'haute': '<i class="fas fa-fire mr-1"></i><strong>Haute :</strong> Traitement en priorité, passera avant les dossiers normaux et moyens',
            'urgente': '<i class="fas fa-exclamation-triangle mr-1"></i><strong class="text-danger">URGENTE :</strong> Traitement immédiat - Ce dossier passera en tête de la queue'
        };

        // Classes d'alerte par priorité
        const alertClasses = {
            'normale': 'alert-info',
            'moyenne': 'alert-warning',
            'haute': 'alert-warning',
            'urgente': 'alert-danger'
        };

        if (prioriteSelect && priorityInfo) {
            prioriteSelect.addEventListener('change', function () {
                const selectedPriority = this.value;

                // Mettre à jour le message d'information
                priorityInfo.className = `alert ${alertClasses[selectedPriority]}`;
                priorityInfo.innerHTML = `<small>${priorityMessages[selectedPriority]}</small>`;

                // Afficher/cacher la justification pour urgente
                if (selectedPriority === 'urgente') {
                    justificationGroup.style.display = 'block';
                    justificationTextarea.required = true;
                } else {
                    justificationGroup.style.display = 'none';
                    justificationTextarea.required = false;
                    justificationTextarea.value = '';
                }

                // Calculer la position estimée
                calculateEstimatedPosition(selectedPriority);
            });
        }

        // ✅ CALCUL DE LA POSITION ESTIMÉE
        function calculateEstimatedPosition(priority) {
            fetch('{{ url('/admin/dossiers/calculate-position') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    priority: priority,
                    dossier_id: {{ $dossier->id }}
            })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        estimatedPosition.textContent = data.position;
                        estimatedPosition.className = data.position <= 3 ? 'text-success font-weight-bold' : 'text-info';
                    }
                })
                .catch(error => {
                    console.error('Erreur calcul position:', error);
                    estimatedPosition.textContent = 'Erreur de calcul';
                });
        }

        // ✅ CHARGEMENT DE LA QUEUE FIFO
        function loadQueuePreview() {
            fetch('{{ url('/admin/dossiers/queue-preview') }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(response => response.json())
                .then(data => {
                    const queuePreview = document.getElementById('queuePreview');
                    if (data.success && data.queue) {
                        let html = '<div class="queue-list">';
                        data.queue.forEach((item, index) => {
                            const isCurrentDossier = item.id === {{ $dossier->id }};
                            const priorityBadge = getPriorityBadge(item.priorite_niveau, item.priorite_urgente);

                            html += `
                        <div class="queue-item ${isCurrentDossier ? 'bg-light border' : ''} p-1 mb-1 rounded">
                            <small>
                                <strong>${index + 1}.</strong> 
                                ${item.numero_dossier} 
                                ${priorityBadge}
                                ${isCurrentDossier ? '<span class="badge badge-primary">Actuel</span>' : ''}
                            </small>
                        </div>
                    `;
                        });
                        html += '</div>';
                        queuePreview.innerHTML = html;
                    } else {
                        queuePreview.innerHTML = '<small class="text-muted">Impossible de charger la queue</small>';
                    }
                })
                .catch(error => {
                    console.error('Erreur chargement queue:', error);
                    document.getElementById('queuePreview').innerHTML = '<small class="text-danger">Erreur de chargement</small>';
                });
        }

        function getPriorityBadge(niveau, urgente) {
            if (urgente) return '<span class="badge badge-danger badge-sm">🚨</span>';
            switch (niveau) {
                case 'haute': return '<span class="badge badge-warning badge-sm">🔥</span>';
                case 'moyenne': return '<span class="badge badge-info badge-sm">⚠️</span>';
                default: return '<span class="badge badge-secondary badge-sm">📋</span>';
            }
        }

        // Charger la queue au démarrage
        loadQueuePreview();

        // ✅ AUTRES FONCTIONNALITÉS EXISTANTES
        const instructionsTextarea = document.getElementById('instructions_agent');
        const charCountSpan = document.getElementById('charCount');

        if (instructionsTextarea && charCountSpan) {
            instructionsTextarea.addEventListener('input', function () {
                const currentLength = this.value.length;
                charCountSpan.textContent = currentLength;

                if (currentLength > 800) {
                    charCountSpan.className = 'text-danger';
                } else if (currentLength > 600) {
                    charCountSpan.className = 'text-warning';
                } else {
                    charCountSpan.className = 'text-muted';
                }
            });
        }

        // Aperçu de l'agent sélectionné
        const agentSelect = document.getElementById('agent_id');
        const agentPreview = document.getElementById('agentPreview');
        const agentDetails = document.getElementById('agentDetails');

        if (agentSelect && agentPreview && agentDetails) {
            agentSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];

                if (this.value && selectedOption) {
                    const email = selectedOption.getAttribute('data-email');
                    const phone = selectedOption.getAttribute('data-phone');
                    const role = selectedOption.getAttribute('data-role') || 'Agent';
                    const workload = selectedOption.getAttribute('data-workload') || '0';

                    agentDetails.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Nom :</strong> ${selectedOption.text.split(' - ')[0]}<br>
                            <strong>Email :</strong> ${email}<br>
                            ${phone ? `<strong>Téléphone :</strong> ${phone}<br>` : ''}
                        </div>
                        <div class="col-md-6">
                            <strong>Rôle :</strong> ${role}<br>
                            <strong>Charge :</strong> ${workload} dossier(s)<br>
                            <strong>Statut :</strong> <span class="badge badge-success">Disponible</span>
                        </div>
                    </div>
                `;

                    agentPreview.style.display = 'block';
                } else {
                    agentPreview.style.display = 'none';
                }
            });
        }

        // Réinitialiser la modal à la fermeture
        $('#assignModal').on('hidden.bs.modal', function () {
            document.getElementById('assignForm').reset();
            if (agentPreview) agentPreview.style.display = 'none';
            if (charCountSpan) {
                charCountSpan.textContent = '0';
                charCountSpan.className = 'text-muted';
            }
            // Remettre la priorité par défaut
            if (prioriteSelect) {
                prioriteSelect.value = 'normale';
                prioriteSelect.dispatchEvent(new Event('change'));
            }
        });
    });
</script>

<!-- Styles spécifiques pour la gestion de priorité -->
<style>
    .queue-item {
        font-size: 0.85rem;
    }

    .queue-item.bg-light {
        border-left: 3px solid #007bff !important;
    }

    .badge-sm {
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
    }

    #priorite_niveau option[value="urgente"] {
        background-color: #f8d7da;
        color: #721c24;
    }

    .priority-badge-urgente {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.7;
        }

        100% {
            opacity: 1;
        }
    }

    /* Responsive modal */
    @media (max-width: 1200px) {
        .modal-dialog.modal-xl {
            max-width: 95%;
        }
    }
</style>