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

        /* Navigation sections */
        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            color: var(--gabon-yellow);
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 1rem;
            margin-bottom: 0.5rem;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            position: relative;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 0.85rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            margin: 0 1rem 0.5rem 1rem;
            border-radius: 12px;
        }

        .nav-link-custom:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            text-decoration: none;
            transform: translateX(3px);
        }

        .nav-link-custom.active {
            background: linear-gradient(135deg, var(--gabon-green) 0%, #00b347 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 158, 63, 0.3);
            position: relative;
        }

        .nav-link-custom.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--gabon-yellow);
            border-radius: 0 4px 4px 0;
        }

        .nav-icon {
            width: 40px;
            height: 40px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .nav-link-custom.active .nav-icon {
            background: rgba(255,255,255,0.2);
            transform: scale(1.1);
        }

        .nav-text {
            flex: 1;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .nav-badge {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            min-width: 24px;
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

        .nav-badge.users {
            background: var(--gabon-red);
            color: white;
        }

        .nav-badge.roles {
            background: var(--gabon-blue);
            color: white;
        }

        .nav-badge.permissions {
            background: #ff6b35;
            color: white;
        }

        .nav-link-custom.disabled {
            color: rgba(255,255,255,0.4) !important;
            cursor: not-allowed;
            pointer-events: auto; /* Permet le onclick pour le message */
        }

        .nav-link-custom.disabled:hover {
            background: rgba(255,255,255,0.05) !important;
            transform: none !important;
        }

        .nav-link-custom.disabled .nav-icon {
            background: rgba(255,255,255,0.05) !important;
        }

        .nav-badge.dev {
            background: #6b7280;
            color: white;
            font-size: 0.6rem;
        }

        /* ✅ STYLES POUR GÉOLOCALISATION - MENU HIÉRARCHIQUE */
        .geo-section {
            margin-bottom: 1rem;
        }

        .geo-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.85rem 1rem;
            margin: 0 1rem 0.5rem 1rem;
            border-radius: 12px;
            background: rgba(255,255,255,0.05);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .geo-section-header:hover {
            background: rgba(255,255,255,0.1);
        }

        .geo-section-header.expanded {
            background: rgba(0, 158, 63, 0.2);
        }

        .geo-header-content {
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.9);
        }

        .geo-header-icon {
            width: 40px;
            height: 40px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            font-size: 1.1rem;
        }

        .geo-header-text {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .geo-toggle-icon {
            color: rgba(255,255,255,0.6);
            font-size: 0.8rem;
            transition: transform 0.3s ease;
        }

        .geo-section-header.expanded .geo-toggle-icon {
            transform: rotate(180deg);
        }

        .geo-subsection {
            padding-left: 1rem;
            border-left: 2px solid rgba(255, 205, 0, 0.3);
            margin-left: 2rem;
            margin-bottom: 0.5rem;
        }

        .geo-level-indicator {
            font-size: 0.65rem;
            color: var(--gabon-yellow);
            background: rgba(255, 205, 0, 0.2);
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 8px;
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
            width: 32px;
            height: 32px;
            background: var(--gabon-green);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .user-info-header {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .user-role {
            font-size: 0.75rem;
            color: #6b7280;
        }

        /* Zone de contenu */
        .content-area {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }

        /* Messages d'alerte */
        .alert {
            border: none;
            border-radius: 8px;
            border-left: 4px solid;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: rgba(0, 158, 63, 0.1);
            border-left-color: var(--gabon-green);
            color: var(--gabon-green);
        }

        .alert-danger {
            background: rgba(139, 21, 56, 0.1);
            border-left-color: var(--gabon-red);
            color: var(--gabon-red);
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
                display: none;
            }

            .header-title {
                font-size: 1.2rem;
            }
        }

        /* Scrollbar personnalisée */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar exacte selon capture -->
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

            <!-- Navigation sections -->
            <nav class="sidebar-nav">
                <!-- Tableau de Bord -->
                <div class="nav-section">
                    <div class="nav-section-title">TABLEAU DE BORD</div>
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

                <!-- Gestion Dossiers -->
                <div class="nav-section">
                    <div class="nav-section-title">GESTION DOSSIERS</div>
                    <ul class="nav-list">
                        @if(Route::has('admin.dossiers.en-attente'))
                        <li class="nav-item">
                            <a href="{{ route('admin.dossiers.en-attente') }}" class="nav-link-custom {{ request()->routeIs('admin.dossiers.en-attente') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clock"></i>
                                <span class="nav-text">En Attente</span>
                                @php
                                    $enAttenteCount = class_exists('App\Models\Dossier') ? \App\Models\Dossier::whereIn('statut', ['soumis'])->count() : 0;
                                @endphp
                                @if($enAttenteCount > 0)
                                    <span class="nav-badge warning">{{ $enAttenteCount }}</span>
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
                                    $enCoursCount = class_exists('App\Models\Dossier') ? \App\Models\Dossier::where('statut', 'en_cours')->count() : 0;
                                @endphp
                                @if($enCoursCount > 0)
                                    <span class="nav-badge info">{{ $enCoursCount }}</span>
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
                                    $terminesCount = class_exists('App\Models\Dossier') ? \App\Models\Dossier::where('statut', 'approuve')->count() : 0;
                                @endphp
                                @if($terminesCount > 0)
                                    <span class="nav-badge success">{{ $terminesCount }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.organisations.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.organisations.index') }}" class="nav-link-custom {{ request()->routeIs('admin.organisations*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-folder-open"></i>
                                <span class="nav-text">Toutes Organisations</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- Base de Données NIP -->
                <div class="nav-section">
                    <div class="nav-section-title">BASE DE DONNÉES</div>
                    <ul class="nav-list">
                        @if(Route::has('admin.nip-database.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.nip-database.index') }}" class="nav-link-custom {{ request()->routeIs('admin.nip-database.index') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-database"></i>
                                <span class="nav-text">Base NIP</span>
                                @php
                                    $nipCount = class_exists('App\Models\NipDatabase') ? \App\Models\NipDatabase::count() : 0;
                                @endphp
                                @if($nipCount > 0)
                                    <span class="nav-badge info">{{ number_format($nipCount) }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.nip-database.import'))
                        <li class="nav-item">
                            <a href="{{ route('admin.nip-database.import') }}" class="nav-link-custom {{ request()->routeIs('admin.nip-database.import') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-upload"></i>
                                <span class="nav-text">Import NIP</span>
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.nip-database.template'))
                        <li class="nav-item">
                            <a href="{{ route('admin.nip-database.template') }}" class="nav-link-custom">
                                <i class="nav-icon fas fa-download"></i>
                                <span class="nav-text">Template Excel</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- ✅ GÉOLOCALISATION GABON - SECTION COMPLÈTE MISE À JOUR -->
                <div class="nav-section">
                    <div class="nav-section-title">GÉOLOCALISATION GABON</div>
                    
                    <!-- NIVEAU 1 : PROVINCES -->
                    <div class="geo-section">
                        <div class="geo-section-header" onclick="toggleGeoSection('provinces')">
                            <div class="geo-header-content">
                                <i class="geo-header-icon fas fa-map"></i>
                                <span class="geo-header-text">Provinces</span>
                                <span class="geo-level-indicator">Niveau 1</span>
                            </div>
                            <i class="geo-toggle-icon fas fa-chevron-down"></i>
                        </div>
                        <div class="geo-subsection" id="geo-provinces" style="display: none;">
                            <ul class="nav-list">
                                @if(Route::has('admin.geolocalisation.provinces.index'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.provinces.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.provinces.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-list"></i>
                                        <span class="nav-text">Liste des Provinces</span>
                                        <span class="nav-badge success">9</span>
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.provinces.create'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.provinces.create') }}" class="nav-link-custom">
                                        <i class="nav-icon fas fa-plus"></i>
                                        <span class="nav-text">Ajouter Province</span>
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.provinces.export'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.provinces.export') }}" class="nav-link-custom">
                                        <i class="nav-icon fas fa-download"></i>
                                        <span class="nav-text">Exporter Provinces</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- NIVEAU 2 : DÉPARTEMENTS -->
                    <div class="geo-section">
                        <div class="geo-section-header" onclick="toggleGeoSection('departements')">
                            <div class="geo-header-content">
                                <i class="geo-header-icon fas fa-building"></i>
                                <span class="geo-header-text">Départements</span>
                                <span class="geo-level-indicator">Niveau 2</span>
                            </div>
                            <i class="geo-toggle-icon fas fa-chevron-down"></i>
                        </div>
                        <div class="geo-subsection" id="geo-departements" style="display: none;">
                            <ul class="nav-list">
                                @if(Route::has('admin.geolocalisation.departements.index'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.departements.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.departements.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-list"></i>
                                        <span class="nav-text">Liste des Départements</span>
                                        @php
                                            $depCount = class_exists('App\Models\Departement') ? \App\Models\Departement::count() : 0;
                                        @endphp
                                        @if($depCount > 0)
                                            <span class="nav-badge info">{{ $depCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.departements.create'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.departements.create') }}" class="nav-link-custom">
                                        <i class="nav-icon fas fa-plus"></i>
                                        <span class="nav-text">Ajouter Département</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- NIVEAU 3 URBAIN : COMMUNES/VILLES - ✅ CORRECTION PRINCIPALE -->
                    <div class="geo-section">
                        <div class="geo-section-header" onclick="toggleGeoSection('communes')">
                            <div class="geo-header-content">
                                <i class="geo-header-icon fas fa-city"></i>
                                <span class="geo-header-text">Communes/Villes</span>
                                <span class="geo-level-indicator">Urbain 3</span>
                            </div>
                            <i class="geo-toggle-icon fas fa-chevron-down"></i>
                        </div>
                        <div class="geo-subsection" id="geo-communes" style="display: none;">
                            <ul class="nav-list">
                                @if(Route::has('admin.geolocalisation.communes.index'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.communes.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.communes.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-list"></i>
                                        <span class="nav-text">Liste Communes/Villes</span>
                                        @php
                                            $communesCount = class_exists('App\Models\CommuneVille') ? \App\Models\CommuneVille::count() : 0;
                                        @endphp
                                        @if($communesCount > 0)
                                            <span class="nav-badge warning">{{ $communesCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.communes.create'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.communes.create') }}" class="nav-link-custom">
                                        <i class="nav-icon fas fa-plus"></i>
                                        <span class="nav-text">Ajouter Commune</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- NIVEAU 4 URBAIN : ARRONDISSEMENTS -->
                    <div class="geo-section">
                        <div class="geo-section-header" onclick="toggleGeoSection('arrondissements')">
                            <div class="geo-header-content">
                                <i class="geo-header-icon fas fa-landmark"></i>
                                <span class="geo-header-text">Arrondissements</span>
                                <span class="geo-level-indicator">Urbain 4</span>
                            </div>
                            <i class="geo-toggle-icon fas fa-chevron-down"></i>
                        </div>
                        <div class="geo-subsection" id="geo-arrondissements" style="display: none;">
                            <ul class="nav-list">
                                @if(Route::has('admin.geolocalisation.arrondissements.index'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.arrondissements.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.arrondissements.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-list"></i>
                                        <span class="nav-text">Liste Arrondissements</span>
                                        @php
                                            $arrCount = class_exists('App\Models\Arrondissement') ? \App\Models\Arrondissement::count() : 0;
                                        @endphp
                                        @if($arrCount > 0)
                                            <span class="nav-badge users">{{ $arrCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.arrondissements.create'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.arrondissements.create') }}" class="nav-link-custom">
                                        <i class="nav-icon fas fa-plus"></i>
                                        <span class="nav-text">Ajouter Arrondissement</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- NIVEAU 3 RURAL : CANTONS -->
                    <div class="geo-section">
                        <div class="geo-section-header" onclick="toggleGeoSection('cantons')">
                            <div class="geo-header-content">
                                <i class="geo-header-icon fas fa-tree"></i>
                                <span class="geo-header-text">Cantons</span>
                                <span class="geo-level-indicator">Rural 3</span>
                            </div>
                            <i class="geo-toggle-icon fas fa-chevron-down"></i>
                        </div>
                        <div class="geo-subsection" id="geo-cantons" style="display: none;">
                            <ul class="nav-list">
                                @if(Route::has('admin.geolocalisation.cantons.index'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.cantons.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.cantons.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-list"></i>
                                        <span class="nav-text">Liste des Cantons</span>
                                        @php
                                            $cantonsCount = class_exists('App\Models\Canton') ? \App\Models\Canton::count() : 0;
                                        @endphp
                                        @if($cantonsCount > 0)
                                            <span class="nav-badge roles">{{ $cantonsCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.cantons.create'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.cantons.create') }}" class="nav-link-custom">
                                        <i class="nav-icon fas fa-plus"></i>
                                        <span class="nav-text">Ajouter Canton</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- NIVEAU 4 RURAL : REGROUPEMENTS -->
                    <div class="geo-section">
                        <div class="geo-section-header" onclick="toggleGeoSection('regroupements')">
                            <div class="geo-header-content">
                                <i class="geo-header-icon fas fa-home"></i>
                                <span class="geo-header-text">Regroupements</span>
                                <span class="geo-level-indicator">Rural 4</span>
                            </div>
                            <i class="geo-toggle-icon fas fa-chevron-down"></i>
                        </div>
                        <div class="geo-subsection" id="geo-regroupements" style="display: none;">
                            <ul class="nav-list">
                                @if(Route::has('admin.geolocalisation.regroupements.index'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.regroupements.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.regroupements.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-list"></i>
                                        <span class="nav-text">Liste Regroupements</span>
                                        @php
                                            $regroupementsCount = class_exists('App\Models\Regroupement') ? \App\Models\Regroupement::count() : 0;
                                        @endphp
                                        @if($regroupementsCount > 0)
                                            <span class="nav-badge permissions">{{ $regroupementsCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.regroupements.create'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.regroupements.create') }}" class="nav-link-custom">
                                        <i class="nav-icon fas fa-plus"></i>
                                        <span class="nav-text">Ajouter Regroupement</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- NIVEAU 5 : LOCALITÉS (QUARTIERS/VILLAGES) -->
                    <div class="geo-section">
                        <div class="geo-section-header" onclick="toggleGeoSection('localites')">
                            <div class="geo-header-content">
                                <i class="geo-header-icon fas fa-map-pin"></i>
                                <span class="geo-header-text">Localités</span>
                                <span class="geo-level-indicator">Niveau 5</span>
                            </div>
                            <i class="geo-toggle-icon fas fa-chevron-down"></i>
                        </div>
                        <div class="geo-subsection" id="geo-localites" style="display: none;">
                            <ul class="nav-list">
                                @if(Route::has('admin.geolocalisation.localites.index'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.localites.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.localites.*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-list"></i>
                                        <span class="nav-text">Toutes Localités</span>
                                        @php
                                            $localitesCount = class_exists('App\Models\Localite') ? \App\Models\Localite::count() : 0;
                                        @endphp
                                        @if($localitesCount > 0)
                                            <span class="nav-badge info">{{ $localitesCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.localites.create'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.localites.create') }}" class="nav-link-custom">
                                        <i class="nav-icon fas fa-plus"></i>
                                        <span class="nav-text">Ajouter Localité</span>
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.localites.index'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.localites.index') }}?type=quartier" class="nav-link-custom">
                                        <i class="nav-icon fas fa-building"></i>
                                        <span class="nav-text">Quartiers (Urbain)</span>
                                        @php
                                            $quartiersCount = class_exists('App\Models\Localite') ? \App\Models\Localite::where('type', 'quartier')->count() : 0;
                                        @endphp
                                        @if($quartiersCount > 0)
                                            <span class="nav-badge warning">{{ $quartiersCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.localites.index') }}?type=village" class="nav-link-custom">
                                        <i class="nav-icon fas fa-tree"></i>
                                        <span class="nav-text">Villages (Rural)</span>
                                        @php
                                            $villagesCount = class_exists('App\Models\Localite') ? \App\Models\Localite::where('type', 'village')->count() : 0;
                                        @endphp
                                        @if($villagesCount > 0)
                                            <span class="nav-badge success">{{ $villagesCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                @endif
                                
                                @if(Route::has('admin.geolocalisation.localites.export'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.geolocalisation.localites.export') }}" class="nav-link-custom">
                                        <i class="nav-icon fas fa-download"></i>
                                        <span class="nav-text">Exporter Localités</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- ✅ OUTILS GÉOLOCALISATION -->
                    <div class="geo-section">
                        <div class="geo-section-header" onclick="toggleGeoSection('geo-tools')">
                            <div class="geo-header-content">
                                <i class="geo-header-icon fas fa-tools"></i>
                                <span class="geo-header-text">Outils Géo</span>
                                <span class="geo-level-indicator">Utils</span>
                            </div>
                            <i class="geo-toggle-icon fas fa-chevron-down"></i>
                        </div>
                        <div class="geo-subsection" id="geo-geo-tools" style="display: none;">
                            <ul class="nav-list">
                                <li class="nav-item">
                                    <a href="#" class="nav-link-custom" onclick="geoGlobalSearch()">
                                        <i class="nav-icon fas fa-search"></i>
                                        <span class="nav-text">Recherche Globale</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link-custom" onclick="geoHierarchyViewer()">
                                        <i class="nav-icon fas fa-sitemap"></i>
                                        <span class="nav-text">Vue Hiérarchique</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link-custom" onclick="geoStatistics()">
                                        <i class="nav-icon fas fa-chart-pie"></i>
                                        <span class="nav-text">Statistiques Géo</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="#" class="nav-link-custom" onclick="geoExportAll()">
                                        <i class="nav-icon fas fa-file-export"></i>
                                        <span class="nav-text">Export Complet</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Utilisateurs -->
                <div class="nav-section">
                    <div class="nav-section-title">UTILISATEURS</div>
                    <ul class="nav-list">
                        @if(Route::has('admin.users.operators'))
                        <li class="nav-item">
                            <a href="{{ route('admin.users.operators') }}" class="nav-link-custom {{ request()->routeIs('admin.users.operators') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <span class="nav-text">Opérateurs</span>
                                @php
                                    $operatorsCount = class_exists('App\Models\User') ? \App\Models\User::where('role', 'operator')->count() : 0;
                                @endphp
                                @if($operatorsCount > 0)
                                    <span class="nav-badge users">{{ $operatorsCount }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.users.agents'))
                        <li class="nav-item">
                            <a href="{{ route('admin.users.agents') }}" class="nav-link-custom {{ request()->routeIs('admin.users.agents') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-tie"></i>
                                <span class="nav-text">Agents</span>
                                @php
                                    $agentsCount = class_exists('App\Models\User') ? \App\Models\User::where('role', 'agent')->count() : 0;
                                @endphp
                                @if($agentsCount > 0)
                                    <span class="nav-badge">{{ $agentsCount }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.users.create'))
                        <li class="nav-item">
                            <a href="{{ route('admin.users.create') }}" class="nav-link-custom {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-plus"></i>
                                <span class="nav-text">Nouvel Agent</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- RÔLES & PERMISSIONS -->
                <div class="nav-section">
                    <div class="nav-section-title">RÔLES & PERMISSIONS</div>
                    <ul class="nav-list">
                        @if(Route::has('admin.roles.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}" class="nav-link-custom {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <span class="nav-text">Gestion Rôles</span>
                                @php
                                    $rolesCount = class_exists('App\Models\Role') ? \App\Models\Role::count() : 0;
                                @endphp
                                @if($rolesCount > 0)
                                    <span class="nav-badge roles">{{ $rolesCount }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.permissions.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.index') }}" class="nav-link-custom {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-key"></i>
                                <span class="nav-text">Permissions</span>
                                @php
                                    $permissionsCount = class_exists('App\Models\Permission') ? \App\Models\Permission::count() : 0;
                                @endphp
                                @if($permissionsCount > 0)
                                    <span class="nav-badge permissions">{{ $permissionsCount }}</span>
                                @endif
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.roles.create'))
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.create') }}" class="nav-link-custom {{ request()->routeIs('admin.roles.create') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-plus-circle"></i>
                                <span class="nav-text">Nouveau Rôle</span>
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.permissions.matrix'))
                        <li class="nav-item">
                            <a href="{{ route('admin.permissions.matrix') }}" class="nav-link-custom {{ request()->routeIs('admin.permissions.matrix') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-table"></i>
                                <span class="nav-text">Matrice Permission</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- Configuration -->
                <div class="nav-section">
                    <div class="nav-section-title">CONFIGURATION</div>
                    <ul class="nav-list">
                        @if(Route::has('admin.referentiels.types-organisations'))
                        <li class="nav-item">
                            <a href="{{ route('admin.referentiels.types-organisations') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.types-organisations') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-building"></i>
                                <span class="nav-text">Types Organisations</span>
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.referentiels.document-types'))
                        <li class="nav-item">
                            <a href="{{ route('admin.referentiels.document-types') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.document-types') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-file-alt"></i>
                                <span class="nav-text">Types Documents</span>
                            </a>
                        </li>
                        @endif
                        
                        @if(Route::has('admin.referentiels.zones.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.referentiels.zones.index') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.zones') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-map-marker-alt"></i>
                                <span class="nav-text">Zones Géographiques</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- Rapports -->
                <div class="nav-section">
                    <div class="nav-section-title">RAPPORTS</div>
                    <ul class="nav-list">
                        @if(Route::has('admin.reports.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.index') }}" class="nav-link-custom {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <span class="nav-text">Rapports Généraux</span>
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

                <!-- Système -->
                <div class="nav-section">
                    <div class="nav-section-title">SYSTÈME</div>
                    <ul class="nav-list">
                        @if(Route::has('admin.notifications.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.notifications.index') }}" class="nav-link-custom {{ request()->routeIs('admin.notifications*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-bell"></i>
                                <span class="nav-text">Notifications</span>
                                @if(auth()->check())
                                    @php
                                        $unreadCount = auth()->user()->unreadNotifications->count() ?? 0;
                                    @endphp
                                    @if($unreadCount > 0)
                                        <span class="nav-badge danger">{{ $unreadCount }}</span>
                                    @endif
                                @endif
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
                        
                        @if(Route::has('admin.settings.index'))
                        <li class="nav-item">
                            <a href="{{ route('admin.settings.index') }}" class="nav-link-custom {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cog"></i>
                                <span class="nav-text">Paramètres</span>
                            </a>
                        </li>
                        @endif
                    </ul>
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
                            <span class="notification-badge">3</span>
                        </button>

                        <button class="action-btn" title="Messages">
                            <i class="fas fa-envelope"></i>
                            <span class="notification-badge">2</span>
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        $(document).ready(function() {
            console.log('SGLP Admin Layout - Géolocalisation Gabon Complète Chargée');
            
            // Auto-dismiss des alertes
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);

            // Animation hover sur les liens de navigation
            $('.nav-link-custom').hover(function() {
                if (!$(this).hasClass('active')) {
                    $(this).css('transform', 'translateX(5px)');
                }
            }, function() {
                if (!$(this).hasClass('active')) {
                    $(this).css('transform', 'translateX(0)');
                }
            });

            // Gestion responsive mobile
            if (window.innerWidth <= 768) {
                $('.header-left').prepend('<button class="btn btn-link p-0 mr-3" onclick="toggleMobileSidebar()"><i class="fas fa-bars"></i></button>');
            }

            // ✅ Initialiser les sections géolocalisation selon les préférences sauvegardées
            const geoPreferences = localStorage.getItem('sglp_geo_sections');
            if (geoPreferences) {
                try {
                    const expanded = JSON.parse(geoPreferences);
                    if (Array.isArray(expanded)) {
                        expanded.forEach(section => {
                            const element = document.getElementById('geo-' + section);
                            const header = document.querySelector(`[onclick="toggleGeoSection('${section}')"]`);
                            if (element && header) {
                                element.style.display = 'block';
                                header.classList.add('expanded');
                            }
                        });
                    }
                } catch (e) {
                    console.log('Erreur lors du chargement des préférences géo:', e);
                }
            }
        });

        // ✅ FONCTIONS GÉOLOCALISATION - GESTION DES SECTIONS EXPANDABLES
        function toggleGeoSection(sectionId) {
            const element = document.getElementById('geo-' + sectionId);
            const header = document.querySelector(`[onclick="toggleGeoSection('${sectionId}')"]`);
            
            if (element && header) {
                const isVisible = element.style.display !== 'none';
                
                if (isVisible) {
                    element.style.display = 'none';
                    header.classList.remove('expanded');
                } else {
                    element.style.display = 'block';
                    header.classList.add('expanded');
                }
                
                // Sauvegarder les préférences
                saveGeoPreferences();
            }
        }

        function saveGeoPreferences() {
            try {
                const expandedSections = [];
                document.querySelectorAll('.geo-section-header.expanded').forEach(header => {
                    const onclick = header.getAttribute('onclick');
                    if (onclick) {
                        const match = onclick.match(/toggleGeoSection\('([^']+)'\)/);
                        if (match) {
                            expandedSections.push(match[1]);
                        }
                    }
                });
                localStorage.setItem('sglp_geo_sections', JSON.stringify(expandedSections));
            } catch (e) {
                console.log('Erreur lors de la sauvegarde des préférences géo:', e);
            }
        }

        // ✅ FONCTIONS UTILITAIRES GÉOLOCALISATION
        function geoGlobalSearch() {
            // Fonction pour recherche globale géographique
            const searchTerm = prompt('Rechercher dans toutes les entités géographiques:');
            if (searchTerm && searchTerm.trim()) {
                // Fallback si la route n'existe pas
                const searchUrl = '{{ route("admin.dashboard") }}' + '?geo_search=' + encodeURIComponent(searchTerm);
                window.location.href = searchUrl;
            }
        }

        function geoHierarchyViewer() {
            // Ouvrir la vue hiérarchique complète
            @if(Route::has('admin.geolocalisation.provinces.index'))
                window.open('{{ route("admin.geolocalisation.provinces.index") }}?view=hierarchy', '_blank');
            @else
                alert('Fonctionnalité en cours de développement');
            @endif
        }

        function geoStatistics() {
            // Afficher les statistiques géographiques
            @if(Route::has('admin.analytics'))
                window.open('{{ route("admin.analytics") }}?section=geolocalisation', '_blank');
            @else
                alert('Module analytics en cours de développement');
            @endif
        }

        function geoExportAll() {
            // Export complet de toutes les données géographiques
            if (confirm('Exporter toutes les données géographiques du Gabon ?')) {
                @if(Route::has('admin.exports.index'))
                    window.location.href = '{{ route("admin.exports.index") }}?type=geolocalisation&format=excel';
                @else
                    alert('Module export en cours de développement');
                @endif
            }
        }

        function toggleMobileSidebar() {
            $('#sidebar').toggleClass('active');
        }

        // Fermer le sidebar mobile en cliquant sur un lien
        $('.nav-link-custom').on('click', function() {
            if (window.innerWidth <= 768) {
                $('#sidebar').removeClass('active');
            }
        });

        // ✅ AJAX pour mise à jour des compteurs en temps réel (avec gestion d'erreur)
        function updateGeoCounts() {
            // Vérifier si une route API existe pour les stats
            @if(Route::has('admin.api.stats.realtime'))
                $.get('{{ route("admin.api.stats.realtime") }}', function(data) {
                    if (data && data.geo) {
                        Object.keys(data.geo).forEach(key => {
                            const badge = $(`.nav-text:contains("${key}")`).siblings('.nav-badge');
                            if (badge.length) {
                                badge.text(data.geo[key]);
                            }
                        });
                    }
                }).fail(function() {
                    console.log('API stats non disponible - fonctionnalité désactivée');
                });
            @endif
        }

        // Mise à jour des compteurs toutes les 5 minutes (uniquement si la route existe)
        @if(Route::has('admin.api.stats.realtime'))
            setInterval(updateGeoCounts, 300000);
        @endif

        // Gestion des erreurs globales JavaScript
        window.addEventListener('error', function(e) {
            console.log('Erreur JavaScript interceptée:', e.message);
            // Ne pas interrompre l'expérience utilisateur pour les erreurs mineures
        });

        // ✅ Fonction de recherche améliorée
        $('.search-input').on('keypress', function(e) {
            if (e.which === 13) { // Entrée
                const searchTerm = $(this).val().trim();
                if (searchTerm) {
                    // Redirection vers une page de recherche ou dashboard avec paramètre
                    window.location.href = '{{ route("admin.dashboard") }}?search=' + encodeURIComponent(searchTerm);
                }
            }
        });

        // ✅ Protection contre les erreurs de routes manquantes
        $('a[href=""]').on('click', function(e) {
            e.preventDefault();
            alert('Cette fonctionnalité est en cours de développement');
        });

        // ✅ Amélioration UX : feedback visuel pour les actions
        $('.nav-link-custom').on('click', function() {
            const href = $(this).attr('href');
            if (href && href !== '#' && href !== '') {
                $(this).addClass('loading');
            }
        });
    </script>

    @stack('scripts')
</body>
</html>