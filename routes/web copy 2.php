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
| Routes Admin - VERSION MINIMALE SANS CONFLITS
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard principal
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
        Route::get('/charts-data', [AnalyticsController::class, 'chartsData'])->name('charts-data');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Routes Admin - Workflow et Gestion
    |--------------------------------------------------------------------------
    */
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/', [WorkflowController::class, 'index'])->name('index');
        Route::get('/statistics', [WorkflowController::class, 'statistics'])->name('statistics');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Routes Admin - Base de données NIP
    |--------------------------------------------------------------------------
    */
    Route::prefix('nip')->name('nip.')->group(function () {
        Route::get('/', [NipDatabaseController::class, 'index'])->name('index');
        Route::post('/verify', [NipDatabaseController::class, 'verify'])->name('verify');
        Route::post('/bulk-verify', [NipDatabaseController::class, 'bulkVerify'])->name('bulk-verify');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Routes Admin - Notifications
    |--------------------------------------------------------------------------
    */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Routes Admin - Profil et Paramètres
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [AdminProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::post('/update', [SettingsController::class, 'update'])->name('update');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Routes Admin - Utilitaires
    |--------------------------------------------------------------------------
    */
    
    // Vérification NIP individuelle
    Route::post('/verify-nip', function(Request $request) {
        $request->validate([
            'nip' => 'required|string'
        ]);
        
        try {
            $nipService = app(\App\Services\NipDatabaseService::class);
            $result = $nipService->verifyNip($request->nip);
            
            return response()->json([
                'success' => true,
                'valid' => $result['valid'],
                'data' => $result['data'] ?? null,
                'message' => $result['message'] ?? 'Vérification effectuée'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
            ], 500);
        }
    })->name('verify-nip');
    
    // Upload de document temporaire
    Route::post('/upload-document', function(Request $request) {
        $request->validate([
            'file' => 'required|file|max:10240',
            'document_type' => 'required|string'
        ]);
        
        $file = $request->file('file');
        $documentType = $request->input('document_type');
        $fileName = time() . '_' . $documentType . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('documents/temp', $fileName, 'public');
        
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

// Inclure les routes supplémentaires
if (file_exists(__DIR__.'/admin.php')) {
    require __DIR__.'/admin.php';
}
if (file_exists(__DIR__.'/operator.php')) {
    require __DIR__.'/operator.php';
}

/*
|--------------------------------------------------------------------------
| ✅ ROUTES ADMIN - DOSSIERS (CORRECTION FINALE)
|--------------------------------------------------------------------------
| Routes corrigées utilisant AdminDossierController au lieu de DossierController
| ✅ Corrigé le : 29/10/2025
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Route pour la liste des dossiers/organisations
    Route::get('/dossiers', [AdminDossierController::class, 'index'])->name('dossiers.index');
    
    // Route pour les dossiers en attente - ✅ CORRIGÉ
    Route::get('/dossiers/en-attente', [AdminDossierController::class, 'enAttente'])->name('dossiers.en-attente');
    
    // Route pour voir un dossier spécifique - ✅ CORRIGÉ
    Route::get('/dossiers/{id}', [AdminDossierController::class, 'show'])->name('dossiers.show');
    
    // Route pour télécharger le récépissé provisoire - ✅ CORRIGÉ
    Route::get('/dossiers/{id}/recepisse-provisoire', [AdminDossierController::class, 'downloadRecepisseProvisoire'])->name('dossiers.recepisse-provisoire');
    
    // Routes pour l'export des organisations (à ajouter le contrôleur ExportController si nécessaire)
    // Route::post('/exports/organisations', [ExportController::class, 'exportOrganisations'])->name('exports.organisations');
});