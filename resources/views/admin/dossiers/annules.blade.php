{{-- resources/views/admin/dossiers/annules.blade.php --}}
@extends('layouts.admin')

@section('title', 'Dossiers Annulés')

@section('content')
    <div class="container-fluid">
        <!-- Header avec titre et actions -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-ban me-2" style="color: #dc3545;"></i>
                    Dossiers Annulés
                </h1>
                <p class="text-muted">Dossiers annulés (corbeille) - Peuvent être supprimés définitivement</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Actualiser
                    </button>
                    <a href="{{ route('admin.dossiers.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="row mb-4">
            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Total Annulés
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['total_annules'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-ban fa-2x text-gray-300"></i>
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
                                    Annulés ce mois
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['annules_ce_mois'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-times fa-2x text-gray-300"></i>
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
                <form method="GET" action="{{ route('admin.dossiers.annules') }}" id="filterForm">
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
                                <a href="{{ route('admin.dossiers.annules') }}" class="btn btn-outline-secondary">
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
                    Liste des Dossiers Annulés
                    @if($dossiers->total() > 0)
                        <span class="badge badge-danger ms-2">{{ $dossiers->total() }}</span>
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
                                    <th>Date d'Annulation</th>
                                    <th>Motif</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dossiers as $dossier)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="status-icon me-2">
                                                    <i class="fas fa-ban text-danger" title="Annulé"></i>
                                                </div>
                                                <div>
                                                    <strong>{{ $dossier->numero_dossier }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        Statut: <span class="badge badge-danger">Annulé</span>
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
                                            <small class="text-muted">
                                                {{ Str::limit($dossier->motif_rejet ?? 'Non spécifié', 50) }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.dossiers.show', $dossier->id) }}"
                                                    class="btn btn-outline-primary btn-sm" title="Voir détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                    title="Supprimer définitivement"
                                                    onclick="confirmDelete({{ $dossier->id }}, '{{ $dossier->numero_dossier }}')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>

                                            <!-- Formulaire de suppression caché -->
                                            <form id="delete-form-{{ $dossier->id }}"
                                                action="{{ route('admin.dossiers.delete-permanently', $dossier->id) }}"
                                                method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
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
                        <h5 class="text-gray-600">Aucun dossier annulé</h5>
                        <p class="text-muted">
                            @if(request('search'))
                                Aucun dossier ne correspond à votre recherche.
                                <br>
                                <a href="{{ route('admin.dossiers.annules') }}" class="btn btn-outline-primary btn-sm mt-2">
                                    <i class="fas fa-times"></i> Effacer la recherche
                                </a>
                            @else
                                La corbeille est vide.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmation de suppression
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer définitivement le dossier <strong
                            id="deleteDossierNumero"></strong> ?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note :</strong> Le dossier sera masqué de l'interface mais conservé en base de données pour
                        audit.
                        Seuls les super-administrateurs pourront y accéder.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash-alt me-2"></i>Supprimer définitivement
                    </button>
                </div>
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

        .border-left-danger {
            border-left: 0.25rem solid #dc3545 !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(220, 53, 69, 0.05);
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

@push('scripts')
    <script>
        let deleteFormId = null;

        function confirmDelete(dossierId, numeroDossier) {
            deleteFormId = dossierId;
            document.getElementById('deleteDossierNumero').textContent = numeroDossier;

            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
        }

        document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
            if (deleteFormId) {
                document.getElementById('delete-form-' + deleteFormId).submit();
            }
        });
    </script>
@endpush