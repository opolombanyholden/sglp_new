{{-- resources/views/admin/users/agents.blade.php - VERSION BOOTSTRAP 5 COHÉRENTE --}}
@extends('layouts.admin')
@section('title', 'Gestion des Agents')

@section('content')
    <div class="container-fluid">
        <!-- Header avec statistiques inspiré du design en-cours -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                    <div class="card-body text-white">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">
                                    <i class="fas fa-user-tie me-2"></i>
                                    Gestion des Agents SGLP
                                </h2>
                                <p class="mb-0 opacity-90">Supervision et coordination de l'équipe d'agents de validation
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-plus me-2"></i>Nouvel Agent
                                    </a>
                                    <button onclick="refreshPage()" class="btn btn-outline-light btn-lg">
                                        <i class="fas fa-sync me-2"></i>Actualiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques Cards avec style gabonais -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['total_agents'] ?? 0 }}</h3>
                                <p class="mb-0 small opacity-90">Total Agents</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-user-tie fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 70%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #ffcd00 0%, #ffa500 100%);">
                    <div class="card-body text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['active_agents'] ?? 0 }}</h3>
                                <p class="mb-0 small">Agents Actifs</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-dark" style="width: 85%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['agents_online'] ?? 0 }}</h3>
                                <p class="mb-0 small opacity-90">En Ligne</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-wifi fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 45%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['agents_with_workload'] ?? 0 }}</h3>
                                <p class="mb-0 small opacity-90">Avec Charge</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-tasks fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignation Rapide -->
        @if(($stats['active_agents'] ?? 0) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm"
                        style="background: linear-gradient(135deg, rgba(255, 205, 0, 0.1), rgba(255, 205, 0, 0.05));">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-0">
                                        <i class="fas fa-bolt me-2" style="color: #ffcd00;"></i>
                                        Agents Disponibles pour Assignation
                                    </h5>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span
                                        class="badge bg-warning text-dark">{{ $agents->where('availability', 'Disponible')->count() }}
                                        disponibles</span>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex flex-wrap gap-2">
                                    @php
                                        $availableAgents = isset($agents) ? $agents->where('availability', 'Disponible')->take(5) : collect();
                                    @endphp
                                    @forelse($availableAgents as $availableAgent)
                                        <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
                                            <div class="bg-success rounded-circle"
                                                style="width: 8px; height: 8px; margin-right: 8px;"></div>
                                            <span class="small fw-bold">{{ $availableAgent->name }}</span>
                                            <span
                                                class="badge bg-light text-dark ms-2">{{ $availableAgent->current_workload ?? 0 }}</span>
                                        </div>
                                    @empty
                                        <span class="text-muted">Aucun agent disponible actuellement</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filtres et Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text border-0 bg-light">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-0 bg-light"
                                        placeholder="Rechercher un agent..." id="searchInput">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select border-0 bg-light" id="filterStatus">
                                    <option value="">Tous les statuts</option>
                                    <option value="active">Actif</option>
                                    <option value="inactive">Inactif</option>
                                    <option value="suspended">Suspendu</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select border-0 bg-light" id="filterAvailability">
                                    <option value="">Toutes disponibilités</option>
                                    <option value="Disponible">Disponible</option>
                                    <option value="Chargé">Chargé</option>
                                    <option value="Occupé">Occupé</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select border-0 bg-light" id="filterWorkload">
                                    <option value="">Toute charge</option>
                                    <option value="low">Faible (0-3)</option>
                                    <option value="medium">Moyenne (4-7)</option>
                                    <option value="high">Élevée (8+)</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="btn-group w-100" role="group">
                                    <button type="button" class="btn btn-outline-success" onclick="assignerDossiers()">
                                        <i class="fas fa-user-plus me-2"></i>Assigner
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" onclick="exporterDonnees()">
                                        <i class="fas fa-download me-2"></i>Exporter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions en lot -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center">
                                    <input type="checkbox" id="selectAll" class="form-check-input me-2">
                                    <label for="selectAll" class="form-check-label me-3">Sélectionner tout</label>
                                    <span id="selectedCount" class="badge bg-light text-dark">0 sélectionné(s)</span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="btn-group" role="group">
                                    <button onclick="activerSelection()" class="btn btn-success" disabled id="btnActiver">
                                        <i class="fas fa-check me-1"></i>Activer
                                    </button>
                                    <button onclick="desactiverSelection()" class="btn btn-warning" disabled
                                        id="btnDesactiver">
                                        <i class="fas fa-pause me-1"></i>Désactiver
                                    </button>
                                    <button onclick="exporterSelection()" class="btn btn-outline-primary" disabled
                                        id="btnExporter">
                                        <i class="fas fa-download me-1"></i>Exporter sélection
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des Agents -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-user-tie me-2" style="color: #009e3f;"></i>
                                Équipe d'Agents SGLP
                            </h5>
                            <span class="badge bg-success">{{ isset($agents) ? $agents->total() : 0 }} agents</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($agents) && count($agents) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">
                                                <input type="checkbox" class="form-check-input" id="selectAllTable">
                                            </th>
                                            <th class="border-0">Agent</th>
                                            <th class="border-0">Charge de Travail</th>
                                            <th class="border-0">Performance</th>
                                            <th class="border-0">Statut Connexion</th>
                                            <th class="border-0">Disponibilité</th>
                                            <th class="border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($agents as $agent)
                                            <tr class="agent-row">
                                                <td>
                                                    <input type="checkbox" class="form-check-input agent-checkbox"
                                                        value="{{ $agent->id }}">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="agent-avatar me-3">
                                                            @php
                                                                $initials = '';
                                                                if (isset($agent->nom) && isset($agent->prenom)) {
                                                                    $initials = strtoupper(substr($agent->nom, 0, 1) . substr($agent->prenom, 0, 1));
                                                                } elseif (!empty($agent->name)) {
                                                                    $parts = explode(' ', trim($agent->name));
                                                                    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                                                } else {
                                                                    $initials = 'AG';
                                                                }
                                                                $isOnline = $agent->last_login_at && $agent->last_login_at->gt(now()->subHours(2));
                                                            @endphp
                                                            <div class="position-relative">
                                                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                                                    style="width: 45px; height: 45px; font-weight: bold;">
                                                                    {{ $initials }}
                                                                </div>
                                                                @if($isOnline)
                                                                    <div class="position-absolute bottom-0 end-0">
                                                                        <span class="badge bg-success rounded-pill"
                                                                            style="width: 12px; height: 12px; padding: 0;"></span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">{{ $agent->name }}</h6>
                                                            <small class="text-muted">{{ $agent->email }}</small>
                                                            @if($agent->nip)
                                                                <br><small class="text-info">NIP: {{ $agent->nip }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        $workload = $agent->current_workload ?? 0;
                                                        $maxWorkload = 10;
                                                        $percentage = min(($workload / $maxWorkload) * 100, 100);
                                                        $level = $percentage <= 50 ? 'success' : ($percentage <= 80 ? 'warning' : 'danger');
                                                    @endphp
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress me-2" style="width: 60px; height: 8px;">
                                                            <div class="progress-bar bg-{{ $level }}"
                                                                style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                        <span class="small fw-bold">{{ $workload }}/{{ $maxWorkload }}</span>
                                                    </div>
                                                    <small class="text-muted">{{ $workload }} dossiers en cours</small>
                                                </td>
                                                <td>
                                                    <div class="text-center">
                                                        <div class="text-success">
                                                            <i class="fas fa-check-circle me-1"></i>
                                                            <strong>{{ $agent->dossiers_traites_mois ?? 0 }}</strong>
                                                        </div>
                                                        <small class="text-muted">Ce mois</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle {{ $isOnline ? 'bg-success' : 'bg-secondary' }}"
                                                            style="width: 10px; height: 10px; margin-right: 8px;"></div>
                                                        <span class="small">
                                                            {{ $isOnline ? 'En ligne' : 'Hors ligne' }}
                                                        </span>
                                                    </div>
                                                    @if($agent->last_activity)
                                                        <small class="text-muted d-block">{{ $agent->last_activity }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $availability = $agent->availability ?? 'N/A';
                                                        $badgeClass = $availability === 'Disponible' ? 'success' :
                                                            ($availability === 'Chargé' ? 'warning' : 'danger');
                                                    @endphp
                                                    <span class="badge bg-{{ $badgeClass }}">
                                                        {{ $availability }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="voirAgent({{ $agent->id }})" title="Voir détails">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-success"
                                                            onclick="modifierAgent({{ $agent->id }})" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        @if($availability === 'Disponible')
                                                            <button type="button" class="btn btn-sm btn-outline-warning"
                                                                onclick="assignationRapide({{ $agent->id }})" title="Assigner dossier">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        @endif
                                                        <div class="btn-group" role="group">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                data-toggle="dropdown" title="Plus d'actions">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a class="dropdown-item" href="#"
                                                                        onclick="changerStatut({{ $agent->id }})">
                                                                        <i class="fas fa-toggle-on me-2"></i>Changer statut
                                                                    </a></li>
                                                                <li><a class="dropdown-item" href="#"
                                                                        onclick="reinitialiserMotDePasse({{ $agent->id }})">
                                                                        <i class="fas fa-key me-2"></i>Réinitialiser mot de passe
                                                                    </a></li>
                                                                <li><a class="dropdown-item" href="#"
                                                                        onclick="voirStatistiques({{ $agent->id }})">
                                                                        <i class="fas fa-chart-bar me-2"></i>Statistiques
                                                                    </a></li>
                                                                @if(!$agent->email_verified_at)
                                                                    <li><a class="dropdown-item text-success" href="#"
                                                                            onclick="activerCompte({{ $agent->id }})">
                                                                            <i class="fas fa-user-check me-2"></i>Activer le compte
                                                                        </a></li>
                                                                @endif
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                                <li><a class="dropdown-item text-danger" href="#"
                                                                        onclick="supprimerAgent({{ $agent->id }})">
                                                                        <i class="fas fa-trash me-2"></i>Supprimer
                                                                    </a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            @if(isset($agents) && method_exists($agents, 'links'))
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $agents->links() }}
                                </div>
                            @endif
                        @else
                            <!-- État vide avec style gabonais -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-user-tie fa-5x text-muted opacity-50"></i>
                                </div>
                                <h4 class="text-muted mb-3">Aucun agent trouvé</h4>
                                <p class="text-muted mb-4">
                                    @if(request()->hasAny(['search', 'status', 'availability']))
                                        Aucun agent ne correspond aux critères de recherche.
                                    @else
                                        Aucun agent n'est encore enregistré dans le système.
                                    @endif
                                </p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-lg">
                                        <i class="fas fa-plus me-2"></i>Créer le premier agent
                                    </a>
                                    <button class="btn btn-outline-primary btn-lg" onclick="refreshPage()">
                                        <i class="fas fa-sync me-2"></i>Actualiser
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAB (Floating Action Button) tricolore -->
    <div class="fab-container">
        <div class="fab-menu" id="fabMenu">
            <div class="fab-main" onclick="toggleFAB()">
                <i class="fas fa-plus fab-icon"></i>
            </div>
            <div class="fab-options">
                <button class="fab-option" style="background: #009e3f;" title="Créer agent" onclick="creerAgent()">
                    <i class="fas fa-user-plus"></i>
                </button>
                <button class="fab-option" style="background: #ffcd00; color: #000;" title="Assigner agents"
                    onclick="assignerDossiers()">
                    <i class="fas fa-tasks"></i>
                </button>
                <button class="fab-option" style="background: #003f7f;" title="Exporter données"
                    onclick="exporterDonnees()">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Détails Agent -->
    <div class="modal fade" id="agentDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-tie me-2"></i>Détails de l'agent
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="agentDetailsContent">
                    <!-- Contenu chargé via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-success" id="editAgentBtn">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Assignation Rapide -->
    <div class="modal fade" id="assignationRapideModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-bolt me-2"></i>Assignation Rapide
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="assignationForm">
                        <input type="hidden" id="assignationAgentId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Agent sélectionné</label>
                            <div id="selectedAgentInfo" class="p-3 bg-light rounded"></div>
                        </div>
                        <div class="mb-3">
                            <label for="dossierSelect" class="form-label fw-bold">Dossier à assigner</label>
                            <select class="form-select" id="dossierSelect" required>
                                <option value="">Chargement des dossiers...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="assignationComment" class="form-label fw-bold">Commentaire (optionnel)</label>
                            <textarea class="form-control" id="assignationComment" rows="3"
                                placeholder="Raison de l'assignation ou instructions spécifiques..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-warning" onclick="confirmerAssignation()">
                        <i class="fas fa-check me-2"></i>Assigner
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Styles gabonais inspirés du design en-cours */
        .stats-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 15px;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
        }

        .agent-row {
            transition: background-color 0.2s ease;
        }

        .agent-row:hover {
            background-color: rgba(0, 158, 63, 0.05);
        }

        .agent-avatar {
            width: 45px;
            text-align: center;
        }

        /* FAB Style gabonais */
        .fab-container {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }

        .fab-menu {
            position: relative;
        }

        .fab-main {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.3s ease;
            border: none;
        }

        .fab-main:hover {
            transform: scale(1.1);
        }

        .fab-icon {
            color: white;
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .fab-options {
            position: absolute;
            bottom: 70px;
            right: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .fab-menu.active .fab-options {
            opacity: 1;
            visibility: visible;
        }

        .fab-menu.active .fab-icon {
            transform: rotate(45deg);
        }

        .fab-option {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            border: none;
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .fab-option:hover {
            transform: scale(1.1);
        }

        /* Animation d'entrée */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Progress bar personnalisée */
        .progress {
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-bar {
            transition: width 0.6s ease;
        }
    </style>

    <script>
        let selectedAgents = [];
        let currentAgentId = null;

        // Toggle FAB Menu
        function toggleFAB() {
            const fabMenu = document.getElementById('fabMenu');
            fabMenu.classList.toggle('active');
        }

        // Fermer FAB en cliquant ailleurs
        document.addEventListener('click', function (event) {
            const fabMenu = document.getElementById('fabMenu');
            if (fabMenu && !fabMenu.contains(event.target)) {
                fabMenu.classList.remove('active');
            }
        });

        // Fonctions principales
        function refreshPage() {
            location.reload();
        }

        function voirAgent(agentId) {
            currentAgentId = agentId;

            // Charger les détails via AJAX
            fetch(`/admin/users/${agentId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        afficherDetailsAgent(data.data);
                        new bootstrap.Modal(document.getElementById('agentDetailsModal')).show();
                    } else {
                        alert('Erreur lors du chargement des détails');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur technique');
                });
        }

        function afficherDetailsAgent(agent) {
            const content = document.getElementById('agentDetailsContent');
            content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-success mb-3">
                        <i class="fas fa-info-circle me-2"></i>Informations personnelles
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Nom complet:</strong></td><td>${agent.name}</td></tr>
                        <tr><td><strong>Email:</strong></td><td>${agent.email}</td></tr>
                        <tr><td><strong>Téléphone:</strong></td><td>${agent.phone || 'Non renseigné'}</td></tr>
                        <tr><td><strong>NIP:</strong></td><td>${agent.nip || 'Non renseigné'}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-success mb-3">
                        <i class="fas fa-tasks me-2"></i>Performance & Charge
                    </h6>
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Dossiers en cours:</strong></td><td><span class="badge bg-warning">${agent.detailed_stats?.dossiers_en_cours || 0}</span></td></tr>
                        <tr><td><strong>Dossiers traités:</strong></td><td><span class="badge bg-success">${agent.detailed_stats?.dossiers_traites || 0}</span></td></tr>
                        <tr><td><strong>Disponibilité:</strong></td><td><span class="badge bg-info">${agent.availability || 'N/A'}</span></td></tr>
                    </table>
                </div>
            </div>
        `;
        }

        function modifierAgent(agentId) {
            window.location.href = `/admin/users/${agentId}/edit`;
        }

        function assignationRapide(agentId) {
            currentAgentId = agentId;

            // Charger les infos de l'agent
            fetch(`/admin/users/${agentId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const agent = data.data;
                        document.getElementById('selectedAgentInfo').innerHTML = `
                    <strong>${agent.name}</strong><br>
                    <small class="text-muted">${agent.email}</small><br>
                    <small class="text-info">Charge actuelle: ${agent.detailed_stats?.dossiers_en_cours || 0} dossiers</small>
                `;
                        document.getElementById('assignationAgentId').value = agentId;
                    }
                });

            // Charger les dossiers disponibles
            chargerDossiersDisponibles();

            new bootstrap.Modal(document.getElementById('assignationRapideModal')).show();
        }

        function chargerDossiersDisponibles() {
            fetch('/admin/api/dossiers/available-for-assignment', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Sélectionner un dossier</option>';
                    if (data.success && data.data.length > 0) {
                        data.data.forEach(dossier => {
                            options += `<option value="${dossier.id}">${dossier.numero} - ${dossier.organisation_nom}</option>`;
                        });
                    } else {
                        options = '<option value="">Aucun dossier disponible</option>';
                    }
                    document.getElementById('dossierSelect').innerHTML = options;
                })
                .catch(error => {
                    document.getElementById('dossierSelect').innerHTML = '<option value="">Erreur de chargement</option>';
                });
        }

        function confirmerAssignation() {
            const agentId = document.getElementById('assignationAgentId').value;
            const dossierId = document.getElementById('dossierSelect').value;
            const comment = document.getElementById('assignationComment').value;

            if (!dossierId) {
                alert('Veuillez sélectionner un dossier');
                return;
            }

            fetch(`/admin/dossiers/${dossierId}/assign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    agent_id: agentId,
                    comment: comment
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('assignationRapideModal')).hide();
                        alert('Dossier assigné avec succès');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors de l\'assignation');
                });
        }

        // Gestion de la sélection multiple
        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.agent-checkbox:checked');
            const count = checkboxes.length;
            document.getElementById('selectedCount').textContent = `${count} sélectionné(s)`;

            const btnActiver = document.getElementById('btnActiver');
            const btnDesactiver = document.getElementById('btnDesactiver');
            const btnExporter = document.getElementById('btnExporter');

            if (count > 0) {
                btnActiver.disabled = false;
                btnDesactiver.disabled = false;
                btnExporter.disabled = false;
            } else {
                btnActiver.disabled = true;
                btnDesactiver.disabled = true;
                btnExporter.disabled = true;
            }

            selectedAgents = Array.from(checkboxes).map(cb => cb.value);
        }

        // Event listeners pour la sélection
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('selectAll');
            const selectAllTable = document.getElementById('selectAllTable');

            if (selectAll) {
                selectAll.addEventListener('change', function () {
                    const checkboxes = document.querySelectorAll('.agent-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    if (selectAllTable) selectAllTable.checked = this.checked;
                    updateSelectedCount();
                });
            }

            if (selectAllTable) {
                selectAllTable.addEventListener('change', function () {
                    const checkboxes = document.querySelectorAll('.agent-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    if (selectAll) selectAll.checked = this.checked;
                    updateSelectedCount();
                });
            }

            document.querySelectorAll('.agent-checkbox').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });
        });

        // Actions en lot
        function activerSelection() {
            if (selectedAgents.length === 0) {
                alert('Veuillez sélectionner au moins un agent');
                return;
            }
            console.log('Activer agents:', selectedAgents);
        }

        function desactiverSelection() {
            if (selectedAgents.length === 0) {
                alert('Veuillez sélectionner au moins un agent');
                return;
            }
            console.log('Désactiver agents:', selectedAgents);
        }

        function exporterSelection() {
            if (selectedAgents.length === 0) {
                alert('Veuillez sélectionner au moins un agent');
                return;
            }
            console.log('Exporter agents:', selectedAgents);
        }

        function creerAgent() {
            window.location.href = '{{ route("admin.users.create") }}';
        }

        function assignerDossiers() {
            console.log('Assigner dossiers aux agents sélectionnés');
        }

        function exporterDonnees() {
            console.log('Exporter données des agents');
        }

        // Recherche en temps réel
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.agent-row');

                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        });

        // Filtres
        ['filterStatus', 'filterAvailability', 'filterWorkload'].forEach(filterId => {
            document.addEventListener('DOMContentLoaded', function () {
                const filter = document.getElementById(filterId);
                if (filter) {
                    filter.addEventListener('change', appliquerFiltres);
                }
            });
        });

        function appliquerFiltres() {
            const status = document.getElementById('filterStatus')?.value || '';
            const availability = document.getElementById('filterAvailability')?.value || '';
            const workload = document.getElementById('filterWorkload')?.value || '';

            const rows = document.querySelectorAll('.agent-row');

            rows.forEach(row => {
                let show = true;
                const text = row.textContent.toLowerCase();

                if (status && !text.includes(status.toLowerCase())) show = false;
                if (availability && !text.includes(availability.toLowerCase())) show = false;

                row.style.display = show ? '' : 'none';
            });
        }

        // Autres fonctions
        function changerStatut(agentId) { console.log('Changer statut:', agentId); }
        function reinitialiserMotDePasse(agentId) { console.log('Réinitialiser MDP:', agentId); }
        function voirStatistiques(agentId) { console.log('Voir stats:', agentId); }
        function supprimerAgent(agentId) { console.log('Supprimer agent:', agentId); }
        
        // Activer le compte (forcer la vérification email)
        function activerCompte(userId) {
            if (!confirm('Voulez-vous vraiment activer ce compte ? L\'utilisateur pourra se connecter immédiatement.')) {
                return;
            }
            
            fetch(`/admin/users/${userId}/force-verify-email`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'Compte activé avec succès');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    alert(data.message || 'Erreur lors de l\'activation');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'activation du compte');
            });
        }
    </script>
@endsection