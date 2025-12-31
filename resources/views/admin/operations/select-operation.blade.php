@extends('layouts.admin')

@section('title', 'Sélectionner une Opération')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-1">
                <i class="fas fa-tasks me-2"></i>
                Sélectionner une Opération
            </h2>
            <p class="text-muted mb-0">Choisissez l'opération à effectuer sur cette organisation</p>
        </div>
        <a href="{{ route('admin.operations.select-organisation') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Changer d'organisation
        </a>
    </div>

    <!-- Carte organisation sélectionnée -->
    <div class="card mb-4 border-primary">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="avatar-circle-lg bg-primary text-white">
                        {{ strtoupper(substr($organisation->sigle ?? $organisation->nom, 0, 2)) }}
                    </div>
                </div>
                <div class="col">
                    <h4 class="mb-1">{{ $organisation->nom }}</h4>
                    @if($organisation->sigle)
                        <span class="badge bg-secondary me-2">{{ $organisation->sigle }}</span>
                    @endif
                    <span class="badge bg-info">{{ $organisation->organisationType->libelle ?? 'N/A' }}</span>
                    <p class="mb-0 text-muted mt-2">
                        <i class="fas fa-map-marker-alt me-1"></i> {{ $organisation->siege_social }}
                        @if($organisation->numero_recepisse)
                            <span class="ms-3"><i class="fas fa-file me-1"></i> {{ $organisation->numero_recepisse }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Opérations disponibles -->
    <div class="row">
        @foreach($operationTypes as $opType)
            @php
                $hasEnCours = isset($dossiersEnCours[$opType->code]);
                $dossierEnCours = $hasEnCours ? $dossiersEnCours[$opType->code] : null;
            @endphp
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 operation-card {{ $hasEnCours ? 'border-warning' : '' }}">
                    <div class="card-body">
                        <div class="operation-icon mb-3">
                            @switch($opType->code)
                                @case('modification')
                                    <i class="fas fa-edit text-info"></i>
                                    @break
                                @case('cessation')
                                    <i class="fas fa-ban text-danger"></i>
                                    @break
                                @case('ajout_adherent')
                                    <i class="fas fa-user-plus text-success"></i>
                                    @break
                                @case('retrait_adherent')
                                    <i class="fas fa-user-minus text-warning"></i>
                                    @break
                                @case('declaration_activite')
                                    <i class="fas fa-file-alt text-primary"></i>
                                    @break
                                @case('changement_statutaire')
                                    <i class="fas fa-gavel text-purple"></i>
                                    @break
                                @default
                                    <i class="fas fa-cog text-secondary"></i>
                            @endswitch
                        </div>
                        <h5 class="card-title">{{ $opType->libelle }}</h5>
                        <p class="card-text text-muted small">{{ $opType->description }}</p>
                        
                        @if($hasEnCours)
                            <div class="alert alert-warning small mb-3">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Un dossier est en cours pour cette opération
                                <br>
                                <small>N° {{ $dossierEnCours->numero_dossier }} - {{ $dossierEnCours->statut_label }}</small>
                            </div>
                            <a href="{{ route('admin.dossiers.show', $dossierEnCours->id) }}" class="btn btn-outline-warning btn-sm">
                                <i class="fas fa-eye me-1"></i> Voir le dossier
                            </a>
                        @else
                            <a href="{{ route('admin.operations.create', [$organisation->id, $opType->code]) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Créer un dossier
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Résumé des opérations existantes -->
    @if($dossiersEnCours->isNotEmpty())
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Dossiers en cours pour cette organisation
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>N° Dossier</th>
                                <th>Type d'opération</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dossiersEnCours as $dossier)
                                <tr>
                                    <td><code>{{ $dossier->numero_dossier }}</code></td>
                                    <td>{{ $dossier->type_operation_label }}</td>
                                    <td>
                                        <span class="badge bg-{{ $dossier->statut_color }}">
                                            {{ $dossier->statut_label }}
                                        </span>
                                    </td>
                                    <td>{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.dossiers.show', $dossier->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
.avatar-circle-lg {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.5rem;
}
.page-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
}
.operation-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}
.operation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border-color: var(--gabon-green, #009e3f);
}
.operation-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f0f0f0, #e0e0e0);
    display: flex;
    align-items: center;
    justify-content: center;
}
.operation-icon i {
    font-size: 1.5rem;
}
.text-purple {
    color: #7c3aed;
}
</style>
@endsection
