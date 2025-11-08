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

        /* ✅ SYSTÈME D'ACCORDÉON UNIFIÉ - NOUVEAU DESIGN */
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
            max-height: 1000px;
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

        /* ✅ GÉOLOCALISATION - SOUS-ACCORDÉONS SPÉCIALISÉS */
        .geo-subsection {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.3s ease;
            padding: 0;
            border-left: 2px solid transparent;
            margin-left: 1rem;
        }

        .geo-subsection.active {
            max-height: 500px;
            padding: 0.25rem 0;
            border-left-color: rgba(255, 205, 0, 0.3);
        }

        .geo-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.6rem 0.75rem;
            margin: 0.25rem 0;
            border-radius: 8px;
            background: rgba(255,255,255,0.05);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .geo-section-header:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255, 205, 0, 0.2);
        }

        .geo-section-header.expanded {
            background: rgba(0, 158, 63, 0.2);
            border-color: var(--gabon-yellow);
        }

        .geo-header-content {
            display: flex;
            align-items: center;
            color: rgba(255,255,255,0.9);
        }

        .geo-header-icon {
            width: 30px;
            height: 30px;
            margin-right: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.1);
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .geo-header-text {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .geo-level-indicator {
            font-size: 0.6rem;
            color: var(--gabon-yellow);
            background: rgba(255, 205, 0, 0.2);
            padding: 2px 6px;
            border-radius: 8px;
            margin-left: 6px;
        }

        .geo-toggle-icon {
            color: rgba(255,255,255,0.6);
            font-size: 0.7rem;
            transition: transform 0.3s ease;
        }

        .geo-section-header.expanded .geo-toggle-icon {
            transform: rotate(180deg);
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

        /* ✅ ANIMATION DE CHARGEMENT POUR UX */
        .nav-link-custom.loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .nav-link-custom.loading::after {
            content: '';
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 12px;
            height: 12px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
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
                <!-- ✅ TABLEAU DE BORD - ACCORDÉON -->
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

                <!-- ✅ GESTION DOSSIERS - ACCORDÉON -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('dossiers')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-folder-open"></i>
                            <span class="nav-section-title">Gestion Dossiers</span>
                            @php
                                $totalDossiers = class_exists('App\Models\Dossier') ? \App\Models\Dossier::count() : 42;
                            @endphp
                            <span class="nav-section-badge">{{ $totalDossiers }}</span>
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
                                    <span class="nav-badge warning">8</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.workflow.en-cours'))
                            <li class="nav-item">
                                <a href="{{ route('admin.workflow.en-cours') }}" class="nav-link-custom {{ request()->routeIs('admin.workflow.en-cours') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-cogs"></i>
                                    <span class="nav-text">En Cours</span>
                                    <span class="nav-badge info">15</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.workflow.termines'))
                            <li class="nav-item">
                                <a href="{{ route('admin.workflow.termines') }}" class="nav-link-custom {{ request()->routeIs('admin.workflow.termines') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-check-circle"></i>
                                    <span class="nav-text">Terminés</span>
                                    <span class="nav-badge success">156</span>
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

                <!-- ✅ BASE DE DONNÉES NIP - ACCORDÉON -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('database')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-database"></i>
                            <span class="nav-section-title">Base de Données</span>
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-database">
                        <ul class="nav-list">
                            @if(Route::has('admin.nip-database.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.nip-database.index') }}" class="nav-link-custom {{ request()->routeIs('admin.nip-database.index') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-list"></i>
                                    <span class="nav-text">Base NIP</span>
                                    <span class="nav-badge info">2,847</span>
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
                </div>

                

                <!-- ✅ DOCUMENTS OFFICIELS - ACCORDÉON ⭐ NOUVEAU MODULE -->
                <div class="nav-section">
                    <div class="nav-section-header" onclick="toggleSection('documents')">
                        <div class="nav-section-title-content">
                            <i class="nav-section-icon fas fa-file-alt"></i>
                            <span class="nav-section-title">Documents Officiels</span>
                            @php
                                $totalDocuments = class_exists('App\Models\DocumentGeneration') ? \App\Models\DocumentGeneration::count() : 0;
                            @endphp
                            @if($totalDocuments > 0)
                            <span class="nav-section-badge">{{ $totalDocuments }}</span>
                            @endif
                        </div>
                        <i class="nav-section-toggle fas fa-chevron-down"></i>
                    </div>
                    <div class="nav-subsection" id="section-documents">
                        <ul class="nav-list">
                            <!-- Templates de Documents -->
                            @if(Route::has('admin.document-templates.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.document-templates.index') }}" class="nav-link-custom {{ request()->routeIs('admin.document-templates*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-code"></i>
                                    <span class="nav-text">Templates Documents</span>
                                    @php
                                        $totalTemplates = class_exists('App\Models\DocumentTemplate') ? \App\Models\DocumentTemplate::count() : 0;
                                    @endphp
                                    @if($totalTemplates > 0)
                                    <span class="nav-badge info">{{ $totalTemplates }}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                            
                            <!-- Créer un nouveau template -->
                            @if(Route::has('admin.document-templates.create'))
                            <li class="nav-item">
                                <a href="{{ route('admin.document-templates.create') }}" class="nav-link-custom {{ request()->routeIs('admin.document-templates.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-plus-circle"></i>
                                    <span class="nav-text">Nouveau Template</span>
                                </a>
                            </li>
                            @endif
                            
                            <!-- Séparateur visuel -->
                            <li class="nav-item" style="margin: 0.5rem 0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.5rem;">
                            </li>
                            
                            <!-- Documents générés -->
                            @if(Route::has('admin.documents.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.documents.index') }}" class="nav-link-custom {{ request()->routeIs('admin.documents.index') || request()->routeIs('admin.documents.show') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-pdf"></i>
                                    <span class="nav-text">Documents Générés</span>
                                    @php
                                        $totalGeneres = class_exists('App\Models\DocumentGeneration') ? \App\Models\DocumentGeneration::where('is_valid', true)->count() : 0;
                                    @endphp
                                    @if($totalGeneres > 0)
                                    <span class="nav-badge success">{{ $totalGeneres }}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                            
                            <!-- Générer un document -->
                            @if(Route::has('admin.documents.create'))
                            <li class="nav-item">
                                <a href="{{ route('admin.documents.create') }}" class="nav-link-custom {{ request()->routeIs('admin.documents.create') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-medical"></i>
                                    <span class="nav-text">Générer Document</span>
                                </a>
                            </li>
                            @endif
                            
                            <!-- Vérifications publiques (historique admin) -->
                            @if(Route::has('admin.document-verifications.history'))
                            <li class="nav-item">
                                <a href="#" class="nav-link-custom" onclick="alert('Fonctionnalité disponible prochainement'); return false;">
                                    <i class="nav-icon fas fa-shield-alt"></i>
                                    <span class="nav-text">Vérifications</span>
                                    @php
                                        $totalVerifications = class_exists('App\Models\DocumentVerification') ? \App\Models\DocumentVerification::count() : 0;
                                    @endphp
                                    @if($totalVerifications > 0)
                                    <span class="nav-badge">{{ $totalVerifications }}</span>
                                    @endif
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>


                <!-- ✅ UTILISATEURS - ACCORDÉON -->
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
                            @if(Route::has('admin.users.operators'))
                            <li class="nav-item">
                                <a href="{{ route('admin.users.operators') }}" class="nav-link-custom {{ request()->routeIs('admin.users.operators') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-cog"></i>
                                    <span class="nav-text">Opérateurs</span>
                                    <span class="nav-badge users">12</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.users.agents'))
                            <li class="nav-item">
                                <a href="{{ route('admin.users.agents') }}" class="nav-link-custom {{ request()->routeIs('admin.users.agents') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-user-tie"></i>
                                    <span class="nav-text">Agents</span>
                                    <span class="nav-badge">25</span>
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
                </div>

                <!-- ✅ RÔLES & PERMISSIONS - ACCORDÉON -->
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
                                    <span class="nav-badge roles">8</span>
                                </a>
                            </li>
                            @endif
                            
                            @if(Route::has('admin.permissions.index'))
                            <li class="nav-item">
                                <a href="{{ route('admin.permissions.index') }}" class="nav-link-custom {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-key"></i>
                                    <span class="nav-text">Permissions</span>
                                    <span class="nav-badge permissions">47</span>
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
                </div>

                <!-- ✅ CONFIGURATION - ACCORDÉON CORRIGÉ -->
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
            <!-- GÉOLOCALISATION GABON - LIENS SIMPLES CORRIGÉS -->
            @if(Route::has('admin.geolocalisation.provinces.index'))
            <li class="nav-item">
                <a href="{{ route('admin.geolocalisation.provinces.index') }}" class="nav-link-custom {{ request()->routeIs('admin.geolocalisation.provinces.*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-map"></i>
                    <span class="nav-text">Provinces</span>
                </a>
            </li>
            @else
            <li class="nav-item">
                <a href="{{ url('/admin/provinces') }}" class="nav-link-custom">
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
            @else
            <li class="nav-item">
                <a href="{{ url('/admin/departements') }}" class="nav-link-custom">
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
            @else
            <li class="nav-item">
                <a href="{{ url('/admin/communes') }}" class="nav-link-custom">
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
            @else
            <li class="nav-item">
                <a href="{{ url('/admin/arrondissements') }}" class="nav-link-custom">
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
            @else
            <li class="nav-item">
                <a href="{{ url('/admin/cantons') }}" class="nav-link-custom">
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
            @else
            <li class="nav-item">
                <a href="{{ url('/admin/regroupements') }}" class="nav-link-custom">
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
            @else
            <li class="nav-item">
                <a href="{{ url('/admin/localites') }}" class="nav-link-custom">
                    <i class="nav-icon fas fa-map-pin"></i>
                    <span class="nav-text">Localités</span>
                </a>
            </li>
            @endif
            
            <!-- SÉPARATEUR VISUEL -->
            <li class="nav-item" style="margin: 0.5rem 0; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.5rem;">
            </li>
            
            <!-- CONFIGURATION TRADITIONNELLE -->
            @if(Route::has('admin.referentiels.types-organisations'))
            <li class="nav-item">
                <a href="{{ route('admin.referentiels.types-organisations') }}" class="nav-link-custom {{ request()->routeIs('admin.referentiels.types-organisations') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-building"></i>
                    <span class="nav-text">Types Organisations</span>
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
            
            @if(Route::has('admin.settings.index'))
            <li class="nav-item">
                <a href="{{ route('admin.settings.index') }}" class="nav-link-custom {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <i class="nav-icon fas fa-sliders-h"></i>
                    <span class="nav-text">Paramètres</span>
                </a>
            </li>
            @endif
        </ul>
    </div>
</div>

                <!-- ✅ RAPPORTS - ACCORDÉON -->
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
                                <a href="{{ route('admin.reports.index') }}" class="nav-link-custom {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                                    <i class="nav-icon fas fa-file-chart-line"></i>
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
                </div>

                <!-- ✅ SYSTÈME - ACCORDÉON -->
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
                                    <span class="nav-badge warning">3</span>
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
            console.log('SGLP Admin Layout Optimisé - Accordéons Activés');
            
            // Auto-dismiss des alertes
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);

            // Charger les préférences d'accordéon sauvegardées
            loadAccordionPreferences();

            // Gestion responsive mobile
            if (window.innerWidth <= 768) {
                $('.header-left').prepend('<button class="btn btn-link p-0 mr-3" onclick="toggleMobileSidebar()"><i class="fas fa-bars"></i></button>');
            }

            // Animation hover sur les liens de navigation
            $('.nav-link-custom').hover(function() {
                if (!$(this).hasClass('active')) {
                    $(this).css('transform', 'translateX(3px)');
                }
            }, function() {
                if (!$(this).hasClass('active')) {
                    $(this).css('transform', 'translateX(0)');
                }
            });
        });

        // ✅ FONCTION PRINCIPALE D'ACCORDÉON UNIFIÉ AVEC FERMETURE AUTOMATIQUE
        function toggleSection(sectionId) {
            const header = document.querySelector(`[onclick="toggleSection('${sectionId}')"]`);
            const subsection = document.getElementById(`section-${sectionId}`);
            
            if (header && subsection) {
                const isActive = header.classList.contains('active');
                
                // ✅ FERMER TOUTES LES AUTRES SECTIONS D'ABORD
                closeAllSections(sectionId);
                
                if (isActive) {
                    // Fermer la section actuelle si elle était ouverte
                    header.classList.remove('active');
                    subsection.classList.remove('active');
                    header.querySelector('.nav-section-toggle').classList.remove('fa-chevron-up');
                    header.querySelector('.nav-section-toggle').classList.add('fa-chevron-down');
                } else {
                    // Ouvrir la section
                    header.classList.add('active');
                    subsection.classList.add('active');
                    header.querySelector('.nav-section-toggle').classList.remove('fa-chevron-down');
                    header.querySelector('.nav-section-toggle').classList.add('fa-chevron-up');
                }
                
                // Sauvegarder les préférences
                saveAccordionPreferences();
                
                // Animation fluide
                if (subsection.classList.contains('active')) {
                    subsection.style.maxHeight = subsection.scrollHeight + 'px';
                } else {
                    subsection.style.maxHeight = '0px';
                }
            }
        }

        // ✅ FONCTION POUR FERMER TOUTES LES SECTIONS SAUF CELLE SPÉCIFIÉE
        function closeAllSections(exceptSectionId = null) {
            document.querySelectorAll('.nav-section-header.active').forEach(header => {
                const onclick = header.getAttribute('onclick');
                if (onclick) {
                    const match = onclick.match(/toggleSection\('([^']+)'\)/);
                    if (match && match[1] !== exceptSectionId) {
                        const sectionId = match[1];
                        const subsection = document.getElementById(`section-${sectionId}`);
                        
                        // Fermer cette section
                        header.classList.remove('active');
                        if (subsection) {
                            subsection.classList.remove('active');
                            subsection.style.maxHeight = '0px';
                        }
                        
                        // Mettre à jour l'icône
                        const toggle = header.querySelector('.nav-section-toggle');
                        if (toggle) {
                            toggle.classList.remove('fa-chevron-up');
                            toggle.classList.add('fa-chevron-down');
                        }
                    }
                }
            });
        }

        // ✅ FONCTION SPÉCIALISÉE POUR GÉOLOCALISATION
        function toggleGeoSection(sectionId) {
            const header = document.querySelector(`[onclick="toggleGeoSection('${sectionId}')"]`);
            const subsection = document.getElementById(`geo-${sectionId}`);
            
            if (header && subsection) {
                const isExpanded = header.classList.contains('expanded');
                
                if (isExpanded) {
                    // Fermer la sous-section géo
                    header.classList.remove('expanded');
                    subsection.classList.remove('active');
                    header.querySelector('.geo-toggle-icon').classList.remove('fa-chevron-up');
                    header.querySelector('.geo-toggle-icon').classList.add('fa-chevron-down');
                } else {
                    // Ouvrir la sous-section géo
                    header.classList.add('expanded');
                    subsection.classList.add('active');
                    header.querySelector('.geo-toggle-icon').classList.remove('fa-chevron-down');
                    header.querySelector('.geo-toggle-icon').classList.add('fa-chevron-up');
                }
                
                // Sauvegarder les préférences géo
                saveGeoPreferences();
                
                // Animation fluide pour les sections géo
                if (subsection.classList.contains('active')) {
                    subsection.style.maxHeight = subsection.scrollHeight + 'px';
                } else {
                    subsection.style.maxHeight = '0px';
                }
            }
        }

        // ✅ SAUVEGARDE DES PRÉFÉRENCES D'ACCORDÉON
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
                console.log('Erreur lors de la sauvegarde des préférences accordéon:', e);
            }
        }

        // ✅ SAUVEGARDE DES PRÉFÉRENCES GÉOLOCALISATION
        function saveGeoPreferences() {
            try {
                const expandedGeoSections = [];
                document.querySelectorAll('.geo-section-header.expanded').forEach(header => {
                    const onclick = header.getAttribute('onclick');
                    if (onclick) {
                        const match = onclick.match(/toggleGeoSection\('([^']+)'\)/);
                        if (match) {
                            expandedGeoSections.push(match[1]);
                        }
                    }
                });
                localStorage.setItem('sglp_geo_sections', JSON.stringify(expandedGeoSections));
            } catch (e) {
                console.log('Erreur lors de la sauvegarde des préférences géo:', e);
            }
        }

        // ✅ CHARGEMENT DES PRÉFÉRENCES SAUVEGARDÉES
        function loadAccordionPreferences() {
            try {
                // Charger les sections principales
                const savedSections = localStorage.getItem('sglp_active_sections');
                if (savedSections) {
                    const activeSections = JSON.parse(savedSections);
                    activeSections.forEach(sectionId => {
                        const header = document.querySelector(`[onclick="toggleSection('${sectionId}')"]`);
                        const subsection = document.getElementById(`section-${sectionId}`);
                        if (header && subsection) {
                            header.classList.add('active');
                            subsection.classList.add('active');
                            header.querySelector('.nav-section-toggle').classList.remove('fa-chevron-down');
                            header.querySelector('.nav-section-toggle').classList.add('fa-chevron-up');
                        }
                    });
                }

                // Charger les sections géolocalisation
                const savedGeoSections = localStorage.getItem('sglp_geo_sections');
                if (savedGeoSections) {
                    const expandedGeoSections = JSON.parse(savedGeoSections);
                    expandedGeoSections.forEach(sectionId => {
                        const header = document.querySelector(`[onclick="toggleGeoSection('${sectionId}')"]`);
                        const subsection = document.getElementById(`geo-${sectionId}`);
                        if (header && subsection) {
                            header.classList.add('expanded');
                            subsection.classList.add('active');
                            header.querySelector('.geo-toggle-icon').classList.remove('fa-chevron-down');
                            header.querySelector('.geo-toggle-icon').classList.add('fa-chevron-up');
                        }
                    });
                }
            } catch (e) {
                console.log('Erreur lors du chargement des préférences:', e);
            }
        }

        // ✅ FONCTIONS UTILITAIRES GÉOLOCALISATION
        function geoGlobalSearch() {
            const searchTerm = prompt('Rechercher dans toutes les entités géographiques:');
            if (searchTerm && searchTerm.trim()) {
                const searchUrl = '{{ route("admin.dashboard") }}' + '?geo_search=' + encodeURIComponent(searchTerm);
                window.location.href = searchUrl;
            }
        }

        function geoHierarchyViewer() {
            @if(Route::has('admin.geolocalisation.provinces.index'))
                window.open('{{ route("admin.geolocalisation.provinces.index") }}?view=hierarchy', '_blank');
            @else
                alert('Fonctionnalité en cours de développement');
            @endif
        }

        function geoStatistics() {
            @if(Route::has('admin.analytics'))
                window.open('{{ route("admin.analytics") }}?section=geolocalisation', '_blank');
            @else
                alert('Module analytics en cours de développement');
            @endif
        }

        function geoExportAll() {
            if (confirm('Exporter toutes les données géographiques du Gabon ?')) {
                @if(Route::has('admin.exports.index'))
                    window.location.href = '{{ route("admin.exports.index") }}?type=geolocalisation&format=excel';
                @else
                    alert('Module export en cours de développement');
                @endif
            }
        }

        // ✅ FONCTIONS RESPONSIVES
        function toggleMobileSidebar() {
            $('#sidebar').toggleClass('active');
        }

        // Fermer le sidebar mobile en cliquant sur un lien
        $('.nav-link-custom').on('click', function() {
            if (window.innerWidth <= 768) {
                $('#sidebar').removeClass('active');
            }
        });

        // ✅ RECHERCHE AMÉLIORÉE
        $('.search-input').on('keypress', function(e) {
            if (e.which === 13) { // Entrée
                const searchTerm = $(this).val().trim();
                if (searchTerm) {
                    window.location.href = '{{ route("admin.dashboard") }}?search=' + encodeURIComponent(searchTerm);
                }
            }
        });

        

        // ✅ GESTION DES ERREURS GRACIEUSES
        window.addEventListener('error', function(e) {
            console.log('Erreur JavaScript interceptée:', e.message);
        });

        // ✅ PROTECTION CONTRE LES ROUTES MANQUANTES
        $('a[href=""]').on('click', function(e) {
            e.preventDefault();
            alert('Cette fonctionnalité est en cours de développement');
        });

        // ✅ ACCORDÉON INTELLIGENT - FERMETURE AUTOMATIQUE DES AUTRES SECTIONS
        function toggleSectionExclusive(sectionId) {
            // Fermer toutes les autres sections
            document.querySelectorAll('.nav-section-header.active').forEach(header => {
                const onclick = header.getAttribute('onclick');
                if (onclick && !onclick.includes(sectionId)) {
                    const match = onclick.match(/toggleSection\('([^']+)'\)/);
                    if (match) {
                        toggleSection(match[1]);
                    }
                }
            });
            
            // Ouvrir/fermer la section actuelle
            toggleSection(sectionId);
        }

        // ✅ RACCOURCIS CLAVIER
        document.addEventListener('keydown', function(e) {
            // Ctrl + B pour toggle sidebar sur mobile
            if (e.ctrlKey && e.key === 'b' && window.innerWidth <= 768) {
                e.preventDefault();
                toggleMobileSidebar();
            }
            
            // Echap pour fermer tous les accordéons
            if (e.key === 'Escape') {
                document.querySelectorAll('.nav-section-header.active').forEach(header => {
                    const onclick = header.getAttribute('onclick');
                    if (onclick) {
                        const match = onclick.match(/toggleSection\('([^']+)'\)/);
                        if (match) {
                            toggleSection(match[1]);
                        }
                    }
                });
            }
        });

        // ✅ MISE À JOUR DES COMPTEURS EN TEMPS RÉEL (SI API DISPONIBLE)
        @if(Route::has('admin.api.stats.realtime'))
        function updateRealtimeStats() {
            $.get('{{ route("admin.api.stats.realtime") }}', function(data) {
                if (data) {
                    // Mettre à jour les badges de compteurs
                    Object.keys(data).forEach(key => {
                        const badge = $(`.nav-text:contains("${key}")`).siblings('.nav-badge');
                        if (badge.length && data[key]) {
                            badge.text(data[key]);
                        }
                    });
                }
            }).fail(function() {
                console.log('API stats temps réel non disponible');
            });
        }

        // Mise à jour toutes les 2 minutes
        setInterval(updateRealtimeStats, 120000);
        @endif

        console.log('✅ Layout Admin SGLP - Accordéons optimisés chargés avec succès');
    </script>

    @stack('scripts')
</body>
</html>