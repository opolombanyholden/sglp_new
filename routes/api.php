<?php
// ========================================================================
// ROUTES API - SGLP/PNGDI
// ========================================================================

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Operator\OrganisationController;
use App\Http\Controllers\Api\ChunkProcessorController;
use App\Http\Controllers\PublicControllers\DocumentVerificationController;

/*
|--------------------------------------------------------------------------
| 🔍 API PUBLIQUE - VÉRIFICATION DE DOCUMENTS (MODULE DOCUMENTS)
|--------------------------------------------------------------------------
| Routes API JSON pour vérifier l'authenticité des documents générés
| ✅ Ajouté le : 28/10/2025
| ✅ Rate limiting : 120 requêtes par minute par IP
| ✅ Format de réponse standardisé JSON
| ✅ Compatible intégrations tierces et applications mobiles
|--------------------------------------------------------------------------
*/

Route::prefix('api')->name('api.')->middleware(['throttle:120,1'])->group(function () {
    
    /**
     * Vérifier un document par token (GET)
     * GET /api/verify-document/{token}
     * 
     * Paramètres :
     * - token : Token de vérification du document
     * 
     * Réponse JSON :
     * {
     *   "success": true,
     *   "valid": true,
     *   "document": {...},
     *   "organisation": {...},
     *   "timestamp": "2025-10-28T10:30:00.000Z"
     * }
     */
    Route::get('/verify-document/{token}', [DocumentVerificationController::class, 'verifyApi'])
        ->name('verify-document-get');
    
    /**
     * Vérifier un document par token (POST)
     * POST /api/verify-document
     * 
     * Body JSON :
     * {
     *   "token": "abc123...",
     *   "numero_document": "DOC-2025-001" (optionnel)
     * }
     * 
     * Réponse JSON :
     * {
     *   "success": true,
     *   "valid": true,
     *   "document": {...},
     *   "message": "Document valide"
     * }
     */
    Route::post('/verify-document', [DocumentVerificationController::class, 'verifyApiPost'])
        ->name('verify-document-post');
    
    /**
     * Statistiques publiques des documents (JSON)
     * GET /api/document-stats
     * 
     * Réponse JSON :
     * {
     *   "success": true,
     *   "stats": {
     *     "total_documents": 1250,
     *     "documents_valides": 1200,
     *     "documents_invalides": 50,
     *     "verifications_today": 350
     *   }
     * }
     */
    Route::get('/document-stats', [DocumentVerificationController::class, 'stats'])
        ->name('document-stats');
    
    /**
     * Vérifier un document via QR Code (POST - mobile)
     * POST /api/verify-qr
     * 
     * Body JSON :
     * {
     *   "qr_data": "https://domaine.ga/document-verify/abc123",
     *   "device_info": {...} (optionnel)
     * }
     * 
     * Réponse JSON :
     * {
     *   "success": true,
     *   "valid": true,
     *   "document": {...}
     * }
     */
    Route::post('/verify-qr', [DocumentVerificationController::class, 'verifyQr'])
        ->name('verify-qr-mobile');
    
    /**
     * Obtenir les informations d'un document sans log
     * GET /api/document-info/{token}
     * 
     * Utile pour prévisualisation sans enregistrer une vérification
     * 
     * Réponse JSON :
     * {
     *   "success": true,
     *   "document": {...},
     *   "cached": true
     * }
     */
    Route::get('/document-info/{token}', [DocumentVerificationController::class, 'documentInfo'])
        ->name('document-info');
});

// ========================================
// ROUTES CHUNKING - AUTHENTIFICATION CORRIGÉE
// ========================================

Route::middleware(['web', 'auth', 'throttle:60,1'])->group(function () {
    
    /**
     * Traitement des chunks d'adhérents
     * POST /api/organisations/process-chunk
     */
    Route::post('/organisations/process-chunk', [ChunkProcessorController::class, 'processChunk'])
        ->name('api.organisations.process-chunk');
    
    /**
     * Rafraîchissement du token CSRF
     * GET /api/csrf-refresh
     */
    Route::get('/csrf-refresh', [ChunkProcessorController::class, 'refreshCSRF'])
        ->name('api.csrf-refresh');
    
    /**
     * Statistiques de performance du chunking
     * GET /api/chunking/performance
     */
    Route::get('/chunking/performance', [ChunkProcessorController::class, 'getPerformanceStats'])
        ->name('api.chunking.performance');
    
});

// ========================================
// ENDPOINT DE DIAGNOSTIC - SANS MIDDLEWARE AUTH
// Pour tester l'authentification sans redirection
// ========================================

Route::middleware(['web'])->group(function () {
    
    /**
     * Endpoint de diagnostic authentification
     * GET /api/chunking/auth-test
     * 
     * Teste l'authentification sans forcer la redirection
     */
    Route::get('/chunking/auth-test', function () {
        return response()->json([
            'success' => true,
            'system' => 'chunking_auth_test',
            'timestamp' => now()->toISOString(),
            
            // Tests d'authentification
            'auth_check' => auth()->check(),
            'auth_user_id' => auth()->id(),
            'auth_user_email' => auth()->user()->email ?? 'N/A',
            'auth_user_role' => auth()->user()->role ?? 'N/A',
            'auth_guard' => auth()->getDefaultDriver(),
            
            // Tests de session
            'session_id' => session()->getId(),
            'session_token' => session()->token(),
            'csrf_token' => csrf_token(),
            
            // Tests de cookies
            'cookies_present' => !empty($_COOKIE),
            'laravel_session_cookie' => isset($_COOKIE['laravel_session']) ? 'Present' : 'Absent',
            'xsrf_token_cookie' => isset($_COOKIE['XSRF-TOKEN']) ? 'Present' : 'Absent',
            
            // Configuration Laravel
            'config' => [
                'auth_guard' => config('auth.defaults.guard'),
                'session_driver' => config('session.driver'),
                'session_lifetime' => config('session.lifetime'),
                'session_cookie' => config('session.cookie'),
            ],
            
            // Headers de la requête
            'request_headers' => [
                'user_agent' => request()->header('User-Agent'),
                'accept' => request()->header('Accept'),
                'cookie' => request()->header('Cookie') ? 'Present' : 'Absent',
                'x_requested_with' => request()->header('X-Requested-With'),
            ],
            
            // Debug spécifique
            'debug' => [
                'request_is_ajax' => request()->ajax(),
                'request_wants_json' => request()->wantsJson(),
                'middleware_applied' => 'web only (no auth)',
                'can_access_protected' => 'To be tested with chunking/health-protected'
            ]
        ]);
    })->name('api.chunking.auth-test');
    
    /**
     * Endpoint protégé pour test
     * GET /api/chunking/health-protected
     */
    Route::get('/chunking/health-protected', function () {
        // Vérifier manuellement l'authentification
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'authenticated' => false,
                'message' => 'Non authentifié',
                'redirect_to_login' => false,
                'debug' => [
                    'session_id' => session()->getId(),
                    'csrf_token' => csrf_token(),
                    'cookies' => $_COOKIE,
                ]
            ], 401);
        }
        
        return response()->json([
            'success' => true,
            'authenticated' => true,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'user_role' => auth()->user()->role,
            'message' => 'Authentifié avec succès',
            'system' => 'chunking',
            'status' => 'operational'
        ]);
    })->name('api.chunking.health-protected');
    
});

// CSRF refresh public (fallback)
Route::middleware(['web', 'throttle:10,1'])->group(function () {
    Route::get('/csrf-refresh-public', [ChunkProcessorController::class, 'refreshCSRF'])
        ->name('api.csrf-refresh-public');
});

// ========================================
// ROUTES ORGANISATIONS EXISTANTES
// ========================================

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/organisations/check-existing-members', [OrganisationController::class, 'checkExistingMembers']);
});

// ========================================
// GROUPE API v1 - MIDDLEWARE WEB + AUTH
// ========================================

Route::prefix('v1')->middleware(['web', 'auth'])->group(function () {
    
    /**
     * Vérification NIP gabonais
     */
    Route::post('verify-nip', function (Request $request) {
        $request->validate([
            'nip' => 'required|string|size:13'
        ]);
        
        $nip = $request->input('nip');
        
        $isValid = preg_match('/^\d{13}$/', $nip) && 
                  !preg_match('/^(\d)\1{12}$/', $nip) && 
                  !in_array($nip, ['1234567890123', '3210987654321']);
        
        return response()->json([
            'success' => $isValid,
            'valid' => $isValid,
            'message' => $isValid ? 'NIP valide' : 'NIP invalide'
        ]);
    });
    
    /**
     * Upload de document
     */
    Route::post('upload-document', function (Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'document_type' => 'required|string',
            'organization_id' => 'nullable|exists:organisations,id'
        ]);
        
        $file = $request->file('file');
        $documentType = $request->input('document_type');
        
        $fileName = time() . '_' . $documentType . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('documents/' . auth()->id(), $fileName, 'public');
        
        return response()->json([
            'success' => true,
            'file_path' => '/storage/' . $path,
            'file_name' => $fileName,
            'file_size' => $file->getSize(),
            'file_type' => $file->getClientMimeType(),
            'message' => 'Document uploadé avec succès'
        ]);
    });
    
});

// ========================================
// ROUTES PUBLIQUES - VÉRIFICATION QR CODE (EXISTANTE)
// ========================================

Route::get('verify-qr/{code}', function ($code) {
    // Logique de vérification QR code
    return response()->json([
        'success' => true,
        'valid' => true,
        'message' => 'Code QR valide (simulation)'
    ]);
});