@extends('layouts.admin')

@section('title', 'Détails Regroupement - ' . $regroupement->nom)

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
                        <li class="breadcrumb-item"><a href="{{ route('admin.geolocalisation.regroupements.index') }}">Regroupements</a></li>
                        <li class="breadcrumb-item active">{{ $regroupement->nom }}</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-home-group"></i> Regroupement {{ $regroupement->nom }}
                    @if(!$regroupement->is_active)
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
                <a href="{{ route('admin.geolocalisation.regroupements.edit', $regroupement) }}" class="btn btn-warning">
                    <i class="mdi mdi-pencil"></i> Modifier
                </a>
                <a href="{{ route('admin.geolocalisation.regroupements.index') }}" class="btn btn-secondary">
                    <i class="mdi mdi-arrow-left"></i> Retour à la liste
                </a>
                <button type="button" class="btn btn-soft-danger delete-btn" 
                        data-id="{{ $regroupement->id }}"
                        data-nom="{{ $regroupement->nom }}">
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
                                    <td><strong>{{ $regroupement->nom }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Code :</th>
                                    <td><code>{{ $regroupement->code }}</code></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Statut :</th>
                                    <td>
                                        @if($regroupement->is_active)
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
                                    <th width="40%" class="text-muted">Canton :</th>
                                    <td>{{ $regroupement->canton->nom }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Département :</th>
                                    <td>{{ $regroupement->canton->departement->nom }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Province :</th>
                                    <td>{{ $regroupement->canton->departement->province->nom }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($regroupement->description)
                        <hr>
                        <div>
                            <h6 class="text-muted">Description :</h6>
                            <p class="mb-0">{{ $regroupement->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
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
                                    {{ $regroupement->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>
                        </div>
                        
                        @if($regroupement->updated_at != $regroupement->created_at)
                            <div class="timeline-sm-item">
                                <div class="timeline-sm-marker bg-info"></div>
                                <div class="timeline-sm-content">
                                    <p class="mb-0 text-muted">
                                        <strong>Dernière modification</strong><br>
                                        {{ $regroupement->updated_at->format('d/m/Y à H:i') }}
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
                <p>Êtes-vous sûr de vouloir supprimer le regroupement <strong id="delete-nom"></strong> ?</p>
                <p class="text-danger small" id="delete-warning">
                    <i class="mdi mdi-alert-triangle"></i> 
                    Cette action est irréversible et supprimera également toutes les données liées.
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
            
            document.getElementById('delete-nom').textContent = nom;
            document.getElementById('delete-form').action = `/admin/geolocalisation/regroupements/${id}`;
            
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        });
    });
});
</script>
@endpush