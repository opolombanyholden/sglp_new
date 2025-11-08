@extends('layouts.admin')

@section('title', 'Vérifications de Documents')

@section('content')
<div class="container-fluid py-4">
    
    <!-- En-tête avec statistiques -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="fas fa-shield-alt text-primary"></i>
                        Vérifications de Documents
                    </h2>
                    <p class="text-muted mb-0">Historique des vérifications publiques via QR Code</p>
                </div>
                <div>
                    <a href="{{ route('admin.document-verifications.export') }}" 
                       class="btn btn-success">
                        <i class="fas fa-file-excel mr-1"></i>
                        Exporter CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded p-3">
                                <i class="fas fa-check-circle fa-2x text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Total Vérifications</p>
                            <h3 class="mb-0">{{ number_format($stats['total']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded p-3">
                                <i class="fas fa-thumbs-up fa-2x text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Réussies</p>
                            <h3 class="mb-0">{{ number_format($stats['reussies']) }}</h3>
                            <small class="text-success">
                                {{ $stats['total'] > 0 ? number_format(($stats['reussies'] / $stats['total']) * 100, 1) : 0 }}%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-danger bg-opacity-10 rounded p-3">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Échouées</p>
                            <h3 class="mb-0">{{ number_format($stats['echouees']) }}</h3>
                            <small class="text-danger">
                                {{ $stats['total'] > 0 ? number_format(($stats['echouees'] / $stats['total']) * 100, 1) : 0 }}%
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded p-3">
                                <i class="fas fa-calendar-day fa-2x text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="text-muted mb-1 small">Aujourd'hui</p>
                            <h3 class="mb-0">{{ number_format($stats['aujourd_hui']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.document-verifications.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Recherche</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="N° document..."
                               value="{{ request('search') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Statut</label>
                        <select name="verification_reussie" class="form-control">
                            <option value="">Tous</option>
                            <option value="1" {{ request('verification_reussie') === '1' ? 'selected' : '' }}>
                                Réussies
                            </option>
                            <option value="0" {{ request('verification_reussie') === '0' ? 'selected' : '' }}>
                                Échouées
                            </option>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Date début</label>
                        <input type="date" 
                               name="date_debut" 
                               class="form-control"
                               value="{{ request('date_debut') }}">
                    </div>
                    
                    <div class="col-md-2">
                        <label class="form-label small">Date fin</label>
                        <input type="date" 
                               name="date_fin" 
                               class="form-control"
                               value="{{ request('date_fin') }}">
                    </div>
                    
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search mr-1"></i> Filtrer
                        </button>
                        <a href="{{ route('admin.document-verifications.index') }}" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-redo mr-1"></i> Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des vérifications -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            @if($verifications->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date/Heure</th>
                            <th>Document</th>
                            <th>Organisation</th>
                            <th>Template</th>
                            <th>IP Address</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($verifications as $verification)
                        <tr>
                            <td>
                                <div class="small">
                                    <i class="fas fa-calendar text-muted mr-1"></i>
                                    {{ $verification->verified_at->format('d/m/Y') }}
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-clock text-muted mr-1"></i>
                                    {{ $verification->verified_at->format('H:i:s') }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    {{ $verification->documentGeneration->numero_document ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <div class="small">
                                    {{ Str::limit($verification->documentGeneration->organisation->nom ?? 'N/A', 40) }}
                                </div>
                            </td>
                            <td>
                                <div class="small">
                                    {{ $verification->documentGeneration->template->nom ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <code class="small">{{ $verification->ip_address }}</code>
                            </td>
                            <td>
                                @if($verification->verification_reussie)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check mr-1"></i> Réussie
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times mr-1"></i> Échouée
                                    </span>
                                    @if($verification->motif_echec)
                                    <div class="small text-danger mt-1">
                                        {{ $verification->motif_echec }}
                                    </div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($verification->documentGeneration)
                                <a href="{{ route('admin.document-verifications.history', $verification->documentGeneration) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   title="Voir historique du document">
                                    <i class="fas fa-history"></i>
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Affichage de {{ $verifications->firstItem() ?? 0 }} à {{ $verifications->lastItem() ?? 0 }} 
                    sur {{ $verifications->total() }} vérifications
                </div>
                {{ $verifications->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                <p class="text-muted">Aucune vérification trouvée</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection