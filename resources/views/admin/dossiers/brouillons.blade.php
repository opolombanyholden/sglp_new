{{-- resources/views/admin/dossiers/brouillons.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dossiers Non Soumis')

@section('content')
    <div class="container-fluid">
        <!-- Header avec titre et actions -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-edit me-2" style="color: #6c757d;"></i>
                    Dossiers Non Soumis
                </h1>
                <p class="text-muted">Dossiers en attente de soumission par les opérateurs</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Total Non Soumis
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['total_brouillons'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-edit fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    En Attente +7 jours
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['en_attente_plus_7_jours'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres de recherche -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-filter me-2"></i>Recherche
                </h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.dossiers.brouillons') }}" id="filterForm">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                    placeholder="Rechercher par nom, sigle ou numéro de dossier..." autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.dossiers.brouillons') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Réinitialiser
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des dossiers -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    Liste des Dossiers Non Soumis
                    @if($dossiers->total() > 0)
                        <span class="badge badge-secondary ms-2">{{ $dossiers->total() }}</span>
                    @endif
                </h6>
            </div>
            <div class="card-body">
                @if($dossiers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dossiersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Dossier</th>
                                    <th>Organisation</th>
                                    <th>Type</th>
                                    <th>Opération</th>
                                    <th>Dernière Modification</th>
                                    <th>Délai</th>
                                    <th width="120">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dossiers as $dossier)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="status-icon me-2">
                                                    <i class="fas fa-edit text-secondary" title="Brouillon"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $dossier->numero_dossier }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Statut: <span class="badge badge-secondary">Non Soumis</span>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $dossier->organisation->nom ?? 'N/A' }}</strong>
                                                @if($dossier->organisation->sigle ?? null)
                                                    <br><small class="text-muted">({{ $dossier->organisation->sigle }})</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ ucfirst(str_replace('_', ' ', $dossier->organisation->type ?? 'N/A')) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">
                                                {{ ucfirst($dossier->type_operation ?? 'création') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                {{ \Carbon\Carbon::parse($dossier->updated_at)->format('d/m/Y') }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($dossier->updated_at)->format('H:i') }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $delai = \Carbon\Carbon::parse($dossier->updated_at)->diffInDays(now());
                                            @endphp
                                            <div class="text-center">
                                                <span
                                                    class="badge {{ $delai > 7 ? 'badge-danger' : ($delai > 3 ? 'badge-warning' : 'badge-success') }}">
                                                    {{ $delai }} jour{{ $delai > 1 ? 's' : '' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.dossiers.show', $dossier->id) }}"
                                                    class="btn btn-outline-primary btn-sm" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.dossiers.edit', $dossier->id) }}"
                                                    class="btn btn-outline-warning btn-sm" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Affichage de {{ $dossiers->firstItem() ?? 0 }} à {{ $dossiers->lastItem() ?? 0 }}
                            sur {{ $dossiers->total() }} résultats
                        </div>
                        <div>
                            {{ $dossiers->links() }}
                        </div>
                    </div>
                @else
                    <!-- État vide -->
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-4x text-gray-300 mb-3"></i>
                        <h5 class="text-gray-600">Aucun dossier non soumis</h5>
                        <p class="text-muted">
                            @if(request('search'))
                                Aucun dossier ne correspond à votre recherche.
                                <br>
                                <a href="{{ route('admin.dossiers.brouillons') }}" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="fas fa-times"></i> Effacer la recherche
                                </a>
                            @else
                                Tous les dossiers ont été soumis.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .status-icon {
            width: 20px;
            text-align: center;
        }

        .card {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            border: 1px solid #e3e6f0;
        }

        .border-left-secondary {
            border-left: 0.25rem solid #6c757d !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .badge {
            font-size: 0.75em;
        }

        .btn-group-sm>.btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.775rem;
        }
    </style>
@endpush