@extends('layouts.operator')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@push('styles')
<style>
    /* ========================================================================
       CHARTE GRAPHIQUE OFFICIELLE GABONAISE - DASHBOARD OPÉRATEUR
       Couleurs officielles : Vert #009e3f, Jaune #ffcd00, Bleu #003f7f
       ======================================================================== */
    
    :root {
        /* Couleurs officielles du Gabon */
        --gabon-green: #009e3f;
        --gabon-green-light: #00c851;
        --gabon-green-dark: #007a32;
        --gabon-yellow: #ffcd00;
        --gabon-yellow-light: #ffe066;
        --gabon-yellow-dark: #e6b800;
        --gabon-blue: #003f7f;
        --gabon-blue-light: #0066cc;
        --gabon-blue-dark: #002d5a;
        --gabon-red: #8b1538;
        --gabon-red-dark: #6d1029;
        
        /* Gradients thématiques gabonais */
        --gradient-primary: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-green-light) 100%);
        --gradient-success: linear-gradient(135deg, var(--gabon-green) 0%, var(--gabon-green-dark) 100%);
        --gradient-warning: linear-gradient(135deg, var(--gabon-yellow) 0%, var(--gabon-yellow-dark) 100%);
        --gradient-info: linear-gradient(135deg, var(--gabon-blue) 0%, var(--gabon-blue-light) 100%);
        --gradient-danger: linear-gradient(135deg, var(--gabon-red) 0%, var(--gabon-red-dark) 100%);
        --gradient-tricolor: linear-gradient(90deg, var(--gabon-green) 0%, var(--gabon-yellow) 50%, var(--gabon-blue) 100%);
        
        /* Couleurs de texte */
        --text-contrast: #ffffff;
        --text-dark: #2c3e50;
        --bg-light: #f8f9fa;
    }

    /* Background animé avec motif gabonais */
    .dashboard-bg {
        position: relative;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        min-height: calc(100vh - var(--header-height));
        padding: 0;
    }

    .dashboard-bg::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="gabon-pattern" x="0" y="0" width="25" height="25" patternUnits="userSpaceOnUse"><circle cx="12.5" cy="12.5" r="1.5" fill="rgba(0,63,127,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23gabon-pattern)"/></svg>');
        opacity: 0.5;
    }

    /* Header moderne aux couleurs gabonaises */
    .dashboard-header {
        background: var(--gradient-info);
        color: white;
        padding: 3rem 2rem;
        margin: 0;
        border-radius: 0;
        box-shadow: 0 10px 30px rgba(0,63,127,0.2);
        position: relative;
        overflow: hidden;
    }

    /* Motif de fond dans le header */
    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    .dashboard-header::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: -5%;
        width: 200px;
        height: 200px;
        background: rgba(255,205,0,0.15);
        border-radius: 50%;
        animation: float 8s ease-in-out infinite reverse;
    }

    /* Bande tricolore en bas du header */
    .dashboard-header .gabon-stripe {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: var(--gradient-tricolor);
        z-index: 10;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(180deg); }
    }

    /* Welcome text */
    .welcome-section {
        position: relative;
        z-index: 1;
    }

    .welcome-title {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        animation: slideInLeft 0.6s ease-out;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }

    .welcome-subtitle {
        font-size: 1.1rem;
        opacity: 0.95;
        animation: slideInLeft 0.8s ease-out;
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* Cards 3D modernes avec couleurs gabonaises */
    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: none;
        height: 100%;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,63,127,0.15);
    }

    /* Barre de couleur en haut de la carte */
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }

    .stat-card:hover::before {
        transform: scaleX(1);
    }

    .stat-card.card-green::before {
        background: var(--gradient-primary);
    }

    .stat-card.card-yellow::before {
        background: var(--gradient-warning);
    }

    .stat-card.card-blue::before {
        background: var(--gradient-info);
    }

    .stat-card.card-red::before {
        background: var(--gradient-danger);
    }

    /* Icônes animées avec couleurs gabonaises */
    .stat-icon {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 15px;
        font-size: 1.5rem;
        position: relative;
        transition: transform 0.3s ease;
        color: white;
    }

    .stat-card:hover .stat-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .icon-green { 
        background: var(--gradient-primary);
        box-shadow: 0 4px 15px rgba(0,158,63,0.3);
    }
    
    .icon-yellow { 
        background: var(--gradient-warning);
        box-shadow: 0 4px 15px rgba(255,205,0,0.3);
    }
    
    .icon-blue { 
        background: var(--gradient-info);
        box-shadow: 0 4px 15px rgba(0,63,127,0.3);
    }
    
    .icon-red { 
        background: var(--gradient-danger);
        box-shadow: 0 4px 15px rgba(139,21,56,0.3);
    }

    /* Texte des statistiques */
    .stat-card h3 {
        font-size: 2rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .stat-card.card-green h3 {
        color: var(--gabon-green);
    }

    .stat-card.card-yellow h3 {
        color: var(--gabon-yellow-dark);
    }

    .stat-card.card-blue h3 {
        color: var(--gabon-blue);
    }

    .stat-card.card-red h3 {
        color: var(--gabon-red);
    }

    /* Progress bars gabonaises */
    .progress-wrapper {
        margin-top: 1rem;
    }

    .progress-custom {
        height: 8px;
        border-radius: 10px;
        background: #e9ecef;
        overflow: visible;
        position: relative;
    }

    .progress-bar-custom {
        height: 100%;
        border-radius: 10px;
        position: relative;
        overflow: visible;
        animation: progressAnimation 1.5s ease-out;
    }

    .progress-bar-green {
        background: var(--gradient-primary);
        box-shadow: 0 2px 10px rgba(0,158,63,0.3);
    }

    .progress-bar-yellow {
        background: var(--gradient-warning);
        box-shadow: 0 2px 10px rgba(255,205,0,0.3);
    }

    .progress-bar-blue {
        background: var(--gradient-info);
        box-shadow: 0 2px 10px rgba(0,63,127,0.3);
    }

    .progress-bar-red {
        background: var(--gradient-danger);
        box-shadow: 0 2px 10px rgba(139,21,56,0.3);
    }

    @keyframes progressAnimation {
        from { width: 0; }
    }

    /* Quick actions cards avec thème gabonais */
    .quick-action-card {
        background: white;
        border-radius: 15px;
        padding: 2rem 1.5rem;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 2px solid transparent;
        position: relative;
        overflow: hidden;
        height: 100%;
        box-shadow: 0 3px 15px rgba(0,0,0,0.06);
    }

    .quick-action-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(0,63,127,0.1) 0%, transparent 70%);
        transform: scale(0);
        transition: transform 0.5s ease;
    }

    .quick-action-card:hover::before {
        transform: scale(1);
    }

    .quick-action-card:hover {
        transform: translateY(-8px);
        border-color: var(--gabon-blue);
        box-shadow: 0 20px 40px rgba(0,63,127,0.15);
    }

    .quick-action-card i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        background: var(--gradient-info);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        transition: transform 0.3s ease;
    }

    .quick-action-card:hover i {
        transform: scale(1.2) rotate(10deg);
    }

    .quick-action-card h5 {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
    }

    .quick-action-card p {
        font-size: 0.85rem;
        color: #6c757d;
        margin: 0;
    }

    /* Timeline moderne gabonaise */
    .timeline-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        height: 100%;
        border-top: 4px solid var(--gabon-blue);
    }

    .timeline-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .timeline-container {
        max-height: 350px;
        overflow-y: auto;
        padding-right: 10px;
    }

    .timeline-container::-webkit-scrollbar {
        width: 6px;
    }

    .timeline-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .timeline-container::-webkit-scrollbar-thumb {
        background: var(--gabon-blue);
        border-radius: 10px;
    }

    .timeline-item {
        position: relative;
        padding-left: 30px;
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-left: 2px solid #e9ecef;
    }

    .timeline-item:last-child {
        border-left: 2px solid transparent;
        margin-bottom: 0;
    }

    .timeline-dot {
        position: absolute;
        left: -6px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: var(--gradient-info);
        box-shadow: 0 0 0 3px white, 0 0 0 5px var(--gabon-blue-light);
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { 
            box-shadow: 0 0 0 3px white, 0 0 0 5px rgba(0,63,127,0.3);
        }
        50% { 
            box-shadow: 0 0 0 3px white, 0 0 0 8px rgba(0,63,127,0);
        }
    }

    .timeline-content h6 {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
    }

    .timeline-content p {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .timeline-time {
        font-size: 0.75rem;
        color: var(--gabon-blue);
        font-weight: 500;
    }

    /* Chart container gabonais */
    .chart-container {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        height: 100%;
        border-top: 4px solid var(--gabon-blue);
    }

    .chart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f0f0f0;
    }

    .chart-wrapper {
        height: 300px;
        position: relative;
    }

    /* Call to action section gabonaise */
    .cta-section {
        background: var(--gradient-info);
        border-radius: 25px;
        padding: 3rem 2rem;
        text-align: center;
        color: white;
        margin: 2rem 0;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,63,127,0.2);
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="cta-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23cta-pattern)"/></svg>');
        opacity: 0.5;
    }

    .cta-content {
        position: relative;
        z-index: 1;
    }

    .cta-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }

    .cta-text {
        font-size: 1.1rem;
        margin-bottom: 2rem;
        opacity: 0.95;
    }

    .btn-cta {
        background: white;
        color: var(--gabon-blue);
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        border: none;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    .btn-cta:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(0,0,0,0.2);
        background: var(--gabon-yellow);
        color: var(--gabon-green);
    }

    /* Badge aux couleurs gabonaises */
    .badge.bg-primary {
        background: var(--gabon-blue) !important;
    }

    .text-primary {
        color: var(--gabon-blue) !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dashboard-header {
            padding: 2rem 1rem;
        }

        .welcome-title {
            font-size: 1.5rem;
        }

        .welcome-subtitle {
            font-size: 1rem;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            font-size: 1.2rem;
        }

        .quick-action-card i {
            font-size: 2rem;
        }

        .cta-title {
            font-size: 1.4rem;
        }

        .cta-text {
            font-size: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-bg">
    <!-- Header avec couleurs gabonaises -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="welcome-section">
                <h1 class="welcome-title">
                    <i class="fas fa-hand-wave me-2"></i>
                    Bienvenue, {{ Auth::user()->name }} !
                </h1>
                <p class="welcome-subtitle">
                    <i class="fas fa-calendar-day me-2"></i>
                    {{ ucfirst(\Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}
                    <span class="ms-3">
                        <i class="fas fa-clock me-1"></i>
                        {{ now()->format('H:i') }}
                    </span>
                </p>
            </div>
        </div>
        <!-- Bande tricolore gabonaise -->
        <div class="gabon-stripe"></div>
    </div>

    <div class="container-fluid py-4">
        <!-- Statistiques principales -->
        <div class="row g-4 mb-4">
            <!-- Total organisations -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card card-green">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted text-uppercase fw-semibold">
                                <i class="fas fa-building me-1"></i>
                                Total Organisations
                            </small>
                            <h3 class="mt-2 mb-0">0</h3>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                Prêt à commencer
                            </small>
                        </div>
                        <div class="stat-icon icon-green">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                    <div class="progress-wrapper">
                        <div class="progress-custom">
                            <div class="progress-bar-custom progress-bar-green" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dossiers en cours -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card card-yellow">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted text-uppercase fw-semibold">
                                <i class="fas fa-folder-open me-1"></i>
                                Dossiers en cours
                            </small>
                            <h3 class="mt-2 mb-0">0</h3>
                            <small class="text-warning">
                                <i class="fas fa-clock me-1"></i>
                                En attente
                            </small>
                        </div>
                        <div class="stat-icon icon-yellow">
                            <i class="fas fa-folder-open"></i>
                        </div>
                    </div>
                    <div class="progress-wrapper">
                        <div class="progress-custom">
                            <div class="progress-bar-custom progress-bar-yellow" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dossiers validés -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card card-blue">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted text-uppercase fw-semibold">
                                <i class="fas fa-check-circle me-1"></i>
                                Dossiers validés
                            </small>
                            <h3 class="mt-2 mb-0">0</h3>
                            <small class="text-info">
                                <i class="fas fa-certificate me-1"></i>
                                Certifiés
                            </small>
                        </div>
                        <div class="stat-icon icon-blue">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="progress-wrapper">
                        <div class="progress-custom">
                            <div class="progress-bar-custom progress-bar-blue" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="col-lg-3 col-md-6">
                <div class="stat-card card-red">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small class="text-muted text-uppercase fw-semibold">
                                <i class="fas fa-comments me-1"></i>
                                Messages
                            </small>
                            <h3 class="mt-2 mb-0">0</h3>
                            <small class="text-danger">
                                <i class="fas fa-envelope me-1"></i>
                                Non lu
                            </small>
                        </div>
                        <div class="stat-icon icon-red">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                    <div class="progress-wrapper">
                        <div class="progress-custom">
                            <div class="progress-bar-custom progress-bar-red" style="width: 20%;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="row mb-4">
            <div class="col-12 mb-3">
                <h4 class="fw-bold">
                    <i class="fas fa-rocket me-2" style="color: var(--gabon-blue);"></i>
                    Actions rapides
                </h4>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="quick-action-card" onclick="location.href='{{ route('operator.organisations.create') }}'">
                    <i class="fas fa-plus-circle"></i>
                    <h5>Créer une organisation</h5>
                    <p>Commencez votre formalisation</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="quick-action-card" onclick="location.href='{{ route('operator.dossiers.index') }}'">
                    <i class="fas fa-file-upload"></i>
                    <h5>Mes dossiers</h5>
                    <p>Gérez vos demandes</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="quick-action-card" onclick="location.href='{{ route('operator.messages.index') }}'">
                    <i class="fas fa-message"></i>
                    <h5>Messagerie</h5>
                    <p>Contactez l'administration</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="quick-action-card" onclick="window.open('{{ route('guides') }}', '_blank')">
                    <i class="fas fa-book-open"></i>
                    <h5>Guides pratiques</h5>
                    <p>Toute la documentation</p>
                </div>
            </div>
        </div>

        <!-- Timeline et graphiques -->
        <div class="row">
            <!-- Timeline -->
            <div class="col-lg-4 mb-4">
                <div class="timeline-card">
                    <div class="timeline-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2" style="color: var(--gabon-blue);"></i>
                            Activité récente
                        </h5>
                        <span class="badge bg-primary">Nouveau</span>
                    </div>
                    <div class="timeline-container">
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>Compte créé</h6>
                                <p>Bienvenue sur SGLP !</p>
                                <span class="timeline-time">{{ now()->format('H:i') }}</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>Email vérifié</h6>
                                <p>Votre compte est activé</p>
                                <span class="timeline-time">{{ now()->subMinutes(5)->format('H:i') }}</span>
                            </div>
                        </div>
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <h6>Première connexion</h6>
                                <p>Découvrez votre espace</p>
                                <span class="timeline-time">Maintenant</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphique -->
            <div class="col-lg-8 mb-4">
                <div class="chart-container">
                    <div class="chart-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area me-2" style="color: var(--gabon-blue);"></i>
                            Vue d'ensemble
                        </h5>
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option>6 derniers mois</option>
                            <option>12 derniers mois</option>
                            <option>Cette année</option>
                        </select>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="dashboardChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Call to action gabonais -->
        <div class="cta-section">
            <div class="cta-content">
                <h2 class="cta-title">Prêt à formaliser votre organisation ?</h2>
                <p class="cta-text">
                    Créez votre première organisation et lancez le processus de formalisation en quelques clics
                </p>
                <button class="btn btn-cta" onclick="location.href='{{ route('operator.organisations.create') }}'">
                    <i class="fas fa-rocket me-2"></i>
                    Commencer maintenant
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Configuration du graphique avec couleurs gabonaises
const ctx = document.getElementById('dashboardChart').getContext('2d');

// Créer un gradient vert gabonais
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(0, 158, 63, 0.5)');
gradient.addColorStop(1, 'rgba(0, 158, 63, 0)');

// Chart configuration
const myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin'],
        datasets: [{
            label: 'Activité',
            data: [0, 0, 0, 0, 0, 1],
            backgroundColor: gradient,
            borderColor: '#009e3f',
            borderWidth: 3,
            pointBackgroundColor: '#009e3f',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointHoverBackgroundColor: '#ffcd00',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(0,158,63,0.9)',
                padding: 12,
                cornerRadius: 8,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                },
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Activité: ' + context.parsed.y;
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        size: 12
                    },
                    color: '#6c757d'
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    borderDash: [5, 5],
                    color: 'rgba(0,158,63,0.1)'
                },
                ticks: {
                    font: {
                        size: 12
                    },
                    color: '#6c757d',
                    stepSize: 1
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

// Animation des nombres
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.textContent = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Animer les statistiques au chargement
document.addEventListener('DOMContentLoaded', function() {
    const stats = document.querySelectorAll('.stat-card h3');
    stats.forEach(stat => {
        const value = parseInt(stat.textContent);
        if (value > 0) {
            animateValue(stat, 0, value, 1500);
        }
    });
    
    // Message de bienvenue personnalisé
    console.log('%c🇬🇦 SGLP - Système de Gestion des Libertés Publiques', 
        'color: #003f7f; font-size: 16px; font-weight: bold;');
    console.log('%cBienvenue sur votre tableau de bord opérateur', 
        'color: #003f7f; font-size: 14px;');
});

// Effet de parallaxe subtil sur les cartes
document.addEventListener('mousemove', (e) => {
    const cards = document.querySelectorAll('.stat-card, .quick-action-card');
    const x = e.clientX / window.innerWidth;
    const y = e.clientY / window.innerHeight;
    
    cards.forEach(card => {
        const rect = card.getBoundingClientRect();
        const cardX = (rect.left + rect.width / 2) / window.innerWidth;
        const cardY = (rect.top + rect.height / 2) / window.innerHeight;
        
        const deltaX = (x - cardX) * 5;
        const deltaY = (y - cardY) * 5;
        
        card.style.transform = `perspective(1000px) rotateX(${deltaY}deg) rotateY(${deltaX}deg)`;
    });
});

// Réinitialiser la transformation au mouseout
document.addEventListener('mouseout', () => {
    const cards = document.querySelectorAll('.stat-card, .quick-action-card');
    cards.forEach(card => {
        card.style.transform = '';
    });
});
</script>
@endpush