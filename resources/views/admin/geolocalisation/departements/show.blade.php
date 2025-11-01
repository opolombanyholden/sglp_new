{{-- resources/views/admin/geolocalisation/departements/show.blade.php --}}
@extends('layouts.admin')

@section('title', $departement->nom)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- En-tête --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-building me-2"></i>
                        {{ $departement->nom }}
                        <span class="badge bg-{{ $departement->is_active ? 'success' : 'secondary' }} ms-2">
                            {{ $departement->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                        @php
                            $typeConfig = [
                                'urbain' => ['class' => 'primary', 'icon' => 'city'],
                                'rural' => ['class' => 'success', 'icon' => 'tree'],
                                'mixte' => ['class' => 'warning', 'icon' => 'exchange-alt'],
                                'non défini' => ['class' => 'secondary', 'icon' => 'question']
                            ];
                            $config = $typeConfig[$departement->type_subdivision] ?? $typeConfig['non défini'];
                        @endphp
                        <span class="badge bg-{{ $config['class'] }} ms-2">
                            <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                            {{ ucfirst($departement->type_subdivision) }}
                        </span>
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-2">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.geolocalisation.provinces.index') }}">Géolocalisation</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.geolocalisation.departements.index') }}">Départements</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.geolocalisation.provinces.show', $departement->province) }}">{{ $departement->province->nom }}</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $departement->nom }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.geolocalisation.departements.edit', $departement) }}" class="btn btn-primary me-2">
                        <i class="fas fa-edit me-2"></i>Modifier
                    </a>
                    <a href="{{ route('admin.geolocalisation.departements.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>

            {{-- Messages flash --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                {{-- Informations générales --}}
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                Informations Générales
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-4">Département :</dt>
                                        <dd class="col-8">{{ $departement->nom }}</dd>
                                        
                                        <dt class="col-4">Code :</dt>
                                        <dd class="col-8">
                                            <span class="badge bg-primary">{{ $departement->code }}</span>
                                        </dd>
                                        
                                        <dt class="col-4">Province :</dt>
                                        <dd class="col-8">
                                            <a href="{{ route('admin.geolocalisation.provinces.show', $departement->province) }}" 
                                               class="text-decoration-none">
                                                {{ $departement->province->nom }}
                                            </a>
                                        </dd>
                                        
                                        <dt class="col-4">Chef-lieu :</dt>
                                        <dd class="col-8">{{ $departement->chef_lieu ?: 'Non renseigné' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-4">Type :</dt>
                                        <dd class="col-8">
                                            <span class="badge bg-{{ $config['class'] }}">
                                                <i class="fas fa-{{ $config['icon'] }} me-1"></i>
                                                {{ ucfirst($departement->type_subdivision) }}
                                            </span>
                                        </dd>
                                        
                                        <dt class="col-4">Créé le :</dt>
                                        <dd class="col-8">{{ $departement->created_at->format('d/m/Y à H:i') }}</dd>
                                        
                                        <dt class="col-4">Modifié le :</dt>
                                        <dd class="col-8">{{ $departement->updated_at->format('d/m/Y à H:i') }}</dd>
                                        
                                        <dt class="col-4">Statut :</dt>
                                        <dd class="col-8">
                                            <span class="badge bg-{{ $departement->is_active ? 'success' : 'secondary' }}">
                                                {{ $departement->is_active ? 'Actif' : 'Inactif' }}
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>

                            @if($departement->description)
                                <div class="mt-3">
                                    <h6>Description :</h6>
                                    <p class="text-muted">{{ $departement->description }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Données géographiques --}}
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-globe me-2"></i>
                                Données Géographiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-ruler-combined fa-2x text-primary mb-2"></i>
                                        <h6>Superficie</h6>
                                        <p class="h5">{{ $departement->superficie_formattee }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-users fa-2x text-success mb-2"></i>
                                        <h6>Population</h6>
                                        <p class="h5">{{ $departement->population_formattee }}</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                                        <h6>Densité</h6>
                                        <p class="h5">{{ $departement->densite_formattee }}</p>
                                    </div>
                                </div>
                            </div>

                            @if($departement->hasCoordinates())
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6><i class="fas fa-map-pin me-2"></i>Coordonnées GPS</h6>
                                        <p class="mb-1">
                                            <strong>Latitude :</strong> {{ $departement->latitude }}°
                                        </p>
                                        <p class="mb-0">
                                            <strong>Longitude :</strong> {{ $departement->longitude }}°
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <a href="https://www.google.com/maps?q={{ $departement->latitude }},{{ $departement->longitude }}" 
                                           target="_blank" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-map-marked-alt me-2"></i>Voir sur Google Maps
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Subdivisions urbaines : Communes/Villes --}}
                    @if($departement->communesVilles->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-city me-2 text-primary"></i>
                                Subdivisions Urbaines - Communes/Villes ({{ $departement->communesVilles->count() }})
                            </h5>
                            {{-- Route temporairement désactivée jusqu'à création du contrôleur --}}
                            {{-- <a href="{{ route('admin.geolocalisation.communes.create', ['departement_id' => $departement->id]) }}" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-2"></i>Nouvelle Commune/Ville
                            </a> --}}
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Commune/Ville</th>
                                            <th>Type</th>
                                            <th>Arrondissements</th>
                                            <th>Statut</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($departement->communesVilles as $commune)
                                        <tr>
                                            <td>
                                                <div>
                                                    <h6 class="mb-1">{{ $commune->nom }}</h6>
                                                    <small class="text-muted">{{ $commune->code }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $commune->type === 'ville' ? 'info' : 'secondary' }}">
                                                    {{ ucfirst($commune->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-primary">{{ $commune->arrondissements_count ?? 0 }} arrondissement(s)</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $commune->is_active ? 'success' : 'secondary' }}">
                                                    {{ $commune->is_active ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.geolocalisation.communes-villes.show', $commune) }}" 
                                                       class="btn btn-outline-info" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.geolocalisation.communes-villes.edit', $commune) }}" 
                                                       class="btn btn-outline-primary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
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
                    @endif

                    {{-- Subdivisions rurales : Cantons --}}
                    @if($departement->cantons->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-tree me-2 text-success"></i>
                                Subdivisions Rurales - Cantons ({{ $departement->cantons->count() }})
                            </h5>
                            <a href="{{ route('admin.geolocalisation.cantons.create', ['departement_id' => $departement->id]) }}" 
                               class="btn btn-success btn-sm">
                                <i class="fas fa-plus me-2"></i>Nouveau Canton
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Canton</th>
                                            <th>Chef-lieu</th>
                                            <th>Regroupements</th>
                                            <th>Statut</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($departement->cantons as $canton)
                                        <tr>
                                            <td>
                                                <div>
                                                    <h6 class="mb-1">{{ $canton->nom }}</h6>
                                                    <small class="text-muted">{{ $canton->code }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $canton->chef_lieu ?: 'Non renseigné' }}</td>
                                            <td>
                                                <span class="text-success">{{ $canton->regroupements_count ?? 0 }} regroupement(s)</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $canton->is_active ? 'success' : 'secondary' }}">
                                                    {{ $canton->is_active ? 'Actif' : 'Inactif' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.geolocalisation.cantons.show', $canton) }}" 
                                                       class="btn btn-outline-info" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.geolocalisation.cantons.edit', $canton) }}" 
                                                       class="btn btn-outline-primary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
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
                    @endif

                    {{-- Message si aucune subdivision --}}
                    @if($departement->communesVilles->count() === 0 && $departement->cantons->count() === 0)
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune subdivision définie</h5>
                            <p class="text-muted mb-4">Ce département ne contient encore aucune subdivision (commune/ville ou canton).</p>
                            <div class="row g-3 justify-content-center">
                                <div class="col-auto">
                                    <a href="" 
                                       class="btn btn-primary">
                                        <i class="fas fa-city me-2"></i>Créer une Commune/Ville 
                                       
                                    </a>
                                </div>
                                <div class="col-auto">
                                    <a href="" 
                                       class="btn btn-success">
                                        <i class="fas fa-tree me-2"></i>Créer un Canton
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Statistiques --}}
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie me-2"></i>
                                Statistiques
                            </h5>
                        </div>
                        <div class="card-body">
                            @foreach([
                                'communes_villes' => ['icon' => 'city', 'label' => 'Communes/Villes', 'color' => 'primary'],
                                'communes_villes_actives' => ['icon' => 'check-circle', 'label' => 'Communes/Villes actives', 'color' => 'primary'],
                                'cantons' => ['icon' => 'tree', 'label' => 'Cantons', 'color' => 'success'],
                                'cantons_actifs' => ['icon' => 'check-circle', 'label' => 'Cantons actifs', 'color' => 'success'],
                                'total_subdivisions' => ['icon' => 'layer-group', 'label' => 'Total subdivisions', 'color' => 'info'],
                                'organisations' => ['icon' => 'sitemap', 'label' => 'Organisations', 'color' => 'warning'],
                                'adherents' => ['icon' => 'users', 'label' => 'Adhérents', 'color' => 'secondary']
                            ] as $key => $config)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-{{ $config['icon'] }} text-{{ $config['color'] }} me-2"></i>
                                        <span>{{ $config['label'] }}</span>
                                    </div>
                                    <span class="badge bg-{{ $config['color'] }}">
                                        {{ number_format($statistiques[$key] ?? 0) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Actions rapides --}}
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Actions Rapides
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-city me-2"></i>Nouvelle Commune/Ville
                                </a>
                                
                                <a href="" 
                                   class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-tree me-2"></i>Nouveau Canton
                                </a>
                                
                                <a href="{{ route('admin.geolocalisation.provinces.show', $departement->province) }}" 
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-map-marked-alt me-2"></i>Voir la Province
                                </a>

                                <hr class="my-2">

                                <form method="POST" 
                                      action="{{ route('admin.geolocalisation.departements.toggle-status', $departement) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="btn btn-outline-{{ $departement->is_active ? 'warning' : 'success' }} btn-sm w-100"
                                            onclick="return confirm('Confirmer le changement de statut ?')">
                                        <i class="fas fa-{{ $departement->is_active ? 'pause' : 'play' }} me-2"></i>
                                        {{ $departement->is_active ? 'Désactiver' : 'Activer' }}
                                    </button>
                                </form>

                                @if($statistiques['total_subdivisions'] === 0 && $statistiques['organisations'] === 0)
                                <form method="POST" 
                                      action="{{ route('admin.geolocalisation.departements.destroy', $departement) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-outline-danger btn-sm w-100"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce département ? Cette action est irréversible.')">
                                        <i class="fas fa-trash me-2"></i>Supprimer
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection