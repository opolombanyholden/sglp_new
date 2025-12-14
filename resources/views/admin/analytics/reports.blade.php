@extends('layouts.admin')

@section('title', 'Rapports & Statistiques')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.analytics') }}">Analytics</a></li>
    <li class="breadcrumb-item active">Rapports</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-chart-bar"></i> {{ $title ?? 'Rapports & Statistiques' }}</h2>
                    <div>
                        <a href="{{ route('admin.analytics') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour Analytics
                        </a>
                    </div>
                </div>

                <!-- Statistiques globales -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $total_organisations ?? 0 }}</h3>
                                <p class="mb-0"><i class="fas fa-building"></i> Total Organisations</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $organisations_actives ?? 0 }}</h3>
                                <p class="mb-0"><i class="fas fa-check-circle"></i> Organisations Actives</p>
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
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h3 class="mb-0">{{ $total_users ?? 0 }}</h3>
                                <p class="mb-0"><i class="fas fa-users"></i> Utilisateurs</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rapports disponibles -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-file-alt"></i> Rapports Mensuels</h5>
                            </div>
                            <div class="card-body">
                                <p>Générez des rapports mensuels détaillés sur les activités et les statistiques.</p>
                                <a href="{{ route('admin.reports.monthly') }}" class="btn btn-primary">
                                    <i class="fas fa-calendar-alt"></i> Voir le Rapport Mensuel
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-file-contract"></i> Rapports Annuels</h5>
                            </div>
                            <div class="card-body">
                                <p>Consultez les rapports annuels consolidés et les analyses de tendances.</p>
                                <a href="{{ route('admin.reports.annual') }}" class="btn btn-success">
                                    <i class="fas fa-chart-line"></i> Voir le Rapport Annuel
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-cog"></i> Rapport Personnalisé</h5>
                            </div>
                            <div class="card-body">
                                <p>Créez un rapport personnalisé selon vos critères et périodes spécifiques.</p>
                                <a href="{{ route('admin.reports.custom') }}" class="btn btn-info">
                                    <i class="fas fa-sliders-h"></i> Créer un Rapport Personnalisé
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-download"></i> Exports</h5>
                            </div>
                            <div class="card-body">
                                <p>Exportez les données en différents formats (Excel, PDF, CSV).</p>
                                <a href="{{ route('admin.exports.index') }}" class="btn btn-warning">
                                    <i class="fas fa-file-export"></i> Accéder aux Exports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info supplémentaire -->
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Information</h5>
                    <p class="mb-0">
                        Les rapports sont générés en temps réel basés sur les données actuelles de la base de données.
                        Pour des analyses plus approfondies, consultez la section <a href="{{ route('admin.analytics') }}"
                            class="alert-link">Analytics</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection