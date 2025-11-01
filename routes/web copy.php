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
    
    // APIs Temps Réel pour Dashboard (SANS CONFLIT)
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStatsApi'])->name('stats');
        Route::get('/activity', [DashboardController::class, 'getActivityFeed'])->name('activity');
        Route::get('/chart-data', [DashboardController::class, 'getChartDataApi'])->name('chart-data');
        Route::get('/agents-status', [DashboardController::class, 'getAgentsStatus'])->name('agents-status');
        Route::get('/priority-dossiers', [DashboardController::class, 'getPriorityDossiersApi'])->name('priority-dossiers');
        Route::get('/performance-metrics', [DashboardController::class, 'getPerformanceMetricsApi'])->name('performance-metrics');
        Route::get('/search/all', function() {
            return response()->json(['results' => [], 'message' => 'Recherche globale - Étape 6 à venir']);
        })->name('search.all');
        Route::get('/notifications/recent', [NotificationController::class, 'recent'])->name('notifications.recent');
    });
});

/*
|--------------------------------------------------------------------------
| Routes Operator - CORRIGÉES POUR CHUNKING
|--------------------------------------------------------------------------
*/
Route::prefix('operator')->name('operator.')->middleware(['web', 'auth', 'verified', 'operator'])->group(function () {
    
    // ========================================
    // ROUTES CHUNKING
    // ========================================
    Route::prefix('chunking')->name('chunking.')->group(function () {
        
        Route::post('/process-chunk', [ChunkingController::class, 'processChunk'])
            ->name('process-chunk')
            ->middleware('throttle:30,1');
        
        Route::get('/csrf-refresh', [ChunkingController::class, 'refreshCSRF'])
            ->name('csrf-refresh');
        
        Route::get('/health', [ChunkingController::class, 'healthCheck'])
            ->name('health');
        
        Route::get('/auth-test', [ChunkingController::class, 'authTest'])
            ->name('auth-test');
    });

    // ========================================
    // TEMPLATES ET MODÈLES
    // ❌ DOUBLON SUPPRIMÉ (était aux lignes 174-177)
    // ✅ Les routes templates sont dans operator.php
    // ========================================

    // Dashboard principal
    Route::get('/', function () {
        return view('operator.dashboard');
    })->name('dashboard');
    Route::get('/dashboard', function () {
        return view('operator.dashboard');
    })->name('dashboard.full');
    
    // Profil opérateur
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::get('/complete', [ProfileController::class, 'complete'])->name('complete');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar.update');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('avatar.delete');
    });
    
    // Gestion des dossiers
    Route::prefix('dossiers')->name('dossiers.')->group(function () {
        Route::get('/', [DossierController::class, 'index'])->name('index');
        Route::get('/create', [DossierController::class, 'create'])->name('create');
        Route::post('/', [DossierController::class, 'store'])->name('store');
        Route::get('/{dossier}', [DossierController::class, 'show'])->name('show');
        Route::get('/{dossier}/edit', [DossierController::class, 'edit'])->name('edit');
        Route::put('/{dossier}', [DossierController::class, 'update'])->name('update');
        
        // Routes AJAX spécifiques
        Route::post('/check-nip', [DossierController::class, 'checkNip'])->name('check-nip');
        Route::post('/validate-members', [DossierController::class, 'validateMembers'])->name('validate-members');
        
        // Gestion des anomalies
        Route::get('/anomalies', [DossierController::class, 'anomalies'])->name('anomalies');
        Route::post('/anomalies/resolve/{adherent}', [DossierController::class, 'resolveAnomalie'])->name('anomalies.resolve');
    });
    
    // Gestion des organisations
    Route::prefix('organisations')->name('organisations.')->group(function () {
        Route::get('/', [OrganisationController::class, 'index'])->name('index');
        Route::get('/create', [OrganisationController::class, 'create'])->name('create');
        Route::post('/', [OrganisationController::class, 'store'])->name('store');
        Route::get('/{organisation}', [OrganisationController::class, 'show'])->name('show');
        Route::get('/{organisation}/edit', [OrganisationController::class, 'edit'])->name('edit');
        Route::put('/{organisation}', [OrganisationController::class, 'update'])->name('update');
        
        // Gestion des adhérents
        Route::prefix('{organisation}/adherents')->name('adherents.')->group(function () {
            Route::get('/', [AdherentController::class, 'index'])->name('index');
            Route::get('/create', [AdherentController::class, 'create'])->name('create');
            Route::post('/', [AdherentController::class, 'store'])->name('store');
            Route::get('/{adherent}/edit', [AdherentController::class, 'edit'])->name('edit');
            Route::put('/{adherent}', [AdherentController::class, 'update'])->name('update');
            Route::delete('/{adherent}', [AdherentController::class, 'destroy'])->name('destroy');
            
            // Import d'adhérents
            Route::get('/import', [AdherentController::class, 'import'])->name('import');
            Route::post('/import', [AdherentController::class, 'processImport'])->name('import.process');
            Route::post('/validate', [AdherentController::class, 'validateImport'])->name('import.validate');
        });
    });
    
    // Documents
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [OperatorDocumentController::class, 'index'])->name('index');
        Route::post('/upload', [OperatorDocumentController::class, 'upload'])->name('upload');
        Route::get('/{document}/download', [OperatorDocumentController::class, 'download'])->name('download');
        Route::delete('/{document}', [OperatorDocumentController::class, 'destroy'])->name('destroy');
    });
    
    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });
});

/*
|--------------------------------------------------------------------------
| Routes API pour Validation en Temps Réel - VERSION ÉTENDUE
|--------------------------------------------------------------------------
*/
Route::prefix('api/v1')->name('api.v1.')->middleware(['auth', 'throttle:60,1'])->group(function () {
    
    // Vérification nom organisation
    Route::post('/verify-organization-name', function (Request $request) {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'type' => 'required|in:association,ong,parti_politique,confession_religieuse',
            'suggest_alternatives' => 'boolean'
        ]);
        
        $name = $request->input('name');
        $type = $request->input('type');
        $suggestAlternatives = $request->input('suggest_alternatives', false);
        
        $exists = \App\Models\Organisation::where('nom', $name)->where('type', $type)->exists();
        
        $response = [
            'success' => true,
            'available' => !$exists,
            'message' => $exists ? 'Ce nom est déjà utilisé pour ce type d\'organisation' : 'Nom disponible'
        ];
        
        if ($suggestAlternatives && $exists) {
            $suggestions = [];
            for ($i = 1; $i <= 3; $i++) {
                $suggestion = $name . ' ' . $i;
                if (!\App\Models\Organisation::where('nom', $suggestion)->where('type', $type)->exists()) {
                    $suggestions[] = $suggestion;
                }
            }
            $response['suggestions'] = array_slice($suggestions, 0, 5);
        }
        
        return response()->json($response);
    })->name('verify-organization-name');
    
    // Upload de document
    Route::post('/upload-document', function (Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_type' => 'required|string',
            'organization_id' => 'nullable|exists:organisations,id'
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