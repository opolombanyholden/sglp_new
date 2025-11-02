<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicControllers\HomeController;
use App\Http\Controllers\PublicControllers\ActualiteController;
use App\Http\Controllers\PublicControllers\DocumentController;
use App\Http\Controllers\PublicControllers\AnnuaireController;
use App\Http\Controllers\PublicControllers\DocumentVerificationController;
use App\Http\Controllers\Operator\ProfileController;
use App\Http\Controllers\Operator\DossierController;
use App\Http\Controllers\Operator\OrganisationController;
use App\Http\Controllers\Operator\AdherentController;
use App\Http\Controllers\Operator\ChunkingController;
use App\Http\Controllers\Operator\DocumentController as OperatorDocumentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\Admin\NipDatabaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Admin\DossierController as AdminDossierController;
use App\Http\Controllers\Operator\DeclarationController;
use App\Http\Controllers\Operator\MessageController;

/*
|--------------------------------------------------------------------------
| Routes Web Publiques
|--------------------------------------------------------------------------
*/

// Page d'accueil
Route::get('/', [HomeController::class, 'index'])->name('home');

// Pages d'information
Route::get('/a-propos', [HomeController::class, 'about'])->name('about');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/guides', [HomeController::class, 'guides'])->name('guides');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'sendContact'])->name('contact.send');

// Actualités
Route::prefix('actualites')->name('actualites.')->group(function () {
    Route::get('/', [ActualiteController::class, 'index'])->name('index');
    Route::get('/{slug}', [ActualiteController::class, 'show'])->name('show');
});

// Documents et ressources
Route::prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    Route::get('/download/{id}', [DocumentController::class, 'download'])->name('download');
});

// Annuaire des organisations
Route::prefix('annuaire')->name('annuaire.')->group(function () {
    Route::get('/', [AnnuaireController::class, 'index'])->name('index');
    Route::get('/associations', [AnnuaireController::class, 'associations'])->name('associations');
    Route::get('/ong', [AnnuaireController::class, 'ong'])->name('ong');
    Route::get('/partis-politiques', [AnnuaireController::class, 'partisPolitiques'])->name('partis');
    Route::get('/confessions-religieuses', [AnnuaireController::class, 'confessionsReligieuses'])->name('confessions');
    Route::get('/{type}/{slug}', [AnnuaireController::class, 'show'])->name('show');
});

// Calendrier des événements
Route::get('/calendrier', [HomeController::class, 'calendrier'])->name('calendrier');

/*
|--------------------------------------------------------------------------
| Routes de vérification QR Code (publiques)
|--------------------------------------------------------------------------
*/

Route::get('/verify/{type}/{code}', function($type, $code) {
    try {
        $qrService = new App\Services\QrCodeService();
        $result = $qrService->verifyCode($type, $code);
        
        if ($result['valid']) {
            return view('public.qr-verification-success', [
                'result' => $result,
                'type' => $type,
                'data' => $result['data']
            ]);
        } else {
            return view('public.qr-verification-error', [
                'result' => $result,
                'message' => $result['message']
            ]);
        }
    } catch (\Exception $e) {
        \Log::error('Erreur vérification QR Code: ' . $e->getMessage());
        
        return view('public.qr-verification-error', [
            'result' => ['valid' => false],
            'message' => 'Erreur lors de la vérification du code'
        ]);
    }
})->name('public.verify');

// Route API pour vérification AJAX
Route::get('/api/verify/{type}/{code}', function($type, $code) {
    try {
        $qrService = new App\Services\QrCodeService();
        $result = $qrService->verifyCode($type, $code);
        
        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([
            'valid' => false,
            'message' => 'Erreur lors de la vérification'
        ], 500);
    }
})->name('api.verify');

/*
|--------------------------------------------------------------------------
| ROUTES PUBLIQUES - VÉRIFICATION DE DOCUMENTS (MODULE DOCUMENTS)
|--------------------------------------------------------------------------
| Routes publiques pour vérifier l'authenticité des documents générés
| ✅ Ajouté le : 28/10/2025
| ✅ Rate limiting : 60 requêtes par minute par IP
| ✅ Throttle téléchargements : 20 par minute par IP
|--------------------------------------------------------------------------
*/

Route::prefix('document-verify')->name('public.document.')->middleware(['throttle:60,1'])->group(function () {
    
    // Page d'accueil de vérification
    Route::get('/', [DocumentVerificationController::class, 'index'])->name('index');
    
    // Page d'aide et guide
    Route::get('/help/guide', [DocumentVerificationController::class, 'help'])->name('help');
    
    // Statistiques publiques (sans données sensibles)
    Route::get('/stats', [DocumentVerificationController::class, 'stats'])->name('stats');
    
    // Widget iframe embarquable
    Route::get('/widget', [DocumentVerificationController::class, 'widget'])->name('widget');
    
    // Vérifier un document (POST formulaire)
    Route::post('/check', [DocumentVerificationController::class, 'check'])->name('check');
    
    // Vérifier un document par token (QR code ou URL)
    Route::get('/{token}', [DocumentVerificationController::class, 'verify'])->name('verify');
    
    // Vérifier par QR Code scan
    Route::post('/qr', [DocumentVerificationController::class, 'verifyQr'])->name('verify-qr');
    
    // Recherche manuelle par numéro de document
    Route::post('/search', [DocumentVerificationController::class, 'search'])->name('search');
    
    // Signaler un document suspect
    Route::post('/report', [DocumentVerificationController::class, 'report'])->name('report');
});

// Téléchargement de documents vérifiés (rate limit plus strict)
Route::middleware(['throttle:20,1'])->group(function () {
    Route::get('/document-verify/{token}/download', [DocumentVerificationController::class, 'download'])
        ->name('public.document.download');
});

// Info document sans enregistrer de log (pour prévisualisation)
Route::get('/document-info/{token}', [DocumentVerificationController::class, 'documentInfo'])
    ->name('public.document.info')
    ->middleware(['throttle:60,1']);

/*
|--------------------------------------------------------------------------
| Routes d'authentification
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Routes Admin - VERSION MINIMALE (ROUTES DÉTAILLÉES DANS admin.php)
|--------------------------------------------------------------------------
| ⚠️ Les routes détaillées admin sont dans admin.php
| ⚠️ Seul le dashboard principal est défini ici pour éviter les conflits
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard principal uniquement
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    /*
    |--------------------------------------------------------------------------
    | Routes Admin - Gestion des Dossiers et Organisations
    |--------------------------------------------------------------------------
    | ✅ Ajouté le : 28/10/2025
    | Ces routes permettent la gestion complète des dossiers et organisations
    |--------------------------------------------------------------------------
    */
    
    // Liste des dossiers/organisations
    Route::get('/organisations', [AdminDossierController::class, 'index'])->name('organisations.index');
    
    // Dossiers en attente
    Route::get('/dossiers/en-attente', [AdminDossierController::class, 'enAttente'])->name('dossiers.en-attente');
    
    // Détail d'un dossier
    Route::get('/dossiers/{id}', [AdminDossierController::class, 'show'])->name('dossiers.show');
    
    // Assigner un dossier
    Route::post('/dossiers/{id}/assign', [AdminDossierController::class, 'assign'])->name('dossiers.assign');
    
    // Valider un dossier
    Route::post('/dossiers/{id}/validate', [AdminDossierController::class, 'validate'])->name('dossiers.validate');
    
    // Rejeter un dossier
    Route::post('/dossiers/{id}/reject', [AdminDossierController::class, 'reject'])->name('dossiers.reject');
    
    // Demander des compléments
    Route::post('/dossiers/{id}/request-supplement', [AdminDossierController::class, 'requestSupplement'])->name('dossiers.request-supplement');
    
    // Télécharger l'accusé de réception
    Route::get('/dossiers/{id}/accuse-reception', [AdminDossierController::class, 'downloadAccuseReception'])->name('dossiers.accuse-reception');
    
    // Télécharger le récépissé provisoire
    Route::get('/dossiers/{id}/recepisse-provisoire', [AdminDossierController::class, 'downloadRecepisseProvisoire'])->name('dossiers.recepisse-provisoire');
    
    // Télécharger le récépissé définitif
    Route::get('/dossiers/{id}/recepisse-definitif', [AdminDossierController::class, 'downloadRecepisseDefinitif'])->name('dossiers.recepisse-definitif');
    
    /*
    |--------------------------------------------------------------------------
    | Routes Admin - Analytics et Statistiques
    |--------------------------------------------------------------------------
    */
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
    });
});

/*
|--------------------------------------------------------------------------
| Routes Opérateur - ROUTES DE BASE (DÉTAILS DANS operator.php)
|--------------------------------------------------------------------------
| ⚠️ Les routes détaillées operator sont dans operator.php
| ⚠️ Seules les routes essentielles sont définies ici
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'operator'])->prefix('operator')->name('operator.')->group(function () {
    
    // Profil operator
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Gestion des organisations
    Route::resource('organisations', OrganisationController::class)->except(['index']);
    Route::get('/organisations', [OrganisationController::class, 'index'])->name('organisations.index');
    
    // Page de confirmation après création
    Route::get('/confirmation/{dossier}', [DossierController::class, 'confirmation'])->name('confirmation');
    
    // Gestion des dossiers
    Route::resource('dossiers', DossierController::class);
    Route::get('/dossiers/anomalies', [DossierController::class, 'anomalies'])->name('dossiers.anomalies');
    Route::post('/dossiers/anomalies/resolve/{adherent}', [DossierController::class, 'resolveAnomalie'])->name('dossiers.anomalies.resolve');
    
    // Gestion des adhérents
    Route::prefix('adherents')->name('adherents.')->group(function () {
        Route::post('/import', [AdherentController::class, 'import'])->name('import');
        Route::post('/import/chunked', [AdherentController::class, 'importChunked'])->name('import.chunked');
        Route::post('/validate-nip', [AdherentController::class, 'validateNip'])->name('validate-nip');
        Route::post('/check-duplicate', [AdherentController::class, 'checkDuplicate'])->name('check-duplicate');
        Route::get('/template/excel', [AdherentController::class, 'downloadExcelTemplate'])->name('template.excel');
    });
    
    // Chunking pour imports volumineux
    Route::prefix('chunking')->name('chunking.')->group(function () {
        Route::post('/init', [ChunkingController::class, 'init'])->name('init');
        Route::post('/upload-chunk', [ChunkingController::class, 'uploadChunk'])->name('upload-chunk');
        Route::post('/finalize', [ChunkingController::class, 'finalize'])->name('finalize');
        Route::post('/cancel', [ChunkingController::class, 'cancel'])->name('cancel');
        Route::get('/status/{sessionId}', [ChunkingController::class, 'status'])->name('status');
    });
    
    // Upload de documents
    Route::post('/upload-document', function (Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'type' => 'required|string',
            'dossier_id' => 'nullable|exists:dossiers,id'
        ]);
        
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('documents/operators', $fileName, 'public');
        
        return response()->json([
            'success' => true,
            'file_path' => $path,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'file_type' => $file->getMimeType(),
            'message' => 'Document uploadé avec succès'
        ]);
    })->name('upload-document');
    
    // Génération exemples NIP
    Route::get('/generate-nip-example', function () {
        try {
            $examples = [];
            $prefixes = ['A1', 'B2', 'C3', '1A', '2B', '3C'];
            $sequences = ['0001', '1234', '5678', '9999'];

            foreach (range(1, 5) as $i) {
                $prefix = $prefixes[array_rand($prefixes)];
                $sequence = $sequences[array_rand($sequences)];
                $year = rand(1960, 2005);
                $month = rand(1, 12);
                $day = rand(1, 28);
                $dateStr = sprintf('%04d%02d%02d', $year, $month, $day);
                $example = $prefix . '-' . $sequence . '-' . $dateStr;

                $examples[] = [
                    'nip' => $example,
                    'prefix' => $prefix,
                    'sequence' => $sequence,
                    'birth_date' => sprintf('%04d-%02d-%02d', $year, $month, $day),
                    'age' => now()->diffInYears(\Carbon\Carbon::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $month, $day)))
                ];
            }

            return response()->json([
                'success' => true,
                'examples' => $examples,
                'format' => 'XX-QQQQ-YYYYMMDD',
                'description' => [
                    'XX' => '2 caractères alphanumériques',
                    'QQQQ' => '4 chiffres',
                    'YYYYMMDD' => 'Date de naissance (ANNÉE MOIS JOUR)'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur génération exemples',
                'error' => $e->getMessage()
            ], 500);
        }
    })->name('generate-nip-example');
});

/*
|--------------------------------------------------------------------------
| Routes pour gestion CSRF et diagnostics
|--------------------------------------------------------------------------
*/
Route::get('/csrf-token', function () {
    return response()->json([
        'token' => csrf_token(),
        'csrf_token' => csrf_token(),
        'expires_at' => now()->addMinutes(config('session.lifetime'))->toISOString(),
        'timestamp' => now()->toISOString(),
        'session_lifetime' => config('session.lifetime')
    ]);
})->middleware('web');

// Route de diagnostic CSRF (pour debug uniquement)
Route::get('/csrf-debug', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'session_driver' => config('session.driver'),
        'session_lifetime' => config('session.lifetime'),
        'session_cookie' => config('session.cookie'),
        'app_key_set' => !empty(config('app.key')),
        'user_authenticated' => auth()->check(),
        'user_id' => auth()->id(),
        'middleware_applied' => 'web',
        'timestamp' => now()->toISOString()
    ]);
})->middleware('web');

// Route de test CSRF POST
Route::post('/csrf-test', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'Token CSRF valide',
        'token_received' => $request->input('_token') ? 'Présent' : 'Absent',
        'timestamp' => now()->toISOString()
    ]);
})->middleware('web');

/*
|--------------------------------------------------------------------------
| 🔒 ROUTES DE DIAGNOSTIC - CHUNKING
|--------------------------------------------------------------------------
*/
Route::prefix('operator/diagnostic')->name('operator.diagnostic.')->middleware(['web', 'auth', 'operator'])->group(function () {
    
    // Test de santé système
    Route::get('/health', function() {
        return response()->json([
            'status' => 'OK',
            'timestamp' => now()->toISOString(),
            'user' => auth()->user()->email ?? 'N/A',
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token()
        ]);
    })->name('health');
    
    // Statistiques des verrous
    Route::get('/verrous/status', function() {
        $stats = \Illuminate\Support\Facades\Cache::get('chunk_locks_stats', [
            'total_locks' => 0,
            'active_locks' => 0,
            'expired_locks' => 0,
            'last_cleanup' => 'Jamais'
        ]);
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
            'verrous_actifs' => cache()->get('active_chunk_locks', []),
            'dernier_nettoyage' => cache('locks_last_cleanup', 'Jamais')
        ]);
    })->name('verrous.status');
});

// Routes de test (développement uniquement)
if (config('app.debug')) {
    Route::get('/test', function () {
        return [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => config('app.env'),
            'database_connected' => DB::connection()->getPdo() ? 'Yes' : 'No',
            'current_user' => auth()->check() ? auth()->user()->email : 'Non connecté',
        ];
    })->name('test');
    
    Route::get('/create-test-users', function () {
        \App\Models\User::firstOrCreate(
            ['email' => 'operator@pngdi.ga'],
            [
                'name' => 'Jean NGUEMA',
                'password' => bcrypt('operator123'),
                'role' => 'operator',
                'phone' => '+24101234569',
                'city' => 'Port-Gentil',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        return 'Utilisateur de test créé !<br><strong>Opérateur :</strong> operator@pngdi.ga / operator123<br><a href="/login">Se connecter</a>';
    })->name('create-test-users');
}

/*
|--------------------------------------------------------------------------
| Inclusion des fichiers de routes supplémentaires
|--------------------------------------------------------------------------
| ⚠️ IMPORTANT : Ces fichiers contiennent les routes détaillées
| ⚠️ Les routes de base ci-dessus servent de fallback
|--------------------------------------------------------------------------
*/

// Inclure les routes admin détaillées

// ✅ CORRECTION : admin.php est chargé automatiquement par RouteServiceProvider.php
// Ne pas le charger ici pour éviter les conflits de namespace
// Voir : app/Providers/RouteServiceProvider.php ligne 52


/*
|--------------------------------------------------------------------------
| âœ… ROUTES OPERATOR PRINCIPALES - À AJOUTER DANS web.php
|--------------------------------------------------------------------------
| Ces routes doivent être ajoutées dans web.php AVANT le require operator.php
| Créé le : 01 Novembre 2025
| Ces sont les routes PRINCIPALES référencées dans layouts/operator.blade.php
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| ROUTES OPERATOR - SECTION PRINCIPALE
|--------------------------------------------------------------------------
| Middleware : web, auth, verified, operator
| Ces routes constituent le cÅ"ur de l'interface opérateur
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth', 'verified', 'operator'])->prefix('operator')->name('operator.')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ  DASHBOARD
    |--------------------------------------------------------------------------
    */
    Route::get('/', [ProfileController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [ProfileController::class, 'dashboard'])->name('dashboard.index');
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ"‹ DOSSIERS - CRUD PRINCIPAL
    |--------------------------------------------------------------------------
    */
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/create/{type?}', [DossierController::class, 'create'])->name('create');
        Route::post('/', [DossierController::class, 'store'])->name('store');
        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');
        Route::get('/{dossier}/edit', [DossierController::class, 'edit'])->name('edit');
        Route::put('/{dossier}', [DossierController::class, 'update'])->name('update');
        Route::delete('/{dossier}', [DossierController::class, 'destroy'])->name('destroy');
        
        // Gestion des anomalies
        Route::get('/anomalies', [DossierController::class, 'anomalies'])->name('anomalies');
        Route::post('/anomalies/resolve/{adherent}', [DossierController::class, 'resolveAnomalie'])->name('anomalies.resolve');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ'¥ ADHÃ‰RENTS / MEMBRES - CRUD PRINCIPAL
    |--------------------------------------------------------------------------
    */
    Route::prefix('members')->name('members.')->group(function () {
        Route::get('/', [AdherentController::class, 'index'])->name('index');
        Route::get('/create', [AdherentController::class, 'create'])->name('create');
        Route::post('/', [AdherentController::class, 'store'])->name('store');
        Route::get('/{adherent}', [AdherentController::class, 'show'])->name('show');
        Route::get('/{adherent}/edit', [AdherentController::class, 'edit'])->name('edit');
        Route::put('/{adherent}', [AdherentController::class, 'update'])->name('update');
        Route::delete('/{adherent}', [AdherentController::class, 'destroy'])->name('destroy');
        
        // Import Excel
        Route::get('/import', [AdherentController::class, 'import'])->name('import');
        Route::post('/import', [AdherentController::class, 'processImport'])->name('import.process');
        Route::get('/import/template', [AdherentController::class, 'downloadTemplate'])->name('import.template');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ" DOCUMENTS / FICHIERS - CRUD PRINCIPAL
    |--------------------------------------------------------------------------
    */
    Route::prefix('files')->name('files.')->group(function () {
        Route::get('/', [OperatorDocumentController::class, 'index'])->name('index');
        Route::get('/create', [OperatorDocumentController::class, 'create'])->name('create');
        Route::post('/', [OperatorDocumentController::class, 'store'])->name('store');
        Route::get('/{document}', [OperatorDocumentController::class, 'show'])->name('show');
        Route::get('/{document}/download', [OperatorDocumentController::class, 'download'])->name('download');
        Route::delete('/{document}', [OperatorDocumentController::class, 'destroy'])->name('destroy');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ"„ DÃ‰CLARATIONS ANNUELLES - CRUD PRINCIPAL
    |--------------------------------------------------------------------------
    */
    Route::prefix('declarations')->name('declarations.')->group(function () {
        Route::get('/', [DeclarationController::class, 'index'])->name('index');
        Route::get('/create', [DeclarationController::class, 'create'])->name('create');
        Route::post('/', [DeclarationController::class, 'store'])->name('store');
        Route::get('/{declaration}', [DeclarationController::class, 'show'])->name('show');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ"Š RAPPORTS D'ACTIVITÃ‰ - ROUTES PRINCIPALES
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ProfileController::class, 'reports'])->name('index');
        Route::get('/monthly', [ProfileController::class, 'monthlyReports'])->name('monthly');
        Route::get('/annual', [ProfileController::class, 'annualReports'])->name('annual');
        Route::get('/export', [ProfileController::class, 'exportReports'])->name('export');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ'° SUBVENTIONS - ROUTES PRINCIPALES
    |--------------------------------------------------------------------------
    */
    Route::prefix('grants')->name('grants.')->group(function () {
        Route::get('/', [DossierController::class, 'grants'])->name('index');
        Route::get('/create', [DossierController::class, 'createGrant'])->name('create');
        Route::post('/', [DossierController::class, 'storeGrant'])->name('store');
        Route::get('/{grant}', [DossierController::class, 'showGrant'])->name('show');
        Route::get('/my-requests', [DossierController::class, 'myGrantRequests'])->name('my-requests');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ'¬ MESSAGES - CRUD PRINCIPAL
    |--------------------------------------------------------------------------
    */
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/create', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
        
        // Actions rapides
        Route::post('/{message}/reply', [MessageController::class, 'reply'])->name('reply');
        Route::post('/{message}/forward', [MessageController::class, 'forward'])->name('forward');
        Route::post('/{message}/mark-as-read', [MessageController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [MessageController::class, 'markAllAsRead'])->name('mark-all-as-read');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ"" NOTIFICATIONS - ROUTE PRINCIPALE (CORRIGÃ‰E âœ…)
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [MessageController::class, 'notifications'])->name('index');
        Route::get('/recent', [MessageController::class, 'recentNotifications'])->name('recent');
        Route::post('/mark-all-as-read', [MessageController::class, 'markAllNotificationsAsRead'])->name('mark-all-as-read');
    });
    
    /*
    |--------------------------------------------------------------------------
    | âš™ï¸ PROFIL UTILISATEUR - ROUTES PRINCIPALES
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::get('/complete', [ProfileController::class, 'complete'])->name('complete');
        Route::post('/complete', [ProfileController::class, 'storeComplete'])->name('complete.store');
        
        // Sécurité
        Route::get('/security', [ProfileController::class, 'security'])->name('security');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::post('/two-factor', [ProfileController::class, 'enableTwoFactor'])->name('two-factor.enable');
        Route::delete('/two-factor', [ProfileController::class, 'disableTwoFactor'])->name('two-factor.disable');
    });
    
    /*
    |--------------------------------------------------------------------------
    | ðŸ'¥ ADHÃ‰RENTS (ALIAS members pour compatibilité)
    |--------------------------------------------------------------------------
    */
    Route::prefix('adherents')->name('adherents.')->group(function () {
        Route::get('/', [AdherentController::class, 'index'])->name('index');
        Route::get('/create', [AdherentController::class, 'create'])->name('create');
        Route::post('/', [AdherentController::class, 'store'])->name('store');
        Route::get('/{adherent}', [AdherentController::class, 'show'])->name('show');
        Route::get('/{adherent}/edit', [AdherentController::class, 'edit'])->name('edit');
        Route::put('/{adherent}', [AdherentController::class, 'update'])->name('update');
        Route::delete('/{adherent}', [AdherentController::class, 'destroy'])->name('destroy');
    });
});


// Inclure les routes operator détaillées
if (file_exists(__DIR__.'/operator.php')) {
    require __DIR__.'/operator.php';
}