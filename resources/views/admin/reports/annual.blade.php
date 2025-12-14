@extends('layouts.admin')

@section('title', $title ?? 'Rapport Annuel')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.analytics') }}">Analytics</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Rapports</a></li>
    <li class="breadcrumb-item active">Rapport Annuel</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-chart-line"></i> {{ $title ?? 'Rapport Annuel' }}</h2>
                    <div>
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour Rapports
                        </a>
                    </div>
                </div>

                <!-- Sélection de l'année -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-filter"></i> Sélection de l'année</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.reports.annual') }}" class="row g-3">
                            <div class="col-md-6">
                                <label for="year" class="form-label">Année</label>
                                <select name="year" id="year" class="form-select">
                                    @for ($y = now()->year; $y >= 2020; $y--)
                                        <option value="{{ $y }}" {{ ($year ?? now()->year) == $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-search"></i> Générer le rapport
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Période affichée -->
                <div class="alert alert-success">
                    <strong><i class="fas fa-calendar"></i> Période analysée :</strong> {{ $period }}
                </div>

                <!-- Statistiques annuelles -->
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

                <!-- Utilisateurs et Stats Globales -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-users"></i> Utilisateurs</h5>
                            </div>
                            <div class="card-body">
                                <h3 class="text-info">{{ $total_users ?? 0 }}</h3>
                                <p class="mb-0">Nouveaux utilisateurs cette année</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-secondary text-white">
                                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Statistiques Globales</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Total organisations :</strong> {{ $total_organisations_global ?? 0 }}</p>
                                <p class="mb-0"><strong>Total utilisateurs :</strong> {{ $total_users_global ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Détails mensuels -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-list"></i> Détails Mensuels</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mois</th>
                                        <th class="text-end">Organisations</th>
                                        <th class="text-end">Utilisateurs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(isset($monthly_data))
                                        @foreach($monthly_data as $month => $data)
                                            <tr>
                                                <td><strong>{{ $data['label'] }}</strong></td>
                                                <td class="text-end">{{ $data['organisations'] }}</td>
                                                <td class="text-end">{{ $data['users'] }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-download"></i> Exporter ce rapport</h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.exports.index') }}?type=annual&year={{ $year }}"
                                class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Excel
                            </a>
                            <a href="{{ route('admin.exports.index') }}?type=annual&year={{ $year }}&format=pdf"
                                class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a href="{{ route('admin.exports.index') }}?type=annual&year={{ $year }}&format=csv"
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