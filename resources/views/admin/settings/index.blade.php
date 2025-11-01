@extends('layouts.admin')

@section('title', 'Paramètres Système')

@section('breadcrumb')
<li class="breadcrumb-item active">Paramètres</li>
@endsection

@section('content')
<div class="container-fluid">
    <!-- En-tête avec actions rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-cogs text-primary"></i>
                        Paramètres Système
                    </h1>
                    <p class="text-muted mb-0">Configuration générale et préférences du système SGLP</p>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-warning" id="clearCachesBtn">
                        <i class="fas fa-broom"></i> Vider Cache
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="maintenanceModeBtn">
                        <i class="fas fa-tools"></i>
                        @if($systemInfo['maintenance_mode'])
                            Désactiver Maintenance
                        @else
                            Mode Maintenance
                        @endif
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation par onglets -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header p-0">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link active" id="system-tab" data-toggle="tab" href="#system" role="tab" aria-controls="system" aria-selected="true">
                                <i class="fas fa-server"></i> Système
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="preferences-tab" data-toggle="tab" href="#preferences" role="tab" aria-controls="preferences" aria-selected="false">
                                <i class="fas fa-user-cog"></i> Préférences
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="security-tab" data-toggle="tab" href="#security" role="tab" aria-controls="security" aria-selected="false">
                                <i class="fas fa-shield-alt"></i> Sécurité
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link" id="monitoring-tab" data-toggle="tab" href="#monitoring" role="tab" aria-controls="monitoring" aria-selected="false">
                                <i class="fas fa-chart-line"></i> Monitoring
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content" id="settingsTabContent">
                        
                        <!-- ====== ONGLET SYSTÈME ====== -->
                        <div class="tab-pane fade show active" id="system" role="tabpanel" aria-labelledby="system-tab">
                            <form id="systemSettingsForm">
                                @csrf
                                <div class="row">
                                    <!-- Configuration Application -->
                                    <div class="col-lg-6">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-cog text-primary"></i>
                                                    Configuration Application
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="app_name" class="form-label">Nom de l'application</label>
                                                    <input type="text" class="form-control" id="app_name" name="app_name" 
                                                           value="{{ $systemSettings['app_name'] ?? 'SGLP' }}">
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label for="app_timezone" class="form-label">Fuseau horaire</label>
                                                    <select class="form-select" id="app_timezone" name="app_timezone">
                                                        <option value="Africa/Libreville" {{ ($systemSettings['app_timezone'] ?? '') == 'Africa/Libreville' ? 'selected' : '' }}>
                                                            Libreville (GMT+1)
                                                        </option>
                                                        <option value="UTC" {{ ($systemSettings['app_timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>
                                                            UTC (GMT+0)
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="session_lifetime" class="form-label">Durée session (minutes)</label>
                                                    <input type="number" class="form-control" id="session_lifetime" name="session_lifetime" 
                                                           value="{{ $systemSettings['session_lifetime'] ?? 120 }}" min="30" max="1440">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Configuration SGLP -->
                                    <div class="col-lg-6">
                                        <div class="card h-100">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-building text-success"></i>
                                                    Configuration SGLP
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="delai_traitement_standard" class="form-label">Délai traitement standard (jours)</label>
                                                    <input type="number" class="form-control" id="delai_traitement_standard" 
                                                           name="delai_traitement_standard" value="{{ $systemSettings['delai_traitement_standard'] ?? 30 }}" 
                                                           min="1" max="365">
                                                </div>

                                                <div class="mb-3">
                                                    <label for="max_organisations_per_operator" class="form-label">Max organisations par opérateur</label>
                                                    <input type="number" class="form-control" id="max_organisations_per_operator" 
                                                           name="max_organisations_per_operator" value="{{ $systemSettings['max_organisations_per_operator'] ?? 5 }}" 
                                                           min="1" max="50">
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="auto_assign_dossiers" 
                                                           name="auto_assign_dossiers" value="1" 
                                                           {{ ($systemSettings['auto_assign_dossiers'] ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="auto_assign_dossiers">
                                                        Attribution automatique des dossiers
                                                    </label>
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="notification_email_enabled" 
                                                           name="notification_email_enabled" value="1"
                                                           {{ ($systemSettings['notification_email_enabled'] ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="notification_email_enabled">
                                                        Notifications email activées
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Sauvegarder les paramètres système
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- ====== ONGLET PRÉFÉRENCES ====== -->
                        <div class="tab-pane fade" id="preferences" role="tabpanel" aria-labelledby="preferences-tab">
                            <form id="userPreferencesForm">
                                @csrf
                                <div class="row">
                                    <!-- Interface -->
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-palette text-info"></i>
                                                    Interface
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-3">
                                                    <label for="theme" class="form-label">Thème</label>
                                                    <select class="form-select" id="theme" name="theme">
                                                        <option value="light" {{ ($userPreferences['theme'] ?? 'light') == 'light' ? 'selected' : '' }}>
                                                            Clair
                                                        </option>
                                                        <option value="dark" {{ ($userPreferences['theme'] ?? 'light') == 'dark' ? 'selected' : '' }}>
                                                            Sombre
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="language" class="form-label">Langue</label>
                                                    <select class="form-select" id="language" name="language">
                                                        <option value="fr" {{ ($userPreferences['language'] ?? 'fr') == 'fr' ? 'selected' : '' }}>
                                                            Français
                                                        </option>
                                                        <option value="en" {{ ($userPreferences['language'] ?? 'fr') == 'en' ? 'selected' : '' }}>
                                                            English
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="items_per_page" class="form-label">Éléments par page</label>
                                                    <select class="form-select" id="items_per_page" name="items_per_page">
                                                        <option value="10" {{ ($userPreferences['items_per_page'] ?? 25) == 10 ? 'selected' : '' }}>10</option>
                                                        <option value="25" {{ ($userPreferences['items_per_page'] ?? 25) == 25 ? 'selected' : '' }}>25</option>
                                                        <option value="50" {{ ($userPreferences['items_per_page'] ?? 25) == 50 ? 'selected' : '' }}>50</option>
                                                        <option value="100" {{ ($userPreferences['items_per_page'] ?? 25) == 100 ? 'selected' : '' }}>100</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Notifications -->
                                    <div class="col-lg-6">
                                        <div class="card">
                                            <div class="card-header">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-bell text-warning"></i>
                                                    Notifications
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="notifications_email" 
                                                           name="notifications_email" value="1"
                                                           {{ ($userPreferences['notifications_email'] ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="notifications_email">
                                                        Notifications par email
                                                    </label>
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="notifications_browser" 
                                                           name="notifications_browser" value="1"
                                                           {{ ($userPreferences['notifications_browser'] ?? true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="notifications_browser">
                                                        Notifications navigateur
                                                    </label>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="auto_logout" class="form-label">Déconnexion auto (minutes)</label>
                                                    <input type="number" class="form-control" id="auto_logout" name="auto_logout" 
                                                           value="{{ $userPreferences['auto_logout'] ?? 120 }}" min="30" max="480">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-user-check"></i> Sauvegarder mes préférences
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- ====== ONGLET SÉCURITÉ ====== -->
                        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                            <div class="row">
                                <!-- Actions sécurité -->
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-shield-alt text-danger"></i>
                                                Actions de Sécurité
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-outline-warning" id="clearCachesAdvanced">
                                                    <i class="fas fa-broom"></i>
                                                    Vider tous les caches
                                                </button>

                                                <button type="button" class="btn btn-outline-info" id="clearLogsBtn">
                                                    <i class="fas fa-file-alt"></i>
                                                    Nettoyer les logs anciens
                                                </button>

                                                <button type="button" class="btn btn-outline-danger" id="force2FABtn">
                                                    <i class="fas fa-mobile-alt"></i>
                                                    Forcer 2FA pour tous les admins
                                                </button>
                                                
                                                <button type="button" class="btn btn-outline-dark" id="resetSessionsBtn">
                                                    <i class="fas fa-users-slash"></i>
                                                    Réinitialiser toutes les sessions
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paramètres sécurité -->
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-lock text-success"></i>
                                                Paramètres de Sécurité
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <form id="securitySettingsForm">
                                                @csrf
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="require_2fa_admin" 
                                                           name="require_2fa_admin" value="1"
                                                           {{ ($systemSettings['require_2fa_admin'] ?? false) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="require_2fa_admin">
                                                        2FA obligatoire pour les admins
                                                    </label>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="max_login_attempts" class="form-label">Tentatives connexion max</label>
                                                    <input type="number" class="form-control" id="max_login_attempts" 
                                                           name="max_login_attempts" value="5" min="3" max="10">
                                                </div>

                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-shield-alt"></i> Mettre à jour la sécurité
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ====== ONGLET MONITORING ====== -->
                        <div class="tab-pane fade" id="monitoring" role="tabpanel" aria-labelledby="monitoring-tab">
                            <div class="row">
                                <!-- Informations système -->
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-server text-primary"></i>
                                                Informations Système
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td><strong>PHP :</strong></td>
                                                    <td>{{ $systemInfo['php_version'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Laravel :</strong></td>
                                                    <td>{{ $systemInfo['laravel_version'] }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>MySQL :</strong></td>
                                                    <td>{{ $systemInfo['mysql_version'] ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Environnement :</strong></td>
                                                    <td>
                                                        <span class="badge bg-{{ $systemInfo['environment'] === 'production' ? 'success' : 'warning' }}">
                                                            {{ ucfirst($systemInfo['environment']) }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Debug :</strong></td>
                                                    <td>
                                                        <span class="badge bg-{{ $systemInfo['debug_mode'] ? 'danger' : 'success' }}">
                                                            {{ $systemInfo['debug_mode'] ? 'Activé' : 'Désactivé' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Cache :</strong></td>
                                                    <td>{{ $systemInfo['cache_driver'] }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Statistiques et usage -->
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-chart-pie text-info"></i>
                                                Usage Système
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            @if(isset($systemInfo['disk_usage']))
                                            <div class="mb-3">
                                                <label class="form-label">Espace disque</label>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $systemInfo['disk_usage']['percent'] }}%">
                                                        {{ $systemInfo['disk_usage']['percent'] }}%
                                                    </div>
                                                </div>
                                                <small class="text-muted">
                                                    {{ $systemInfo['disk_usage']['used'] }} / {{ $systemInfo['disk_usage']['total'] }}
                                                </small>
                                            </div>
                                            @endif

                                            @if(isset($systemInfo['memory_usage']))
                                            <div class="mb-3">
                                                <strong>Mémoire PHP :</strong><br>
                                                <small>Actuelle: {{ $systemInfo['memory_usage']['current'] }}</small><br>
                                                <small>Pic: {{ $systemInfo['memory_usage']['peak'] }}</small><br>
                                                <small>Limite: {{ $systemInfo['memory_usage']['limit'] }}</small>
                                            </div>
                                            @endif

                                            @if(isset($generalStats))
                                            <div class="mb-3">
                                                <strong>Utilisateurs :</strong><br>
                                                <small>Total: {{ $generalStats['users']['total'] ?? 0 }}</small><br>
                                                <small>Actifs: {{ $generalStats['users']['active'] ?? 0 }}</small><br>
                                                <small>Admins: {{ $generalStats['users']['admins'] ?? 0 }}</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Maintenance et état -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">
                                                <i class="fas fa-tools text-warning"></i>
                                                Maintenance Système
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-{{ $systemInfo['maintenance_mode'] ? 'warning' : 'success' }}">
                                                <i class="fas fa-{{ $systemInfo['maintenance_mode'] ? 'exclamation-triangle' : 'check-circle' }}"></i>
                                                Le système est actuellement 
                                                <strong>{{ $systemInfo['maintenance_mode'] ? 'en maintenance' : 'opérationnel' }}</strong>
                                            </div>

                                            @if($systemInfo['maintenance_mode'])
                                            <p class="text-warning">
                                                <i class="fas fa-info-circle"></i>
                                                Le mode maintenance est activé. Seuls les administrateurs peuvent accéder au système.
                                            </p>
                                            @endif

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-outline-primary w-100" id="systemStatusBtn">
                                                        <i class="fas fa-sync-alt"></i>
                                                        Actualiser le statut
                                                    </button>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-outline-warning w-100" id="systemHealthBtn">
                                                        <i class="fas fa-heartbeat"></i>
                                                        Vérifier la santé du système
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Modal Maintenance Mode -->
<div class="modal fade" id="maintenanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-tools"></i>
                    {{ $systemInfo['maintenance_mode'] ? 'Désactiver' : 'Activer' }} le mode maintenance
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(!$systemInfo['maintenance_mode'])
                <div class="mb-3">
                    <label for="maintenance_message" class="form-label">Message de maintenance</label>
                    <textarea class="form-control" id="maintenance_message" rows="3" 
                              placeholder="Le système est temporairement en maintenance..."></textarea>
                </div>
                @endif
                <p class="text-muted">
                    {{ $systemInfo['maintenance_mode'] ? 'Le système redeviendra accessible à tous les utilisateurs.' : 'Seuls les administrateurs pourront accéder au système.' }}
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-{{ $systemInfo['maintenance_mode'] ? 'success' : 'warning' }}" id="confirmMaintenanceBtn">
                    {{ $systemInfo['maintenance_mode'] ? 'Désactiver' : 'Activer' }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Clear Caches -->
<div class="modal fade" id="clearCacheModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-broom"></i>
                    Vider les caches
                </h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Sélectionnez les types de cache à vider :</p>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cache_application" value="application" checked>
                    <label class="form-check-label" for="cache_application">Cache application</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cache_config" value="config" checked>
                    <label class="form-check-label" for="cache_config">Cache configuration</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cache_route" value="route" checked>
                    <label class="form-check-label" for="cache_route">Cache routes</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="cache_view" value="view" checked>
                    <label class="form-check-label" for="cache_view">Cache vues</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-warning" id="confirmClearCacheBtn">
                    <i class="fas fa-broom"></i> Vider les caches
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    
    // =====================================================================
    // ACTIVATION DES ONGLETS - COMPATIBLE BOOTSTRAP 4/5
    // =====================================================================
    
    // Initialisation des onglets avec gestion d'erreurs
    function initializeTabs() {
        try {
            // Support Bootstrap 4 et 5
            if (typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                // Bootstrap 5
                console.log('🎛️ Initialisation Bootstrap 5 tabs');
                const tabElements = document.querySelectorAll('#settingsTabs a[data-toggle="tab"]');
                tabElements.forEach(function(tabElement) {
                    tabElement.addEventListener('click', function(e) {
                        e.preventDefault();
                        const tab = new bootstrap.Tab(this);
                        tab.show();
                    });
                });
            } else if (typeof $.fn.tab !== 'undefined') {
                // Bootstrap 4 avec jQuery
                console.log('🎛️ Initialisation Bootstrap 4 tabs');
                $('#settingsTabs a[data-toggle="tab"]').on('click', function(e) {
                    e.preventDefault();
                    $(this).tab('show');
                });
            } else {
                // Fallback manuel
                console.log('🎛️ Fallback manuel pour les tabs');
                $('#settingsTabs a[data-toggle="tab"]').on('click', function(e) {
                    e.preventDefault();
                    
                    // Désactiver tous les onglets
                    $('#settingsTabs .nav-link').removeClass('active');
                    $('.tab-content .tab-pane').removeClass('show active');
                    
                    // Activer l'onglet cliqué
                    $(this).addClass('active');
                    const target = $(this).attr('href');
                    $(target).addClass('show active');
                    
                    // Sauvegarder l'onglet actif
                    localStorage.setItem('settingsActiveTab', this.id);
                });
            }
            
            // Restaurer l'onglet actif depuis localStorage
            const activeTab = localStorage.getItem('settingsActiveTab') || 'system-tab';
            const activeTabElement = document.getElementById(activeTab);
            if (activeTabElement) {
                // Désactiver tous les onglets
                document.querySelectorAll('#settingsTabs .nav-link').forEach(tab => tab.classList.remove('active'));
                document.querySelectorAll('.tab-content .tab-pane').forEach(pane => {
                    pane.classList.remove('show', 'active');
                });
                
                // Activer l'onglet sélectionné
                activeTabElement.classList.add('active');
                const targetPane = document.querySelector(activeTabElement.getAttribute('href'));
                if (targetPane) {
                    targetPane.classList.add('show', 'active');
                }
            }
            
            console.log('✅ Onglets initialisés avec succès');
            
        } catch (error) {
            console.error('❌ Erreur initialisation tabs:', error);
            
            // Fallback d'urgence - onglets cliquables manuellement
            $('#settingsTabs a').on('click', function(e) {
                e.preventDefault();
                
                $('#settingsTabs .nav-link').removeClass('active');
                $('.tab-content .tab-pane').removeClass('show active');
                
                $(this).addClass('active');
                const target = $(this).attr('href');
                $(target).addClass('show active');
            });
        }
    }
    
    // Initialiser après chargement complet
    setTimeout(initializeTabs, 100);
    
    // =====================================================================
    // GESTION DES FORMULAIRES - SECTION COMPLÈTE
    // =====================================================================
    
    // Formulaire paramètres système
    $('#systemSettingsForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sauvegarde...');
        
        $.ajax({
            url: '{{ route("admin.settings.update-system") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function(xhr) {
                console.error('Erreur AJAX système:', xhr);
                let message = 'Erreur lors de la sauvegarde des paramètres système';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                if (xhr.status === 404) {
                    message = 'Route non trouvée - Vérifiez que la route admin.settings.update-system existe';
                }
                showNotification('error', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Sauvegarder les paramètres système');
            }
        });
    });
    
    // Formulaire préférences utilisateur (MANQUANT - À AJOUTER)
    $('#userPreferencesForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sauvegarde...');
        
        $.ajax({
            url: '{{ route("admin.settings.update-preferences") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function(xhr) {
                console.error('Erreur AJAX préférences:', xhr);
                let message = 'Erreur lors de la sauvegarde des préférences';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                if (xhr.status === 404) {
                    message = 'Route non trouvée - Vérifiez que la route admin.settings.update-preferences existe';
                }
                showNotification('error', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-user-check"></i> Sauvegarder mes préférences');
            }
        });
    });
    
    // Formulaire sécurité
    $('#securitySettingsForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mise à jour...');
        
        $.ajax({
            url: '{{ route("admin.settings.update-security") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function(xhr) {
                console.error('Erreur AJAX sécurité:', xhr);
                let message = 'Erreur lors de la mise à jour de la sécurité';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                if (xhr.status === 404) {
                    message = 'Route non trouvée - Vérifiez que la route admin.settings.update-security existe';
                }
                showNotification('error', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-shield-alt"></i> Mettre à jour la sécurité');
            }
        });
    });
    
    // =====================================================================
    // ACTIONS SÉCURITÉ SPÉCIFIQUES
    // =====================================================================
    
    // Vider caches avancé
    $('#clearCachesAdvanced').on('click', function() {
        if (!confirm('⚠️ Êtes-vous sûr de vouloir vider tous les caches ? Cela peut ralentir temporairement le système.')) {
            return;
        }
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Nettoyage...');
        
        $.ajax({
            url: '{{ route("admin.settings.clear-caches") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                types: ['application', 'config', 'route', 'view']
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'Erreur lors du vidage des caches');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-broom"></i> Vider tous les caches');
            }
        });
    });
    
    // Nettoyer les logs
    $('#clearLogsBtn').on('click', function() {
        const days = prompt('Conserver les logs des X derniers jours :', '30');
        if (!days || isNaN(days) || days < 1) {
            showNotification('warning', 'Nombre de jours invalide');
            return;
        }
        
        if (!confirm(`⚠️ Supprimer tous les logs de plus de ${days} jours ?`)) {
            return;
        }
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Nettoyage...');
        
        $.ajax({
            url: '{{ route("admin.settings.clear-logs") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                days: parseInt(days)
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'Erreur lors du nettoyage des logs');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-file-alt"></i> Nettoyer les logs anciens');
            }
        });
    });
    
    // Forcer 2FA pour tous les admins
    $('#force2FABtn').on('click', function() {
        if (!confirm('⚠️ ATTENTION: Forcer la 2FA pour TOUS les administrateurs ?\n\nCette action ne peut pas être annulée et tous les admins devront configurer leur 2FA.')) {
            return;
        }
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Activation...');
        
        $.ajax({
            url: '{{ route("admin.settings.force-2fa") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'Erreur lors de l\'activation forcée de la 2FA');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-mobile-alt"></i> Forcer 2FA pour tous les admins');
            }
        });
    });
    
    // Réinitialiser toutes les sessions
    $('#resetSessionsBtn').on('click', function() {
        if (!confirm('🚨 ATTENTION CRITIQUE 🚨\n\nCette action va déconnecter TOUS les utilisateurs du système (sauf vous).\n\nContinuer ?')) {
            return;
        }
        
        const reason = prompt('Raison de la réinitialisation (optionnel) :', 'Maintenance sécurité');
        
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Réinitialisation...');
        
        $.ajax({
            url: '{{ route("admin.settings.reset-sessions") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'Erreur lors de la réinitialisation des sessions');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-users-slash"></i> Réinitialiser toutes les sessions');
            }
        });
    });
    
    // =====================================================================
    // ACTIONS RAPIDES
    // =====================================================================
    
    // Mode maintenance
    $('#maintenanceModeBtn').on('click', function() {
        $('#maintenanceModal').modal('show');
    });
    
    $('#confirmMaintenanceBtn').on('click', function() {
        const message = $('#maintenance_message').val() || 'Système en maintenance';
        
        $.ajax({
            url: '{{ route("admin.settings.toggle-maintenance") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                message: message
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'Erreur lors du changement de mode maintenance');
            }
        });
        
        $('#maintenanceModal').modal('hide');
    });
    
    // Vider caches (bouton rapide)
    $('#clearCachesBtn').on('click', function() {
        $('#clearCacheModal').modal('show');
    });
    
    $('#confirmClearCacheBtn').on('click', function() {
        const selectedTypes = [];
        $('#clearCacheModal input[type="checkbox"]:checked').each(function() {
            selectedTypes.push($(this).val());
        });
        
        if (selectedTypes.length === 0) {
            showNotification('warning', 'Veuillez sélectionner au moins un type de cache');
            return;
        }
        
        $.ajax({
            url: '{{ route("admin.settings.clear-caches") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                types: selectedTypes
            },
            success: function(response) {
                if (response.success) {
                    showNotification('success', response.message);
                } else {
                    showNotification('error', response.message);
                }
            },
            error: function() {
                showNotification('error', 'Erreur lors du vidage des caches');
            }
        });
        
        $('#clearCacheModal').modal('hide');
    });
    
    // =====================================================================
    // FONCTIONS UTILITAIRES
    // =====================================================================
    
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 'alert-danger';
        
        const iconClass = type === 'success' ? 'check-circle' : 
                         type === 'warning' ? 'exclamation-triangle' : 'times-circle';
        
        const notification = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-${iconClass}"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        $('body').append(notification);
        
        // Auto-remove après 5 secondes
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
    
    // Debug pour vérifier la version Bootstrap
    console.log('🔍 Version Bootstrap détectée:', {
        'Bootstrap global': typeof bootstrap !== 'undefined' ? 'Bootstrap 5' : 'Non détecté',
        'jQuery tab': typeof $.fn.tab !== 'undefined' ? 'Bootstrap 4 avec jQuery' : 'Non détecté',
        'jQuery version': typeof $ !== 'undefined' ? $.fn.jquery : 'Non détecté'
    });
    
    // Tests des onglets au clic direct
    $('#settingsTabs a').each(function(index) {
        console.log(`📝 Onglet ${index + 1}:`, {
            id: this.id,
            href: this.getAttribute('href'),
            hasTarget: !!document.querySelector(this.getAttribute('href'))
        });
    });
});
</script>
@endpush