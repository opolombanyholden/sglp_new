@extends('layouts.admin')

@section('title', $title ?? 'Rapport Personnalisé')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.analytics') }}">Analytics</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Rapports</a></li>
    <li class="breadcrumb-item active">Rapport Personnalisé</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-sliders-h"></i> {{ $title ?? 'Rapport Personnalisé' }}</h2>
                    <div>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour Rapports
                        </a>
                    </div>
                </div>

                <!-- Sélection de période personnalisée -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Sélection de la période</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.custom') }}" class="row g-3">
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Date de début</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    value="{{ $start_date ?? now()->subMonth()->toDateString() }}" required>
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">Date de fin</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    value="{{ $end_date ?? now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-info w-100">
                                    <i class="fas fa-search"></i> Générer le rapport
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Période affichée -->
                <div class="alert alert-info">
                    <strong><i class="fas fa-calendar-check"></i> Période analysée :</strong> {{ $period }}
                </div>

                <!-- Statistiques de la période -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $total_organisations ?? 0 }}</h3>
                                <p class="mb-0"><i class="fas fa-building"></i> Nouvelles Organisations</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $organisations_approuvees ?? 0 }}</h3>
                                <p class="mb-0"><i class="fas fa-check-circle"></i> Approuvées</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $organisations_en_attente ?? 0 }}</h3>
                                <p class="mb-0"><i class="fas fa-clock"></i> En Attente</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $organisations_rejetees ?? 0 }}</h3>
                                <p class="mb-0"><i class="fas fa-times-circle"></i> Rejetées</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Utilisateurs -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-users"></i> Utilisateurs</h5>
                            </div>
                            <div class="card-body">
                                <h3 class="text-info">{{ $total_users ?? 0 }}</h3>
                                <p class="mb-0">Nouveaux utilisateurs pendant la période</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Statistiques Globales</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Total organisations :</strong> {{ $total_organisations_global ?? 0 }}</p>
                                <p class="mb-0"><strong>Total utilisateurs :</strong> {{ $total_users_global ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Distribution par statut -->
                @if(isset($status_distribution) && $status_distribution->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Distribution par Statut</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Statut</th>
                                            <th class="text-end">Nombre</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($status_distribution as $statut => $count)
                                            <tr>
                                                <td>
                                                    <span class="badge 
                                                            @if($statut == 'approuve') bg-success
                                                            @elseif($statut == 'rejete') bg-danger
                                                            @elseif(in_array($statut, ['soumis', 'en_validation'])) bg-warning
                                                            @else bg-secondary
                                                            @endif">
                                                        {{ ucfirst($statut) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">{{ $count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-download"></i> Exporter ce rapport</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.exports.index') }}?type=custom&start_date={{ $start_date }}&end_date={{ $end_date }}"
                                class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                            <a href="{{ route('admin.exports.index') }}?type=custom&start_date={{ $start_date }}&end_date={{ $end_date }}&format=pdf"
                                class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a href="{{ route('admin.exports.index') }}?type=custom&start_date={{ $start_date }}&end_date={{ $end_date }}&format=csv"
                                class="btn btn-info">
                                <i class="fas fa-file-csv"></i> CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection