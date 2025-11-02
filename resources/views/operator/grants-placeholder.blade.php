@extends('layouts.operator')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h3 class="card-title">💰 Subventions</h3>
                </div>
                <div class="card-body text-center py-5">
                    <i class="fas fa-hand-holding-usd fa-5x text-warning mb-4"></i>
                    <h4>Module Subventions</h4>
                    <p class="lead text-muted">
                        {{ $message ?? 'Ce module est en cours de développement' }}
                    </p>
                    <div class="alert alert-info mt-4">
                        <i class="fas fa-info-circle"></i>
                        <strong>Bientôt disponible :</strong>
                        <ul class="text-left mt-2">
                            <li>Demande de subventions</li>
                            <li>Suivi des dossiers de subventions</li>
                            <li>Historique des subventions obtenues</li>
                            <li>Rapports financiers</li>
                        </ul>
                    </div>
                    <a href="{{ route('operator.dashboard') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-left"></i> Retour au Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection