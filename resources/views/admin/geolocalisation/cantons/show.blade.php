@extends('layouts.admin')

@section('title', 'Détails Canton - ' . $canton->nom)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Administration</a></li>
                        <li class="breadcrumb-item"><a href="#">Géolocalisation</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.geolocalisation.cantons.index') }}">Cantons</a></li>
                        <li class="breadcrumb-item active">{{ $canton->nom }}</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-pine-tree"></i> Canton {{ $canton->nom }}
                    @if(!$canton->is_active)
                        <span class="badge bg-danger ms-2">Inactif</span>
                    @endif
                </h4>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.geolocalisation.cantons.edit', $canton) }}" class="btn btn-warning">
                    <i class="mdi mdi-pencil"></i> Modifier
                </a>
                <a href="{{ route('admin.geolocalisation.cantons.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Retour à la liste
                </a>
                <button type="button" class="btn btn-soft-danger delete-btn" 
                        data-id="{{ $canton->id }}"
                        data-nom="{{ $canton->nom }}"
                        data-can-delete="{{ $canton->canBeDeleted() ? 'true' : 'false' }}"
                        data-blockers="{{ implode(', ', $canton->deletion_blockers) }}">
                    <i class="mdi mdi-delete"></i> Supprimer
                </button>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations principales -->
        <div class="col-lg-8">
            <!-- Informations générales -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-information"></i> Informations générales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="40%" class="text-muted">Nom :</th>
                                    <td><strong>{{ $canton->nom }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Code :</th>
                                    <td><code>{{ $canton->code }}</code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Statut :</th>
                                    <td>
                                        @if($canton->is_active)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless mb-0">
                                <tr>
                                    <th width="40%" class="text-muted">Département :</th>
                                    <td>{{ $canton->departement->nom }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Province :</th>
                                    <td>{{ $canton->departement->province->nom }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($canton->description)
                        <hr>
                        <div>
                            <h6 class="text-muted">Description :</h6>
                            <p class="mb-0">{{ $canton->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Regroupements -->
            @if($canton->regroupements->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="mdi mdi-home-group"></i> Regroupements ({{ $canton->regroupements->count() }})
                            </h5>
                            <a href="#" class="btn btn-sm btn-primary">
                                <i class="mdi mdi-plus"></i> Ajouter un regroupement
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nom</th>
                                        <th>Code</th>
                                        <th class="text-center">Villages</th>
                                        <th class="text-center">Statut</th>
                                        <th width="100" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($canton->regroupements as $regroupement)
                                        <tr>
                                            <td>
                                                <strong>{{ $regroupement->nom }}</strong>
                                                @if($regroupement->description)
                                                    <br><small class="text-muted">{{ Str::limit($regroupement->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td><code>{{ $regroupement->code }}</code></td>
                                            <td class="text-center">
                                                <span class="badge bg-soft-info text-info">
                                                    {{ $regroupement->nombre_villages ?? 0 }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($regroupement->is_active)
                                                    <span class="badge bg-success">Actif</span>
                                                @else
                                                    <span class="badge bg-danger">Inactif</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="#" class="btn btn-soft-info" title="Voir">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-soft-warning" title="Modifier">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="mdi mdi-home-group"></i> Regroupements
                        </h5>
                    </div>
                    <div class="card-body text-center py-4">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title bg-soft-success text-success rounded-circle">
                                <i class="mdi mdi-home-group font-24"></i>
                            </div>
                        </div>
                        <h5>Aucun regroupement</h5>
                        <p class="text-muted">Ce canton ne contient encore aucun regroupement de villages.</p>
                        <a href="#" class="btn btn-primary">
                            <i class="mdi mdi-plus-circle"></i> Ajouter le premier regroupement
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistiques -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-chart-pie"></i> Statistiques
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h3 class="text-success mb-1">{{ $stats['regroupements_count'] }}</h3>
                                <small class="text-muted">Regroupements</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h3 class="text-primary mb-1">{{ $stats['villages_count'] }}</h3>
                            <small class="text-muted">Villages</small>
                        </div>
                        <div class="col-6">
                            <div class="border-end">
                                <h3 class="text-info mb-1">{{ $stats['organisations_count'] }}</h3>
                                <small class="text-muted">Organisations</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h3 class="text-warning mb-1">{{ $stats['adherents_count'] }}</h3>
                            <small class="text-muted">Adhérents</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-clock-outline"></i> Historique
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline-sm">
                        <div class="timeline-sm-item">
                            <div class="timeline-sm-marker bg-success"></div>
                            <div class="timeline-sm-content">
                                <p class="mb-0 text-muted">
                                    <strong>Créé</strong><br>
                                    {{ $canton->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>
                        
                        @if($canton->updated_at != $canton->created_at)
                            <div class="timeline-sm-item">
                                <div class="timeline-sm-marker bg-info"></div>
                                <div class="timeline-sm-content">
                                    <p class="mb-0 text-muted">
                                        <strong>Dernière modification</strong><br>
                                        {{ $canton->updated_at->format('d/m/Y à H:i') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer le canton <strong id="delete-nom"></strong> ?</p>
                <div id="delete-blockers" class="alert alert-warning" style="display: none;">
                    <i class="mdi mdi-alert-triangle"></i> 
                    <strong>Impossible de supprimer :</strong>
                    <span id="delete-blockers-text"></span>
                </div>
                <p class="text-danger small" id="delete-warning">
                    <i class="mdi mdi-alert-triangle"></i> 
                    Cette action est irréversible et supprimera également tous les regroupements et villages liés.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirm-delete-btn">
                        <i class="mdi mdi-delete"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.page-title {
    color: #2c5282;
    font-weight: 600;
}

.breadcrumb-item a {
    color: #4299e1;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #2b77c7;
    text-decoration: underline;
}

.avatar-lg {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-soft-success {
    background-color: #d4edda;
}

.bg-soft-info {
    background-color: #d1ecf1;
}

.btn-soft-info {
    background-color: #d1ecf1;
    color: #0c5460;
}

.btn-soft-warning {
    background-color: #fff3cd;
    color: #856404;
}

.btn-soft-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.timeline-sm {
    position: relative;
    padding-left: 30px;
}

.timeline-sm-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-sm-marker {
    position: absolute;
    left: -30px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
}

.timeline-sm-marker.bg-success {
    background-color: #38a169;
}

.timeline-sm-marker.bg-info {
    background-color: #4299e1;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la suppression
    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const nom = this.dataset.nom;
            const canDelete = this.dataset.canDelete === 'true';
            const blockers = this.dataset.blockers;
            
            document.getElementById('delete-nom').textContent = nom;
            document.getElementById('delete-form').action = `/admin/geolocalisation/cantons/${id}`;
            
            if (!canDelete && blockers) {
                document.getElementById('delete-blockers').style.display = 'block';
                document.getElementById('delete-blockers-text').textContent = blockers;
                document.getElementById('delete-warning').style.display = 'none';
                document.getElementById('confirm-delete-btn').disabled = true;
                document.getElementById('confirm-delete-btn').classList.add('disabled');
            } else {
                document.getElementById('delete-blockers').style.display = 'none';
                document.getElementById('delete-warning').style.display = 'block';
                document.getElementById('confirm-delete-btn').disabled = false;
                document.getElementById('confirm-delete-btn').classList.remove('disabled');
            }
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
});
</script>
@endpush