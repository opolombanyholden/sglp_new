@extends('layouts.admin')

@section('title', isset($operationType) ? $operationType->libelle . ' - Sélectionner une Organisation' : 'Sélectionner une Organisation')

@section('content')
    <div class="container-fluid">
        <!-- En-tête -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                @if(isset($operationType))
                    <h2 class="page-title mb-1">
                        @switch($selectedOperation)
                            @case('modification')
                                <i class="fas fa-edit me-2 text-info"></i>
                                @break
                            @case('cessation')
                                <i class="fas fa-ban me-2 text-danger"></i>
                                @break
                            @case('ajout_adherent')
                                <i class="fas fa-user-plus me-2 text-success"></i>
                                @break
                            @case('retrait_adherent')
                                <i class="fas fa-user-minus me-2 text-warning"></i>
                                @break
                            @case('declaration_activite')
                                <i class="fas fa-file-alt me-2 text-primary"></i>
                                @break
                            @case('changement_statutaire')
                                <i class="fas fa-gavel me-2 text-purple"></i>
                                @break
                            @default
                                <i class="fas fa-building me-2"></i>
                        @endswitch
                        {{ $operationType->libelle }}
                    </h2>
                    <p class="text-muted mb-0">Sélectionnez l'organisation concernée par cette opération</p>
                @else
                    <h2 class="page-title mb-1">
                        <i class="fas fa-building me-2"></i>
                        Sélectionner une Organisation
                    </h2>
                    <p class="text-muted mb-0">Choisissez l'organisation sur laquelle effectuer une opération</p>
                @endif
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Retour au Dashboard
            </a>
        </div>

        @if(isset($operationType))
            <div class="alert alert-info d-flex align-items-center mb-4">
                <i class="fas fa-info-circle me-2 fs-4"></i>
                <div>
                    <strong>Opération sélectionnée :</strong> {{ $operationType->libelle }}<br>
                    <small>{{ $operationType->description }}</small>
                </div>
            </div>
        @endif

        <!-- Filtres de recherche -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.operations.select-organisation') }}" method="GET" class="row g-3">
                    @if($selectedOperation ?? null)
                        <input type="hidden" name="operation" value="{{ $selectedOperation }}">
                    @endif
                    <div class="col-md-6">
                        <label for="search" class="form-label">Rechercher</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="search" name="search"
                                value="{{ request('search') }}" placeholder="Nom, sigle ou numéro récépissé...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="type" class="form-label">Type d'organisation</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">Tous les types</option>
                            @foreach($organisationTypes as $type)
                                <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                                    {{ $type->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des organisations -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    Organisations validées ({{ $organisations->total() }})
                </h5>
            </div>
            <div class="card-body p-0">
                @if($organisations->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune organisation trouvée.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Organisation</th>
                                    <th>Type</th>
                                    <th>N° Récépissé</th>
                                    <th>Date de création</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($organisations as $organisation)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-primary text-white me-3">
                                                    {{ strtoupper(substr($organisation->sigle ?? $organisation->nom, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $organisation->nom }}</strong>
                                                    @if($organisation->sigle)
                                                        <small class="text-muted">({{ $organisation->sigle }})</small>
                                                    @endif
                                                    <br>
                                                    <small class="text-muted">{{ $organisation->siege_social }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $organisation->organisationType->nom ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <code>{{ $organisation->numero_recepisse ?? 'N/A' }}</code>
                                        </td>
                                        <td>
                                            {{ $organisation->created_at->format('d/m/Y') }}
                                        </td>
                                        <td class="text-center">
                                            @if(isset($selectedOperation))
                                                {{-- Lien direct vers le formulaire --}}
                                                <a href="{{ route('admin.operations.create', [$organisation->id, $selectedOperation]) }}"
                                                    class="btn btn-success btn-sm">
                                                    <i class="fas fa-play me-1"></i> Commencer
                                                </a>
                                            @else
                                                {{-- Lien vers sélection opération --}}
                                                <a href="{{ route('admin.operations.select-operation', $organisation->id) }}"
                                                    class="btn btn-success btn-sm">
                                                    <i class="fas fa-forward me-1"></i> Choisir
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Affichage de {{ $organisations->firstItem() }} à {{ $organisations->lastItem() }}
                            sur {{ $organisations->total() }} organisations
                        </div>
                        {{ $organisations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .avatar-circle {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }
    </style>
@endsection