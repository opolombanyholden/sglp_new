{{-- resources/views/admin/dossiers/supprimes.blade.php --}}
{{-- Vue accessible uniquement aux super_admin pour audit --}}
@extends('layouts.admin')

@section('title', 'Dossiers Supprimés (Audit)')

@section('content')
    <div class="container-fluid">
        <!-- Header avec titre et actions -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-archive me-2" style="color: #6c757d;"></i>
                    Dossiers Supprimés (Audit)
                </h1>
                <p class="text-muted">Vue d'audit des dossiers supprimés - Réservée aux super-administrateurs</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="{{ route('admin.dossiers.annules') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Retour aux Annulés
                </a>
            </div>
        </div>

        <!-- Alerte d'information -->
        <div class="alert alert-info mb-4">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note :</strong> Cette page affiche les dossiers qui ont été supprimés de l'interface mais qui sont
            conservés
            en base de données pour des raisons d'audit et de traçabilité. Ces données ne sont visibles que par les
            super-administrateurs.
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-dark shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                    Total Supprimés
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['total_supprimes'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-archive fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Supprimés ce mois
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['supprimes_ce_mois'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-minus fa-2x text-gray-300"></i>
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
                <form method="GET" action="{{ route('admin.dossiers.supprimes') }}" id="filterForm">
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
                                <a href="{{ route('admin.dossiers.supprimes') }}" class="btn btn-outline-secondary">
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
            <div class="card-header py-3 d-flex justify-content-between align-items-center bg-dark text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-archive me-2"></i>
                    Liste des Dossiers Supprimés (Audit)
                    @if($dossiers->total() > 0)
                        <span class="badge badge-light ms-2">{{ $dossiers->total() }}</span>
                    @endif
                </h6>
            </div>
            <div class="card-body">
                @if($dossiers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="dossiersTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Dossier</th>
                                    <th>Organisation</th>
                                    <th>Type</th>
                                    <th>Opération</th>
                                    <th>Date de Suppression</th>
                                    <th>Ancien Statut</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dossiers as $dossier)
                                    <tr class="table-secondary">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="status-icon me-2">
                                                    <i class="fas fa-archive text-secondary" title="Supprimé"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $dossier->numero_dossier }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        ID: {{ $dossier->id }}
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
                                            <span class="badge badge-secondary">
                                                {{ ucfirst(str_replace('_', ' ', $dossier->organisation->type ?? 'N/A')) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-dark">
                                                {{ ucfirst($dossier->type_operation ?? 'création') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                {{ \Carbon\Carbon::parse($dossier->deleted_at)->format('d/m/Y H:i') }}
                                                <br>
                                                <small class="text-muted">
                                                    Il y a {{ \Carbon\Carbon::parse($dossier->deleted_at)->diffForHumans() }}
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-dark">{{ $dossier->statut }}</span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-outline-info btn-sm" title="Voir détails" disabled>
                                                <i class="fas fa-eye"></i>
                                            </button>
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
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h5 class="text-gray-600">Aucun dossier supprimé</h5>
                        <p class="text-muted">
                            @if(request('search'))
                                Aucun dossier ne correspond à votre recherche.
                            @else
                                Aucun dossier n'a été supprimé définitivement.
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

        .border-left-dark {
            border-left: 0.25rem solid #343a40 !important;
        }

        .border-left-secondary {
            border-left: 0.25rem solid #6c757d !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(108, 117, 125, 0.1);
        }

        .badge {
            font-size: 0.75em;
        }
    </style>
@endpush