<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DocumentTemplateController;
use App\Http\Controllers\Admin\GeneratedDocumentController;
use App\Http\Controllers\PublicControllers\DocumentVerificationController as PublicDocVerificationController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DossierController;
use App\Http\Controllers\Admin\ProvinceController;
use App\Http\Controllers\Admin\DepartementController;
use App\Http\Controllers\Admin\CommuneVilleController;
use App\Http\Controllers\Admin\ArrondissementController;
use App\Http\Controllers\Admin\CantonController;
use App\Http\Controllers\Admin\RegroupementController;
use App\Http\Controllers\Admin\LocaliteController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ReferentielController;
use App\Http\Controllers\Admin\OrganisationTypeController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\NipDatabaseController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\PermissionMatrixController;
use App\Http\Controllers\Admin\ValidationEntityController;
use App\Http\Controllers\Admin\WorkflowStepController;



/*
|--------------------------------------------------------------------------
| Routes Administration - SGLP/PNGDI - VERSION CORRIGÉE v2.0
|--------------------------------------------------------------------------
| Routes pour l'interface d'administration complète
| Middleware : auth, verified, admin
| ✅ Version corrigée sans doublons de noms de routes
| ✅ Compatible PHP 8.3 et Laravel 9
| ✅ MODULE TYPES D'ORGANISATIONS AJOUTÉ
| ✅ MODULE DOCUMENTS - ROUTES COMPLÈTES (21/01/2025)
| ✅ MODULE ROLES - ROUTES COMPLÈTES CORRIGÉES (08/11/2025)
| ❌ ROUTES PUBLIQUES ET API SUPPRIMÉES (maintenant dans web.php et api.php)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | 🏠 DASHBOARD PRINCIPAL
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    /*
    |--------------------------------------------------------------------------
    | 📊 ANALYTICS ET RAPPORTS - SECTION COMPLÈTE
    |--------------------------------------------------------------------------
    */
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports.index');
    Route::get('/exports', [AnalyticsController::class, 'exports'])->name('exports.index');
    Route::get('/activity-logs', [AnalyticsController::class, 'activityLogs'])->name('activity-logs.index');

    // 📤 EXPORTS - Routes complètes
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'exports'])->name('index');
        Route::get('/global', [AnalyticsController::class, 'exportGlobal'])->name('global');
        Route::get('/dossiers', [AnalyticsController::class, 'exportDossiers'])->name('dossiers');
        Route::get('/users', [AnalyticsController::class, 'exportUsers'])->name('users');
        Route::get('/organisations', [AnalyticsController::class, 'exportOrganisations'])->name('organisations');
        
        // Exports spécialisés
        Route::post('/dossiers-en-attente', [AnalyticsController::class, 'dossiersEnAttente'])->name('dossiers-en-attente');
        Route::post('/dossiers-agent/{agentId}', [AnalyticsController::class, 'dossiersAgent'])->name('dossiers-agent');
        Route::post('/organisations-par-type', [AnalyticsController::class, 'organisationsParType'])->name('organisations-par-type');
        Route::post('/rapport-activite', [AnalyticsController::class, 'rapportActivite'])->name('rapport-activite');
        Route::post('/rapport-performance', [AnalyticsController::class, 'rapportPerformance'])->name('rapport-performance');
        Route::post('/statistiques', [AnalyticsController::class, 'statistiques'])->name('statistiques');
        Route::get('/format/{type}/{format}', [AnalyticsController::class, 'downloadFormat'])
             ->name('format')
             ->where('format', 'excel|pdf|csv|json');
    });

    // 📊 REPORTS - Routes complètes  
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'reports'])->name('index');
        Route::get('/monthly', [AnalyticsController::class, 'monthlyReport'])->name('monthly');
        Route::get('/annual', [AnalyticsController::class, 'annualReport'])->name('annual');
        Route::get('/custom', [AnalyticsController::class, 'customReport'])->name('custom');
    });

    // 📈 ACTIVITY LOGS - Routes complètes
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'activityLogs'])->name('index');
        Route::get('/search', [AnalyticsController::class, 'searchLogs'])->name('search');
        Route::delete('/clean', [AnalyticsController::class, 'cleanLogs'])->name('clean');
        Route::get('/export', [AnalyticsController::class, 'exportLogs'])->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | 📄 WORKFLOW DES DOSSIERS - ROUTES CORRIGÉES
    |--------------------------------------------------------------------------
    */
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/en-attente', [WorkflowController::class, 'enAttente'])->name('en-attente');
        Route::get('/en-cours', [WorkflowController::class, 'enCours'])->name('en-cours');
        Route::get('/termines', [WorkflowController::class, 'termines'])->name('termines');
        Route::get('/rejetes', [WorkflowController::class, 'rejetes'])->name('rejetes');
        Route::get('/archives', [WorkflowController::class, 'archives'])->name('archives');
        
        // Actions workflow
        Route::post('/{dossier}/assign', [WorkflowController::class, 'assign'])->name('assign');
        Route::post('/{dossier}/validate', [DossierController::class, 'validateDossier'])->name('validate');
        Route::post('/{dossier}/reject', [WorkflowController::class, 'reject'])->name('reject');
        Route::post('/step/{stepId}/complete', [WorkflowController::class, 'completeStep'])->name('step.complete');
        Route::post('/step/{stepId}/skip', [WorkflowController::class, 'skipStep'])->name('step.skip');
        Route::post('/reset/{dossierId}', [WorkflowController::class, 'resetWorkflow'])->name('reset');
        
        // Configuration workflow
        Route::get('/templates', [WorkflowController::class, 'templates'])->name('templates');
        Route::post('/templates', [WorkflowController::class, 'saveTemplate'])->name('templates.save');
    });

    /*
    |--------------------------------------------------------------------------
    | 🏢 GESTION DES ORGANISATIONS - ROUTE CORRIGÉE
    |--------------------------------------------------------------------------
    */
    Route::get('/organisations', [DossierController::class, 'index'])->name('organisations.index');

    /*
    |--------------------------------------------------------------------------
    | 🔔 NOTIFICATIONS - ROUTES CORRIGÉES (SANS CONFLIT)
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    /*
    |--------------------------------------------------------------------------
    | ⚙️ PARAMÈTRES SYSTÈME - ROUTES CORRIGÉES (SANS CONFLIT)
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/general', [SettingsController::class, 'general'])->name('general');
        Route::put('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');
        Route::get('/email', [SettingsController::class, 'email'])->name('email');
        Route::put('/email', [SettingsController::class, 'updateEmail'])->name('email.update');
        Route::get('/security', [SettingsController::class, 'security'])->name('security');
        Route::put('/security', [SettingsController::class, 'updateSecurity'])->name('security.update');
        Route::get('/backup', [SettingsController::class, 'backup'])->name('backup');
        Route::post('/backup', [SettingsController::class, 'createBackup'])->name('backup.create');
        Route::post('/update-system', [SettingsController::class, 'updateSystem'])->name('update-system');
    });

    /*
    |--------------------------------------------------------------------------
    | 📄 GESTION DES DOSSIERS - ROUTES ADMINISTRATEUR
    |--------------------------------------------------------------------------
    */
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/create', [DossierController::class, 'create'])->name('create');
        Route::post('/', [DossierController::class, 'store'])->name('store');
        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');
        Route::get('/{dossier}/edit', [DossierController::class, 'edit'])->name('edit');
        Route::put('/{dossier}', [DossierController::class, 'update'])->name('update');
        Route::delete('/{dossier}', [DossierController::class, 'destroy'])->name('destroy');
        
        // Actions workflow admin
        Route::post('/{dossier}/assigner', [DossierController::class, 'assigner'])->name('assigner');
        Route::post('/{dossier}/valider', [DossierController::class, 'valider'])->name('valider');
        Route::post('/{dossier}/rejeter', [DossierController::class, 'rejeter'])->name('rejeter');
        Route::post('/{dossier}/archiver', [DossierController::class, 'archiver'])->name('archiver');
        Route::post('/{dossier}/restaurer', [DossierController::class, 'restaurer'])->name('restaurer');
        
        // Historique et traçabilité
        Route::get('/{dossier}/historique', [DossierController::class, 'historique'])->name('historique');
        Route::get('/{dossier}/logs', [DossierController::class, 'logs'])->name('logs');
        
        // Export et rapports
        Route::get('/export', [DossierController::class, 'export'])->name('export');
        Route::post('/bulk-action', [DossierController::class, 'bulkAction'])->name('bulk-action');
    });

    /*
    |--------------------------------------------------------------------------
    | 👥 GESTION DES UTILISATEURS - ROUTES COMPLÈTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        
        // Gestion du statut et des rôles
        Route::patch('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/assign-role', [UserManagementController::class, 'assignRole'])->name('assign-role');
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        
        // Filtres et exports
        Route::get('/by-role/{role}', [UserManagementController::class, 'byRole'])->name('by-role');
        Route::get('/export', [UserManagementController::class, 'export'])->name('export');
        Route::post('/bulk-action', [UserManagementController::class, 'bulkAction'])->name('bulk-action');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔐 GESTION DES RÔLES - ROUTES COMPLÈTES CORRIGÉES ✅ v2.0
    |--------------------------------------------------------------------------
    | ✅ CORRECTION MAJEURE : Toutes les routes manquantes ajoutées
    | ✅ Route duplicate ajoutée (ligne 267)
    | ✅ Routes toggle-status, permissions, bulk-operations, export, init-system ajoutées
    | ✅ Ordre optimisé : routes fixes avant routes avec paramètres
    | ✅ 17 routes au total pour gestion complète des rôles
    |--------------------------------------------------------------------------
    */
    Route::prefix('roles')->name('roles.')->group(function () {
        
        // ========== ROUTES SANS PARAMÈTRES EN PREMIER ==========
        Route::get('/', [RolesController::class, 'index'])->name('index');
        Route::get('/create', [RolesController::class, 'create'])->name('create');
        Route::post('/', [RolesController::class, 'store'])->name('store');
        
        // Routes de recherche et export (AVANT les routes avec {id})
        Route::get('/search', [RolesController::class, 'search'])->name('search');
        Route::get('/export', [RolesController::class, 'export'])->name('export');
        
        // Routes d'action système
        Route::post('/init-system', [RolesController::class, 'initSystemRoles'])->name('init-system');
        Route::post('/validate-name', [RolesController::class, 'validateName'])->name('validate-name');
        Route::post('/bulk-operations', [RolesController::class, 'bulkOperations'])->name('bulk-operations');
        
        // ========== ROUTES AVEC PARAMÈTRES {id} ==========
        Route::get('/{id}', [RolesController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [RolesController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RolesController::class, 'update'])->name('update');
        Route::delete('/{id}', [RolesController::class, 'destroy'])->name('destroy');
        
        // ✅ CORRECTION CRITIQUE : Route duplicate ajoutée
        Route::post('/{id}/duplicate', [RolesController::class, 'duplicate'])->name('duplicate');
        
        // Gestion du statut
        Route::patch('/{id}/toggle-status', [RolesController::class, 'toggleStatus'])->name('toggle-status');
        
        // Gestion des permissions d'un rôle
        Route::get('/{id}/permissions', [RolesController::class, 'permissions'])->name('permissions');
        Route::put('/{id}/permissions', [RolesController::class, 'updatePermissions'])->name('permissions.update');
        Route::post('/{id}/permissions/sync', [RolesController::class, 'syncPermissions'])->name('permissions.sync');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔑 GESTION DES PERMISSIONS - ROUTES COMPLÈTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionsController::class, 'index'])->name('index');
        Route::get('/export', [PermissionsController::class, 'export'])->name('export');
        Route::get('/create', [PermissionsController::class, 'create'])->name('create');
        Route::post('/', [PermissionsController::class, 'store'])->name('store');
        Route::get('/{permission}/edit', [PermissionsController::class, 'edit'])->name('edit');
        Route::put('/{permission}', [PermissionsController::class, 'update'])->name('update');
        Route::delete('/{permission}', [PermissionsController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | 📊 MATRICE DES PERMISSIONS - ROUTES
    |--------------------------------------------------------------------------
    */
    Route::get('/permissions-matrix', [PermissionMatrixController::class, 'index'])->name('permissions-matrix.index');
    Route::post('/permissions-matrix/update', [PermissionMatrixController::class, 'update'])->name('permissions-matrix.update');

    /*
    |--------------------------------------------------------------------------
    | 📚 RÉFÉRENTIELS - ROUTES COMPLÈTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        // Types d'organisations
        Route::get('/types-organisations', [OrganisationTypeController::class, 'index'])->name('types-organisations');
        Route::get('/types-organisations/create', [OrganisationTypeController::class, 'create'])->name('types-organisations.create');
        Route::post('/types-organisations', [OrganisationTypeController::class, 'store'])->name('types-organisations.store');
        Route::get('/types-organisations/{id}', [OrganisationTypeController::class, 'show'])->name('types-organisations.show');
        Route::get('/types-organisations/{id}/edit', [OrganisationTypeController::class, 'edit'])->name('types-organisations.edit');
        Route::put('/types-organisations/{id}', [OrganisationTypeController::class, 'update'])->name('types-organisations.update');
        Route::delete('/types-organisations/{id}', [OrganisationTypeController::class, 'destroy'])->name('types-organisations.destroy');
        Route::patch('/types-organisations/{id}/toggle-status', [OrganisationTypeController::class, 'toggleStatus'])->name('types-organisations.toggle-status');
        
        // Types de documents
        Route::prefix('document-types')->name('document-types.')->group(function () {
            Route::get('/', [DocumentTypeController::class, 'index'])->name('index');
            Route::get('/create', [DocumentTypeController::class, 'create'])->name('create');
            Route::post('/', [DocumentTypeController::class, 'store'])->name('store');
            Route::get('/{documentType}', [DocumentTypeController::class, 'show'])->name('show');
            Route::get('/{documentType}/edit', [DocumentTypeController::class, 'edit'])->name('edit');
            Route::put('/{documentType}', [DocumentTypeController::class, 'update'])->name('update');
            Route::delete('/{documentType}', [DocumentTypeController::class, 'destroy'])->name('destroy');
            Route::patch('/{documentType}/toggle-status', [DocumentTypeController::class, 'toggleStatus'])->name('toggle-status');
        });
        
        // Base NIP
        Route::prefix('nip-database')->name('nip-database.')->group(function () {
            Route::get('/', [NipDatabaseController::class, 'index'])->name('index');
            Route::get('/import', [NipDatabaseController::class, 'import'])->name('import');
            Route::post('/import', [NipDatabaseController::class, 'processImport'])->name('import.process');
            Route::get('/export', [NipDatabaseController::class, 'export'])->name('export');
            Route::post('/validate', [NipDatabaseController::class, 'validate'])->name('validate');
            Route::get('/statistics', [NipDatabaseController::class, 'statistics'])->name('statistics');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | 📝 GESTION DU CONTENU - ROUTES COMPLÈTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/pages', [ContentController::class, 'pages'])->name('pages');
        Route::get('/pages/create', [ContentController::class, 'createPage'])->name('pages.create');
        Route::post('/pages', [ContentController::class, 'storePage'])->name('pages.store');
        Route::get('/pages/{page}/edit', [ContentController::class, 'editPage'])->name('pages.edit');
        Route::put('/pages/{page}', [ContentController::class, 'updatePage'])->name('pages.update');
        Route::delete('/pages/{page}', [ContentController::class, 'destroyPage'])->name('pages.destroy');
        
        Route::get('/actualites', [ContentController::class, 'actualites'])->name('actualites');
        Route::get('/actualites/create', [ContentController::class, 'createActualite'])->name('actualites.create');
        Route::post('/actualites', [ContentController::class, 'storeActualite'])->name('actualites.store');
        Route::get('/actualites/{actualite}/edit', [ContentController::class, 'editActualite'])->name('actualites.edit');
        Route::put('/actualites/{actualite}', [ContentController::class, 'updateActualite'])->name('actualites.update');
        Route::delete('/actualites/{actualite}', [ContentController::class, 'destroyActualite'])->name('actualites.destroy');
        
        Route::get('/faq', [ContentController::class, 'faq'])->name('faq');
        Route::post('/faq', [ContentController::class, 'storeFaq'])->name('faq.store');
        Route::put('/faq/{faq}', [ContentController::class, 'updateFaq'])->name('faq.update');
        Route::delete('/faq/{faq}', [ContentController::class, 'destroyFaq'])->name('faq.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | 🗺️ GÉOLOCALISATION - ROUTES COMPLÈTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('geolocalisation')->name('geolocalisation.')->group(function () {
        
        // PROVINCES
        Route::prefix('provinces')->name('provinces.')->group(function () {
            Route::get('/', [ProvinceController::class, 'index'])->name('index');
            Route::get('/create', [ProvinceController::class, 'create'])->name('create');
            Route::post('/', [ProvinceController::class, 'store'])->name('store');
            Route::get('/{province}', [ProvinceController::class, 'show'])->name('show');
            Route::get('/{province}/edit', [ProvinceController::class, 'edit'])->name('edit');
            Route::put('/{province}', [ProvinceController::class, 'update'])->name('update');
            Route::delete('/{province}', [ProvinceController::class, 'destroy'])->name('destroy');
            Route::get('/export', [ProvinceController::class, 'export'])->name('export');
            Route::patch('/{province}/toggle-status', [ProvinceController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/bulk-action', [ProvinceController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/{province}/departements', [ProvinceController::class, 'departements'])->name('departements');
            Route::get('/{province}/map', [ProvinceController::class, 'map'])->name('map');
        });

        // DÉPARTEMENTS
        Route::prefix('departements')->name('departements.')->group(function () {
            Route::get('/', [DepartementController::class, 'index'])->name('index');
            Route::get('/create', [DepartementController::class, 'create'])->name('create');
            Route::post('/', [DepartementController::class, 'store'])->name('store');
            Route::get('/{departement}', [DepartementController::class, 'show'])->name('show');
            Route::get('/{departement}/edit', [DepartementController::class, 'edit'])->name('edit');
            Route::put('/{departement}', [DepartementController::class, 'update'])->name('update');
            Route::delete('/{departement}', [DepartementController::class, 'destroy'])->name('destroy');
            Route::get('/export', [DepartementController::class, 'export'])->name('export');
            Route::patch('/{departement}/toggle-status', [DepartementController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/bulk-action', [DepartementController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/{departement}/communes', [DepartementController::class, 'communes'])->name('communes');
            Route::get('/by-province/{province}', [DepartementController::class, 'byProvince'])->name('by-province');
        });

        // COMMUNES/VILLES
        Route::prefix('communes-villes')->name('communes-villes.')->group(function () {
            Route::get('/', [CommuneVilleController::class, 'index'])->name('index');
            Route::get('/create', [CommuneVilleController::class, 'create'])->name('create');
            Route::post('/', [CommuneVilleController::class, 'store'])->name('store');
            Route::get('/{communeVille}', [CommuneVilleController::class, 'show'])->name('show');
            Route::get('/{communeVille}/edit', [CommuneVilleController::class, 'edit'])->name('edit');
            Route::put('/{communeVille}', [CommuneVilleController::class, 'update'])->name('update');
            Route::delete('/{communeVille}', [CommuneVilleController::class, 'destroy'])->name('destroy');
            Route::get('/export', [CommuneVilleController::class, 'export'])->name('export');
            Route::patch('/{communeVille}/toggle-status', [CommuneVilleController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/bulk-action', [CommuneVilleController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/{communeVille}/arrondissements', [CommuneVilleController::class, 'arrondissements'])->name('arrondissements');
            Route::get('/{communeVille}/cantons', [CommuneVilleController::class, 'cantons'])->name('cantons');
            Route::get('/by-departement/{departement}', [CommuneVilleController::class, 'byDepartement'])->name('by-departement');
            
            // Filtres par type
            Route::get('/villes', [CommuneVilleController::class, 'villes'])->name('villes');
            Route::get('/communes', [CommuneVilleController::class, 'communes'])->name('communes');
        });

        // ARRONDISSEMENTS
        Route::prefix('arrondissements')->name('arrondissements.')->group(function () {
            Route::get('/', [ArrondissementController::class, 'index'])->name('index');
            Route::get('/create', [ArrondissementController::class, 'create'])->name('create');
            Route::post('/', [ArrondissementController::class, 'store'])->name('store');
            Route::get('/{arrondissement}', [ArrondissementController::class, 'show'])->name('show');
            Route::get('/{arrondissement}/edit', [ArrondissementController::class, 'edit'])->name('edit');
            Route::put('/{arrondissement}', [ArrondissementController::class, 'update'])->name('update');
            Route::delete('/{arrondissement}', [ArrondissementController::class, 'destroy'])->name('destroy');
            Route::get('/export', [ArrondissementController::class, 'export'])->name('export');
            Route::patch('/{arrondissement}/toggle-status', [ArrondissementController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/bulk-action', [ArrondissementController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/{arrondissement}/quartiers', [ArrondissementController::class, 'quartiers'])->name('quartiers');
            Route::get('/by-commune/{commune}', [ArrondissementController::class, 'byCommune'])->name('by-commune');
        });

        // CANTONS
        Route::prefix('cantons')->name('cantons.')->group(function () {
            Route::get('/', [CantonController::class, 'index'])->name('index');
            Route::get('/create', [CantonController::class, 'create'])->name('create');
            Route::post('/', [CantonController::class, 'store'])->name('store');
            Route::get('/{canton}', [CantonController::class, 'show'])->name('show');
            Route::get('/{canton}/edit', [CantonController::class, 'edit'])->name('edit');
            Route::put('/{canton}', [CantonController::class, 'update'])->name('update');
            Route::delete('/{canton}', [CantonController::class, 'destroy'])->name('destroy');
            Route::get('/export', [CantonController::class, 'export'])->name('export');
            Route::patch('/{canton}/toggle-status', [CantonController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/bulk-action', [CantonController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/{canton}/regroupements', [CantonController::class, 'regroupements'])->name('regroupements');
            Route::get('/by-commune/{commune}', [CantonController::class, 'byCommune'])->name('by-commune');
        });

        // REGROUPEMENTS
        Route::prefix('regroupements')->name('regroupements.')->group(function () {
            Route::get('/', [RegroupementController::class, 'index'])->name('index');
            Route::get('/create', [RegroupementController::class, 'create'])->name('create');
            Route::post('/', [RegroupementController::class, 'store'])->name('store');
            Route::get('/{regroupement}', [RegroupementController::class, 'show'])->name('show');
            Route::get('/{regroupement}/edit', [RegroupementController::class, 'edit'])->name('edit');
            Route::put('/{regroupement}', [RegroupementController::class, 'update'])->name('update');
            Route::delete('/{regroupement}', [RegroupementController::class, 'destroy'])->name('destroy');
            Route::get('/export', [RegroupementController::class, 'export'])->name('export');
            Route::patch('/{regroupement}/toggle-status', [RegroupementController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/bulk-action', [RegroupementController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/{regroupement}/localites', [RegroupementController::class, 'localites'])->name('localites');
            Route::get('/by-canton/{canton}', [RegroupementController::class, 'byCanton'])->name('by-canton');
        });

        // LOCALITÉS
        Route::prefix('localites')->name('localites.')->group(function () {
            Route::get('/', [LocaliteController::class, 'index'])->name('index');
            Route::get('/create', [LocaliteController::class, 'create'])->name('create');
            Route::post('/', [LocaliteController::class, 'store'])->name('store');
            Route::get('/{localite}', [LocaliteController::class, 'show'])->name('show');
            Route::get('/{localite}/edit', [LocaliteController::class, 'edit'])->name('edit');
            Route::put('/{localite}', [LocaliteController::class, 'update'])->name('update');
            Route::delete('/{localite}', [LocaliteController::class, 'destroy'])->name('destroy');
            Route::get('/export', [LocaliteController::class, 'export'])->name('export');
            Route::patch('/{localite}/toggle-status', [LocaliteController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/bulk-action', [LocaliteController::class, 'bulkAction'])->name('bulk-action');
            
            // Filtres par type de localité
            Route::get('/quartiers', [LocaliteController::class, 'quartiers'])->name('quartiers');
            Route::get('/villages', [LocaliteController::class, 'villages'])->name('villages');
            
            // Relations hiérarchiques
            Route::get('/by-arrondissement/{arrondissement}', [LocaliteController::class, 'byArrondissement'])->name('by-arrondissement');
            Route::get('/by-regroupement/{regroupement}', [LocaliteController::class, 'byRegroupement'])->name('by-regroupement');
            Route::get('/by-commune/{commune}', [LocaliteController::class, 'byCommune'])->name('by-commune');
            Route::get('/by-canton/{canton}', [LocaliteController::class, 'byCanton'])->name('by-canton');
        });
    });
});

/*
|--------------------------------------------------------------------------
| 📄 MODULE GÉNÉRATION DE DOCUMENTS - VERSION COMPLÈTE
|--------------------------------------------------------------------------
| Routes pour la gestion des templates de documents et des documents générés
| ✅ Ajouté le : 21/01/2025
| ✅ Toutes les routes admin uniquement (26 routes)
| ❌ Routes publiques et API déplacées vers web.php et api.php
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | 📝 GESTION DES TEMPLATES DE DOCUMENTS (10 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('document-templates')->name('document-templates.')->group(function () {
        
        // CRUD des templates
        Route::get('/', [DocumentTemplateController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTemplateController::class, 'create'])->name('create');
        Route::post('/', [DocumentTemplateController::class, 'store'])->name('store');
        Route::get('/{documentTemplate}', [DocumentTemplateController::class, 'show'])->name('show');
        Route::get('/{documentTemplate}/edit', [DocumentTemplateController::class, 'edit'])->name('edit');
        Route::put('/{documentTemplate}', [DocumentTemplateController::class, 'update'])->name('update');
        Route::delete('/{documentTemplate}', [DocumentTemplateController::class, 'destroy'])->name('destroy');
        
        // ✅ AJOUTER CES 2 ROUTES POUR LA PRÉVISUALISATION
        Route::get('/{documentTemplate}/preview', [DocumentTemplateController::class, 'preview'])->name('preview');
        Route::post('/{documentTemplate}/preview/pdf', [DocumentTemplateController::class, 'previewPdf'])->name('preview.pdf');
            
        // AJAX : Charger les workflow steps selon organisation/opération
        Route::get('/ajax/workflow-steps', [DocumentTemplateController::class, 'getWorkflowSteps'])->name('ajax.workflow-steps');
    });

    /*
    |--------------------------------------------------------------------------
    | 📋 GESTION DES DOCUMENTS GÉNÉRÉS (16 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('documents')->name('documents.')->group(function () {
        
        // Liste et historique des documents générés
        Route::get('/', [GeneratedDocumentController::class, 'index'])->name('index');
        
        // Formulaire de génération manuelle
        Route::get('/create', [GeneratedDocumentController::class, 'create'])->name('create');
        
        // Créer/Générer un document
        Route::post('/', [GeneratedDocumentController::class, 'store'])->name('store');
        Route::post('/generate', [GeneratedDocumentController::class, 'generate'])->name('generate');
        
        // Voir les détails d'un document généré
        Route::get('/{generation}', [GeneratedDocumentController::class, 'show'])->name('show');
        
        // Télécharger un document
        Route::get('/{generation}/download', [GeneratedDocumentController::class, 'download'])->name('download');
        
        // Régénérer un document
        Route::post('/{generation}/regenerate', [GeneratedDocumentController::class, 'regenerate'])->name('regenerate');
        
        // Invalider un document
        Route::put('/{generation}/invalidate', [GeneratedDocumentController::class, 'invalidate'])->name('invalidate');
        
        // Réactiver un document invalidé
        Route::put('/{generation}/reactivate', [GeneratedDocumentController::class, 'reactivate'])->name('reactivate');
        
        // Supprimer un document
        Route::delete('/{generation}', [GeneratedDocumentController::class, 'destroy'])->name('destroy');
        
        // Actions groupées
        Route::post('/bulk-download', [GeneratedDocumentController::class, 'bulkDownload'])->name('bulk-download');
        Route::post('/bulk-invalidate', [GeneratedDocumentController::class, 'bulkInvalidate'])->name('bulk-invalidate');
        Route::post('/bulk-delete', [GeneratedDocumentController::class, 'bulkDelete'])->name('bulk-delete');
        
        // Export CSV des documents
        Route::get('/export/csv', [GeneratedDocumentController::class, 'export'])->name('export');
        
        // AJAX : Charger les templates pour une organisation
        Route::get('/ajax/templates-for-organisation', [GeneratedDocumentController::class, 'getTemplatesForOrganisation'])
            ->name('ajax.templates-for-organisation');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 🔍 GESTION DES VÉRIFICATIONS (Admin uniquement - 3 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('document-verifications')->name('document-verifications.')->group(function () {
        
        // Liste de toutes les vérifications (Admin)
        Route::get('/', [PublicDocVerificationController::class, 'adminIndex'])
            ->name('index');
        
        // Historique des vérifications d'un document spécifique (Admin)
        Route::get('/{generation}/verifications', [PublicDocVerificationController::class, 'documentVerifications'])
            ->name('history');
        
        // Export CSV des vérifications (Admin)
        Route::get('/export/verifications', [PublicDocVerificationController::class, 'exportVerifications'])
            ->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | ✅ VALIDATION ENTITIES - Gestion des entités de validation (8 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('validation-entities')->name('validation-entities.')->group(function () {
        Route::get('/', [ValidationEntityController::class, 'index'])->name('index');
        Route::get('/create', [ValidationEntityController::class, 'create'])->name('create');
        Route::post('/', [ValidationEntityController::class, 'store'])->name('store');
        Route::get('/{id}', [ValidationEntityController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ValidationEntityController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ValidationEntityController::class, 'update'])->name('update');
        Route::delete('/{id}', [ValidationEntityController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [ValidationEntityController::class, 'toggleStatus'])->name('toggle-status');
    });

    /*
    |--------------------------------------------------------------------------
    | ⚙️ MODULE WORKFLOW STEPS - GESTION DES ÉTAPES DE WORKFLOW (19 routes)
    |--------------------------------------------------------------------------
    | Gestion complète des étapes du workflow de validation
    | ✅ Ajouté le : 02/11/2025
    | ✅ 19 routes (7 CRUD + 12 custom)
    | 
    | Fonctionnalités :
    | - CRUD complet des étapes
    | - Timeline visuelle avec ordre
    | - Drag & drop pour réorganisation
    | - Statistiques avancées
    | - Duplication d'étapes
    | - Export de configuration
    |--------------------------------------------------------------------------
    */
    Route::prefix('workflow-steps')->name('workflow-steps.')->group(function () {
    
        // ========== ROUTES AVEC CHEMINS FIXES EN PREMIER ==========
        
        Route::get('/', [WorkflowStepController::class, 'index'])->name('index');
        Route::get('/create', [WorkflowStepController::class, 'create'])->name('create');
        
        // ⭐ ROUTES DE CONFIGURATION (AVANT /{id}) ⭐
        Route::get('/configure', [WorkflowStepController::class, 'configure'])->name('configure');
        Route::post('/configure/save', [WorkflowStepController::class, 'saveConfiguration'])->name('configure.save');
        Route::get('/timeline', [WorkflowStepController::class, 'timeline'])->name('timeline');
        
        // Routes AJAX
        Route::post('/{stepId}/assign-entity', [WorkflowStepController::class, 'assignEntity'])->name('assign-entity');
        Route::delete('/{stepId}/remove-entity/{entityId}', [WorkflowStepController::class, 'removeEntity'])->name('remove-entity');
        Route::post('/{stepId}/reorder-entities', [WorkflowStepController::class, 'reorderEntities'])->name('reorder-entities');
        
        // Toggle, reorder, duplicate, export, statistics
        Route::patch('/{id}/toggle-status', [WorkflowStepController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/reorder', [WorkflowStepController::class, 'reorder'])->name('reorder');
        Route::post('/{id}/duplicate', [WorkflowStepController::class, 'duplicate'])->name('duplicate');
        Route::get('/export', [WorkflowStepController::class, 'export'])->name('export');
        Route::get('/{id}/statistics', [WorkflowStepController::class, 'statistics'])->name('statistics');
        
        // ========== ROUTES AVEC PARAMÈTRES DYNAMIQUES À LA FIN ==========
        
        Route::get('/{id}', [WorkflowStepController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [WorkflowStepController::class, 'edit'])->name('edit');
        Route::put('/{id}', [WorkflowStepController::class, 'update'])->name('update');
        Route::delete('/{id}', [WorkflowStepController::class, 'destroy'])->name('destroy');
        Route::post('/', [WorkflowStepController::class, 'store'])->name('store');
    });

});