{{-- resources/views/admin/organisations/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Détails de l\'Organisation')

@section('content')
    <div class="container-fluid">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-2">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('admin.organisations.index') }}">Organisations</a></li>
                                <li class="breadcrumb-item active">{{ $organisation->nom }}</li>
                            </ol>
                        </nav>
                        <h1 class="h3 mb-0" style="color: #003f7f;">
                            <i class="fas fa-building me-2"></i>
                            {{ $organisation->nom }}
                            @if($organisation->sigle)
                                <span class="text-muted">({{ $organisation->sigle }})</span>
                            @endif
                        </h1>
                    </div>
                    <div>
                        <a href="{{ route('admin.organisations.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                        </a>
                        @if($dernierDossier)
                            <a href="{{ route('admin.dossiers.show', $dernierDossier->id) }}" class="btn btn-primary">
                                <i class="fas fa-folder-open me-2"></i>Voir le dossier
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Dossiers</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_dossiers'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-folder fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approuvés</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['dossiers_approuves'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En Cours</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['dossiers_en_cours'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cogs fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejetés</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['dossiers_rejetes'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Information principales -->
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Informations Générales
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Dénomination :</strong><br>{{ $organisation->nom }}</p>
                                @if($organisation->sigle)
                                    <p><strong>Sigle :</strong><br>{{ $organisation->sigle }}</p>
                                @endif
                                <p><strong>Type :</strong><br>
                                    <span
                                        class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $organisation->type)) }}</span>
                                </p>
                                @if($organisation->numero_recepisse)
                                    <p><strong>N° Récépissé :</strong><br>
                                        <span class="text-success font-weight-bold">{{ $organisation->numero_recepisse }}</span>
                                    </p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <p><strong>Siège Social :</strong><br>{{ $organisation->siege_social ?? 'Non renseigné' }}
                                </p>
                                <p><strong>Commune :</strong><br>{{ $organisation->commune ?? 'Non renseigné' }}</p>
                                <p><strong>Province :</strong><br>{{ $organisation->prefecture ?? 'Non renseigné' }}</p>
                                @if($organisation->boite_postale)
                                    <p><strong>Boîte Postale :</strong><br>{{ $organisation->boite_postale }}</p>
                                @endif
                            </div>
                        </div>
                        @if($organisation->objet)
                            <hr>
                            <p><strong>Objet :</strong></p>
                            <p class="text-muted">{{ $organisation->objet }}</p>
                        @endif
                    </div>
                </div>

                <!-- Liste des dossiers -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-folder-open me-2"></i>Historique des Dossiers
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($organisation->dossiers->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N° Dossier</th>
                                            <th>Type Opération</th>
                                            <th>Statut</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($organisation->dossiers as $dossier)
                                            <tr>
                                                <td><strong>{{ $dossier->numero_dossier }}</strong></td>
                                                <td>{{ ucfirst($dossier->type_operation) }}</td>
                                                <td>
                                                    @php
                                                        $statusConfig = [
                                                            'brouillon' => 'secondary',
                                                            'soumis' => 'warning',
                                                            'en_cours' => 'info',
                                                            'approuve' => 'success',
                                                            'rejete' => 'danger'
                                                        ];
                                                    @endphp
                                                    <span class="badge bg-{{ $statusConfig[$dossier->statut] ?? 'secondary' }}">
                                                        {{ ucfirst($dossier->statut) }}
                                                    </span>
                                                </td>
                                                <td>{{ $dossier->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.dossiers.show', $dossier->id) }}"
                                                        class="btn btn-sm btn-outline-primary" title="Voir le dossier">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-gray-300 mb-3"></i>
                                <p class="text-muted">Aucun dossier pour cette organisation</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Contact -->
                <div class="card shadow mb-4" id="contacts">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user me-2"></i>Contact Principal
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($organisation->user)
                            <p><strong>{{ $organisation->user->name }}</strong></p>
                            <p><i class="fas fa-envelope me-2 text-muted"></i>{{ $organisation->user->email }}</p>
                            @if($organisation->user->phone)
                                <p><i class="fas fa-phone me-2 text-muted"></i>{{ $organisation->user->phone }}</p>
                            @endif
                            <a href="mailto:{{ $organisation->user->email }}" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fas fa-envelope me-2"></i>Envoyer un email
                            </a>
                        @else
                            <p class="text-muted">Aucun contact renseigné</p>
                        @endif
                    </div>
                </div>

                <!-- Membres du bureau -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-users me-2"></i>Membres du Bureau
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($organisation->fondateurs && $organisation->fondateurs->count() > 0)
                            @foreach($organisation->fondateurs->take(5) as $membre)
                                <div class="mb-3 pb-2 border-bottom">
                                    <strong>{{ $membre->prenom }} {{ $membre->nom }}</strong>
                                    <br><small class="text-muted">{{ $membre->fonction ?? 'Membre' }}</small>
                                </div>
                            @endforeach
                            @if($organisation->fondateurs->count() > 5)
                                <p class="text-muted text-center mb-0">
                                    <small>Et {{ $organisation->fondateurs->count() - 5 }} autres membres...</small>
                                </p>
                            @endif
                        @else
                            <p class="text-muted text-center mb-0">Aucun membre enregistré</p>
                        @endif
                    </div>
                </div>

                <!-- Dates -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-calendar me-2"></i>Dates Importantes
                        </h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Création :</strong><br>{{ $organisation->created_at->format('d/m/Y à H:i') }}</p>
                        <p class="mb-0"><strong>Dernière modification
                                :</strong><br>{{ $organisation->updated_at->format('d/m/Y à H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }

        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }

        .border-left-danger {
            border-left: 0.25rem solid #e74a3b !important;
        }

        .card {
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }
    </style>
@endpush