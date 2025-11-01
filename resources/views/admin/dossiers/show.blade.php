{{-- resources/views/admin/dossiers/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Détail Dossier - ' . ($dossier->numero_dossier ?? 'N/A'))

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.dossiers.en-attente') }}">Dossiers</a></li>
                    <li class="breadcrumb-item active">{{ $dossier->numero_dossier ?? 'Détail' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header du dossier avec actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" 
                 style="background: linear-gradient(135deg, #003f7f 0%, #0056b3 100%);">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center mb-3">
                                <div class="status-circle me-3">
                                    @php
                                        $statusIcons = [
                                            'brouillon' => ['icon' => 'edit', 'bg' => 'secondary'],
                                            'soumis' => ['icon' => 'clock', 'bg' => 'warning'],
                                            'en_cours' => ['icon' => 'cogs', 'bg' => 'info'],
                                            'approuve' => ['icon' => 'check', 'bg' => 'success'],
                                            'rejete' => ['icon' => 'times', 'bg' => 'danger']
                                        ];
                                        $statusConfig = $statusIcons[$dossier->statut] ?? ['icon' => 'question', 'bg' => 'secondary'];
                                    @endphp
                                    <div class="status-circle bg-{{ $statusConfig['bg'] }}">
                                        <i class="fas fa-{{ $statusConfig['icon'] }} text-white fa-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <h2 class="mb-1">{{ $dossier->numero_dossier }}</h2>
                                    <h4 class="mb-0 opacity-90">{{ $dossier->organisation->nom ?? 'Organisation non définie' }}</h4>
                                    <div class="mt-2">
                                        <span class="badge bg-light text-dark fs-6">
                                            {{ ucfirst(str_replace('_', ' ', $dossier->organisation->type ?? 'N/A')) }}
                                        </span>
                                        @if($dossier->organisation && $dossier->organisation->prefecture)
                                            <span class="badge bg-light text-dark fs-6 ms-2">
                                                {{ $dossier->organisation->prefecture }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Informations de base -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <small class="opacity-75">Date de soumission</small>
                                        <div class="fw-bold">{{ \Carbon\Carbon::parse($dossier->created_at)->format('d/m/Y à H:i') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <small class="opacity-75">Délai d'attente</small>
                                        <div class="fw-bold">
                                            @php
                                                $delai = \Carbon\Carbon::parse($dossier->created_at)->diffInDays(now());
                                            @endphp
                                            {{ $delai }} jour{{ $delai > 1 ? 's' : '' }}
                                            @if($delai > 7)
                                                <i class="fas fa-exclamation-triangle text-warning ms-1" title="Priorité haute"></i>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <!-- Actions principales -->
                            {{-- BOUTONS D'ACTION - À VÉRIFIER --}}
                            @if($dossier->statut === 'soumis')
                                <button type="button" class="btn btn-success mb-2" onclick="assignerDossier()">
                                    <i class="fas fa-user-check"></i> Assigner à un Agent
                                </button>
                                <button type="button" class="btn btn-warning mb-2" onclick="demanderModification()">
                                    <i class="fas fa-edit"></i> Demander Modification
                                </button>
                            @elseif($dossier->statut === 'en_cours')
                                <button type="button" class="btn btn-success mb-2" onclick="approuverDossier()">
                                    <i class="fas fa-check"></i> Approuver
                                </button>
                                <button type="button" class="btn btn-danger mb-2" onclick="rejeterDossier()">
                                    <i class="fas fa-times"></i> Rejeter
                                </button>
                            @endif
                            <!-- FIN Actions principales -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Colonne principale - Détails -->
        <div class="col-lg-8">
            <!-- Informations de l'organisation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-building me-2"></i>Informations de l'Organisation
                    </h6>
                </div>
                <div class="card-body">
                    @if($dossier->organisation)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Nom complet</label>
                                    <div class="fw-bold">{{ $dossier->organisation->nom }}</div>
                                </div>
                                @if($dossier->organisation->sigle)
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Sigle</label>
                                    <div class="fw-bold">{{ $dossier->organisation->sigle }}</div>
                                </div>
                                @endif
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Type d'organisation</label>
                                    <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $dossier->organisation->type)) }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if($dossier->organisation->numero_recepisse)
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Numéro de récépissé</label>
                                    <div class="fw-bold">{{ $dossier->organisation->numero_recepisse }}</div>
                                </div>
                                @endif
                                <div class="info-group mb-3">
                                    <label class="text-muted small">Localisation</label>
                                    <div class="fw-bold">
                                        {{ $dossier->organisation->prefecture ?? 'Non renseigné' }}
                                        @if($dossier->organisation->commune)
                                            <br><small>{{ $dossier->organisation->commune }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($dossier->organisation->objet)
                        <div class="info-group">
                            <label class="text-muted small">Objet social</label>
                            <div class="fw-bold">{{ $dossier->organisation->objet }}</div>
                        </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Aucune information d'organisation disponible
                        </div>
                    @endif
                </div>
            </div>

            

            <!-- Historique et commentaires -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Historique et Commentaires
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Événement de création -->
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary">
                                <i class="fas fa-plus text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="mb-1">Dossier créé</h6>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($dossier->created_at)->format('d/m/Y à H:i') }}
                                        par {{ $dossier->user->name ?? 'Système' }}
                                    </small>
                                </div>
                                <p class="mb-0">Le dossier a été créé et soumis pour traitement.</p>
                            </div>
                        </div>

                        <!-- Commentaires s'il y en a -->
                        @if($dossier->operations && $dossier->operations->where('type_operation', 'commentaire')->count() > 0)
                            @foreach($dossier->operations->where('type_operation', 'commentaire')->sortBy('created_at') as $comment)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info">
                                    <i class="fas fa-comment text-white"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <h6 class="mb-1">
                                            {{ ucfirst($comment->type) }}
                                            @if($comment->type === 'assignation')
                                                <span class="badge badge-success">Assignation</span>
                                            @elseif($comment->type === 'validation')
                                                <span class="badge badge-warning">Validation</span>
                                            @else
                                                <span class="badge badge-info">Note</span>
                                            @endif
                                        </h6>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($comment->created_at)->format('d/m/Y à H:i') }}
                                            par {{ $comment->user->name ?? 'Système' }}
                                        </small>
                                    </div>
                                    <p class="mb-0">{{ $comment->contenu }}</p>
                                </div>
                            </div>
                            @endforeach
                        @endif

                        <!-- Assignation si elle existe -->
                        @if($dossier->assigned_to && $dossier->assignedAgent)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success">
                                <i class="fas fa-user-check text-white"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="mb-1">Dossier assigné</h6>
                                    <small class="text-muted">
                                        {{ $dossier->assigned_at ? \Carbon\Carbon::parse($dossier->assigned_at)->format('d/m/Y à H:i') : 'Date non renseignée' }}
                                    </small>
                                </div>
                                <p class="mb-0">
                                    Assigné à <strong>{{ $dossier->assignedAgent->name }}</strong>
                                    ({{ $dossier->assignedAgent->email }})
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Formulaire d'ajout de commentaire -->
                    <div class="mt-4">
                        <h6 class="mb-3">Ajouter un commentaire</h6>
                        <form id="commentForm">
                            <div class="form-group mb-3">
                                <textarea name="comment_text" 
                                          id="comment_text" 
                                          class="form-control" 
                                          rows="3"
                                          placeholder="Votre commentaire sur ce dossier..."
                                          required></textarea>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-comment"></i> Ajouter le Commentaire
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Colonne secondaire - Informations complémentaires -->
        <div class="col-lg-4">
            <!-- Statut et assignation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Statut du Dossier
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="status-badge-large bg-{{ $statusConfig['bg'] }} text-white">
                            <i class="fas fa-{{ $statusConfig['icon'] }} fa-2x mb-2"></i>
                            <h5 class="mb-0">{{ ucfirst($dossier->statut) }}</h5>
                        </div>
                    </div>
                    
                    @if($dossier->assigned_to && $dossier->assignedAgent)
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="fas fa-user-check"></i> Agent Assigné
                        </h6>
                        <strong>{{ $dossier->assignedAgent->name }}</strong><br>
                        <small>{{ $dossier->assignedAgent->email }}</small>
                        @if($dossier->assigned_at)
                            <hr class="my-2">
                            <small class="text-muted">
                                Assigné le {{ \Carbon\Carbon::parse($dossier->assigned_at)->format('d/m/Y à H:i') }}
                            </small>
                        @endif
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Non assigné</strong><br>
                        Ce dossier n'est pas encore assigné à un agent.
                    </div>
                    @endif

                    <!-- Priorité calculée -->
                    <div class="mb-3">
                        <label class="text-muted small">Priorité</label>
                        <div>
                            @php
                                $isPriority = false;
                                if ($dossier->organisation && $dossier->organisation->type === 'parti_politique') {
                                    $isPriority = true;
                                    $reason = 'Parti politique';
                                } elseif (\Carbon\Carbon::parse($dossier->created_at)->diffInDays(now()) > 7) {
                                    $isPriority = true;
                                    $reason = 'Délai > 7 jours';
                                } else {
                                    $reason = 'Normale';
                                }
                            @endphp
                            
                            @if($isPriority)
                                <span class="badge badge-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Haute
                                </span>
                                <br><small class="text-muted">{{ $reason }}</small>
                            @else
                                <span class="badge badge-secondary">Normale</span>
                            @endif
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    @if($dossier->statut === 'soumis')
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-sm" onclick="assignerDossier()">
                            <i class="fas fa-user-check"></i> Assigner
                        </button>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Informations du demandeur -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user me-2"></i>Demandeur
                    </h6>
                </div>
                <div class="card-body">
                    @if($dossier->user)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle bg-primary text-white me-3">
                                {{ strtoupper(substr($dossier->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <strong>{{ $dossier->user->name }}</strong><br>
                                <small class="text-muted">{{ $dossier->user->email }}</small>
                            </div>
                        </div>
                        
                        @if($dossier->user->phone)
                        <div class="mb-2">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <span>{{ $dossier->user->phone }}</span>
                        </div>
                        @endif
                        
                        <div class="mb-2">
                            <i class="fas fa-calendar text-muted me-2"></i>
                            <span>Inscrit le {{ \Carbon\Carbon::parse($dossier->user->created_at)->format('d/m/Y') }}</span>
                        </div>
                        
                        <div class="mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="contacterDemandeur()">
                                <i class="fas fa-envelope"></i> Contacter
                            </button>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Aucune information de demandeur disponible
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistiques du dossier -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar me-2"></i>Statistiques
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-primary">{{ $dossier->documents ? $dossier->documents->count() : 0 }}</h4>
                                <small class="text-muted">Document{{ ($dossier->documents && $dossier->documents->count() > 1) ? 's' : '' }}</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-item">
                                <h4 class="text-info">{{ $dossier->operations ? $dossier->operations->where('type_operation', 'commentaire')->count() : 0 }}</h4>
                                <small class="text-muted">Commentaire{{ ($dossier->operations && $dossier->operations->where('type_operation', 'commentaire')->count() > 1) ? 's' : '' }}</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Section Actions PDF rapides --}}
                    <div class="mb-3">
                        <h6 class="text-muted small mb-2">Actions PDF</h6>
                        <div class="d-grid gap-2">
                         {{-- ======================================= --}}
{{-- SECTION BOUTONS PDF - VERSION COMPLÈTE --}}
{{-- ======================================= --}}

<div class="card">
    <div class="card-header">
        <h5 class="card-title">
            <i class="fas fa-file-pdf me-2"></i>
            Documents Officiels
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            
            <!-- Accusé de réception (toujours disponible) -->
            <div class="col-md-4">
                <div class="d-grid">
                    <a href="{{ route('admin.dossiers.accuse-reception', $dossier->id) }}" 
                       class="btn btn-outline-primary"
                       title="Confirme la réception du dossier">
                        <i class="fas fa-file-alt me-2"></i>
                        Accusé de Réception
                    </a>
                </div>
                <small class="text-muted d-block mt-1">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    Toujours disponible
                </small>
            </div>

            <!-- Récépissé provisoire (NOUVEAU) -->
            <div class="col-md-4">
                <div class="d-grid">
                    @if(in_array($dossier->statut, ['soumis', 'en_cours', 'en_attente']))
                        <a href="{{ route('admin.dossiers.recepisse-provisoire', $dossier->id) }}" 
                           class="btn btn-outline-warning"
                           title="Atteste du dépôt en cours de traitement">
                            <i class="fas fa-file-contract me-2"></i>
                            Récépissé Provisoire
                        </a>
                        <small class="text-success d-block mt-1">
                            <i class="fas fa-check-circle me-1"></i>
                            Disponible
                        </small>
                    @else
                        <button class="btn btn-outline-secondary" disabled
                                title="Disponible uniquement pour les dossiers en cours de traitement">
                            <i class="fas fa-file-contract me-2"></i>
                            Récépissé Provisoire
                        </button>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-times-circle me-1"></i>
                            Non disponible (statut: {{ ucfirst($dossier->statut) }})
                        </small>
                    @endif
                </div>
            </div>

            <!-- Récépissé définitif (existant) -->
            <div class="col-md-4">
                <div class="d-grid">
                    @if($dossier->statut === 'approuve')
                        <a href="{{ route('admin.dossiers.recepisse-definitif', $dossier->id) }}" 
                           class="btn btn-outline-success"
                           title="Document officiel final après approbation">
                            <i class="fas fa-certificate me-2"></i>
                            Récépissé Définitif
                        </a>
                        <small class="text-success d-block mt-1">
                            <i class="fas fa-check-circle me-1"></i>
                            Disponible
                        </small>
                    @else
                        <button class="btn btn-outline-secondary" disabled
                                title="Disponible uniquement après approbation du dossier">
                            <i class="fas fa-certificate me-2"></i>
                            Récépissé Définitif
                        </button>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-times-circle me-1"></i>
                            Après approbation
                        </small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations sur les documents -->
        <div class="mt-4">
            <div class="alert alert-info mb-0">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    Informations sur les documents
                </h6>
                <div class="row">
                    <div class="col-md-4">
                        <strong>Accusé de réception :</strong>
                        <br><small>Confirme la réception de votre dossier par nos services</small>
                    </div>
                    <div class="col-md-4">
                        <strong>Récépissé provisoire :</strong>
                        <br><small>Atteste du dépôt complet en cours de traitement</small>
                    </div>
                    <div class="col-md-4">
                        <strong>Récépissé définitif :</strong>
                        <br><small>Document officiel final après validation complète</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript pour améliorer l'expérience utilisateur --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ajouter des tooltips Bootstrap si disponible
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Ajouter des indicateurs de chargement sur les boutons PDF
    document.querySelectorAll('a[href*="download"]').forEach(function(button) {
        button.addEventListener('click', function() {
            // Ajouter un spinner pendant le téléchargement
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Génération...';
            this.classList.add('disabled');
            
            // Restaurer après 3 secondes (le temps du téléchargement)
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove('disabled');
            }, 3000);
        });
    });
});
</script>
                        </div>
                    </div>

                    <hr>

                    {{-- Informations de dates --}}
                    <div class="small">
                        <div class="d-flex justify-content-between">
                            <span>Créé le:</span>
                            <strong>{{ \Carbon\Carbon::parse($dossier->created_at)->format('d/m/Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Dernière maj:</span>
                            <strong>{{ \Carbon\Carbon::parse($dossier->updated_at)->format('d/m/Y') }}</strong>
                        </div>
                        @if($dossier->statut === 'approuve' && $dossier->validated_at)
                        <div class="d-flex justify-content-between">
                            <span>Approuvé le:</span>
                            <strong>{{ \Carbon\Carbon::parse($dossier->validated_at)->format('d/m/Y') }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section Debug (temporaire) -->
<div class="debug-info" style="display: none;" id="debugInfo">
    <strong>📋 DEBUG PDF - URLs TESTÉES ET CONFIRMÉES</strong><br>
    Dossier ID: {{ $dossier->id }}<br>
    Statut: {{ $dossier->statut }}<br>
    ✅ URL Accusé (TESTÉE): /admin/dossiers/{{ $dossier->id }}/accuse-reception<br>
    ✅ URL Récépissé (TESTÉE): /admin/dossiers/{{ $dossier->id }}/recepisse-definitif<br>
    🔍 URL Dossier complet: /admin/dossiers/{{ $dossier->id }}/pdf<br>
    Organisation: {{ $dossier->organisation->nom ?? 'N/A' }}<br>
    <small>💡 Utilisez showDebugInfo() dans la console pour afficher</small>
</div>

<!-- Modales -->
@include('admin.dossiers.modals.assign')
@include('admin.dossiers.modals.approve') 
@include('admin.dossiers.modals.reject')
@include('admin.dossiers.modals.request-modification')

@endsection

{{-- ======================================================================= --}}
{{-- REMPLACER COMPLÈTEMENT LA SECTION @push('scripts') DANS show.blade.php --}}
{{-- ======================================================================= --}}

@push('scripts')
<script>
    // ========== VARIABLES GLOBALES ==========
    window.dossierId = {{ $dossier->id }};
    let dossierId = {{ $dossier->id }};

    console.log('🚀 SCRIPT BOOTSTRAP 4 CHARGÉ - Dossier ID:', dossierId);

    // ========== FONCTIONS D'OUVERTURE DE MODALES (BOOTSTRAP 4) ==========

    /**
     * Ouvrir la modal d'assignation - Version Bootstrap 4
     */
    window.assignerDossier = function() {
        console.log('👤 Ouverture modal assignation - Dossier:', dossierId);
        
        const modalElement = document.getElementById('assignModal');
        if (!modalElement) {
            console.error('❌ Modal assignModal non trouvée');
            showAlert('error', 'Erreur : Modal d\'assignation non trouvée');
            return;
        }
        
        try {
            // ✅ BOOTSTRAP 4 : Utiliser jQuery uniquement
            $('#assignModal').modal('show');
            console.log('✅ Modal assignation ouverte avec succès (Bootstrap 4)');
        } catch (error) {
            console.error('❌ Erreur ouverture modal assignation:', error);
            showAlert('error', 'Erreur lors de l\'ouverture de la modal');
        }
    };

    /**
     * Ouvrir la modal d'approbation - Version Bootstrap 4
     */
    window.approuverDossier = function() {
        console.log('✅ Ouverture modal approbation - Dossier:', dossierId);
        
        const modalElement = document.getElementById('approveModal');
        if (!modalElement) {
            console.error('❌ Modal approveModal non trouvée');
            showAlert('error', 'Erreur : Modal d\'approbation non trouvée');
            return;
        }
        
        try {
            // ✅ BOOTSTRAP 4 : Utiliser jQuery uniquement
            $('#approveModal').modal('show');
            
            // Auto-générer numéro de récépissé après ouverture
            setTimeout(() => {
                const numeroField = document.getElementById('numero_recepisse_final');
                if (numeroField && !numeroField.value.trim()) {
                    const year = new Date().getFullYear();
                    const random = Math.floor(Math.random() * 9999).toString().padStart(4, '0');
                    const typeOrg = '{{ strtoupper(substr($dossier->organisation->type ?? "ORG", 0, 3)) }}';
                    numeroField.value = `${typeOrg}-${year}-${random}`;
                    console.log('🔢 Numéro auto-généré:', numeroField.value);
                }
            }, 60000);
            
            console.log('✅ Modal approbation ouverte avec succès (Bootstrap 4)');
        } catch (error) {
            console.error('❌ Erreur ouverture modal approbation:', error);
            showAlert('error', 'Erreur lors de l\'ouverture de la modal');
        }
    };

    /**
     * Ouvrir la modal de rejet - Version Bootstrap 4
     */
    window.rejeterDossier = function() {
        console.log('❌ Ouverture modal rejet - Dossier:', dossierId);
        
        const modalElement = document.getElementById('rejectModal');
        if (!modalElement) {
            console.error('❌ Modal rejectModal non trouvée');
            showAlert('error', 'Erreur : Modal de rejet non trouvée');
            return;
        }
        
        try {
            // ✅ BOOTSTRAP 4 : Utiliser jQuery uniquement
            $('#rejectModal').modal('show');
            console.log('✅ Modal rejet ouverte avec succès (Bootstrap 4)');
        } catch (error) {
            console.error('❌ Erreur ouverture modal rejet:', error);
            showAlert('error', 'Erreur lors de l\'ouverture de la modal');
        }
    };

    /**
     * Ouvrir la modal de demande de modification - Version Bootstrap 4
     */
    window.demanderModification = function() {
        console.log('✏️ Ouverture modal modification - Dossier:', dossierId);
        
        const modalElement = document.getElementById('requestModificationModal');
        if (!modalElement) {
            console.error('❌ Modal requestModificationModal non trouvée');
            showAlert('error', 'Erreur : Modal de modification non trouvée');
            return;
        }
        
        try {
            // ✅ BOOTSTRAP 4 : Utiliser jQuery uniquement
            $('#requestModificationModal').modal('show');
            console.log('✅ Modal modification ouverte avec succès (Bootstrap 4)');
        } catch (error) {
            console.error('❌ Erreur ouverture modal modification:', error);
            showAlert('error', 'Erreur lors de l\'ouverture de la modal');
        }
    };

    // ========== FONCTIONS PDF ==========

    window.telechargerAccuse = function() {
        console.log('📄 Téléchargement accusé - Dossier:', dossierId);
        
        showLoadingAlert('Génération de l\'accusé de réception...');
        
        const url = `/admin/dossiers/${dossierId}/accuse-reception`;
        console.log('🔗 URL accusé:', url);
        
        try {
            const link = document.createElement('a');
            link.href = url;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            setTimeout(() => {
                hideLoadingAlert();
                showAlert('success', 'Accusé de réception téléchargé', 8000); // ✅ Délai prolongé
            }, 60000);
            
        } catch (error) {
            console.error('❌ Erreur téléchargement accusé:', error);
            hideLoadingAlert();
            showAlert('error', 'Erreur lors du téléchargement', 12000); // ✅ Délai prolongé pour erreur
        }
    };

    window.telechargerRecepisse = function() {
        const statutDossier = '{{ $dossier->statut }}';
        console.log('🏆 Téléchargement récépissé - Statut:', statutDossier);
        
        if (statutDossier !== 'approuve') {
            showAlert('warning', 'Le récépissé n\'est disponible que pour les dossiers approuvés', 10000);
            return;
        }
        
        showLoadingAlert('Génération du récépissé définitif...');
        
        const url = `/admin/dossiers/${dossierId}/recepisse-definitif`;
        console.log('🔗 URL récépissé:', url);
        
        try {
            const link = document.createElement('a');
            link.href = url;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            setTimeout(() => {
                hideLoadingAlert();
                showAlert('success', 'Récépissé définitif téléchargé', 8000);
            }, 60000);
            
        } catch (error) {
            console.error('❌ Erreur téléchargement récépissé:', error);
            hideLoadingAlert();
            showAlert('error', 'Erreur lors du téléchargement', 12000);
        }
    };

    window.telechargerRecepisseProvisoire = function() {
        console.log('📋 Téléchargement récépissé provisoire - Dossier:', dossierId);
        
        showLoadingAlert('Génération du récépissé provisoire...');
        
        const url = `/admin/dossiers/${dossierId}/recepisse-provisoire`;
        console.log('🔗 URL récépissé provisoire:', url);
        
        try {
            const link = document.createElement('a');
            link.href = url;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            setTimeout(() => {
                hideLoadingAlert();
                showAlert('success', 'Récépissé provisoire téléchargé', 8000);
            }, 60000);
            
        } catch (error) {
            console.error('❌ Erreur téléchargement récépissé provisoire:', error);
            hideLoadingAlert();
            showAlert('error', 'Erreur lors du téléchargement', 12000);
        }
    };

    window.exporterDossierComplet = function() {
        console.log('📁 Export dossier complet - Dossier:', dossierId);
        
        showLoadingAlert('Génération du dossier complet...');
        
        const url = `/admin/dossiers/${dossierId}/pdf`;
        
        try {
            window.open(url, '_blank');
            
            setTimeout(() => {
                hideLoadingAlert();
                showAlert('success', 'Dossier complet généré', 6000);
            }, 60000);
            
        } catch (error) {
            console.error('❌ Erreur export dossier:', error);
            hideLoadingAlert();
            showAlert('error', 'Erreur lors de l\'export', 12000);
        }
    };

    window.imprimerDossier = function() {
        console.log('🖨️ Impression dossier');
        
        const elementsToHide = document.querySelectorAll('.btn, .breadcrumb, .dropdown-menu');
        elementsToHide.forEach(el => el.style.display = 'none');
        
        const titre = document.createElement('h1');
        titre.innerHTML = `DOSSIER {{ $dossier->numero_dossier ?? 'N/A' }}`;
        titre.style.textAlign = 'center';
        titre.style.marginBottom = '20px';
        titre.className = 'print-title';
        document.querySelector('.container-fluid').insertBefore(titre, document.querySelector('.row'));
        
        window.print();
        
        setTimeout(() => {
            elementsToHide.forEach(el => el.style.display = '');
            const printTitle = document.querySelector('.print-title');
            if (printTitle) printTitle.remove();
        }, 60000);
    };

    // ========== FONCTIONS UTILITAIRES AMÉLIORÉES ==========

    function showLoadingAlert(message) {
        const existingAlerts = document.querySelectorAll('.loading-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-info loading-alert';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="spinner-border spinner-border-sm mr-2" role="status">
                    <span class="sr-only">Chargement...</span>
                </div>
                <strong>${message}</strong>
            </div>
        `;
        
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function hideLoadingAlert() {
        const loadingAlerts = document.querySelectorAll('.loading-alert');
        loadingAlerts.forEach(alert => {
            alert.style.transition = 'opacity 0.3s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 60000);
        });
    }

    function showAlert(type, message, duration = null) {
        // ✅ DURÉES PROLONGÉES ET ADAPTÉES
        const defaultDurations = {
            'success': 60000,  // 8 secondes pour succès
            'error': 60000,   // 12 secondes pour erreur
            'warning': 60000, // 10 secondes pour avertissement
            'info': 60000      // 6 secondes pour info
        };
        
        const alertDuration = duration || defaultDurations[type] || 8000;
        
        const typeMap = {
            'success': 'success',
            'error': 'danger', 
            'warning': 'warning',
            'info': 'info'
        };
        
        const alertClass = typeMap[type] || 'info';
        const iconMap = {
            'success': 'check-circle',
            'error': 'exclamation-triangle',
            'warning': 'exclamation-circle',
            'info': 'info-circle'
        };
        
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${alertClass} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${iconMap[type]} mr-2"></i>
                <strong>${message}</strong>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        const container = document.querySelector('.container-fluid');
        if (container) {
            container.insertBefore(alertDiv, container.firstChild);
            
            // ✅ Auto-suppression avec durée prolongée
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    $(alertDiv).fadeOut(300, function() {
                        this.remove();
                    });
                }
            }, alertDuration);
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // ========== FONCTIONS SUPPLÉMENTAIRES ==========

    window.envoyerEmail = function() {
        showAlert('info', 'Fonction d\'envoi d\'email à implémenter', 6000);
    };

    window.contacterDemandeur = function() {
        showAlert('info', 'Fonction de contact à implémenter', 6000);
    };

    // ========== GESTIONNAIRES DE FORMULAIRES (BOOTSTRAP 4) ==========

    document.addEventListener('DOMContentLoaded', function() {
        console.log('📦 DOM chargé - Initialisation gestionnaires Bootstrap 4');
        
        // Vérifier jQuery (requis pour Bootstrap 4)
        if (typeof $ === 'undefined') {
            console.error('❌ jQuery non disponible - requis pour Bootstrap 4');
            return;
        }
        
        console.log('✅ jQuery disponible pour Bootstrap 4');
        
        // Initialiser les gestionnaires de formulaires après délai
        setTimeout(initializeFormHandlers, 500);
        
        // Gestionnaire commentaire
        const commentForm = document.getElementById('commentForm');
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleCommentSubmission(this);
            });
            console.log('✅ Gestionnaire commentaire initialisé');
        }
    });

    function initializeFormHandlers() {
        console.log('🔧 Initialisation gestionnaires formulaires Bootstrap 4');
        
        // Formulaire d'approbation
        const approveForm = document.getElementById('approveForm');
        if (approveForm) {
            approveForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleApproveSubmission(this);
            });
            console.log('✅ Gestionnaire approbation initialisé');
        }
        
        // Formulaire d'assignation
        const assignForm = document.getElementById('assignForm');
        if (assignForm) {
            assignForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleAssignSubmission(this);
            });
            console.log('✅ Gestionnaire assignation initialisé');
        }
        
        // Formulaire de rejet
        const rejectForm = document.getElementById('rejectForm');
        if (rejectForm) {
            rejectForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleRejectSubmission(this);
            });
            console.log('✅ Gestionnaire rejet initialisé');
        }
        
        // Formulaire de demande de modification
        const modificationForm = document.getElementById('requestModificationForm');
        if (modificationForm) {
            modificationForm.addEventListener('submit', function(e) {
                e.preventDefault();
                handleModificationSubmission(this);
            });
            console.log('✅ Gestionnaire modification initialisé');
        }
    }

    // ========== GESTIONNAIRES DE SOUMISSION CORRIGÉS BOOTSTRAP 4 ==========

    function handleApproveSubmission(form) {
        console.log('🚀 Soumission formulaire approbation');
        
        const numeroRecepisse = form.querySelector('#numero_recepisse_final').value.trim();
        const dateApprobation = form.querySelector('#date_approbation').value;
        
        if (!numeroRecepisse) {
            showAlert('warning', 'Le numéro de récépissé est obligatoire', 10000);
            return;
        }
        
        if (!dateApprobation) {
            showAlert('warning', 'La date d\'approbation est obligatoire', 10000);
            return;
        }
        
        showLoadingAlert('Traitement de l\'approbation en cours...');
        
        const formData = new FormData(form);
        
        fetch(`/admin/dossiers/${dossierId}/validate`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingAlert();
            
            if (data.success) {
                // ✅ BOOTSTRAP 4 : Utiliser jQuery pour fermer la modal
                $('#approveModal').modal('hide');
                
                showAlert('success', 'Dossier approuvé avec succès !', 8000);
                
                setTimeout(() => {
                    window.location.reload();
                }, 60000);
                
            } else {
                showAlert('error', data.message || 'Erreur lors de l\'approbation', 12000);
            }
        })
        .catch(error => {
            hideLoadingAlert();
            console.error('❌ Erreur approbation:', error);
            showAlert('error', 'Erreur technique lors de l\'approbation', 12000);
        });
    }

   // ========== GESTIONNAIRE D'ASSIGNATION COMPLET AVEC FIFO + PRIORITÉ ==========

function handleAssignSubmission(form) {
    console.log('🚀 Soumission formulaire assignation avec FIFO + priorité');
    
    // ✅ VALIDATION DES DONNÉES REQUISES
    const agentId = form.querySelector('#agent_id').value;
    const prioriteNiveau = form.querySelector('#priorite_niveau').value;
    
    if (!agentId) {
        showAlert('warning', 'Veuillez sélectionner un agent', 10000);
        return;
    }
    
    // ✅ VALIDATION SPÉCIALE POUR PRIORITÉ URGENTE
    if (prioriteNiveau === 'urgente') {
        const justification = form.querySelector('#priorite_justification').value.trim();
        
        if (!justification || justification.length < 20) {
            showAlert('warning', 'Une justification détaillée (minimum 20 caractères) est obligatoire pour la priorité urgente', 12000);
            document.getElementById('priorite_justification').focus();
            return;
        }
        
        // Confirmation supplémentaire pour urgente
        if (!confirm('⚠️ ATTENTION: Vous allez placer ce dossier en TÊTE DE LA QUEUE.\n\nCeci va décaler tous les autres dossiers.\n\nÊtes-vous sûr de vouloir continuer ?')) {
            return;
        }
    }
    
    // ✅ RÉCUPÉRATION DES DONNÉES DU FORMULAIRE
    const formData = {
        agent_id: agentId,
        priorite_niveau: prioriteNiveau,
        priorite_justification: form.querySelector('#priorite_justification').value.trim(),
        instructions_agent: form.querySelector('#instructions_agent').value.trim(),
        notifier_agent_email: form.querySelector('#notifier_agent_email').checked,
        notification_immediate: form.querySelector('#notification_immediate').checked
    };
    
    // ✅ INFORMATIONS DE L'AGENT SÉLECTIONNÉ
    const agentSelect = form.querySelector('#agent_id');
    const selectedOption = agentSelect.options[agentSelect.selectedIndex];
    const agentName = selectedOption.text.split(' - ')[0];
    const agentEmail = selectedOption.getAttribute('data-email');
    
    console.log('📋 Données d\'assignation avec priorité:', {
        ...formData,
        agentName: agentName,
        agentEmail: agentEmail
    });
    
    // ✅ MESSAGE DE LOADING ADAPTÉ À LA PRIORITÉ
    let loadingMessage = 'Assignation du dossier en cours...';
    if (prioriteNiveau === 'urgente') {
        loadingMessage = '🚨 Assignation URGENTE en cours - Réorganisation de la queue...';
    } else if (prioriteNiveau === 'haute') {
        loadingMessage = '🔥 Assignation prioritaire en cours...';
    }
    
    showLoadingAlert(loadingMessage);
    
    // ✅ PRÉPARATION DES DONNÉES POUR L'ENVOI
    const formDataToSend = new FormData();
    Object.keys(formData).forEach(key => {
        if (formData[key] !== null && formData[key] !== undefined) {
            formDataToSend.append(key, formData[key]);
        }
    });
    
    // Ajouter les données de l'agent
    formDataToSend.append('agent_name', agentName);
    formDataToSend.append('agent_email', agentEmail);
    
    // ✅ ENVOI DE LA REQUÊTE AVEC GESTION D'ERREURS AMÉLIORÉE
    fetch(`/admin/dossiers/${dossierId}/assign`, {
        method: 'POST',
        body: formDataToSend,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        hideLoadingAlert();
        
        if (data.success) {
            // ✅ FERMER LA MODAL (BOOTSTRAP 4)
            $('#assignModal').modal('hide');
            
            // ✅ MESSAGES DE SUCCÈS PERSONNALISÉS SELON LA PRIORITÉ
            let successMessage = `Dossier assigné avec succès à ${agentName}`;
            
            if (data.data && data.data.queue_info) {
                const queueInfo = data.data.queue_info;
                
                if (queueInfo.priorite === 'urgente') {
                    successMessage += ` 🚨 EN PRIORITÉ URGENTE (Position 1)`;
                } else {
                    successMessage += ` - Position ${queueInfo.position} (${queueInfo.priorite})`;
                }
                
                if (queueInfo.queue_reorganized) {
                    successMessage += ` - Queue réorganisée`;
                }
            }
            
            showAlert('success', successMessage, 10000);
            
            // ✅ AFFICHER LES INFORMATIONS SUPPLÉMENTAIRES
            if (formData.instructions_agent) {
                setTimeout(() => {
                    const instructionsPreview = formData.instructions_agent.length > 80 
                        ? formData.instructions_agent.substring(0, 80) + '...' 
                        : formData.instructions_agent;
                    showAlert('info', `📝 Instructions transmises: "${instructionsPreview}"`, 8000);
                }, 60000);
            }
            
            if (formData.notifier_agent_email && data.data.email_sent) {
                setTimeout(() => {
                    showAlert('info', `📧 Email de notification envoyé à ${agentEmail}`, 6000);
                }, 60000);
            } else if (formData.notifier_agent_email && !data.data.email_sent) {
                setTimeout(() => {
                    showAlert('warning', '⚠️ Email de notification non envoyé - Vérifier la configuration', 8000);
                }, 60000);
            }
            
            // ✅ AFFICHER LES DÉTAILS DE LA QUEUE SI PRIORITÉ SPÉCIALE
            if (prioriteNiveau !== 'normale' && data.data.queue_info) {
                setTimeout(() => {
                    showFifoQueueUpdate(data.data.queue_info);
                }, 60000);
            }
            
            // ✅ RECHARGEMENT DE LA PAGE
            setTimeout(() => {
                window.location.reload();
            }, 60000); // Délai plus long pour laisser le temps de lire les messages
            
        } else {
            // ✅ GESTION D'ERREURS MÉTIER
            let errorMessage = data.message || 'Erreur lors de l\'assignation';
            
            if (data.errors) {
                // Erreurs de validation
                const errorsList = Object.values(data.errors).flat().join(', ');
                errorMessage += ': ' + errorsList;
            }
            
            showAlert('error', errorMessage, 15000);
        }
    })
    .catch(error => {
        hideLoadingAlert();
        console.error('❌ Erreur assignation avec priorité:', error);
        
        let errorMessage = 'Erreur technique lors de l\'assignation';
        
        if (error.message.includes('HTTP 403')) {
            errorMessage = '🚫 Permissions insuffisantes pour cette priorité';
        } else if (error.message.includes('HTTP 422')) {
            errorMessage = '📝 Données invalides - Vérifiez le formulaire';
        } else if (error.message.includes('HTTP 500')) {
            errorMessage = '💥 Erreur serveur - Contactez l\'administrateur';
        }
        
        showAlert('error', errorMessage, 15000);
    });
}

// ========== FONCTION POUR AFFICHER LA MISE À JOUR DE LA QUEUE ==========

function showFifoQueueUpdate(queueInfo) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-info alert-dismissible fade show fifo-queue-alert';
    
    let queueIcon = '📋';
    let queueColor = 'info';
    
    if (queueInfo.priorite === 'urgente') {
        queueIcon = '🚨';
        queueColor = 'danger';
        alertDiv.className = alertDiv.className.replace('alert-info', 'alert-danger');
    } else if (queueInfo.priorite === 'haute') {
        queueIcon = '🔥';
        queueColor = 'warning';
        alertDiv.className = alertDiv.className.replace('alert-info', 'alert-warning');
    }
    
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="mr-3" style="font-size: 1.5em;">${queueIcon}</div>
            <div>
                <strong>Queue FIFO mise à jour</strong><br>
                <small>
                    Position dans la queue: <strong>#${queueInfo.position}</strong> 
                    (Priorité: ${queueInfo.priorite})
                    ${queueInfo.queue_reorganized ? '<br>🔄 Toute la queue a été réorganisée' : ''}
                </small>
            </div>
        </div>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    const container = document.querySelector('.container-fluid');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-suppression après 10 secondes
        setTimeout(() => {
            if (alertDiv.parentNode) {
                $(alertDiv).fadeOut(300, function() {
                    this.remove();
                });
            }
        }, 10000);
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

// ========== FONCTION POUR PRÉVISUALISER L'IMPACT DE LA PRIORITÉ ==========

function previewPriorityImpact(prioriteNiveau) {
    // Calculer et afficher l'impact sur la queue
    fetch(`/admin/dossiers/calculate-position`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            priority: prioriteNiveau,
            dossier_id: dossierId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const estimatedPosition = document.getElementById('estimatedPosition');
            if (estimatedPosition) {
                estimatedPosition.textContent = `Position ${data.position}`;
                
                // Changer la couleur selon la position
                if (data.position <= 3) {
                    estimatedPosition.className = 'text-success font-weight-bold';
                } else if (data.position <= 10) {
                    estimatedPosition.className = 'text-warning font-weight-bold';
                } else {
                    estimatedPosition.className = 'text-info';
                }
            }
            
            // Mettre à jour l'info de la position actuelle
            const currentPosition = document.getElementById('currentPosition');
            if (currentPosition && prioriteNiveau !== 'normale') {
                currentPosition.innerHTML = `
                    <span class="badge badge-secondary">Actuel: ${data.current_position || 'N/A'}</span>
                    <span class="badge badge-primary">Nouveau: ${data.position}</span>
                `;
            }
        }
    })
    .catch(error => {
        console.error('Erreur calcul position:', error);
        const estimatedPosition = document.getElementById('estimatedPosition');
        if (estimatedPosition) {
            estimatedPosition.textContent = 'Erreur de calcul';
            estimatedPosition.className = 'text-danger';
        }
    });
}

// ========== STYLES CSS POUR LES ALERTES FIFO ==========

const fifoStyles = document.createElement('style');
fifoStyles.textContent = `
.fifo-queue-alert {
    border-left: 4px solid #17a2b8;
    animation: slideInFromTop 0.5s ease-out;
}

.fifo-queue-alert.alert-danger {
    border-left-color: #dc3545;
}

.fifo-queue-alert.alert-warning {
    border-left-color: #ffc107;
}

@keyframes slideInFromTop {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.priority-impact-info {
    padding: 10px;
    border-radius: 5px;
    margin: 10px 0;
    border-left: 3px solid #007bff;
    background: linear-gradient(90deg, #f8f9fc 0%, #e3e6f0 100%);
}
`;

document.head.appendChild(fifoStyles);

console.log('✅ Gestionnaire FIFO + Priorité chargé avec succès');

    function handleRejectSubmission(form) {
        console.log('🚀 Soumission formulaire rejet');
        
        const motifRejet = form.querySelector('#motif_rejet').value;
        const justificationRejet = form.querySelector('#justification_rejet').value.trim();
        
        if (!motifRejet) {
            showAlert('warning', 'Veuillez sélectionner un motif de rejet', 10000);
            return;
        }
        
        if (!justificationRejet) {
            showAlert('warning', 'La justification est obligatoire', 10000);
            return;
        }
        
        showLoadingAlert('Traitement du rejet en cours...');
        
        const formData = new FormData(form);
        
        fetch(`/admin/dossiers/${dossierId}/reject`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingAlert();
            
            if (data.success) {
                // ✅ BOOTSTRAP 4 : Utiliser jQuery pour fermer la modal
                $('#rejectModal').modal('hide');
                
                showAlert('success', data.message || 'Dossier rejeté avec succès', 8000);
                
                setTimeout(() => {
                    window.location.reload();
                }, 60000);
                
            } else {
                showAlert('error', data.message || 'Erreur lors du rejet', 12000);
            }
        })
        .catch(error => {
            hideLoadingAlert();
            console.error('❌ Erreur rejet:', error);
            showAlert('error', 'Erreur technique lors du rejet', 12000);
        });
    }

    function handleModificationSubmission(form) {
        console.log('🚀 Soumission formulaire demande modification');
        
        const detailsModifications = form.querySelector('#details_modifications').value.trim();
        
        if (!detailsModifications) {
            showAlert('warning', 'Veuillez détailler les modifications demandées', 10000);
            return;
        }
        
        // Vérifier qu'au moins une modification est cochée
        const checkedModifications = form.querySelectorAll('input[name="modifications[]"]:checked');
        if (checkedModifications.length === 0) {
            showAlert('warning', 'Veuillez cocher au moins un type de modification', 10000);
            return;
        }
        
        showLoadingAlert('Envoi de la demande de modification...');
        
        const formData = new FormData(form);
        
        fetch(`/admin/dossiers/${dossierId}/request-modification`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingAlert();
            
            if (data.success) {
                // ✅ BOOTSTRAP 4 : Utiliser jQuery pour fermer la modal
                $('#requestModificationModal').modal('hide');
                
                showAlert('success', data.message || 'Demande de modification envoyée avec succès', 8000);
                
                setTimeout(() => {
                    window.location.reload();
                }, 60000);
                
            } else {
                showAlert('error', data.message || 'Erreur lors de l\'envoi de la demande', 12000);
            }
        })
        .catch(error => {
            hideLoadingAlert();
            console.error('❌ Erreur demande modification:', error);
            showAlert('error', 'Erreur technique lors de l\'envoi', 12000);
        });
    }

    function handleCommentSubmission(form) {
        console.log('🚀 Soumission formulaire commentaire');
        
        const commentText = form.querySelector('#comment_text').value.trim();
        
        if (!commentText) {
            showAlert('warning', 'Veuillez saisir un commentaire', 10000);
            return;
        }
        
        const formData = new FormData(form);
        
        fetch(`/admin/dossiers/${dossierId}/comment`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'Commentaire ajouté avec succès', 8000);
                form.reset();
                
                setTimeout(() => {
                    window.location.reload();
                }, 60000);
                
            } else {
                showAlert('error', data.message || 'Erreur lors de l\'ajout du commentaire', 12000);
            }
        })
        .catch(error => {
            console.error('❌ Erreur commentaire:', error);
            showAlert('error', 'Erreur technique lors de l\'ajout', 12000);
        });
    }

    // ========== LOG DE DÉMARRAGE ==========
    console.log('✅ SCRIPT BOOTSTRAP 4 SHOW.BLADE.PHP CHARGÉ AVEC SUCCÈS');
    console.log('📊 Fonctions disponibles:', {
        assignerDossier: typeof window.assignerDossier,
        approuverDossier: typeof window.approuverDossier,
        rejeterDossier: typeof window.rejeterDossier,
        demanderModification: typeof window.demanderModification
    });
    console.log('🎯 Toutes les fonctions utilisent jQuery/Bootstrap 4');
</script>
@endpush

@push('styles')
<style>
.status-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.info-item {
    margin-bottom: 1rem;
}

.info-group {
    margin-bottom: 1rem;
}

.status-badge-large {
    padding: 1.5rem;
    border-radius: 1rem;
    margin-bottom: 1rem;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -40px;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-content {
    background: #f8f9fc;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 3px solid #4e73df;
}

.timeline-header h6 {
    color: #5a5c69;
    margin-bottom: 0.25rem;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.stat-item h4 {
    margin-bottom: 0.25rem;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    border: 1px solid #e3e6f0;
}

/* ========== STYLES PDF AMÉLIORÉS ========== */

/* Améliorations pour les alertes de chargement */
.loading-alert {
    border-left: 4px solid #4e73df;
    background: linear-gradient(90deg, #f8f9fc 0%, #e3e6f0 100%);
    animation: slideDown 0.3s ease-out, pulse 2s infinite;
    font-weight: 500;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

/* Dropdown PDF avec style gabonais */
.dropdown-menu {
    border: 1px solid #e3e6f0;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    min-width: 220px;
    padding: 0.5rem 0;
}

.dropdown-item {
    padding: 0.75rem 1.25rem;
    border-radius: 0;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
}

.dropdown-item:hover {
    background: linear-gradient(90deg, #f8f9fc 0%, #e3e6f0 100%);
    color: #2c3e50;
    transform: translateX(3px);
}

.dropdown-item i {
    width: 24px;
    margin-right: 12px;
    font-size: 1.1em;
}

/* Amélioration des boutons PDF */
.btn-outline-primary.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.btn-outline-success.btn-sm:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

/* Style pour les alertes améliorées */
.alert {
    border-radius: 0.5rem;
    border-width: 1px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.alert-success {
    background: linear-gradient(45deg, #d4edda 0%, #c3e6cb 100%);
    border-color: #b8dacc;
}

.alert-danger {
    background: linear-gradient(45deg, #f8d7da 0%, #f5c6cb 100%);
    border-color: #f1b2b7;
}

.alert-warning {
    background: linear-gradient(45deg, #fff3cd 0%, #ffeaa7 100%);
    border-color: #fde68a;
}

.alert-info {
    background: linear-gradient(45deg, #d1ecf1 0%, #bee5eb 100%);
    border-color: #abdde5;
}

/* Spinner personnalisé */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
    border-width: 0.125rem;
}

/* Style pour l'impression */
@media print {
    .btn, .breadcrumb, .dropdown-menu, .card-header {
        display: none !important;
    }
    
    .print-title {
        color: #000;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }
    
    .timeline-marker {
        background-color: #ddd !important;
    }
}

/* Responsiveness pour mobile */
@media (max-width: 768px) {
    .dropdown-menu {
        min-width: 200px;
        margin-left: -80px;
    }
    
    .dropdown-item {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .loading-alert {
        font-size: 0.9rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

/* Style pour debug */
.debug-info {
    background: #1a1a1a;
    color: #00ff00;
    padding: 0.5rem;
    font-family: 'Courier New', monospace;
    font-size: 0.8rem;
    border-radius: 0.25rem;
    margin: 0.5rem 0;
}
</style>
@endpush