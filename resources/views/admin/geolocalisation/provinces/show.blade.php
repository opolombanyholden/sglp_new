{{-- resources/views/admin/provinces/show.blade.php --}}
@extends('layouts.admin')

@section('title', $province->nom)

@section('content')
<div class="container-fluid">
    <!-- Header principal avec informations de la province -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div style="width: 80px; height: 80px; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 1.5rem; backdrop-filter: blur(10px);">
                                    <i class="fas fa-map-marked-alt fa-3x"></i>
                                </div>
                                <div>
                                    <h2 class="mb-1">{{ $province->nom }}</h2>
                                    <code class="text-white-50 fs-6">{{ $province->code }}</code>
                                    @if($province->chef_lieu)
                                        <div class="mt-2">
                                            <span style="background: rgba(255, 255, 255, 0.2); padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600; backdrop-filter: blur(10px); display: inline-flex; align-items: center; gap: 0.5rem;">
                                                <i class="fas fa-building"></i>
                                                Chef-lieu : {{ $province->chef_lieu }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center flex-wrap gap-3">
                                <span style="font-size: 1.25rem; padding: 8px 20px; border-radius: 25px; font-weight: 700; display: inline-flex; align-items: center; gap: 0.5rem; background: linear-gradient(135deg, #003f7f, #0056b3); color: white;">
                                    <i class="fas fa-sort-numeric-up"></i>
                                    Ordre {{ $province->ordre_affichage }}
                                </span>
                                
                                <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 6px 12px; border-radius: 20px; font-size: 0.875rem; font-weight: 600; @if($province->is_active) background: rgba(0, 158, 63, 0.2); color: #009e3f; @else background: rgba(108, 117, 125, 0.2); color: #6c757d; @endif">
                                    <i class="fas fa-circle"></i>
                                    {{ $province->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-end">
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('admin.geolocalisation.provinces.edit', $province) }}" class="btn btn-light btn-lg" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-edit me-2"></i>
                                    Modifier
                                </a>
                                <a href="{{ route('admin.geolocalisation.provinces.index') }}" class="btn btn-outline-light btn-lg" style="border-radius: 25px; padding: 12px 30px;">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Retour
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages flash -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius: 12px;">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistiques de la province -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);">
                <div class="card-body text-white text-center">
                    <div style="width: 60px; height: 60px; margin: 0 auto 1rem; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ $statistiques['departements'] ?? 0 }}</div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Départements</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ffcd00 0%, #ffd700 100%);">
                <div class="card-body text-dark text-center">
                    <div style="width: 60px; height: 60px; margin: 0 auto 1rem; background: rgba(33, 37, 41, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-sitemap fa-2x"></i>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ number_format($statistiques['organisations'] ?? 0) }}</div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Organisations</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                <div class="card-body text-white text-center">
                    <div style="width: 60px; height: 60px; margin: 0 auto 1rem; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ number_format($statistiques['adherents'] ?? 0) }}</div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Adhérents</div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%);">
                <div class="card-body text-white text-center">
                    <div style="width: 60px; height: 60px; margin: 0 auto 1rem; background: rgba(255, 255, 255, 0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-store fa-2x"></i>
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">{{ number_format($statistiques['etablissements'] ?? 0) }}</div>
                    <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">Établissements</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Informations générales -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2" style="color: #009e3f;"></i>
                            Informations Générales
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Nom de la province :</label>
                        <div class="fw-bold" style="color: #003f7f;">{{ $province->nom }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Code :</label>
                        <div>
                            <code style="background: #f8f9fc; padding: 4px 8px; border-radius: 6px;">{{ $province->code }}</code>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Chef-lieu :</label>
                        <div>{{ $province->chef_lieu ?: 'Non renseigné' }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Ordre d'affichage :</label>
                        <div>{{ $province->ordre_affichage }}</div>
                    </div>
                    
                    @if($province->description)
                        <div class="mb-3">
                            <label class="fw-bold text-muted small">Description :</label>
                            <div>{{ $province->description }}</div>
                        </div>
                    @endif
                    
                    <div class="mb-3">
                        <label class="fw-bold text-muted small">Créé le :</label>
                        <div>{{ $province->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                    
                    <div>
                        <label class="fw-bold text-muted small">Dernière modification :</label>
                        <div>{{ $province->updated_at->format('d/m/Y à H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Données géographiques -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-globe me-2" style="color: #17a2b8;"></i>
                            Données Géographiques
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="text-center">
                                <i class="fas fa-ruler-combined fa-2x text-primary mb-2"></i>
                                <h6>Superficie</h6>
                                <p class="h5">{{ $province->superficie_formattee ?? 'Non définie' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <div class="text-center">
                                <i class="fas fa-users fa-2x text-success mb-2"></i>
                                <h6>Population</h6>
                                <p class="h5">{{ $province->population_formattee ?? 'Non définie' }}</p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="text-center">
                                <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                                <h6>Densité</h6>
                                <p class="h5">{{ $province->densite_formattee ?? 'Non définie' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($province->hasCoordinates())
                        <hr>
                        <div class="text-center">
                            <h6><i class="fas fa-map-pin me-2"></i>Coordonnées GPS</h6>
                            <p class="mb-1">
                                <strong>Latitude :</strong> {{ $province->latitude }}°
                            </p>
                            <p class="mb-3">
                                <strong>Longitude :</strong> {{ $province->longitude }}°
                            </p>
                            <a href="https://www.google.com/maps?q={{ $province->latitude }},{{ $province->longitude }}" 
                               target="_blank" 
                               class="btn btn-outline-primary btn-sm" style="border-radius: 20px;">
                                <i class="fas fa-map-marked-alt me-2"></i>Voir sur Google Maps
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-bolt me-2" style="color: #ffcd00;"></i>
                            Actions Rapides
                        </h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.geolocalisation.departements.create', ['province_id' => $province->id]) }}" 
                           class="btn btn-lg" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;">
                            <i class="fas fa-plus me-2"></i>Nouveau Département
                        </a>
                        
                        <a href="{{ route('admin.organisations.index', ['province_id' => $province->id]) }}" 
                           class="btn btn-lg" style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;">
                            <i class="fas fa-sitemap me-2"></i>Voir les Organisations
                        </a>

                        <hr class="my-2">

                        <form method="POST" 
                              action="{{ route('admin.geolocalisation.provinces.toggle-status', $province) }}" 
                              class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="btn btn-lg w-100" 
                                    style="background: linear-gradient(135deg, {{ $province->is_active ? '#ffcd00' : '#009e3f' }} 0%, {{ $province->is_active ? '#fd7e14' : '#00b347' }} 100%); color: {{ $province->is_active ? '#212529' : 'white' }}; border: none; border-radius: 25px; padding: 12px 30px;"
                                    onclick="return confirm('Confirmer le changement de statut ?')">
                                <i class="fas fa-{{ $province->is_active ? 'pause' : 'play' }} me-2"></i>
                                {{ $province->is_active ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>

                        @if(($statistiques['departements'] ?? 0) === 0 && ($statistiques['organisations'] ?? 0) === 0)
                        <form method="POST" 
                              action="{{ route('admin.geolocalisation.provinces.destroy', $province) }}" 
                              class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="btn btn-lg w-100" 
                                    style="background: linear-gradient(135deg, #8b1538 0%, #c41e3a 100%); color: white; border: none; border-radius: 25px; padding: 12px 30px;"
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette province ? Cette action est irréversible.')">
                                <i class="fas fa-trash me-2"></i>Supprimer
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Départements de la province -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-2" style="color: #003f7f;"></i>
                            Départements ({{ $province->departements->count() }})
                        </h5>
                        <a href="{{ route('admin.geolocalisation.departements.create', ['province_id' => $province->id]) }}" 
                           class="btn btn-primary btn-sm" style="border-radius: 20px;">
                            <i class="fas fa-plus me-2"></i>Nouveau Département
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($province->departements->count() > 0)
                        <div style="position: relative; padding-left: 2rem;">
                            <div style="position: absolute; left: 15px; top: 0; bottom: 0; width: 2px; background: linear-gradient(135deg, #009e3f 0%, #00b347 100%);"></div>
                            
                            @foreach($province->departements as $index => $departement)
                            <div style="position: relative; padding-bottom: 1.5rem;">
                                <div style="position: absolute; left: -23px; top: 5px; width: 12px; height: 12px; background: white; border: 3px solid #009e3f; border-radius: 50%;"></div>
                                <div style="background: white; padding: 1.5rem; border-radius: 15px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center gap-3">
                                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                                    {{ strtoupper(substr($departement->nom, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-bold" style="color: #003f7f;">
                                                        {{ $departement->nom }}
                                                    </div>
                                                    <div class="text-muted small">
                                                        <i class="fas fa-hashtag me-1"></i>{{ $departement->code }}
                                                    </div>
                                                    @if($departement->chef_lieu)
                                                        <div class="text-muted small">
                                                            <i class="fas fa-building me-1"></i>{{ $departement->chef_lieu }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="small">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <i class="fas fa-city text-primary"></i>
                                                    <span>{{ $departement->communes_count ?? 0 }} communes</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="fas fa-map text-success"></i>
                                                    <span>{{ $departement->cantons_count ?? 0 }} cantons</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="badge" style="background: {{ $departement->is_active ? '#009e3f' : '#6c757d' }}; color: white; padding: 6px 12px; border-radius: 15px;">
                                                {{ $departement->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.geolocalisation.departements.show', $departement) }}" 
                                                   class="btn btn-outline-info btn-sm" title="Voir" style="border-radius: 8px;">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.geolocalisation.departements.edit', $departement) }}" 
                                                   class="btn btn-outline-primary btn-sm ms-1" title="Modifier" style="border-radius: 8px;">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-building fa-4x text-muted mb-3" style="opacity: 0.5;"></i>
                            <h5 class="text-muted">Aucun département</h5>
                            <p class="text-muted mb-3">Cette province ne contient encore aucun département.</p>
                            <a href="{{ route('admin.geolocalisation.departements.create', ['province_id' => $province->id]) }}" 
                               class="btn btn-primary" style="background: linear-gradient(135deg, #009e3f 0%, #00b347 100%); border: none; border-radius: 25px; padding: 12px 30px;">
                                <i class="fas fa-plus me-2"></i>
                                Créer le premier département
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAB (Floating Action Button) tricolore spécialisé -->
<div style="position: fixed; bottom: 2rem; right: 2rem; z-index: 1000;">
    <div id="fabMenu">
        <div onclick="toggleFAB()" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #009e3f 0%, #ffcd00 50%, #003f7f 100%); box-shadow: 0 4px 12px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.3s ease;">
            <i class="fas fa-map-marked-alt" style="color: white; font-size: 1.5rem;"></i>
        </div>
        <div class="fab-options" style="position: absolute; bottom: 70px; right: 0; display: flex; flex-direction: column; gap: 10px; opacity: 0; visibility: hidden; transition: all 0.3s ease;">
            <button onclick="window.location.href='{{ route('admin.geolocalisation.provinces.edit', $province) }}'" style="width: 45px; height: 45px; border-radius: 50%; border: none; background: #009e3f; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;" title="Modifier">
                <i class="fas fa-edit"></i>
            </button>
            <button onclick="window.location.href='{{ route('admin.geolocalisation.departements.create', ['province_id' => $province->id]) }}'" style="width: 45px; height: 45px; border-radius: 50%; border: none; background: #ffcd00; color: #000; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;" title="Nouveau Département">
                <i class="fas fa-plus"></i>
            </button>
            <button onclick="window.location.href='{{ route('admin.organisations.index', ['province_id' => $province->id]) }}'" style="width: 45px; height: 45px; border-radius: 50%; border: none; background: #003f7f; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.2); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: transform 0.2s ease;" title="Organisations">
                <i class="fas fa-sitemap"></i>
            </button>
        </div>
    </div>
</div>

<style>
/* Animation d'entrée */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.card {
    animation: fadeInUp 0.6s ease-out;
}

#fabMenu.active .fab-options {
    opacity: 1;
    visibility: visible;
}

.fab-options button:hover {
    transform: scale(1.1);
}
</style>

<script>
console.log('Module Détail Province SGLP chargé');

/**
 * FAB toggle
 */
function toggleFAB() {
    const fabMenu = document.getElementById('fabMenu');
    fabMenu.classList.toggle('active');
}

// Fermer FAB en cliquant ailleurs
document.addEventListener('click', function(event) {
    const fabMenu = document.getElementById('fabMenu');
    if (!fabMenu.contains(event.target)) {
        fabMenu.classList.remove('active');
    }
});

/**
 * Basculer le statut de la province
 */
function toggleProvinceStatus(provinceId) {
    fetch(`/admin/geolocalisation/provinces/${provinceId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors du changement de statut: ' + (data.message || 'Erreur inconnue'));
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors du changement de statut');
    });
}
</script>
@endsection