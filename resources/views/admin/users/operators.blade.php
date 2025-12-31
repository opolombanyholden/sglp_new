{{-- resources/views/admin/users/operators.blade.php - VERSION CORRIGÉE QUI FONCTIONNE --}}
@extends('layouts.admin')
@section('title', 'Gestion des Opérateurs')

@section('content')
    <div class="container-fluid">
        <!-- Header avec statistiques inspiré du design en-cours -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                    <div class="card-body text-white">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">
                                    <i class="fas fa-users me-2"></i>
                                    Gestion des Opérateurs SGLP
                                </h2>
                                <p class="mb-0 opacity-90">Administration et supervision des comptes opérateurs</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-light btn-lg">
                                        <i class="fas fa-plus me-2"></i>Nouvel Opérateur
                                    </a>
                                    <button type="button" class="btn btn-outline-light btn-lg" onclick="location.reload()">
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
                    style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['total_operators'] ?? 0 }}</h3>
                                <p class="mb-0 small opacity-90">Total Opérateurs</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm stats-card"
                    style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                    <div class="card-body text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-1">{{ $stats['active_operators'] ?? 0 }}</h3>
                                <p class="mb-0 small opacity-90">Actifs</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 85%"></div>
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
                                <h3 class="mb-1">{{ $stats['pending_operators'] ?? 0 }}</h3>
                                <p class="mb-0 small">En Attente</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-dark" style="width: 45%"></div>
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
                                <h3 class="mb-1">
                                    {{ \App\Models\User::where('role', 'operator')->where('created_at', '>=', now()->startOfWeek())->count() }}
                                </h3>
                                <p class="mb-0 small opacity-90">Cette Semaine</p>
                            </div>
                            <div class="icon-circle">
                                <i class="fas fa-calendar-week fa-2x"></i>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-light" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres et Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.users.operators') }}" id="filterForm">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">
                                            <i class="fas fa-search text-muted"></i>
                                        </span>
                                        <input type="text" class="form-control border-0 bg-light" name="search"
                                            value="{{ request('search') }}" placeholder="Nom, email, téléphone, NIP...">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select border-0 bg-light">
                                        <option value="">Tous les statuts</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif
                                        </option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                            Inactif</option>
                                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>
                                            Suspendu</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En
                                            attente</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="is_active" class="form-select border-0 bg-light">
                                        <option value="">Tous</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Compte actif
                                        </option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Compte
                                            désactivé</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Filtrer
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <div class="btn-group w-100" role="group">
                                        <button type="button" class="btn btn-outline-success" onclick="exportData('excel')">
                                            <i class="fas fa-file-excel me-2"></i>Excel
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" onclick="exportData('pdf')">
                                            <i class="fas fa-file-pdf me-2"></i>PDF
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="exportData('csv')">
                                            <i class="fas fa-file-csv me-2"></i>CSV
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des opérateurs -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2" style="color: #003f7f;"></i>
                                Liste des Opérateurs SGLP
                            </h5>
                            <span class="badge bg-primary">{{ isset($operators) ? $operators->total() : 0 }}
                                opérateurs</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(isset($operators) && count($operators) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="border-0">Opérateur</th>
                                            <th class="border-0">Contact</th>
                                            <th class="border-0">Organisations</th>
                                            <th class="border-0">Statut</th>
                                            <th class="border-0">Dernière Connexion</th>
                                            <th class="border-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($operators as $operator)
                                            <tr class="operator-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="operator-avatar me-3">
                                                            @php
                                                                $initials = '';
                                                                if (isset($operator->nom) && isset($operator->prenom)) {
                                                                    $initials = strtoupper(substr($operator->nom, 0, 1) . substr($operator->prenom, 0, 1));
                                                                } elseif (!empty($operator->name)) {
                                                                    $parts = explode(' ', trim($operator->name));
                                                                    $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                                                } else {
                                                                    $initials = 'OP';
                                                                }
                                                                $isOnline = $operator->last_login_at && $operator->last_login_at->gt(now()->subHours(2));
                                                            @endphp
                                                            <div class="position-relative">
                                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
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
                                                            <h6 class="mb-1">{{ $operator->name }}</h6>
                                                            @if($operator->nip)
                                                                <small class="text-info">NIP: {{ $operator->nip }}</small><br>
                                                            @endif
                                                            <small class="text-muted">Inscrit
                                                                {{ $operator->created_at->format('d/m/Y') }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong>{{ $operator->email }}</strong>
                                                        @if($operator->phone)
                                                            <br><small class="text-muted">{{ $operator->phone }}</small>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">
                                                        {{ $operator->organisations_count ?? 0 }}
                                                    </span>
                                                    <br><small class="text-muted">organisation(s)</small>
                                                </td>
                                                <td>
                                                    @php
                                                        $currentStatus = $operator->status ?? 'active';
                                                        $statusConfig = [
                                                            'active' => ['class' => 'success', 'icon' => 'check', 'label' => 'Actif'],
                                                            'inactive' => ['class' => 'secondary', 'icon' => 'pause', 'label' => 'Inactif'],
                                                            'suspended' => ['class' => 'warning', 'icon' => 'exclamation-triangle', 'label' => 'Suspendu'],
                                                            'pending' => ['class' => 'info', 'icon' => 'clock', 'label' => 'En attente']
                                                        ];
                                                        $config = $statusConfig[$currentStatus] ?? $statusConfig['active'];
                                                    @endphp
                                                    <span class="badge bg-{{ $config['class'] }}">
                                                        <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                                        {{ $config['label'] }}
                                                    </span>
                                                    @if(!$operator->is_active)
                                                        <br><small class="text-danger">Compte désactivé</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($operator->last_login_at)
                                                        <div>
                                                            {{ $operator->last_login_at->format('d/m/Y') }}
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ $operator->last_login_at->format('H:i') }}
                                                            </small>
                                                        </div>
                                                    @else
                                                        <span class="text-muted">Jamais connecté</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <!-- ✅ BOUTON DÉTAILS - MÉTHODE QUI FONCTIONNE -->
                                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                            onclick="viewOperator({{ $operator->id }})" title="Voir détails">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <!-- Bouton éditer standard -->
                                                        <a href="{{ route('admin.users.edit', $operator->id) }}"
                                                            class="btn btn-sm btn-outline-success" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <!-- Menu dropdown -->
                                                        <div class="btn-group" role="group">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                                data-toggle="dropdown" aria-expanded="false" title="Plus d'actions">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                        onclick="changeOperatorStatus({{ $operator->id }}, '{{ addslashes($operator->name) }}', '{{ $operator->status ?? 'active' }}')">
                                                                        <i class="fas fa-toggle-on me-2"></i>Changer statut
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="#"
                                                                        onclick="resetOperatorPassword({{ $operator->id }}, '{{ addslashes($operator->name) }}')">
                                                                        <i class="fas fa-key me-2"></i>Réinitialiser mot de passe
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('admin.organisations.index') }}?operator={{ $operator->id }}">
                                                                        <i class="fas fa-building me-2"></i>Voir organisations
                                                                    </a>
                                                                </li>
                                                                @if(!$operator->email_verified_at)
                                                                    <li>
                                                                        <a class="dropdown-item text-success" href="#"
                                                                            onclick="activateAccount({{ $operator->id }})">
                                                                            <i class="fas fa-user-check me-2"></i>Activer le compte
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                                <li>
                                                                    <hr class="dropdown-divider">
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item text-danger" href="#"
                                                                        onclick="deleteOperator({{ $operator->id }}, '{{ addslashes($operator->name) }}')">
                                                                        <i class="fas fa-trash me-2"></i>Supprimer
                                                                    </a>
                                                                </li>
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
                            @if(isset($operators) && method_exists($operators, 'links'))
                                <div class="d-flex justify-content-center mt-4">
                                    {{ $operators->links() }}
                                </div>
                            @endif
                        @else
                            <!-- État vide -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-users fa-5x text-muted opacity-50"></i>
                                </div>
                                <h4 class="text-muted mb-3">Aucun opérateur trouvé</h4>
                                <p class="text-muted mb-4">
                                    @if(request()->hasAny(['search', 'status', 'is_active']))
                                        Aucun opérateur ne correspond aux critères de recherche.
                                    @else
                                        Aucun opérateur n'est encore enregistré dans le système.
                                    @endif
                                </p>
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-plus me-2"></i>Créer le premier opérateur
                                    </a>
                                    <button class="btn btn-outline-primary btn-lg" onclick="location.reload()">
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

    <!-- ✅ MODAL DÉTAILS OPÉRATEUR - BOUTON FERMER CORRIGÉ -->
    <div class="modal fade" id="operatorModal" tabindex="-1" aria-labelledby="operatorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="operatorModalLabel">
                        <i class="fas fa-user me-2"></i>Détails de l'opérateur
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" id="operatorModalContent">
                    <!-- Contenu chargé via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-success" id="editOperatorFromModal">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ MODAL CHANGEMENT STATUT - BOUTON FERMER CORRIGÉ -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="statusModalLabel">
                        <i class="fas fa-toggle-on me-2"></i>Changer le statut
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Modifier le statut de l'opérateur <strong id="statusOperatorName"></strong> :</p>
                    <form id="statusForm">
                        <div class="mb-3">
                            <label for="newStatus" class="form-label">Nouveau statut :</label>
                            <select class="form-select" id="newStatus" name="status" required>
                                <option value="active">Actif</option>
                                <option value="inactive">Inactif</option>
                                <option value="suspended">Suspendu</option>
                                <option value="pending">En attente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statusReason" class="form-label">Motif (optionnel) :</label>
                            <textarea class="form-control" id="statusReason" name="reason" rows="3"
                                placeholder="Raison du changement de statut..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-warning" id="confirmStatusChange">
                        <i class="fas fa-check me-2"></i>Modifier le statut
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ✅ MODAL SUPPRESSION - BOUTON FERMER CORRIGÉ -->
    <div class="modal fade" id="deleteOperatorModal" tabindex="-1" aria-labelledby="deleteOperatorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="deleteOperatorModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer l'opérateur <strong id="deleteOperatorName"></strong> ?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        Cette action est irréversible et supprimera toutes les données associées.
                    </div>
                    <div id="operatorConstraintsWarning" style="display: none;">
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Contraintes détectées :</h6>
                            <ul id="operatorConstraintsList"></ul>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteOperatorBtn">
                        <i class="fas fa-trash me-2"></i>Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Styles cohérents */
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

        .operator-row {
            transition: background-color 0.2s ease;
        }

        .operator-row:hover {
            background-color: rgba(0, 63, 127, 0.05);
        }

        /* Notification moderne */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
    </style>

    <script>
        // ✅ JAVASCRIPT BASÉ SUR LE MODÈLE INDEX.BLADE.PHP QUI FONCTIONNE
        document.addEventListener('DOMContentLoaded', function () {
            console.log('Module Gestion Opérateurs SGLP chargé');
        });

        let currentOperatorId = null;

        // ✅ VOIR DÉTAILS OPÉRATEUR - COPIE EXACTE DE INDEX.BLADE.PHP
        function viewOperator(operatorId) {
            fetch(`/admin/users/${operatorId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const operator = data.data;

                        const html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Informations personnelles
                                </h6>
                                <table class="table table-sm table-borderless">
                                    <tr><td class="fw-bold" style="width: 40%;">Nom complet:</td><td>${operator.name}</td></tr>
                                    <tr><td class="fw-bold">Email:</td><td>${operator.email}</td></tr>
                                    <tr><td class="fw-bold">Téléphone:</td><td>${operator.phone || 'Non renseigné'}</td></tr>
                                    <tr><td class="fw-bold">NIP:</td><td>${operator.nip || 'Non renseigné'}</td></tr>
                                    <tr><td class="fw-bold">Adresse:</td><td>${operator.address || 'Non renseignée'}</td></tr>
                                    <tr><td class="fw-bold">Ville:</td><td>${operator.city || 'Non renseignée'}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-user-shield me-2"></i>Informations système
                                </h6>
                                <table class="table table-sm table-borderless">
                                    <tr><td class="fw-bold">Rôle:</td><td><span class="badge bg-primary">${operator.role_label || 'Opérateur'}</span></td></tr>
                                    <tr><td class="fw-bold">Statut:</td><td><span class="badge ${operator.status === 'active' ? 'bg-success' : 'bg-secondary'}">${operator.status_label || 'Actif'}</span></td></tr>
                                    <tr><td class="fw-bold">Email vérifié:</td><td>${operator.is_verified ? '✅ Oui' : '❌ Non'}</td></tr>
                                    <tr><td class="fw-bold">Dernière connexion:</td><td>${operator.last_login || 'Jamais'}</td></tr>
                                    <tr><td class="fw-bold">Créé le:</td><td>${operator.created_at}</td></tr>
                                    <tr><td class="fw-bold">Organisations:</td><td><span class="badge bg-info">${operator.organisations_count || 0}</span></td></tr>
                                </table>
                            </div>
                        </div>
                    `;

                        document.getElementById('operatorModalContent').innerHTML = html;
                        document.getElementById('editOperatorFromModal').onclick = () => {
                            window.location.href = `/admin/users/${operator.id}/edit`;
                        };

                        // ✅ CRÉATION MODAL EXACTEMENT COMME INDEX.BLADE.PHP
                        const modal = new bootstrap.Modal(document.getElementById('operatorModal'));
                        modal.show();
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('error', 'Erreur lors du chargement des détails');
                });
        }

        // ✅ CHANGER STATUT OPÉRATEUR
        function changeOperatorStatus(operatorId, operatorName, currentStatus) {
            currentOperatorId = operatorId;
            document.getElementById('statusOperatorName').textContent = operatorName;
            document.getElementById('newStatus').value = currentStatus;

            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        }

        // ✅ SUPPRIMER OPÉRATEUR
        function deleteOperator(operatorId, operatorName) {
            currentOperatorId = operatorId;
            document.getElementById('deleteOperatorName').textContent = operatorName;

            // Vérifier les contraintes
            fetch(`/admin/users/${operatorId}/check-constraints`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    const constraintsWarning = document.getElementById('operatorConstraintsWarning');
                    const constraintsList = document.getElementById('operatorConstraintsList');

                    if (data.success && data.has_constraints) {
                        constraintsList.innerHTML = '';
                        data.constraints.forEach(constraint => {
                            const li = document.createElement('li');
                            li.textContent = constraint;
                            constraintsList.appendChild(li);
                        });
                        constraintsWarning.style.display = 'block';
                    } else {
                        constraintsWarning.style.display = 'none';
                    }

                    const modal = new bootstrap.Modal(document.getElementById('deleteOperatorModal'));
                    modal.show();
                })
                .catch(error => {
                    document.getElementById('operatorConstraintsWarning').style.display = 'none';
                    const modal = new bootstrap.Modal(document.getElementById('deleteOperatorModal'));
                    modal.show();
                });
        }

        // ✅ RESET PASSWORD
        function resetOperatorPassword(operatorId, operatorName) {
            if (!confirm(`Voulez-vous vraiment réinitialiser le mot de passe de ${operatorName} ?`)) {
                return;
            }

            fetch(`/admin/users/${operatorId}/reset-password`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', data.message);
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'Erreur lors de la réinitialisation');
                });
        }

        // ✅ ACTIVER LE COMPTE
        function activateAccount(userId) {
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
                        showNotification('success', data.message);
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'Erreur lors de l\'activation du compte');
                });
        }

        // ✅ EVENT LISTENERS POUR MODALS
        document.getElementById('confirmStatusChange')?.addEventListener('click', function () {
            if (!currentOperatorId) return;

            const newStatus = document.getElementById('newStatus').value;
            const reason = document.getElementById('statusReason').value;

            fetch(`/admin/users/${currentOperatorId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    status: newStatus,
                    reason: reason
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
                        showNotification('success', data.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'Erreur lors de la modification du statut');
                });
        });

        document.getElementById('confirmDeleteOperatorBtn')?.addEventListener('click', function () {
            if (!currentOperatorId) return;

            const btn = this;
            const originalText = btn.innerHTML;

            btn.innerHTML = '<div class="spinner-border spinner-border-sm me-2"></div>Suppression...';
            btn.disabled = true;

            fetch(`/admin/users/${currentOperatorId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('deleteOperatorModal')).hide();
                        showNotification('success', data.message);
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    showNotification('error', 'Erreur lors de la suppression');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });

        // ✅ SYSTÈME DE NOTIFICATIONS - COPIÉ DE INDEX.BLADE.PHP
        function showNotification(type, message, duration = 5000) {
            const colors = {
                success: 'alert-success',
                error: 'alert-danger',
                warning: 'alert-warning',
                info: 'alert-info'
            };

            const icons = {
                success: 'fas fa-check-circle',
                error: 'fas fa-exclamation-circle',
                warning: 'fas fa-exclamation-triangle',
                info: 'fas fa-info-circle'
            };

            const notification = document.createElement('div');
            notification.className = `alert ${colors[type]} alert-dismissible fade show notification`;
            notification.innerHTML = `
                <i class="${icons[type]} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification && notification.parentNode) {
                    const alert = new bootstrap.Alert(notification);
                    alert.close();
                }
            }, duration);
        }

        // ✅ AUTRES FONCTIONS
        function exportData(format) {
            console.log('Export:', format);
            // Implémenter l'export si nécessaire
        }

        console.log('✅ Script Opérateurs chargé avec succès - Méthode index.blade.php');
    </script>
@endsection