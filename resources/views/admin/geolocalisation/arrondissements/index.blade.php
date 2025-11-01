@extends('layouts.admin')

@section('title', 'Gestion des Arrondissements')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-building me-2"></i>
                        Arrondissements
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-2">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.dashboard') }}">Administration</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#">Géolocalisation</a>
                            </li>
                            <li class="breadcrumb-item active">Arrondissements</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.geolocalisation.arrondissements.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Nouvel Arrondissement
                </a>
            </div>

            {{-- Messages flash --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Filtres et recherche --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filtres et Recherche
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.geolocalisation.arrondissements.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Département</label>
                            <select name="departement_id" id="filter-departement" class="form-select">
                                <option value="">Tous les départements</option>
                                @foreach($departements as $departement)
                                    <option value="{{ $departement->id }}" {{ request('departement_id') == $departement->id ? 'selected' : '' }}>
                                        {{ $departement->nom }} ({{ $departement->province->nom }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Commune/Ville</label>
                            <select name="commune_ville_id" id="filter-commune-ville" class="form-select">
                                <option value="">Toutes les communes/villes</option>
                                @foreach($communesVilles as $communeVille)
                                    <option value="{{ $communeVille->id }}" 
                                            data-departement="{{ $communeVille->departement_id }}"
                                            {{ request('commune_ville_id') == $communeVille->id ? 'selected' : '' }}>
                                        {{ $communeVille->nom }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Numéro</label>
                            <select name="numero_arrondissement" class="form-select">
                                <option value="">Tous</option>
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}" {{ request('numero_arrondissement') == $i ? 'selected' : '' }}>
                                        {{ $i === 1 ? '1er' : $i . 'ème' }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Statut</label>
                            <select name="is_active" class="form-select">
                                <option value="">Tous</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actif</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                            </div>
                        </div>
                    </form>
                    @if(request()->hasAny(['departement_id', 'commune_ville_id', 'numero_arrondissement', 'is_active', 'search']))
                        <div class="mt-3">
                            <a href="{{ route('admin.geolocalisation.arrondissements.index') }}" class="btn btn-link text-muted p-0">
                                <i class="fas fa-times me-1"></i>Effacer les filtres
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Liste des arrondissements --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        Liste des Arrondissements ({{ $arrondissements->total() }})
                    </h5>
                    <small class="text-muted">
                        Page {{ $arrondissements->currentPage() }} sur {{ $arrondissements->lastPage() }}
                    </small>
                </div>
                <div class="card-body p-0">
                    @if($arrondissements->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Arrondissement</th>
                                        <th>Code</th>
                                        <th>Commune/Ville</th>
                                        <th>Département</th>
                                        <th>Province</th>
                                        <th>Délégué</th>
                                        <th>Population</th>
                                        <th>Statut</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($arrondissements as $arrondissement)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        <a href="{{ route('admin.geolocalisation.arrondissements.show', $arrondissement) }}" 
                                                           class="text-decoration-none">
                                                            {{ $arrondissement->nom }}
                                                        </a>
                                                    </h6>
                                                    @if($arrondissement->numero_arrondissement)
                                                        <small class="text-muted">
                                                            {{ $arrondissement->numero_arrondissement === 1 ? '1er' : $arrondissement->numero_arrondissement . 'ème' }} Arrondissement
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small class="text-muted">Code: {{ $arrondissement->code }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.geolocalisation.communes.show', $arrondissement->communeVille) }}" 
                                               class="text-decoration-none">
                                                {{ $arrondissement->communeVille->nom }}
                                            </a>
                                            <br><small class="text-muted">{{ ucfirst($arrondissement->communeVille->type) }}</small>
                                        </td>
                                        <td>{{ $arrondissement->communeVille->departement->nom }}</td>
                                        <td>{{ $arrondissement->communeVille->departement->province->nom }}</td>
                                        <td>
                                            @if($arrondissement->delegue)
                                                {{ $arrondissement->delegue }}
                                                @if($arrondissement->telephone)
                                                    <br><small class="text-muted">{{ $arrondissement->telephone }}</small>
                                                @endif
                                            @else
                                                <span class="text-muted">Non assigné</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($arrondissement->population_estimee)
                                                {{ number_format($arrondissement->population_estimee, 0, ',', ' ') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $arrondissement->is_active ? 'success' : 'secondary' }}">
                                                {{ $arrondissement->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.geolocalisation.arrondissements.show', $arrondissement) }}" 
                                                   class="btn btn-outline-info" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.geolocalisation.arrondissements.edit', $arrondissement) }}" 
                                                   class="btn btn-outline-primary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" 
                                                      action="{{ route('admin.geolocalisation.arrondissements.toggle-status', $arrondissement) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-outline-{{ $arrondissement->is_active ? 'warning' : 'success' }}"
                                                            title="{{ $arrondissement->is_active ? 'Désactiver' : 'Activer' }}"
                                                            onclick="return confirm('Confirmer le changement de statut ?')">
                                                        <i class="fas fa-{{ $arrondissement->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" 
                                                      action="{{ route('admin.geolocalisation.arrondissements.destroy', $arrondissement) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger"
                                                            title="Supprimer"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet arrondissement ? Cette action est irréversible.')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        <div class="card-footer">
                            {{ $arrondissements->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucun arrondissement trouvé</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['departement_id', 'commune_ville_id', 'numero_arrondissement', 'is_active', 'search']))
                                    Aucun résultat ne correspond à vos critères de recherche.
                                    <br>
                                    <a href="{{ route('admin.geolocalisation.arrondissements.index') }}" class="btn btn-outline-primary btn-sm mt-2">
                                        Réinitialiser les filtres
                                    </a>
                                @else
                                    Commencez par créer votre premier arrondissement.
                                    <br>
                                    <a href="{{ route('admin.geolocalisation.arrondissements.create') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="fas fa-plus me-2"></i>Créer un arrondissement
                                    </a>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript pour le filtrage en cascade --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const departementSelect = document.getElementById('filter-departement');
    const communeVilleSelect = document.getElementById('filter-commune-ville');
    
    if (departementSelect && communeVilleSelect) {
        departementSelect.addEventListener('change', function() {
            const departementId = this.value;
            const options = communeVilleSelect.querySelectorAll('option');
            
            // Réinitialiser la sélection
            communeVilleSelect.value = '';
            
            // Filtrer les options
            options.forEach(option => {
                if (!option.value) {
                    option.style.display = 'block';
                    return;
                }
                
                const optionDept = option.dataset.departement;
                if (!departementId || optionDept === departementId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        });
        
        // Déclencher le filtrage au chargement si un département est sélectionné
        if (departementSelect.value) {
            departementSelect.dispatchEvent(new Event('change'));
        }
    }
});
</script>
@endpush
@endsection