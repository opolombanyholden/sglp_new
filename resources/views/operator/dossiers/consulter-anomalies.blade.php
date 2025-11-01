{{-- 
    FICHIER : resources/views/operator/dossiers/consulter-anomalies.blade.php
    Vue pour la consultation en ligne des anomalies - CORRIGÉE
--}}
@extends('layouts.operator')

@section('title', 'Consultation des Anomalies')

@section('content')
<div class="container-fluid py-4">
    {{-- HEADER --}}
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-dark mb-1">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Consultation des Anomalies
                    </h1>
                    <p class="text-muted mb-0">
                        Organisation : <strong>{{ $organisation->nom ?? 'N/A' }}</strong> |
                        Dossier : <strong>{{ $dossier->numero_dossier ?? $dossier->id }}</strong>
                    </p>
                </div>
                <div>
                    <a href="{{ route('operator.dossiers.confirmation', $dossier->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Retour Confirmation
                    </a>
                    <a href="{{ route('operator.dossiers.rapport-anomalies', $dossier->id) }}" class="btn btn-warning" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i>
                        Télécharger PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- STATISTIQUES RÉSUMÉ --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body text-center">
                    <h3 class="card-title">{{ $stats['total'] }}</h3>
                    <p class="card-text">Total Adhérents</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="card-title">{{ $stats['valides'] }}</h3>
                    <p class="card-text">Adhérents Valides</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="card-title">{{ $stats['avec_anomalies'] }}</h3>
                    <p class="card-text">Avec Anomalies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="card-title">{{ $stats['taux_validite'] }}%</h3>
                    <p class="card-text">Taux de Validité</p>
                </div>
            </div>
        </div>
    </div>

    {{-- RÉPARTITION DES ANOMALIES --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Anomalies Critiques
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-danger">{{ $stats['anomalies_critiques'] }}</h2>
                    <small class="text-muted">Action immédiate requise</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Anomalies Majeures
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-warning">{{ $stats['anomalies_majeures'] }}</h2>
                    <small class="text-muted">À traiter sous 48h</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Anomalies Mineures
                    </h6>
                </div>
                <div class="card-body text-center">
                    <h2 class="text-info">{{ $stats['anomalies_mineures'] }}</h2>
                    <small class="text-muted">Recommandé de corriger</small>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLEAU DES ANOMALIES --}}
    <div class="card shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>
                Liste Détaillée des Anomalies
            </h5>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i>Actualiser
            </button>
        </div>
        <div class="card-body">
            @if($anomalies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>NIP</th>
                                <th>Adhérent</th>
                                <th>Type</th>
                                <th>Champ Concerné</th>
                                <th>Message</th>
                                <th>Impact Métier</th>
                                <th>Priorité</th>
                                <th>Détectée le</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($anomalies as $anomalie)
                            <tr>
                                <td>
                                    <code class="text-primary">{{ $anomalie->nip }}</code>
                                </td>
                                <td>
                                    <strong>{{ $anomalie->civilite }} {{ $anomalie->nom }} {{ $anomalie->prenom }}</strong>
                                </td>
                                <td>
                                    @if($anomalie->type_anomalie == 'critique')
                                        <span class="badge bg-danger">CRITIQUE</span>
                                    @elseif($anomalie->type_anomalie == 'majeure')
                                        <span class="badge bg-warning text-dark">MAJEURE</span>
                                    @else
                                        <span class="badge bg-info">MINEURE</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ strtoupper($anomalie->champ_concerne) }}</span>
                                </td>
                                <td>
                                    <small>{{ $anomalie->message_anomalie }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $anomalie->impact_metier }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $anomalie->priorite == 1 ? 'danger' : ($anomalie->priorite == 2 ? 'warning' : 'info') }}">
                                        P{{ $anomalie->priorite }}
                                    </span>
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($anomalie->detectee_le)->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    @if($anomalie->statut == 'detectee')
                                        <span class="badge bg-warning">En Attente</span>
                                    @elseif($anomalie->statut == 'resolu')
                                        <span class="badge bg-success">Résolue</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($anomalie->statut) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="d-flex justify-content-center mt-4">
                    {{ $anomalies->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    <h4 class="text-success mt-3">Aucune Anomalie Détectée</h4>
                    <p class="text-muted">Félicitations ! Tous les adhérents ont été importés sans anomalie.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- SCRIPTS --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Page consultation anomalies chargée');
    
    // ✅ AUTO-REFRESH TOUTES LES 60 SECONDES
    setInterval(function() {
        console.log('🔄 Auto-refresh anomalies...');
        location.reload();
    }, 60000);
});
</script>
@endpush
@endsection