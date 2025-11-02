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



/*
|--------------------------------------------------------------------------
| Routes Administration - SGLP/PNGDI - VERSION CORRIGÃ‰E
|--------------------------------------------------------------------------
| Routes pour l'interface d'administration complÃ¨te
| Middleware : auth, verified, admin
| âœ… Version corrigÃ©e sans doublons de noms de routes
| âœ… Compatible PHP 8.3 et Laravel 9
| âœ… MODULE TYPES D'ORGANISATIONS AJOUTÃ‰
| âœ… MODULE DOCUMENTS - ROUTES COMPLÃˆTES (21/01/2025)
| âŒ ROUTES PUBLIQUES ET API SUPPRIMÃ‰ES (maintenant dans web.php et api.php)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ  DASHBOARD PRINCIPAL
    |--------------------------------------------------------------------------
    */
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ“Š ANALYTICS ET RAPPORTS - SECTION COMPLÃˆTE
    |--------------------------------------------------------------------------
    */
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
    Route::get('/reports', [AnalyticsController::class, 'reports'])->name('reports.index');
    Route::get('/exports', [AnalyticsController::class, 'exports'])->name('exports.index');
    Route::get('/activity-logs', [AnalyticsController::class, 'activityLogs'])->name('activity-logs.index');

    // ðŸ“¤ EXPORTS - Routes complÃ¨tes
    Route::prefix('exports')->name('exports.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'exports'])->name('index');
        Route::get('/global', [AnalyticsController::class, 'exportGlobal'])->name('global');
        Route::get('/dossiers', [AnalyticsController::class, 'exportDossiers'])->name('dossiers');
        Route::get('/users', [AnalyticsController::class, 'exportUsers'])->name('users');
        Route::get('/organisations', [AnalyticsController::class, 'exportOrganisations'])->name('organisations');
        
        // Exports spÃ©cialisÃ©s
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

    // ðŸ“Š REPORTS - Routes complÃ¨tes  
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'reports'])->name('index');
        Route::get('/monthly', [AnalyticsController::class, 'monthlyReport'])->name('monthly');
        Route::get('/annual', [AnalyticsController::class, 'annualReport'])->name('annual');
        Route::get('/custom', [AnalyticsController::class, 'customReport'])->name('custom');
    });

    // ðŸ“ˆ ACTIVITY LOGS - Routes complÃ¨tes
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'activityLogs'])->name('index');
        Route::get('/search', [AnalyticsController::class, 'searchLogs'])->name('search');
        Route::delete('/clean', [AnalyticsController::class, 'cleanLogs'])->name('clean');
        Route::get('/export', [AnalyticsController::class, 'exportLogs'])->name('export');
    });

    /*
    |--------------------------------------------------------------------------
    | ðŸ“„ WORKFLOW DES DOSSIERS - ROUTES CORRIGÃ‰ES
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
    | ðŸ¢ GESTION DES ORGANISATIONS - ROUTE CORRIGÃ‰E
    |--------------------------------------------------------------------------
    */
    Route::get('/organisations', [DossierController::class, 'index'])->name('organisations.index');

    /*
    |--------------------------------------------------------------------------
    | ðŸ”” NOTIFICATIONS - ROUTES CORRIGÃ‰ES (SANS CONFLIT)
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
    | âš™ï¸ PARAMÃˆTRES SYSTÃˆME - ROUTES CORRIGÃ‰ES (SANS CONFLIT)
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
    | ðŸ“„ GESTION DES DOSSIERS - ROUTES ADMINISTRATEUR
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
        
        // Historique et traÃ§abilitÃ©
        Route::get('/{dossier}/historique', [DossierController::class, 'historique'])->name('historique');
        Route::get('/{dossier}/logs', [DossierController::class, 'logs'])->name('logs');
        
        // Export et rapports
        Route::get('/export', [DossierController::class, 'export'])->name('export');
        Route::post('/bulk-action', [DossierController::class, 'bulkAction'])->name('bulk-action');
    });

    /*
    |--------------------------------------------------------------------------
    | ðŸ‘¥ GESTION DES UTILISATEURS - ROUTES COMPLÃˆTES
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
        
        // Gestion du statut et des rÃ´les
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
    | ðŸ” GESTION DES RÃ”LES ET PERMISSIONS - ROUTES CORRIGÃ‰ES
    |--------------------------------------------------------------------------
    */
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolesController::class, 'index'])->name('index');
        Route::get('/search', [RolesController::class, 'search'])->name('search');
        Route::get('/create', [RolesController::class, 'create'])->name('create');
        Route::post('/', [RolesController::class, 'store'])->name('store');
        Route::get('/{role}', [RolesController::class, 'show'])->name('show');
        Route::get('/{role}/edit', [RolesController::class, 'edit'])->name('edit');
        Route::put('/{role}', [RolesController::class, 'update'])->name('update');
        Route::delete('/{role}', [RolesController::class, 'destroy'])->name('destroy');
        
        // Gestion des permissions
        Route::post('/{role}/permissions', [RolesController::class, 'syncPermissions'])->name('permissions.sync');
    });

    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [PermissionsController::class, 'index'])->name('index');
        Route::get('/export', [PermissionsController::class, 'export'])->name('export');
        Route::get('/create', [PermissionsController::class, 'create'])->name('create');
        Route::post('/', [PermissionsController::class, 'store'])->name('store');
        Route::get('/{permission}/edit', [PermissionsController::class, 'edit'])->name('edit');
        Route::put('/{permission}', [PermissionsController::class, 'update'])->name('update');
        Route::delete('/{permission}', [PermissionsController::class, 'destroy'])->name('destroy');
    });

    // Matrice des permissions
    Route::get('/permission-matrix', [PermissionMatrixController::class, 'index'])->name('permission-matrix.index');
    Route::post('/permission-matrix/update', [PermissionMatrixController::class, 'update'])->name('permission-matrix.update');

    /*
    |--------------------------------------------------------------------------
    | ðŸ“š GESTION DU CONTENU - ROUTES CORRIGÃ‰ES (SANS CONFLIT)
    |--------------------------------------------------------------------------
    */
    Route::prefix('content')->name('content.')->group(function () {
        // ActualitÃ©s
        Route::resource('actualites', ContentController::class)->parameters([
            'actualites' => 'actualite'
        ]);
        
        // Documents publics
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [ContentController::class, 'documents'])->name('index');
            Route::get('/create', [ContentController::class, 'createDocument'])->name('create');
            Route::post('/', [ContentController::class, 'storeDocument'])->name('store');
            Route::get('/{document}/edit', [ContentController::class, 'editDocument'])->name('edit');
            Route::put('/{document}', [ContentController::class, 'updateDocument'])->name('update');
            Route::delete('/{document}', [ContentController::class, 'destroyDocument'])->name('destroy');
        });
        
        // Pages statiques
        Route::prefix('pages')->name('pages.')->group(function () {
            Route::get('/', [ContentController::class, 'pages'])->name('index');
            Route::get('/{page}/edit', [ContentController::class, 'editPage'])->name('edit');
            Route::put('/{page}', [ContentController::class, 'updatePage'])->name('update');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | ðŸ“‹ RÃ‰FÃ‰RENTIELS - ROUTES CORRIGÃ‰ES (SANS CONFLIT)
    |--------------------------------------------------------------------------
    */
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::get('/', [ReferentielController::class, 'index'])->name('index');
        
        // Types d'organisations
        Route::prefix('organisation-types')->name('organisation-types.')->group(function () {
            Route::get('/', [OrganisationTypeController::class, 'index'])->name('index');
            Route::get('/create', [OrganisationTypeController::class, 'create'])->name('create');
            Route::post('/', [OrganisationTypeController::class, 'store'])->name('store');
            Route::get('/{organisationType}', [OrganisationTypeController::class, 'show'])->name('show');
            Route::get('/{organisationType}/edit', [OrganisationTypeController::class, 'edit'])->name('edit');
            Route::put('/{organisationType}', [OrganisationTypeController::class, 'update'])->name('update');
            Route::delete('/{organisationType}', [OrganisationTypeController::class, 'destroy'])->name('destroy');
            Route::patch('/{organisationType}/toggle-status', [OrganisationTypeController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/export', [OrganisationTypeController::class, 'export'])->name('export');
            Route::post('/bulk-action', [OrganisationTypeController::class, 'bulkAction'])->name('bulk-action');
        });
        
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
    });

    /*
    |--------------------------------------------------------------------------
    | ðŸ—„ï¸ BASE DE DONNÃ‰ES NIP - ROUTES COMPLÃˆTES
    |--------------------------------------------------------------------------
    */
    Route::prefix('nip-database')->name('nip-database.')->group(function () {
        Route::get('/', [NipDatabaseController::class, 'index'])->name('index');
        Route::get('/create', [NipDatabaseController::class, 'create'])->name('create');
        Route::post('/', [NipDatabaseController::class, 'store'])->name('store');
        Route::get('/{nip}', [NipDatabaseController::class, 'show'])->name('show');
        Route::get('/{nip}/edit', [NipDatabaseController::class, 'edit'])->name('edit');
        Route::put('/{nip}', [NipDatabaseController::class, 'update'])->name('update');
        Route::delete('/{nip}', [NipDatabaseController::class, 'destroy'])->name('destroy');
        
        // Recherche et vÃ©rification
        Route::post('/verify', [NipDatabaseController::class, 'verify'])->name('verify');
        Route::post('/search', [NipDatabaseController::class, 'search'])->name('search');
        
        // Import et export
        Route::get('/import', [NipDatabaseController::class, 'import'])->name('import');
        Route::post('/import', [NipDatabaseController::class, 'processImport'])->name('import.process');
        Route::get('/export', [NipDatabaseController::class, 'export'])->name('export');
        Route::get('/template', [NipDatabaseController::class, 'template'])->name('template');
        
        // Statistiques
        Route::get('/stats', [NipDatabaseController::class, 'stats'])->name('stats');
    });

    /*
    |--------------------------------------------------------------------------
    | ðŸŒ TERRITOIRES - HIÃ‰RARCHIE GÃ‰OGRAPHIQUE COMPLÃˆTE
    |--------------------------------------------------------------------------
    */
    Route::prefix('territoires')->name('territoires.')->group(function () {
        
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
        });

        // DÃ‰PARTEMENTS
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

        // COMMUNES ET VILLES
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
            
            // Relations hiÃ©rarchiques
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

        // LOCALITÃ‰S
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
            
            // Filtres par type de localitÃ©
            Route::get('/quartiers', [LocaliteController::class, 'quartiers'])->name('quartiers');
            Route::get('/villages', [LocaliteController::class, 'villages'])->name('villages');
            
            // Relations hiÃ©rarchiques
            Route::get('/by-arrondissement/{arrondissement}', [LocaliteController::class, 'byArrondissement'])->name('by-arrondissement');
            Route::get('/by-regroupement/{regroupement}', [LocaliteController::class, 'byRegroupement'])->name('by-regroupement');
            Route::get('/by-commune/{commune}', [LocaliteController::class, 'byCommune'])->name('by-commune');
            Route::get('/by-canton/{canton}', [LocaliteController::class, 'byCanton'])->name('by-canton');
        });
    });
});

/*
|--------------------------------------------------------------------------
| ðŸ“„ MODULE GÃ‰NÃ‰RATION DE DOCUMENTS - VERSION COMPLÃˆTE
|--------------------------------------------------------------------------
| Routes pour la gestion des templates de documents et des documents gÃ©nÃ©rÃ©s
| âœ… AjoutÃ© le : 21/01/2025
| âœ… Toutes les routes admin uniquement (26 routes)
| âŒ Routes publiques et API dÃ©placÃ©es vers web.php et api.php
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ“ GESTION DES TEMPLATES DE DOCUMENTS (10 routes)
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
        
        // PrÃ©visualisation d'un template
        Route::get('/{documentTemplate}/preview', [DocumentTemplateController::class, 'preview'])->name('preview');
        Route::get('/{documentTemplate}/preview-pdf', [DocumentTemplateController::class, 'previewPdf'])->name('preview-pdf');
        
        // AJAX : Charger les workflow steps selon organisation/opÃ©ration
        Route::get('/ajax/workflow-steps', [DocumentTemplateController::class, 'getWorkflowSteps'])->name('ajax.workflow-steps');
    });

    /*
    |--------------------------------------------------------------------------
    | ðŸ“‹ GESTION DES DOCUMENTS GÃ‰NÃ‰RÃ‰S (16 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('documents')->name('documents.')->group(function () {
        
        // Liste et historique des documents gÃ©nÃ©rÃ©s
        Route::get('/', [GeneratedDocumentController::class, 'index'])->name('index');
        
        // Formulaire de gÃ©nÃ©ration manuelle
        Route::get('/create', [GeneratedDocumentController::class, 'create'])->name('create');
        
        // CrÃ©er/GÃ©nÃ©rer un document
        Route::post('/', [GeneratedDocumentController::class, 'store'])->name('store');
        Route::post('/generate', [GeneratedDocumentController::class, 'generate'])->name('generate');
        
        // Voir les dÃ©tails d'un document gÃ©nÃ©rÃ©
        Route::get('/{generation}', [GeneratedDocumentController::class, 'show'])->name('show');
        
        // TÃ©lÃ©charger un document
        Route::get('/{generation}/download', [GeneratedDocumentController::class, 'download'])->name('download');
        
        // RÃ©gÃ©nÃ©rer un document
        Route::post('/{generation}/regenerate', [GeneratedDocumentController::class, 'regenerate'])->name('regenerate');
        
        // Invalider un document
        Route::put('/{generation}/invalidate', [GeneratedDocumentController::class, 'invalidate'])->name('invalidate');
        
        // RÃ©activer un document invalidÃ©
        Route::put('/{generation}/reactivate', [GeneratedDocumentController::class, 'reactivate'])->name('reactivate');
        
        // Supprimer un document
        Route::delete('/{generation}', [GeneratedDocumentController::class, 'destroy'])->name('destroy');
        
        // Actions groupÃ©es
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
    | ðŸ” GESTION DES VÃ‰RIFICATIONS (Admin uniquement - 2 routes)
    |--------------------------------------------------------------------------
    */
    Route::prefix('document-verifications')->name('document-verifications.')->group(function () {
        
        // Historique des vÃ©rifications (Admin)
        Route::get('/{generation}/verifications', [PublicDocVerificationController::class, 'documentVerifications'])
            ->name('history');
        
        // Export CSV des vÃ©rifications (Admin)
        Route::get('/export/verifications', [PublicDocVerificationController::class, 'exportVerifications'])
            ->name('export');
    });

    // ============================================================================
    // ROUTES VALIDATION ENTITIES - Gestion des entités de validation
    // ============================================================================
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

});