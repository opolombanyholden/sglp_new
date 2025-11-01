<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DocumentTemplateController;
use App\Http\Controllers\Admin\GeneratedDocumentController;
use App\Http\Controllers\PublicControllers\DocumentVerificationController;
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
use App\Http\Controllers\Admin\OrganisationTypeController; // ✨ NOUVEAU IMPORT
use App\Http\Controllers\Admin\DocumentTypeController; // ✨ AJOUTEZ CETTE LIGNE
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\NipDatabaseController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\PermissionMatrixController;



/*
|--------------------------------------------------------------------------
| Routes Administration - SGLP/PNGDI - VERSION SANS CONFLITS
|--------------------------------------------------------------------------
| Routes pour l'interface d'administration complète
| Middleware : auth, verified, admin
| ✅ Version corrigée sans doublons de noms de routes
| ✅ Compatible PHP 8.3 et Laravel moderne
| ✅ MODULE TYPES D'ORGANISATIONS AJOUTÉ
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
    | ⚙️ PARAMÈTRES SYSTÈME - ROUTES CORRIGÉES SANS CONFLITS
    |--------------------------------------------------------------------------
    */
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/clear-cache', [SettingsController::class, 'clearCache'])->name('clear-cache');
        
        // ✅ CORRECTION CRITIQUE : Nom unique pour éviter les conflits
        Route::get('/system-info-page', [SettingsController::class, 'systemInfo'])->name('system-info-page');

        // Paramètres système
        Route::post('/system/update', [SettingsController::class, 'updateSystemSettings'])->name('update-system');
        Route::post('/maintenance/toggle', [SettingsController::class, 'toggleMaintenanceMode'])->name('toggle-maintenance');
        Route::post('/caches/clear', [SettingsController::class, 'clearCaches'])->name('clear-caches');
        
        // Préférences utilisateur
        Route::post('/preferences/update', [SettingsController::class, 'updateUserPreferences'])->name('update-preferences');
        Route::get('/preferences/export', [SettingsController::class, 'exportUserPreferences'])->name('export-preferences');
        Route::post('/preferences/import', [SettingsController::class, 'importUserPreferences'])->name('import-preferences');
        
        // Monitoring et diagnostic - NOMS UNIQUES
        Route::get('/system/health-check', [SettingsController::class, 'systemHealthCheck'])->name('system-health-check');
        Route::get('/system/information', [SettingsController::class, 'getSystemInfo'])->name('system-information');
        Route::post('/system/optimize', [SettingsController::class, 'optimizeSystem'])->name('system-optimize');
        
        // Sécurité avancée
        Route::post('/security/force-2fa', [SettingsController::class, 'force2FAForAdmins'])->name('force-2fa');
        Route::post('/security/reset-sessions', [SettingsController::class, 'resetAllSessions'])->name('reset-sessions');
        Route::post('/logs/cleanup', [SettingsController::class, 'cleanupOldLogs'])->name('cleanup-logs');
        
        // Backup et restauration
        Route::post('/backup/create', [SettingsController::class, 'createBackup'])->name('create-backup');
        Route::get('/backup/download/{file}', [SettingsController::class, 'downloadBackup'])->name('download-backup');
        Route::get('/backup/list', [SettingsController::class, 'listBackups'])->name('list-backups');

        // Routes sécurité
        Route::post('/security/update', [SettingsController::class, 'updateSecuritySettings'])->name('update-security');
        Route::post('/security/clear-logs', [SettingsController::class, 'clearOldLogs'])->name('clear-logs');
    });

    /*
    |--------------------------------------------------------------------------
    | 📋 GESTION DES DOSSIERS DE VALIDATION - SECTION CORRIGÉE ET COMPLÈTE
    |--------------------------------------------------------------------------
    */
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        // Routes de listing et filtrage
        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/en-attente', [DossierController::class, 'enAttente'])->name('en-attente');
        Route::get('/in-progress', [DossierController::class, 'inProgress'])->name('in-progress');
        Route::get('/pending', [DossierController::class, 'pending'])->name('pending');
        Route::get('/approved', [DossierController::class, 'approved'])->name('approved');
        Route::get('/valides', [DossierController::class, 'valides'])->name('valides');
        Route::get('/rejetes', [DossierController::class, 'rejetes'])->name('rejetes');
        Route::get('/archives', [DossierController::class, 'archives'])->name('archives');
        
        // Affichage d'un dossier spécifique
        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');

        // Routes de validation principales
        Route::post('/{dossier}/validate', [DossierController::class, 'validateDossier'])->name('validate');
        Route::post('/{dossier}/reject', [DossierController::class, 'rejeter'])->name('reject');
        Route::post('/{dossier}/assign', [DossierController::class, 'assign'])->name('assign');
        Route::post('/{dossier}/comment', [DossierController::class, 'addComment'])->name('comment');
        
        // Routes de compatibilité
        Route::post('/{dossier}/valider', [DossierController::class, 'valider'])->name('valider');
        Route::post('/{dossier}/rejeter', [DossierController::class, 'rejeter'])->name('rejeter');
        Route::post('/{dossier}/attribuer', [DossierController::class, 'attribuer'])->name('attribuer');

        // Actions complémentaires
        Route::post('/{dossier}/demander-complement', [DossierController::class, 'demanderComplement'])->name('complement');
        Route::post('/{dossier}/request-modification', [DossierController::class, 'requestModification'])->name('request-modification');
        Route::post('/{dossier}/archiver', [DossierController::class, 'archiver'])->name('archiver');

        // Routes de téléchargement PDF
        Route::get('/{dossier}/download-accuse', [DossierController::class, 'downloadAccuse'])->name('download-accuse');
        Route::get('/{dossier}/download-recepisse', [DossierController::class, 'downloadRecepisse'])->name('download-recepisse');
        Route::get('/{dossier}/download-recepisse-provisoire', [DossierController::class, 'downloadRecepisseProvisoire'])->name('download-recepisse-provisoire');

        // Gestion du verrouillage
        Route::post('/{dossier}/lock', [DossierController::class, 'lock'])->name('lock');
        Route::delete('/{dossier}/unlock', [DossierController::class, 'unlock'])->name('unlock');
        
        // Historique et audit
        Route::get('/{dossier}/history', [DossierController::class, 'history'])->name('history');
        Route::get('/{dossier}/timeline', [DossierController::class, 'timeline'])->name('timeline');
        
        // Documents associés
        Route::get('/{dossier}/documents', [DossierController::class, 'documents'])->name('documents');
        Route::get('/{dossier}/documents/{documentId}/download', [DossierController::class, 'downloadDocument'])->name('documents.download');
        Route::get('/{dossier}/documents/{documentId}/preview', [DossierController::class, 'previewDocument'])->name('documents.preview');
        
        // Export et impression
        Route::post('/export', [DossierController::class, 'export'])->name('export');
        Route::get('/{dossier}/print', [DossierController::class, 'print'])->name('print');
        Route::get('/{dossier}/pdf', [DossierController::class, 'generatePDF'])->name('pdf');

        // Assignation multiple
        Route::post('/assign-multiple', [DossierController::class, 'assignMultiple'])->name('assign-multiple');
        Route::get('/{dossier}/comments', [DossierController::class, 'getComments'])->name('comments.list');

        // Gestion des verrous (admin uniquement)
        Route::middleware('admin.only')->group(function () {
            Route::post('/{dossier}/force-unlock', [DossierController::class, 'forceUnlock'])->name('force-unlock');
            Route::get('/locks/status', [DossierController::class, 'locksStatus'])->name('locks.status');
            Route::post('/locks/clean-expired', [DossierController::class, 'cleanExpiredLocks'])->name('locks.clean');
        });
        
        // Rapports et exports
        Route::get('/export/excel', [DossierController::class, 'exportExcel'])->name('export.excel');
        Route::get('/export/pdf', [DossierController::class, 'exportPdf'])->name('export.pdf');
        Route::post('/rapport/generer', [DossierController::class, 'genererRapport'])->name('rapport.generer');
        Route::post('/{dossier}/calculate-position', [DossierController::class, 'calculatePosition'])->name('calculate-position');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 👥 GESTION DES UTILISATEURS - SECTION CORRIGÉE
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->name('users.')->group(function () {
        // Routes principales pour le menu admin
        Route::get('/operators', [UserManagementController::class, 'operators'])->name('operators');
        Route::get('/agents', [UserManagementController::class, 'agents'])->name('agents');
        
        // Routes CRUD complètes
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        
        // Route pour les rôles (référencée dans le layout)
        Route::get('/roles', [RolesController::class, 'index'])->name('roles');
        
        // Actions spéciales
        Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{user}/update-status', [UserManagementController::class, 'updateStatus'])->name('update-status');
        Route::post('/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('reset-password');
        Route::post('/{user}/force-verify-email', [UserManagementController::class, 'forceVerifyEmail'])->name('force-verify-email');
        Route::post('/{user}/disable-2fa', [UserManagementController::class, 'disable2FA'])->name('disable-2fa');
        Route::post('/{user}/send-welcome', [UserManagementController::class, 'sendWelcomeEmail'])->name('send-welcome');
        
        // Import/Export utilisateurs
        Route::get('/export/excel', [UserManagementController::class, 'exportExcel'])->name('export.excel');
        Route::post('/import', [UserManagementController::class, 'import'])->name('import');
        Route::get('/import/template', [UserManagementController::class, 'downloadTemplate'])->name('import.template');
    });

    // Module Gestion Base NIP
    Route::prefix('nip-database')->name('nip-database.')->group(function () {
        Route::get('/', [NipDatabaseController::class, 'index'])->name('index');
        Route::get('/import', [NipDatabaseController::class, 'import'])->name('import');
        Route::post('/import', [NipDatabaseController::class, 'processImport'])->name('process-import');
        Route::get('/template', [NipDatabaseController::class, 'downloadTemplate'])->name('template');
        Route::get('/export', [NipDatabaseController::class, 'export'])->name('export');
        Route::post('/cleanup', [NipDatabaseController::class, 'cleanup'])->name('cleanup');
        Route::get('/search', [NipDatabaseController::class, 'search'])->name('search');
        Route::post('/verify', [NipDatabaseController::class, 'verify'])->name('verify');
        Route::get('/{id}', [NipDatabaseController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [NipDatabaseController::class, 'edit'])->name('edit');
        Route::put('/{id}', [NipDatabaseController::class, 'update'])->name('update');
        Route::delete('/{id}', [NipDatabaseController::class, 'destroy'])->name('destroy');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 🗃️ GESTION DES RÉFÉRENTIELS - SECTION CORRIGÉE
    |--------------------------------------------------------------------------
    */
    Route::prefix('referentiels')->name('referentiels.')->group(function () {
        Route::get('/', [ReferentielController::class, 'index'])->name('index');
        Route::get('/types-organisations', [ReferentielController::class, 'typesOrganisations'])->name('types-organisations');
        Route::get('/document-types', [ReferentielController::class, 'documentTypes'])->name('document-types');
        Route::get('/zones', [ReferentielController::class, 'zones'])->name('zones');
        
        // ========================================
        // ✨ NOUVEAU MODULE : TYPES D'ORGANISATIONS
        // ========================================
        Route::prefix('organisation-types')->name('organisation-types.')->group(function () {
            // Routes CRUD principales
            Route::get('/', [OrganisationTypeController::class, 'index'])->name('index');
            Route::get('/create', [OrganisationTypeController::class, 'create'])->name('create');
            Route::post('/', [OrganisationTypeController::class, 'store'])->name('store');
            Route::get('/{id}', [OrganisationTypeController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [OrganisationTypeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [OrganisationTypeController::class, 'update'])->name('update');
            Route::delete('/{id}', [OrganisationTypeController::class, 'destroy'])->name('destroy');
            
            // Gestion des documents requis
            Route::post('/{id}/documents', [OrganisationTypeController::class, 'attachDocuments'])->name('documents.attach');
            Route::delete('/{id}/documents/{docId}', [OrganisationTypeController::class, 'detachDocument'])->name('documents.detach');
            
            // Réorganisation
            Route::post('/reorder', [OrganisationTypeController::class, 'reorder'])->name('reorder');
        });
        
        // ========================================
        // âœ¨ MODULE : TYPES DE DOCUMENTS
        // ========================================
        Route::prefix('document-types')->name('document-types.')->group(function () {
            // Routes CRUD principales
            Route::get('/', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'update'])->name('update');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'destroy'])->name('destroy');
            
            // Réorganisation
            Route::post('/reorder', [\App\Http\Controllers\Admin\DocumentTypeController::class, 'reorder'])->name('reorder');
        });


        // CRUD Types d'organisations (anciennes routes - gardées pour compatibilité)
        Route::prefix('types')->name('types.')->group(function () {
            Route::get('/', [ReferentielController::class, 'typesIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'typesStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'typesUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'typesDestroy'])->name('destroy');
            Route::post('/reorder', [ReferentielController::class, 'typesReorder'])->name('reorder');
        });
        
        // CRUD Types de documents
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [ReferentielController::class, 'documentsIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'documentsStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'documentsUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'documentsDestroy'])->name('destroy');
        });
        
        // CRUD Zones géographiques
        Route::prefix('zones')->name('zones.')->group(function () {
            Route::get('/', [ReferentielController::class, 'zonesIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'zonesStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'zonesUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'zonesDestroy'])->name('destroy');
            Route::get('/provinces/{province}/departements', [ReferentielController::class, 'getDepartements'])->name('provinces.departements');
            Route::get('/departements/{departement}/communes', [ReferentielController::class, 'getCommunes'])->name('departements.communes');
        });
        
        // Statuts des dossiers/organisations
        Route::prefix('statuts')->name('statuts.')->group(function () {
            Route::get('/', [ReferentielController::class, 'statutsIndex'])->name('index');
            Route::post('/', [ReferentielController::class, 'statutsStore'])->name('store');
            Route::put('/{id}', [ReferentielController::class, 'statutsUpdate'])->name('update');
            Route::delete('/{id}', [ReferentielController::class, 'statutsDestroy'])->name('destroy');
        });
        
        // Workflow et étapes de validation
        Route::prefix('workflow')->name('workflow.')->group(function () {
            Route::get('/', [ReferentielController::class, 'workflowIndex'])->name('index');
            Route::post('/steps', [ReferentielController::class, 'workflowStepStore'])->name('steps.store');
            Route::put('/steps/{id}', [ReferentielController::class, 'workflowStepUpdate'])->name('steps.update');
            Route::delete('/steps/{id}', [ReferentielController::class, 'workflowStepDestroy'])->name('steps.destroy');
            Route::post('/steps/reorder', [ReferentielController::class, 'workflowStepsReorder'])->name('steps.reorder');
        });
    });
    
    /*
    |--------------------------------------------------------------------------
    | 🛡️ GESTION DES RÔLES - VERSION COMPLÈTE ET CORRIGÉE
    |--------------------------------------------------------------------------
    */
    Route::prefix('roles')->name('roles.')->group(function () {
        // Routes CRUD principales
        Route::get('/', [RolesController::class, 'index'])->name('index');
        Route::get('/create', [RolesController::class, 'create'])->name('create');
        Route::post('/', [RolesController::class, 'store'])->name('store');
        Route::get('/{id}', [RolesController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [RolesController::class, 'edit'])->name('edit');
        Route::put('/{id}', [RolesController::class, 'update'])->name('update');
        Route::delete('/{id}', [RolesController::class, 'destroy'])->name('destroy');
        
        // ✅ ROUTES PERMISSIONS CORRIGÉES
        Route::get('/{id}/permissions', [RolesController::class, 'permissions'])->name('permissions');
        Route::put('/{id}/permissions', [RolesController::class, 'updatePermissions'])->name('permissions.update');
        
        // ✅ ROUTES MANQUANTES AJOUTÉES
        Route::get('/search', [RolesController::class, 'search'])->name('search');
        Route::post('/bulk-operations', [RolesController::class, 'bulkOperations'])->name('bulk-operations');
        Route::post('/{id}/duplicate', [RolesController::class, 'duplicate'])->name('duplicate');
        Route::patch('/{id}/toggle-status', [RolesController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/validate-name', [RolesController::class, 'validateName'])->name('validate-name');
        Route::get('/export', [RolesController::class, 'export'])->name('export');
        Route::post('/init-system-roles', [RolesController::class, 'initSystemRoles'])->name('init-system');
        
        // Actions spécialisées
        Route::get('/{id}/users', [RolesController::class, 'getUsers'])->name('users');
        Route::get('/{id}/stats', [RolesController::class, 'getStats'])->name('stats');
        
        // ✅ ROUTES UTILITAIRES
        Route::get('/matrix', [RolesController::class, 'matrix'])->name('matrix');
    });

    /*
    |--------------------------------------------------------------------------
    | 🔒 GESTION DES PERMISSIONS - VERSION COMPLÈTE SANS CONFLITS
    |--------------------------------------------------------------------------
    */
    Route::prefix('permissions')->name('permissions.')->group(function () {
        // Routes CRUD principales
        Route::get('/', [PermissionsController::class, 'index'])->name('index');
        Route::get('/create', [PermissionsController::class, 'create'])->name('create');
        Route::post('/', [PermissionsController::class, 'store'])->name('store');
        Route::get('/{id}', [PermissionsController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PermissionsController::class, 'edit'])->name('edit');
        Route::put('/{id}', [PermissionsController::class, 'update'])->name('update');
        Route::delete('/{id}', [PermissionsController::class, 'destroy'])->name('destroy');
        
        // Actions spéciales pour permissions système
        Route::post('/init-system-permissions', [PermissionsController::class, 'initSystemPermissions'])->name('init-system-permissions');
        Route::get('/export', [PermissionsController::class, 'export'])->name('export');
        
        // Opérations en lot
        Route::post('/bulk-delete', [PermissionsController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/bulk-assign', [PermissionsController::class, 'bulkAssign'])->name('bulk-assign');
        
        // API pour autocomplete et recherche
        Route::get('/api/search', [PermissionsController::class, 'apiSearch'])->name('api.search');
        Route::get('/api/categories', [PermissionsController::class, 'apiCategories'])->name('api.categories');
        Route::post('/validate-name', [PermissionsController::class, 'validateName'])->name('validate-name');
        
        // Statistiques et rapports
        Route::get('/stats/dashboard', [PermissionsController::class, 'statsDashboard'])->name('stats.dashboard');
        Route::get('/stats/usage', [PermissionsController::class, 'statsUsage'])->name('stats.usage');
        
        // Gestion des rôles-permissions
        Route::prefix('role-permissions')->name('role-permissions.')->group(function () {
            Route::get('/matrix', [PermissionsController::class, 'permissionMatrix'])->name('matrix');
            Route::post('/assign-bulk', [PermissionsController::class, 'assignBulkPermissions'])->name('assign-bulk');
            Route::post('/revoke-bulk', [PermissionsController::class, 'revokeBulkPermissions'])->name('revoke-bulk');
        });

        // Cache et maintenance
        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::post('/clear-cache', [PermissionsController::class, 'clearCache'])->name('clear-cache');
            Route::post('/rebuild-cache', [PermissionsController::class, 'rebuildCache'])->name('rebuild-cache');
            Route::get('/health-check', [PermissionsController::class, 'healthCheck'])->name('health-check');
        });
        
        // Routes de consultation avancées
        Route::get('/all', [PermissionsController::class, 'getAllPermissions'])->name('all');
        Route::get('/by-category', [PermissionsController::class, 'getByCategory'])->name('by-category');
        Route::get('/by-risk', [PermissionsController::class, 'getByRisk'])->name('by-risk');
        Route::get('/search', [PermissionsController::class, 'search'])->name('search');
        Route::get('/unused', [PermissionsController::class, 'getUnusedPermissions'])->name('unused');
        Route::get('/most-used', [PermissionsController::class, 'getMostUsedPermissions'])->name('most-used');
        
        // Relations et statistiques
        Route::get('/{id}/roles', [PermissionsController::class, 'getRoles'])->name('roles');
        Route::get('/{id}/users', [PermissionsController::class, 'getUsers'])->name('users');
        Route::get('/{id}/stats', [PermissionsController::class, 'getStats'])->name('stats');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 📝 GESTION DU CONTENU PUBLIC
    |--------------------------------------------------------------------------
    */
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/actualites', [ContentController::class, 'actualites'])->name('actualites');
        Route::get('/documents', [ContentController::class, 'documents'])->name('documents');
        Route::get('/faq', [ContentController::class, 'faq'])->name('faq');
        Route::get('/actualites/create', [ContentController::class, 'createActualite'])->name('actualites.create');
        Route::post('/documents/upload', [ContentController::class, 'uploadDocument'])->name('documents.upload');
        Route::post('/faq/create', [ContentController::class, 'createFaq'])->name('faq.create');
    });

    /*
    |--------------------------------------------------------------------------
    | 📊 STATISTIQUES ET RAPPORTS
    |--------------------------------------------------------------------------
    */
    Route::prefix('statistiques')->name('statistiques.')->group(function () {
        Route::get('/', [DashboardController::class, 'statistiques'])->name('index');
        Route::get('/dashboard-data', [DashboardController::class, 'getDashboardData'])->name('dashboard-data');
        Route::get('/organisations', [DashboardController::class, 'statsOrganisations'])->name('organisations');
        Route::get('/dossiers', [DashboardController::class, 'statsDossiers'])->name('dossiers');
        Route::get('/utilisateurs', [DashboardController::class, 'statsUtilisateurs'])->name('utilisateurs');
        Route::get('/activite', [DashboardController::class, 'statsActivite'])->name('activite');
        Route::get('/export/global', [DashboardController::class, 'exportGlobal'])->name('export.global');
        Route::post('/rapport/personnalise', [DashboardController::class, 'generateCustomReport'])->name('rapport.personnalise');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 👤 PROFIL ADMINISTRATEUR
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [SettingsController::class, 'profile'])->name('index');
        Route::put('/', [SettingsController::class, 'updateProfile'])->name('update');
        Route::put('/password', [SettingsController::class, 'updatePassword'])->name('password');
        Route::post('/avatar', [SettingsController::class, 'updateAvatar'])->name('avatar');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 📋 JOURNALISATION ET AUDIT
    |--------------------------------------------------------------------------
    */
    Route::prefix('logs')->name('logs.')->group(function () {
        Route::get('/', [DashboardController::class, 'logs'])->name('index');
        Route::get('/search', [DashboardController::class, 'searchLogs'])->name('search');
        Route::get('/export', [DashboardController::class, 'exportLogs'])->name('export');
        Route::delete('/clean', [DashboardController::class, 'cleanOldLogs'])->name('clean');
        Route::get('/stats', [DashboardController::class, 'logsStats'])->name('stats');
    });
    
    /*
    |--------------------------------------------------------------------------
    | 📄 OPERATIONS EN LOT
    |--------------------------------------------------------------------------
    */
    Route::prefix('batch')->name('batch.')->group(function () {
        Route::post('/assign-multiple', [DossierController::class, 'batchAssign'])->name('assign-multiple');
        Route::post('/change-status', [DossierController::class, 'batchChangeStatus'])->name('change-status');
        Route::post('/add-comment', [DossierController::class, 'batchAddComment'])->name('add-comment');
        Route::post('/export-selected', [DossierController::class, 'batchExport'])->name('export-selected');
        Route::post('/users/activate', [UserManagementController::class, 'batchActivate'])->name('users.activate');
        Route::post('/users/deactivate', [UserManagementController::class, 'batchDeactivate'])->name('users.deactivate');
        Route::post('/users/change-role', [UserManagementController::class, 'batchChangeRole'])->name('users.change-role');
    });
});

/*
|--------------------------------------------------------------------------
| 🔗 Routes API Admin pour les interfaces AJAX - VERSION SANS CONFLITS
|--------------------------------------------------------------------------
*/
Route::prefix('api/admin')->name('api.admin.')->middleware(['auth', 'admin'])->group(function () {
    // Recherche rapide
    Route::get('/search/{type}', [DashboardController::class, 'quickSearch'])->name('search');
    Route::get('/stats/realtime', [DashboardController::class, 'realtimeStats'])->name('stats.realtime');
    Route::get('/system/health', [DashboardController::class, 'systemHealth'])->name('system.health');
    
    // Gestion des verrous en temps réel
    Route::get('/locks/list', [DossierController::class, 'listActiveLocks'])->name('locks.list');
    Route::post('/locks/{lock}/release', [DossierController::class, 'releaseLock'])->name('locks.release');
    
    // Notifications
    Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount'])->name('notifications.count');

    // API AJAX pour dossiers
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/search', [DossierController::class, 'apiSearch'])->name('search');
        Route::get('/validation-status/{id}', [DossierController::class, 'validationStatus'])->name('validation-status');
        Route::post('/quick-action/{id}', [DossierController::class, 'quickAction'])->name('quick-action');
    });

    // API pour permissions - NOMS UNIQUES
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/search', [PermissionsController::class, 'apiSearch'])->name('search');
        Route::get('/categories', [PermissionsController::class, 'apiCategories'])->name('categories');
        Route::post('/validate-name', [PermissionsController::class, 'validateName'])->name('validate-name');
        Route::get('/stats', [PermissionsController::class, 'statsDashboard'])->name('stats');
        Route::post('/clear-cache', [PermissionsController::class, 'clearCache'])->name('clear-cache');
    });

    // API générales
    Route::get('/agents/available', [UserManagementController::class, 'availableAgents'])->name('agents.available');
    Route::get('/organisations/search', [DossierController::class, 'searchOrganisations'])->name('organisations.search');
    Route::get('/stats/dossiers', [AnalyticsController::class, 'dossiersStats'])->name('stats.dossiers');
    Route::get('/stats/agents', [AnalyticsController::class, 'agentsStats'])->name('stats.agents');
    Route::get('/stats/performance', [AnalyticsController::class, 'performanceStats'])->name('stats.performance');
    
    // API notifications
    Route::get('/notifications/unread', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    
    // API validation NIP
    Route::post('/validate-nip', function(\Illuminate\Http\Request $request) {
        return response()->json(['valid' => true, 'message' => 'NIP valide']);
    })->name('validate-nip');

    // API Géolocalisation - Provinces
    Route::prefix('provinces')->name('provinces.')->group(function () {
        Route::get('/', [ProvinceController::class, 'api'])->name('index');
        Route::get('/search', [ProvinceController::class, 'search'])->name('search');
        Route::get('/{province}', [ProvinceController::class, 'apiShow'])->name('show');
        Route::get('/{province}/stats', [ProvinceController::class, 'apiStats'])->name('stats');
        Route::get('/cached', [ProvinceController::class, 'apiCached'])->name('cached');
    });
});

// Routes pour la matrice des permissions
Route::prefix('permissions')->name('admin.permissions.')->group(function () {
    // Vue principale de la matrice
    Route::get('/matrix', [PermissionMatrixController::class, 'index'])->name('matrix');
    
    // API pour charger les données de la matrice
    Route::post('/matrix/data', [PermissionMatrixController::class, 'data'])->name('matrix.data');
    
    // API pour mettre à jour les associations
    Route::post('/matrix/update', [PermissionMatrixController::class, 'update'])->name('matrix.update');
    
    // API pour l'audit des permissions
    Route::post('/matrix/audit', [PermissionMatrixController::class, 'audit'])->name('matrix.audit');
    
    // Export de la matrice
    Route::post('/matrix/export', [PermissionMatrixController::class, 'export'])->name('matrix.export');
});

/*
|--------------------------------------------------------------------------
| 🗺️ GESTION DE LA GÉOLOCALISATION - SECTION COMPLÈTE
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
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
            Route::get('/{province}/organisations', [ProvinceController::class, 'organisations'])->name('organisations');
            Route::get('/{province}/adherents', [ProvinceController::class, 'adherents'])->name('adherents');
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
            Route::get('/{departement}/communes', [DepartementController::class, 'communesVilles'])->name('communes');
            Route::get('/{departement}/cantons', [DepartementController::class, 'cantons'])->name('cantons');
            Route::get('/by-province/{province}', [DepartementController::class, 'byProvince'])->name('by-province');
        });

        // COMMUNES/VILLES
        Route::prefix('communes')->name('communes.')->group(function () {
            Route::get('/', [CommuneVilleController::class, 'index'])->name('index');
            Route::get('/create', [CommuneVilleController::class, 'create'])->name('create');
            Route::post('/', [CommuneVilleController::class, 'store'])->name('store');
            Route::get('/{communeVille}', [CommuneVilleController::class, 'show'])->name('show');
            Route::get('/{communeVille}/edit', [CommuneVilleController::class, 'edit'])->name('edit');
            Route::put('/{communeVille}', [CommuneVilleController::class, 'update'])->name('update');
            Route::delete('/{communeVille}', [CommuneVilleController::class, 'destroy'])->name('destroy');
            Route::get('/export', [CommuneVilleController::class, 'export'])->name('export');
            Route::patch('/{commune}/toggle-status', [CommuneVilleController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/bulk-action', [CommuneVilleController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/{commune}/arrondissements', [CommuneVilleController::class, 'arrondissements'])->name('arrondissements');
            Route::get('/{commune}/localites', [CommuneVilleController::class, 'localites'])->name('localites');
            Route::get('/by-departement/{departement}', [CommuneVilleController::class, 'byDepartement'])->name('by-departement');
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
            Route::get('/{arrondissement}/localites', [ArrondissementController::class, 'localites'])->name('localites');
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
            Route::get('/{canton}/localites', [CantonController::class, 'localites'])->name('localites');
            Route::get('/by-departement/{departement}', [CantonController::class, 'byDepartement'])->name('by-departement');
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
| MODULE GÉNÉRATION DE DOCUMENTS
|--------------------------------------------------------------------------
| Routes pour la gestion des templates de documents et des documents générés
| Ajouté le : 21/01/2025
*/

// ========================================
// ROUTES ADMIN - TEMPLATES DE DOCUMENTS
// ========================================
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    
    // Gestion des templates de documents
    Route::prefix('document-templates')->name('document-templates.')->group(function () {
        
        // CRUD des templates
        Route::get('/', [DocumentTemplateController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTemplateController::class, 'create'])->name('create');
        Route::post('/', [DocumentTemplateController::class, 'store'])->name('store');
        Route::get('/{documentTemplate}', [DocumentTemplateController::class, 'show'])->name('show');
        Route::get('/{documentTemplate}/edit', [DocumentTemplateController::class, 'edit'])->name('edit');
        Route::put('/{documentTemplate}', [DocumentTemplateController::class, 'update'])->name('update');
        Route::delete('/{documentTemplate}', [DocumentTemplateController::class, 'destroy'])->name('destroy');
        
        // Prévisualisation d'un template
        Route::get('/{documentTemplate}/preview', [DocumentTemplateController::class, 'preview'])->name('preview');
        
        // AJAX : Charger les workflow steps selon organisation/opération
        Route::get('/ajax/workflow-steps', [DocumentTemplateController::class, 'getWorkflowSteps'])->name('ajax.workflow-steps');
    });

    // ========================================
    // ROUTES ADMIN - DOCUMENTS GÉNÉRÉS
    // ========================================
    Route::prefix('documents')->name('documents.')->group(function () {
        
        // Liste et historique des documents générés
        Route::get('/', [GeneratedDocumentController::class, 'index'])->name('index');
        
        // Formulaire de génération manuelle
        Route::get('/create', [GeneratedDocumentController::class, 'create'])->name('create');
        
        // Générer un document manuellement
        Route::post('/generate', [GeneratedDocumentController::class, 'generate'])->name('generate');
        
        // Voir les détails d'un document généré
        Route::get('/{generation}', [GeneratedDocumentController::class, 'show'])->name('show');
        
        // Télécharger (régénérer) un document
        Route::get('/{generation}/download', [GeneratedDocumentController::class, 'download'])->name('download');
        
        // Invalider un document
        Route::put('/{generation}/invalidate', [GeneratedDocumentController::class, 'invalidate'])->name('invalidate');
        
        // Réactiver un document invalidé
        Route::put('/{generation}/reactivate', [GeneratedDocumentController::class, 'reactivate'])->name('reactivate');
        
        // Export CSV des documents
        Route::get('/export/csv', [GeneratedDocumentController::class, 'export'])->name('export');
        
        // AJAX : Charger les templates pour une organisation
        Route::get('/ajax/templates-for-organisation', [GeneratedDocumentController::class, 'getTemplatesForOrganisation'])
            ->name('ajax.templates-for-organisation');
    });
});

// ========================================
// ROUTES PUBLIQUES - VÉRIFICATION DE DOCUMENTS
// ========================================
Route::prefix('document-verify')->name('public.document.')->group(function () {
    
    // Page d'accueil de vérification
    Route::get('/', [DocumentVerificationController::class, 'index'])->name('index');
    
    // Vérifier un document par token (QR code)
    Route::get('/{token}', [DocumentVerificationController::class, 'verify'])->name('verify');
    
    // Recherche manuelle par numéro de document
    Route::post('/search', [DocumentVerificationController::class, 'search'])->name('search');
    
    // Télécharger un document vérifié (optionnel)
    Route::get('/{token}/download', [DocumentVerificationController::class, 'download'])->name('download');
    
    // Page d'aide
    Route::get('/help/guide', [DocumentVerificationController::class, 'help'])->name('help');
    
    // Signaler un document suspect
    Route::post('/report', [DocumentVerificationController::class, 'report'])->name('report');
});

// ========================================
// API PUBLIQUE - VÉRIFICATION DE DOCUMENTS
// ========================================
Route::prefix('api')->name('api.')->group(function () {
    
    // Vérifier un document (format JSON)
    Route::get('/verify-document/{token}', [DocumentVerificationController::class, 'apiVerify'])
        ->name('verify-document');
});

// Limiter les vérifications publiques : 60 par minute par IP
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/verify/{token}', [DocumentVerificationController::class, 'verify']);
    Route::post('/verify/search', [DocumentVerificationController::class, 'search']);
});

// Limiter les téléchargements : 20 par minute par IP
Route::middleware('throttle:20,1')->group(function () {
    Route::get('/verify/{token}/download', [DocumentVerificationController::class, 'download']);
});