@extends('layouts.admin')

@section('title', 'Détails Entité de Validation')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-building text-info"></i> Détails de l'Entité
            </h1>
            <p class="text-muted mb-0">
                <code>{{ $entity->code }}</code>
            </p>
        </div>
        <div>
            <a href="{{ route('admin.validation-entities.edit', $entity->id) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Modifier
            </a>
            <a href="{{ route('admin.validation-entities.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- Colonne gauche - Informations -->
        <div class="col-lg-8">
            <!-- Informations principales -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle"></i> Informations Principales
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Code</label>
                            <p class="mb-0">
                                <code class="fs-5">{{ $entity->code }}</code>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Type</label>
                            <p class="mb-0">
                                @php
                                    $typeClass = [
                                        'direction' => 'primary',
                                        'service' => 'info',
                                        'departement' => 'warning',
                                        'commission' => 'success',
                                        'externe' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $typeClass[$entity->type] ?? 'secondary' }} fs-6">
                                    {{ ucfirst($entity->type) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Nom de l'Entité</label>
                        <h5 class="mb-0">{{ $entity->nom }}</h5>
                    </div>

                    @if($entity->description)
                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <p class="mb-0">{{ $entity->description }}</p>
                    </div>
                    @endif

                    <div class="row">
                        @if($entity->email_notification)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Email de Notification</label>
                            <p class="mb-0">
                                <a href="mailto:{{ $entity->email_notification }}">
                                    <i class="fas fa-envelope"></i> {{ $entity->email_notification }}
                                </a>
                            </p>
                        </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Capacité de Traitement</label>
                            <p class="mb-0">
                                <span class="badge bg-secondary fs-6">
                                    <i class="fas fa-tasks"></i> {{ $entity->capacite_traitement }} dossiers / jour
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="text-muted small">Statut</label>
                        <p class="mb-0">
                            @if($entity->is_active)
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check-circle"></i> Actif
                                </span>
                            @else
                                <span class="badge bg-danger fs-6">
                                    <i class="fas fa-times-circle"></i> Inactif
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Horaires de travail -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-warning text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-clock"></i> Horaires de Travail
                    </h6>
                </div>
                <div class="card-body">
                    @php
                    $horaires = json_decode($entity->horaires_travail, true) ?? [];
                    $joursMap = [
                        'lundi' => 'Lundi',
                        'mardi' => 'Mardi',
                        'mercredi' => 'Mercredi',
                        'jeudi' => 'Jeudi',
                        'vendredi' => 'Vendredi',
                        'samedi' => 'Samedi',
                        'dimanche' => 'Dimanche'
                    ];
                    @endphp

                    @if(empty($horaires))
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Aucun horaire défini
                    </p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="30%">Jour</th>
                                    <th width="35%">Heure de début</th>
                                    <th width="35%">Heure de fin</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($horaires as $jour => $heures)
                                <tr>
                                    <td>
                                        <i class="fas fa-calendar-day text-primary"></i> 
                                        <strong>{{ $joursMap[$jour] ?? ucfirst($jour) }}</strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-clock text-success"></i> {{ $heures[0] ?? '-' }}
                                    </td>
                                    <td>
                                        <i class="fas fa-clock text-danger"></i> {{ $heures[1] ?? '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Workflow Steps assignés -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-project-diagram"></i> Étapes de Workflow Assignées
                        <span class="badge bg-light text-dark ms-2">{{ $stats['workflow_steps_assignes'] }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($stats['workflow_steps_assignes'] > 0)
                    @php
                    $assignedSteps = DB::table('workflow_step_entities as wse')
                        ->join('workflow_steps as ws', 'wse.workflow_step_id', '=', 'ws.id')
                        ->where('wse.validation_entity_id', $entity->id)
                        ->select('ws.id', 'ws.code', 'ws.libelle', 'ws.type_organisation', 'ws.type_operation', 'ws.numero_passage', 'wse.ordre')
                        ->orderBy('ws.type_organisation')
                        ->orderBy('ws.type_operation')
                        ->orderBy('ws.numero_passage')
                        ->get();
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="25%">Étape</th>
                                    <th width="20%">Organisation</th>
                                    <th width="20%">Opération</th>
                                    <th width="15%">Passage</th>
                                    <th width="15%">Ordre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignedSteps as $step)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $step->libelle }}</strong>
                                        <br><small class="text-muted">{{ $step->code }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $step->type_organisation)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $step->type_operation)) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $step->numero_passage }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">{{ $step->ordre }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle"></i> Cette entité n'est assignée à aucune étape de workflow
                    </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Colonne droite - Statistiques -->
        <div class="col-lg-4">
            <!-- Statistiques de validation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar"></i> Statistiques de Validation
                    </h6>
                </div>
                <div class="card-body">
                    <!-- Total validations -->
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-clipboard-list fa-2x text-primary"></i>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0">{{ $stats['total_validations'] }}</h3>
                                <small class="text-muted">Total Validations</small>
                            </div>
                        </div>
                    </div>

                    <!-- En cours -->
                    <div class="mb-3 p-3 bg-warning bg-opacity-10 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0">{{ $stats['validations_en_cours'] }}</h3>
                                <small class="text-muted">En Attente</small>
                            </div>
                        </div>
                    </div>

                    <!-- Approuvées -->
                    <div class="mb-3 p-3 bg-success bg-opacity-10 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0">{{ $stats['validations_approuvees'] }}</h3>
                                <small class="text-muted">Approuvées</small>
                            </div>
                        </div>
                    </div>

                    <!-- Rejetées -->
                    <div class="mb-3 p-3 bg-danger bg-opacity-10 rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0">{{ $stats['validations_rejetees'] }}</h3>
                                <small class="text-muted">Rejetées</small>
                            </div>
                        </div>
                    </div>

                    <!-- Taux d'approbation -->
                    @if($stats['total_validations'] > 0)
                    @php
                    $tauxApprobation = round(($stats['validations_approuvees'] / $stats['total_validations']) * 100, 1);
                    @endphp
                    <div class="mt-4">
                        <label class="text-muted small">Taux d'Approbation</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: {{ $tauxApprobation }}%"
                                 aria-valuenow="{{ $tauxApprobation }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ $tauxApprobation }}%
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations système -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-database"></i> Informations Système
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>ID :</strong> 
                        <span class="badge bg-secondary">{{ $entity->id }}</span>
                    </p>
                    <hr>
                    <p class="mb-2">
                        <strong>Créé le :</strong><br>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> 
                            {{ \Carbon\Carbon::parse($entity->created_at)->format('d/m/Y à H:i') }}
                        </small>
                    </p>
                    <p class="mb-0">
                        <strong>Dernière modification :</strong><br>
                        <small class="text-muted">
                            <i class="fas fa-calendar"></i> 
                            {{ \Carbon\Carbon::parse($entity->updated_at)->format('d/m/Y à H:i') }}
                        </small>
                    </p>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow">
                <div class="card-header py-3 bg-dark text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-bolt"></i> Actions Rapides
                    </h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.validation-entities.edit', $entity->id) }}" 
                       class="btn btn-warning w-100 mb-2">
                        <i class="fas fa-edit"></i> Modifier l'Entité
                    </a>
                    <a href="{{ route('admin.validation-entities.index') }}" 
                       class="btn btn-secondary w-100">
                        <i class="fas fa-list"></i> Liste des Entités
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush