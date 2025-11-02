@extends('layouts.admin')

@section('title', 'Entités de Validation')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-building text-primary"></i> Entités de Validation
            </h1>
            <p class="text-muted mb-0">Gestion des services et directions de validation</p>
        </div>
        <a href="{{ route('admin.validation-entities.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nouvelle Entité
        </a>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Card principale -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Entités</h6>
        </div>
        <div class="card-body">
            <!-- Filtres -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="filterType" class="form-label">Type</label>
                    <select id="filterType" class="form-select">
                        <option value="">Tous les types</option>
                        <option value="direction">Direction</option>
                        <option value="service">Service</option>
                        <option value="departement">Département</option>
                        <option value="commission">Commission</option>
                        <option value="externe">Externe</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterStatus" class="form-label">Statut</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="1">Actif</option>
                        <option value="0">Inactif</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="searchInput" class="form-label">Recherche</label>
                    <input type="text" id="searchInput" class="form-control" placeholder="Rechercher par nom, code...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" id="btnFilter" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filtrer
                    </button>
                </div>
            </div>

            <!-- Tableau -->
            <div class="table-responsive">
                <table id="entitiesTable" class="table table-bordered table-hover" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">ID</th>
                            <th width="15%">Code</th>
                            <th width="25%">Nom</th>
                            <th width="12%">Type</th>
                            <th width="10%">Capacité</th>
                            <th width="10%">Statut</th>
                            <th width="23%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entities as $entity)
                        <tr>
                            <td>{{ $entity->id }}</td>
                            <td><code>{{ $entity->code }}</code></td>
                            <td>
                                <strong>{{ $entity->nom }}</strong>
                                @if($entity->description)
                                <br><small class="text-muted">{{ Str::limit($entity->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $typeClass = [
                                        'direction' => 'primary',
                                        'service' => 'info',
                                        'departement' => 'warning',
                                        'commission' => 'success',
                                        'externe' => 'secondary'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $typeClass[$entity->type] ?? 'secondary' }}">
                                    {{ ucfirst($entity->type) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $entity->capacite_traitement }} / jour</span>
                            </td>
                            <td class="text-center">
                                @if($entity->is_active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Actif
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> Inactif
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.validation-entities.show', $entity->id) }}" 
                                       class="btn btn-info" 
                                       title="Détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.validation-entities.edit', $entity->id) }}" 
                                       class="btn btn-warning" 
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-{{ $entity->is_active ? 'secondary' : 'success' }} btn-toggle-status" 
                                            data-id="{{ $entity->id }}"
                                            data-status="{{ $entity->is_active }}"
                                            title="{{ $entity->is_active ? 'Désactiver' : 'Activer' }}">
                                        <i class="fas fa-{{ $entity->is_active ? 'ban' : 'check' }}"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-danger btn-delete" 
                                            data-id="{{ $entity->id }}"
                                            data-nom="{{ $entity->nom }}"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($entities->hasPages())
            <div class="mt-3">
                {{ $entities->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer l'entité <strong id="deleteEntityName"></strong> ?</p>
                <p class="text-danger mb-0">
                    <i class="fas fa-info-circle"></i> Cette action est irréversible.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Fonction pour recharger le tableau
    function reloadTable() {
        location.reload();
    }
    
    // Toggle status (Activer/Désactiver)
    $('.btn-toggle-status').on('click', function() {
        const btn = $(this);
        const entityId = btn.data('id');
        const currentStatus = btn.data('status');
        const action = currentStatus ? 'désactiver' : 'activer';
        
        if (!confirm(`Êtes-vous sûr de vouloir ${action} cette entité ?`)) {
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: `/admin/validation-entities/${entityId}/toggle-status`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Afficher message de succès
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('.container-fluid').prepend(alertHtml);
                    
                    // Recharger après 1 seconde
                    setTimeout(reloadTable, 1000);
                } else {
                    alert('Erreur: ' + response.message);
                    btn.prop('disabled', false).html('<i class="fas fa-ban"></i>');
                }
            },
            error: function(xhr) {
                alert('Erreur lors du changement de statut');
                btn.prop('disabled', false).html('<i class="fas fa-ban"></i>');
            }
        });
    });
    
    // Bouton supprimer
    $('.btn-delete').on('click', function() {
        const entityId = $(this).data('id');
        const entityNom = $(this).data('nom');
        
        $('#deleteEntityName').text(entityNom);
        $('#deleteForm').attr('action', `/admin/validation-entities/${entityId}`);
        
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    });
    
    // Filtres
    $('#btnFilter').on('click', function() {
        const type = $('#filterType').val();
        const status = $('#filterStatus').val();
        const search = $('#searchInput').val();
        
        let url = '{{ route("admin.validation-entities.index") }}?';
        const params = [];
        
        if (type) params.push('type=' + type);
        if (status) params.push('is_active=' + status);
        if (search) params.push('search=' + search);
        
        if (params.length > 0) {
            url += params.join('&');
        }
        
        window.location.href = url;
    });
    
    // Recherche en temps réel (optionnel)
    let searchTimeout;
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#btnFilter').click();
        }, 500);
    });
    
    // Auto-dismiss alerts après 5 secondes
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush