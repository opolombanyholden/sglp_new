<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') - SGLP</title>
    
    <!-- Bootstrap 4 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* Variables CSS - Couleurs Gabonaises */
        :root {
            --gabon-green: #009e3f;
            --gabon-yellow: #ffcd00;
            --gabon-blue: #003f7f;
            --gabon-red: #8b1538;
            --sidebar-width: 280px;
            --header-height: 70px;
        }

        /* Reset et base */
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            font-size: 14px;
        }

        /* Layout principal */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar exacte selon capture */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #0e2f5b;
            overflow-y: auto;
            z-index: 1000;
        }

        /* Header sidebar avec logos */
        .sidebar-header {
            padding: 1.5rem 1rem;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .main-logo {
            background: var(--gabon-green);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .logo-text-group {
            display: flex;
            flex-direction: column;
        }

        .sidebar-title {
            color: var(--gabon-yellow);
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
        }

        .sidebar-subtitle {
            color: var(--gabon-yellow);
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }

        .settings-icon {
            background: rgba(255,255,255,0.1);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        /* Profil utilisateur selon capture */
        .sidebar-profile {
            padding: 1rem;
            margin: 0 1rem 1.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-avatar {
            width: 45px;
            height: 45px;
            background: var(--gabon-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1rem;
            position: relative;
        }

        .profile-avatar::after {
            content: '';
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: var(--gabon-green);
            border: 2px solid #1e40af;
            border-radius: 50%;
        }

        .profile-info h6 {
            color: white;
            margin: 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .profile-info small {
            color: var(--gabon-yellow);
            font-size: 0.75rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* ✅ SYSTÈME D'ACCORDÉON UNIFIÉ */
        .nav-section {
            margin-bottom: 0.5rem;
        }

        .nav-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1rem;
            margin: 0 1rem 0.25rem 1rem;
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .nav-section-header:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255, 205, 0, 0.3);
            transform: translateX(2px);
        }

        .nav-section-header.active {
            background: linear-gradient(135deg, var(--gabon-green) 0%, #00b347 100%);
            border-color: var(--gabon-yellow);
            box-shadow: 0 4px 15px rgba(0, 158, 63, 0.3);
        }

        .nav-section-title-content {
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.9);
        }

        .nav-section-icon {
            width: 35px;
            height: 35px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .nav-section-header.active .nav-section-icon {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        .nav-section-title {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .nav-section-toggle {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .nav-section-header.active .nav-section-toggle {
            transform: rotate(180deg);
            color: var(--gabon-yellow);
        }

        .nav-section-badge {
            background: var(--gabon-yellow);
            color: #1e3a8a;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            margin-left: 8px;
        }

        /* ✅ SOUS-SECTIONS D'ACCORDÉON */
        .nav-subsection {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.3s ease;
            padding: 0 1rem;
            border-left: 3px solid transparent;
            margin-left: 1.5rem;
        }

        .nav-subsection.active {
            max-height: 1500px;
            padding: 0.5rem 1rem;
            border-left-color: rgba(255, 205, 0, 0.4);
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            position: relative;
            margin-bottom: 0.25rem;
        }

        /* ✅ LIENS DE NAVIGATION OPTIMISÉS */
        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 0.7rem 0.75rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            border-radius: 8px;
            border: 1px solid transparent;
        }

        .nav-link-custom:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            text-decoration: none;
            transform: translateX(3px);
            border-color: rgba(255,255,255,0.2);
        }

        .nav-link-custom.active {
            background: linear-gradient(135deg, var(--gabon-green) 0%, #00b347 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 158, 63, 0.3);
            border-color: var(--gabon-yellow);
        }

        .nav-link-custom.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: var(--gabon-yellow);
            border-radius: 0 3px 3px 0;
        }

        .nav-icon {
            width: 32px;
            height: 32px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .nav-link-custom.active .nav-icon {
            background: rgba(255,255,255,0.2);
            transform: scale(1.05);
        }

        .nav-text {
            flex: 1;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .nav-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 700;
            min-width: 20px;
            text-align: center;
        }

        .nav-badge.warning {
            background: var(--gabon-yellow);
            color: #1e3a8a;
        }

        .nav-badge.info {
            background: #3b82f6;
            color: white;
        }

        .nav-badge.success {
            background: var(--gabon-green);
            color: white;
        }

        .nav-badge.danger {
            background: var(--gabon-red);
            color: white;
        }

        /* Contenu principal */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header principal */
        .main-header {
            background: white;
            height: var(--header-height);
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Barre de recherche */
        .search-container {
            position: relative;
            width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.85rem;
            background: #f9fafb;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--gabon-green);
            box-shadow: 0 0 0 3px rgba(0, 158, 63, 0.1);
            background: white;
        }

        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Actions header */
        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .action-btn {
            position: relative;
            width: 36px;
            height: 36px;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #6b7280;
            font-size: 0.9rem;
        }

        .action-btn:hover {
            border-color: var(--gabon-green);
            color: var(--gabon-green);
            background: rgba(0, 158, 63, 0.05);
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: var(--gabon-red);
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 5px;
            border-radius: 8px;
            min-width: 16px;
            text-align: center;
        }

        /* Menu utilisateur */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
        }

        .user-menu:hover {
            border-color: var(--gabon-green);
            background: rgba(0, 158, 63, 0.05);
        }

        .user-avatar-header {
            width: 36px;
            height: 36px;
            background: var(--gabon-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
        }

        .user-info-header {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1f2937;
        }

        .user-role {
            font-size: 0.7rem;
            color: #6b7280;
        }

        /* Zone de contenu */
        .content-area {
            flex: 1;
            padding: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .search-container {
                width: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar optimisée avec accordéons -->
        <aside class="sidebar" id="sidebar">
            <!-- Logo et titre -->
            <div class="sidebar-header">
                <div class="logo-section">
                    <div class="main-logo">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="logo-text-group">
                        <h5 class="sidebar-title">SGLP</h5>
                        <div class="sidebar-subtitle">Admin</div>
                    </div>
                </div>
                <div class="settings-icon">
                    <i class="fas fa-expand-arrows-alt"></i>
                </div>
            </div>

            <!-- Profil utilisateur -->
            <div class="sidebar-profile">
                <div class="profile-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'AD', 0, 2)) }}
                </div>
                <div class="profile-info">
                    <h6>{{ auth()->user()->name ?? 'Administrateur SGLP' }}</h6>
                    <small>
                        <i class="fas fa-crown"></i> 
                        {{ auth()->user()->role ?? 'Administrateur' }}
                    </small>
                </div>
            </div>

            <!-- Navigation avec accordéons -->
            <nav class="sidebar-nav">
                <!-- ✅ TABLEAU DE BORD -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('dashboard')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-tachometer-alt"></i>
                            <span class="nav-section-title">Tableau de Bord</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-dashboard">
                        <ul class="nav-list">
                            <li class="nav-item">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link-custom {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-eye"></i>
                                    <span class="nav-text">Vue d'ensemble</span>
                                </a>
                            </li>
                            @if(Route::has('admin.analytics'))
                            <li class="nav-item">
                                <a href="{{ route('admin.analytics') }}" class="nav-link-custom {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-line"></i>
                                    <span class="nav-text">Analytiques</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ GESTION DOSSIERS -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('dossiers')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-folder-open"></i>
                            <span class="nav-section-title">Gestion Dossiers</span>
                            @php
                                $totalDossiers = class_exists('App\Models\Dossier') ? \App\Models\Dossier::count() : 0;
                            @endphp
                            @if($totalDossiers > 0)
                            <span class="nav-section-badge">{{ $totalDossiers }}</span>
                            @endif
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-dossiers">
                        <ul class="nav-list">
                            @if(Route::has('admin.dossiers.en-attente'))
                            <li class="nav-item">
                                <a href="{{ route('admin.dossiers.en-attente') }}" class="nav-link-custom {{ request()->routeIs('admin.dossiers.en-attente') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-clock"></i>
                                    <span class="nav-text">En Attente</span>
                                    @php
                                        $enAttente = class_exists('App\Models\Dossier') ? \App\Models\Dossier::where('statut', 'en_attente')->count() : 0;
                                    @endphp
                                    @if($enAttente > 0)
                                    <span class="nav-badge warning">{{ $enAttente }}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.workflow.en-cours'))
                            <li class="nav-item">
                                <a href="{{ route('admin.workflow.en-cours') }}" class="nav-link-custom {{ request()->routeIs('admin.workflow.en-cours') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <span class="nav-text">En Cours</span>
                                    @php
                                        $enCours = class_exists('App\Models\Dossier') ? \App\Models\Dossier::where('statut', 'en_cours')->count() : 0;
                                    @endphp
                                    @if($enCours > 0)
                                    <span class="nav-badge info">{{ $enCours }}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.workflow.termines'))
                            <li class="nav-item">
                                <a href="{{ route('admin.workflow.termines') }}" class="nav-link-custom {{ request()->routeIs('admin.workflow.termines') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-check-circle"></i>
                                    <span class="nav-text">Terminés</span>
                                    @php
                                        $termines = class_exists('App\Models\Dossier') ? \App\Models\Dossier::whereIn('statut', ['approuve', 'rejete'])->count() : 0;
                                    @endphp
                                    @if($termines > 0)
                                    <span class="nav-badge success">{{ $termines }}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.organisations.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.organisations.index') }}" class="nav-link-custom {{ request()->routeIs('admin.organisations*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <span class="nav-text">Toutes Organisations</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ NOUVEAU : GESTION WORKFLOW -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('workflow')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-project-diagram"></i>
                            <span class="nav-section-title">Gestion Workflow</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-workflow">
                        <ul class="nav-list">
                            @if(Route::has('admin.workflow-steps.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.workflow-steps.index') }}" class="nav-link-custom {{ request()->routeIs('admin.workflow-steps*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-list-ol"></i>
                                    <span class="nav-text">Étapes Workflow</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.validation-entities.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.validation-entities.index') }}" class="nav-link-custom {{ request()->routeIs('admin.validation-entities*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sitemap"></i>
                                    <span class="nav-text">Entités Validation</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.entity-agents.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.entity-agents.index') }}" class="nav-link-custom {{ request()->routeIs('admin.entity-agents*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users-cog"></i>
                                    <span class="nav-text">Agents Entités</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.workflow.configuration'))
                            <li class="nav-item">
                                <a href="{{ route('admin.workflow.configuration') }}" class="nav-link-custom {{ request()->routeIs('admin.workflow.configuration') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sliders-h"></i>
                                    <span class="nav-text">Configuration</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ NOUVEAU : TEMPLATES & DOCUMENTS -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('templates')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-file-invoice"></i>
                            <span class="nav-section-title">Templates & Documents</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-templates">
                        <ul class="nav-list">
                            @if(Route::has('admin.document-templates.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.document-templates.index') }}" class="nav-link-custom {{ request()->routeIs('admin.document-templates*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-code"></i>
                                    <span class="nav-text">Templates Documents</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.document-types.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.document-types.index') }}" class="nav-link-custom {{ request()->routeIs('admin.document-types*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <span class="nav-text">Types Documents</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.documents.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.documents.index') }}" class="nav-link-custom {{ request()->routeIs('admin.documents.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-folder"></i>
                                    <span class="nav-text">Tous Documents</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.documents.create'))
                            <li class="nav-item">
                                <a href="{{ route('admin.documents.create') }}" class="nav-link-custom {{ request()->routeIs('admin.documents.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-medical"></i>
                                    <span class="nav-text">Générer Document</span>
                                </a>
                            </li>
                            @endif
                            
                            <!-- Vérifications publiques (index admin) -->
                            @if(Route::has('admin.document-verifications.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.document-verifications.index') }}" class="nav-link-custom {{ request()->routeIs('admin.document-verifications*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-shield-alt"></i>
                                    <span class="nav-text">Vérifications QR</span>
                                    @php
                                        $totalVerifications = class_exists('App\Models\DocumentVerification') ? \App\Models\DocumentVerification::count() : 0;
                                    @endphp
                                    @if($totalVerifications > 0)
                                    <span class="nav-badge info">{{ $totalVerifications }}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ UTILISATEURS -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('users')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-users"></i>
                            <span class="nav-section-title">Utilisateurs</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-users">
                        <ul class="nav-list">
                            @if(Route::has('admin.users.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.users.index') }}" class="nav-link-custom {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-users"></i>
                                    <span class="nav-text">Tous Utilisateurs</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.users.operators'))
                            <li class="nav-item">
                                <a href="{{ route('admin.users.operators') }}" class="nav-link-custom {{ request()->routeIs('admin.users.operators') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-cog"></i>
                                    <span class="nav-text">Opérateurs</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.users.agents'))
                            <li class="nav-item">
                                <a href="{{ route('admin.users.agents') }}" class="nav-link-custom {{ request()->routeIs('admin.users.agents') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-tie"></i>
                                    <span class="nav-text">Agents Validation</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.users.create'))
                            <li class="nav-item">
                                <a href="{{ route('admin.users.create') }}" class="nav-link-custom {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-plus"></i>
                                    <span class="nav-text">Nouvel Utilisateur</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ RÔLES & PERMISSIONS -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('roles')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-user-shield"></i>
                            <span class="nav-section-title">Rôles & Permissions</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-roles">
                        <ul class="nav-list">
                            @if(Route::has('admin.roles.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.roles.index') }}" class="nav-link-custom {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-tag"></i>
                                    <span class="nav-text">Gestion Rôles</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.permissions.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.permissions.index') }}" class="nav-link-custom {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-key"></i>
                                    <span class="nav-text">Permissions</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.permissions.matrix'))
                            <li class="nav-item">
                                <a href="{{ route('admin.permissions.matrix') }}" class="nav-link-custom {{ request()->routeIs('admin.permissions.matrix') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-table"></i>
                                    <span class="nav-text">Matrice Permissions</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ CONFIGURATION -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('config')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-cogs"></i>
                            <span class="nav-section-title">Configuration</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-config">
                        <ul class="nav-list">
                            <!-- GÉOLOCALISATION GABON -->
                            @if(Route::has('admin.geolocalisation.provinces.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.geolocalisation.provinces.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.provinces.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-map"></i>
                                    <span class="nav-text">Provinces</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.geolocalisation.departements.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.geolocalisation.departements.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.departements.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <span class="nav-text">Départements</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.geolocalisation.communes-villes.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.geolocalisation.communes-villes.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.communes.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-city"></i>
                                    <span class="nav-text">Communes/Villes</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.geolocalisation.arrondissements.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.geolocalisation.arrondissements.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.arrondissements.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-landmark"></i>
                                    <span class="nav-text">Arrondissements</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.geolocalisation.cantons.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.geolocalisation.cantons.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.cantons.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tree"></i>
                                    <span class="nav-text">Cantons</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.geolocalisation.regroupements.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.geolocalisation.regroupements.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.regroupements.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-home"></i>
                                    <span class="nav-text">Regroupements</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.geolocalisation.localites.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.geolocalisation.localites.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.localites.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-map-pin"></i>
                                    <span class="nav-text">Localités</span>
                                </a>
                            </li>
                            @endif
                            
                            <!-- SÉPARATEUR -->
                            <li class="nav-item" style="margin: 0.5rem 0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.5rem;"></li>
                            
                            <!-- PARAMÈTRES SYSTÈME -->
                            @if(Route::has('admin.settings.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.settings.index') }}" class="nav-link-custom {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sliders-h"></i>
                                    <span class="nav-text">Paramètres Système</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ NOUVEAU : RÉFÉRENTIELS -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('referentiels')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-database"></i>
                            <span class="nav-section-title">Référentiels</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-referentiels">
                        <ul class="nav-list">
                            @if(Route::has('admin.referentiels.types-organisations'))
                            <li class="nav-item">
                                <a href="{{ route('admin.referentiels.types-organisations') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.types-organisations') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <span class="nav-text">Types Organisations</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.referentiels.operation-types.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.referentiels.operation-types.index') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.operation-types.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tasks"></i>
                                    <span class="nav-text">Types Opérations</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.referentiels.document-types.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.referentiels.document-types.index') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.document-types.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-alt"></i>
                                    <span class="nav-text">Types Documents</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.referentiels.civilites'))
                            <li class="nav-item">
                                <a href="{{ route('admin.referentiels.civilites') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.civilites') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-id-card"></i>
                                    <span class="nav-text">Civilités</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.referentiels.professions'))
                            <li class="nav-item">
                                <a href="{{ route('admin.referentiels.professions') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.professions') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-briefcase"></i>
                                    <span class="nav-text">Professions</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ NOUVEAU : ARCHIVES -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('archives')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-archive"></i>
                            <span class="nav-section-title">Archives</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-archives">
                        <ul class="nav-list">
                            @if(Route::has('admin.archives.dossiers'))
                            <li class="nav-item">
                                <a href="{{ route('admin.archives.dossiers') }}" class="nav-link-custom {{ request()->routeIs('admin.archives.dossiers') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-folder-open"></i>
                                    <span class="nav-text">Dossiers Archivés</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.archives.organisations'))
                            <li class="nav-item">
                                <a href="{{ route('admin.archives.organisations') }}" class="nav-link-custom {{ request()->routeIs('admin.archives.organisations') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-building"></i>
                                    <span class="nav-text">Organisations Archivées</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.archives.search'))
                            <li class="nav-item">
                                <a href="{{ route('admin.archives.search') }}" class="nav-link-custom {{ request()->routeIs('admin.archives.search') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-search"></i>
                                    <span class="nav-text">Rechercher Archives</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ RAPPORTS -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('reports')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-chart-bar"></i>
                            <span class="nav-section-title">Rapports</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-reports">
                        <ul class="nav-list">
                            @if(Route::has('admin.reports.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.index') }}" class="nav-link-custom {{ request()->routeIs('admin.reports.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-chart-line"></i>
                                    <span class="nav-text">Rapports Généraux</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.reports.statistiques'))
                            <li class="nav-item">
                                <a href="{{ route('admin.reports.statistiques') }}" class="nav-link-custom {{ request()->routeIs('admin.reports.statistiques') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-chart-pie"></i>
                                    <span class="nav-text">Statistiques</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.exports.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.exports.index') }}" class="nav-link-custom {{ request()->routeIs('admin.exports*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-download"></i>
                                    <span class="nav-text">Exports</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- ✅ SYSTÈME -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('system')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-server"></i>
                            <span class="nav-section-title">Système</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-system">
                        <ul class="nav-list">
                            @if(Route::has('admin.notifications.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.notifications.index') }}" class="nav-link-custom {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-bell"></i>
                                    <span class="nav-text">Notifications</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.activity-logs.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.activity-logs.index') }}" class="nav-link-custom {{ request()->routeIs('admin.activity-logs*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-history"></i>
                                    <span class="nav-text">Logs d'Activité</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.system.maintenance'))
                            <li class="nav-item">
                                <a href="{{ route('admin.system.maintenance') }}" class="nav-link-custom {{ request()->routeIs('admin.system.maintenance') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-tools"></i>
                                    <span class="nav-text">Maintenance</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.system.cache'))
                            <li class="nav-item">
                                <a href="{{ route('admin.system.cache') }}" class="nav-link-custom {{ request()->routeIs('admin.system.cache') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-sync"></i>
                                    <span class="nav-text">Gestion Cache</span>
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="main-content">
            <!-- Header principal -->
            <header class="main-header">
                <div class="header-left">
                    <h1 class="header-title">
                        <i class="fas fa-shield-alt" style="color: var(--gabon-blue);"></i>
                        @yield('title', 'Administration SGLP')
                    </h1>
                </div>

                <div class="header-right">
                    <!-- Recherche -->
                    <div class="search-container">
                        <i class="search-icon fas fa-search"></i>
                        <input type="text" class="search-input" placeholder="Rechercher dans l'administration...">
                    </div>

                    <!-- Actions -->
                    <div class="header-actions">
                        <button class="action-btn" title="Notifications">
                            <i class="fas fa-bell"></i>
                            @php
                                $notifCount = auth()->check() && method_exists(auth()->user(), 'unreadNotifications') ? auth()->user()->unreadNotifications->count() : 0;
                            @endphp
                            @if($notifCount > 0)
                            <span class="notification-badge">{{ $notifCount }}</span>
                            @endif
                        </button>

                        <button class="action-btn" title="Messages">
                            <i class="fas fa-envelope"></i>
                        </button>

                        <!-- Menu utilisateur -->
                        <div class="dropdown">
                            <div class="user-menu" data-toggle="dropdown">
                                <div class="user-avatar-header">
                                    {{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'AD' }}
                                </div>
                                <div class="user-info-header">
                                    <div class="user-name">{{ auth()->user()->name ?? 'Admin SGLP' }}</div>
                                    <div class="user-role">{{ auth()->user()->role ?? 'Administrateur' }}</div>
                                </div>
                                <i class="fas fa-chevron-down ml-2" style="color: #6b7280; font-size: 0.8rem;"></i>
                            </div>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user mr-2"></i> Mon profil
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cog mr-2"></i> Paramètres
                                </a>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Zone de contenu -->
            <div class="content-area">
                <!-- Messages d'alerte -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert">
                            <span>&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Contenu de la page -->
                @yield('content')
            </div>
        </main>
    </div>

    <!-- jQuery (requis pour Bootstrap 4) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ✅ FONCTION TOGGLE ACCORDÉON
        function toggleSection(sectionId) {
            const header = document.querySelector(`[onclick="toggleSection('${sectionId}')"]`);
            const subsection = document.getElementById(`section-${sectionId}`);
            
            if (header && subsection) {
                const isActive = header.classList.contains('active');
                
                // Toggle classes
                header.classList.toggle('active');
                subsection.classList.toggle('active');
                
                // Toggle icône
                const toggleIcon = header.querySelector('.nav-section-toggle');
                if (toggleIcon) {
                    if (isActive) {
                        toggleIcon.classList.remove('fa-chevron-up');
                        toggleIcon.classList.add('fa-chevron-down');
                    } else {
                        toggleIcon.classList.remove('fa-chevron-down');
                        toggleIcon.classList.add('fa-chevron-up');
                    }
                }
                
                // Sauvegarder préférence
                saveAccordionPreferences();
            }
        }

        // ✅ SAUVEGARDE PRÉFÉRENCES
        function saveAccordionPreferences() {
            try {
                const activeSections = [];
                document.querySelectorAll('.nav-section-header.active').forEach(header => {
                    const onclick = header.getAttribute('onclick');
                    if (onclick) {
                        const match = onclick.match(/toggleSection\('([^']+)'\)/);
                        if (match) {
                            activeSections.push(match[1]);
                        }
                    }
                });
                localStorage.setItem('sglp_active_sections', JSON.stringify(activeSections));
            } catch (e) {
                console.log('Erreur sauvegarde préférences:', e);
            }
        }

        // ✅ CHARGEMENT PRÉFÉRENCES
        function loadAccordionPreferences() {
            try {
                const savedSections = localStorage.getItem('sglp_active_sections');
                if (savedSections) {
                    const activeSections = JSON.parse(savedSections);
                    activeSections.forEach(sectionId => {
                        const header = document.querySelector(`[onclick="toggleSection('${sectionId}')"]`);
                        const subsection = document.getElementById(`section-${sectionId}`);
                        if (header && subsection) {
                            header.classList.add('active');
                            subsection.classList.add('active');
                            const toggleIcon = header.querySelector('.nav-section-toggle');
                            if (toggleIcon) {
                                toggleIcon.classList.remove('fa-chevron-down');
                                toggleIcon.classList.add('fa-chevron-up');
                            }
                        }
                    });
                }
            } catch (e) {
                console.log('Erreur chargement préférences:', e);
            }
        }

        // ✅ INITIALISATION
        document.addEventListener('DOMContentLoaded', function() {
            // Charger préférences accordéon
            loadAccordionPreferences();
            
            // Ouvrir automatiquement la section active
            const activeLink = document.querySelector('.nav-link-custom.active');
            if (activeLink) {
                const subsection = activeLink.closest('.nav-subsection');
                if (subsection) {
                    const sectionId = subsection.id.replace('section-', '');
                    const header = document.querySelector(`[onclick="toggleSection('${sectionId}')"]`);
                    if (header && !header.classList.contains('active')) {
                        toggleSection(sectionId);
                    }
                }
            }
            
            console.log('✅ Layout Admin SGLP - Toutes fonctionnalités chargées');
        });

        // ✅ RECHERCHE
        $('.search-input').on('keypress', function(e) {
            if (e.which === 13) {
                const searchTerm = $(this).val().trim();
                if (searchTerm) {
                    window.location.href = '{{ route("admin.dashboard") }}?search=' + encodeURIComponent(searchTerm);
                }
            }
        });
    </script>

    @stack('scripts')
</body>
</html>