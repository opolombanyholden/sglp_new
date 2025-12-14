@extends('layouts.admin')

@section('title', $title ?? 'Exports de données')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.analytics') }}">Analytics</a></li>
    <li class="breadcrumb-item active">Exports</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-file-export"></i> {{ $title ?? 'Exports de données' }}</h2>
                    <div>
                        <a href="{{ route('admin.analytics') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Retour Analytics
                        </a>
                    </div>
                </div>

                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Exports de données</h5>
                    <p class="mb-0">
                        Exportez les données de la base de données dans différents formats (Excel, PDF, CSV, JSON).
                        Sélectionnez le type de données et le format souhaité.
                    </p>
                </div>

                <!-- Options d'export -->
                <div class="row">
                    @if(isset($available_exports))
                        @foreach($available_exports as $key => $label)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header 
                                                @if($key == 'organisations') bg-primary text-white
                                                @elseif($key == 'users') bg-success text-white
                                                @elseif($key == 'dossiers') bg-warning text-dark
                                                @else bg-info text-white
                                                @endif">
                                        <h5 class="mb-0">
                                            <i class="fas fa-
                                                        @if($key == 'organisations') building
                                                        @elseif($key == 'users') users
                                                        @elseif($key == 'dossiers') folder
                                                        @else chart-bar
                                                        @endif"></i>
                                            {{ $label }}
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Exportez les données {{ strtolower($label) }} dans le format de votre choix.</p>

                                        <div class="btn-group w-100" role="group">
                                            <a href="{{ route('admin.exports.' . $key) }}?format=excel" class="btn btn-success">
                                                <i class="fas fa-file-excel"></i> Excel
                                            </a>
                                            <a href="{{ route('admin.exports.' . $key) }}?format=pdf" class="btn btn-danger">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                            <a href="{{ route('admin.exports.' . $key) }}?format=csv" class="btn btn-info">
                                                <i class="fas fa-file-csv"></i> CSV
                                            </a>
                                            <a href="{{ route('admin.exports.' . $key) }}?format=json" class="btn btn-dark">
                                                <i class="fas fa-code"></i> JSON
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <!-- Exports par défaut si la variable n'est pas définie -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-building"></i> Export des organisations</h5>
                                </div>
                                <div class="card-body">
                                    <p>Exportez toutes les organisations enregistrées.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('admin.exports.organisations') }}?format=excel"
                                            class="btn btn-success">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>
                                        <a href="{{ route('admin.exports.organisations') }}?format=pdf" class="btn btn-danger">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a href="{{ route('admin.exports.organisations') }}?format=csv" class="btn btn-info">
                                            <i class="fas fa-file-csv"></i> CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="fas fa-users"></i> Export des utilisateurs</h5>
                                </div>
                                <div class="card-body">
                                    <p>Exportez tous les utilisateurs du système.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('admin.exports.users') }}?format=excel" class="btn btn-success">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>
                                        <a href="{{ route('admin.exports.users') }}?format=pdf" class="btn btn-danger">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a href="{{ route('admin.exports.users') }}?format=csv" class="btn btn-info">
                                            <i class="fas fa-file-csv"></i> CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h5 class="mb-0"><i class="fas fa-folder"></i> Export des dossiers</h5>
                                </div>
                                <div class="card-body">
                                    <p>Exportez tous les dossiers et leurs données.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('admin.exports.dossiers') }}?format=excel" class="btn btn-success">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>
                                        <a href="{{ route('admin.exports.dossiers') }}?format=pdf" class="btn btn-danger">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a href="{{ route('admin.exports.dossiers') }}?format=csv" class="btn btn-info">
                                            <i class="fas fa-file-csv"></i> CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Export des statistiques</h5>
                                </div>
                                <div class="card-body">
                                    <p>Exportez les statistiques globales du système.</p>
                                    <div class="btn-group w-100" role="group">
                                        <a href="{{ route('admin.exports.statistiques') }}?format=excel"
                                            class="btn btn-success">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>
                                        <a href="{{ route('admin.exports.statistiques') }}?format=pdf" class="btn btn-danger">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a href="{{ route('admin.exports.statistiques') }}?format=csv" class="btn btn-info">
                                            <i class="fas fa-file-csv"></i> CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Export global -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-database"></i> Export Global</h5>
                    </div>
                    <div class="card-body">
                        <p>Exportez toutes les données du système en une seule fois.</p>
                        <a href="{{ route('admin.exports.global') }}" class="btn btn-dark">
                            <i class="fas fa-download"></i> Télécharger l'export global (ZIP)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection