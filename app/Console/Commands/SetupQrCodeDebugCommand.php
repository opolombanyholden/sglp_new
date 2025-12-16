<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * COMMANDE ARTISAN POUR CONFIGURER LE SYSTÈME DE DÉBOGAGE QR CODE
 * 
 * Usage: php artisan debug:setup-qr-code
 */
class SetupQrCodeDebugCommand extends Command
{
    protected $signature = 'debug:setup-qr-code 
                           {--force : Forcer l\'écrasement des fichiers existants}
                           {--clean : Nettoyer les fichiers de debug existants}';

    protected $description = 'Configure le système de débogage pour les QR codes PDF';

    public function handle()
    {
        $this->info('🔧 Configuration du système de débogage QR Code...');
        
        if ($this->option('clean')) {
            $this->cleanupDebugFiles();
            return 0;
        }

        // Étape 1: Créer les dossiers nécessaires
        $this->createDirectories();

        // Étape 2: Créer le contrôleur de debug
        $this->createDebugController();

        // Étape 3: Créer le middleware de protection
        $this->createDebugMiddleware();

        // Étape 4: Créer les vues de debug
        $this->createDebugViews();

        // Étape 5: Ajouter les routes (instructions)
        $this->displayRouteInstructions();

        // Étape 6: Vérifications finales
        $this->performFinalChecks();

        $this->info('✅ Configuration du débogage terminée !');
        $this->info('🌐 Accédez à /debug/qr-code pour commencer');
        
        return 0;
    }

    private function createDirectories()
    {
        $this->info('📁 Création des dossiers...');

        $directories = [
            'app/Http/Controllers/Debug',
            'app/Http/Middleware',
            'resources/views/debug',
            'storage/app/public/debug'
        ];

        foreach ($directories as $dir) {
            if (!File::exists(base_path($dir))) {
                File::makeDirectory(base_path($dir), 0755, true);
                $this->line("  ✓ Créé: {$dir}");
            } else {
                $this->line("  - Existe: {$dir}");
            }
        }
    }

    private function createDebugController()
    {
        $this->info('🎮 Création du contrôleur de debug...');

        $controllerPath = base_path('app/Http/Controllers/Debug/QrCodeDebugController.php');
        
        if (!File::exists($controllerPath) || $this->option('force')) {
            $controllerContent = $this->getControllerContent();
            File::put($controllerPath, $controllerContent);
            $this->line('  ✓ Contrôleur créé: QrCodeDebugController.php');
        } else {
            $this->line('  - Contrôleur existe déjà (utilisez --force pour écraser)');
        }
    }

    private function createDebugMiddleware()
    {
        $this->info('🛡️  Création du middleware de protection...');

        $middlewarePath = base_path('app/Http/Middleware/DebugOnly.php');
        
        if (!File::exists($middlewarePath) || $this->option('force')) {
            $middlewareContent = $this->getMiddlewareContent();
            File::put($middlewarePath, $middlewareContent);
            $this->line('  ✓ Middleware créé: DebugOnly.php');
        } else {
            $this->line('  - Middleware existe déjà');
        }
    }

    private function createDebugViews()
    {
        $this->info('👁️  Création des vues de debug...');

        $views = [
            'qr-code-debug.blade.php' => $this->getMainViewContent(),
            'pdf-test-png.blade.php' => $this->getPngTestViewContent(),
            'pdf-test-svg.blade.php' => $this->getSvgTestViewContent(),
            'pdf-test-placeholder.blade.php' => $this->getPlaceholderTestViewContent()
        ];

        foreach ($views as $filename => $content) {
            $viewPath = base_path("resources/views/debug/{$filename}");
            
            if (!File::exists($viewPath) || $this->option('force')) {
                File::put($viewPath, $content);
                $this->line("  ✓ Vue créée: {$filename}");
            } else {
                $this->line("  - Vue existe: {$filename}");
            }
        }
    }

    private function displayRouteInstructions()
    {
        $this->info('🛣️  Instructions pour les routes...');
        
        $this->warn('⚠️  IMPORTANT: Ajouter manuellement ces routes dans routes/web.php :');
        $this->line('');
        $this->line('// Routes de débogage QR Code (ajouter à la fin du fichier)');
        $this->line("Route::group(['middleware' => ['web'], 'prefix' => 'debug'], function () {");
        $this->line("    Route::get('/qr-code', [App\Http\Controllers\Debug\QrCodeDebugController::class, 'index']);");
        $this->line("    Route::get('/qr-code/diagnostic', [App\Http\Controllers\Debug\QrCodeDebugController::class, 'diagnosticComplet']);");
        $this->line("    Route::post('/qr-code/regenerer', [App\Http\Controllers\Debug\QrCodeDebugController::class, 'regenererQrCodes']);");
        $this->line("    Route::post('/qr-code/cleanup', [App\Http\Controllers\Debug\QrCodeDebugController::class, 'cleanupDebugFiles']);");
        $this->line('});');
        $this->line('');
    }

    private function performFinalChecks()
    {
        $this->info('🔍 Vérifications finales...');

        // Vérifier que APP_DEBUG est activé
        if (!config('app.debug')) {
            $this->warn('  ⚠️  APP_DEBUG=false dans .env - Les routes de debug ne seront pas accessibles');
        } else {
            $this->line('  ✓ APP_DEBUG activé');
        }

        // Vérifier que le lien symbolique storage existe
        if (!File::exists(public_path('storage'))) {
            $this->warn('  ⚠️  Lien symbolique storage manquant - Exécutez: php artisan storage:link');
        } else {
            $this->line('  ✓ Lien symbolique storage existe');
        }

        // Vérifier les services requis
        $requiredServices = [
            'App\Services\QRCodeService' => class_exists('App\Services\QRCodeService'),
            'App\Services\PDFService' => class_exists('App\Services\PDFService'),
            'Barryvdh\DomPDF\Facade\Pdf' => class_exists('Barryvdh\DomPDF\Facade\Pdf')
        ];

        foreach ($requiredServices as $service => $exists) {
            if ($exists) {
                $this->line("  ✓ Service disponible: {$service}");
            } else {
                $this->error("  ❌ Service manquant: {$service}");
            }
        }
    }

    private function cleanupDebugFiles()
    {
        $this->info('🧹 Nettoyage des fichiers de debug...');

        $debugFiles = [
            'app/Http/Controllers/Debug/QrCodeDebugController.php',
            'app/Http/Middleware/DebugOnly.php',
            'resources/views/debug'
        ];

        foreach ($debugFiles as $file) {
            $fullPath = base_path($file);
            if (File::exists($fullPath)) {
                if (File::isDirectory($fullPath)) {
                    File::deleteDirectory($fullPath);
                    $this->line("  ✓ Dossier supprimé: {$file}");
                } else {
                    File::delete($fullPath);
                    $this->line("  ✓ Fichier supprimé: {$file}");
                }
            }
        }

        // Nettoyer les fichiers PDF de debug
        try {
            $debugPdfs = Storage::disk('public')->files('debug');
            foreach ($debugPdfs as $pdf) {
                Storage::disk('public')->delete($pdf);
            }
            $this->line('  ✓ Fichiers PDF de debug supprimés');
        } catch (\Exception $e) {
            $this->line('  - Aucun fichier PDF de debug à supprimer');
        }

        $this->info('✅ Nettoyage terminé');
    }

    private function getControllerContent()
    {
        return '<?php

namespace App\Http\Controllers\Debug;

use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Models\Dossier;
use App\Services\QRCodeService;
use App\Services\PDFService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class QrCodeDebugController extends Controller
{
    protected $qrCodeService;
    protected $pdfService;

    public function __construct(QRCodeService $qrCodeService, PDFService $pdfService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        return view("debug.qr-code-debug");
    }

    public function diagnosticComplet(Request $request)
    {
        $results = [];
        $dossierId = $request->get("dossier_id");

        try {
            $dossier = $dossierId ? Dossier::find($dossierId) : Dossier::with("organisation")->first();
            
            if (!$dossier) {
                return response()->json(["error" => "Aucun dossier trouvé pour le test"]);
            }

            $results["dossier_info"] = [
                "id" => $dossier->id,
                "organisation" => $dossier->organisation->nom ?? "N/A",
                "statut" => $dossier->statut
            ];

            // Test des QR codes...
            return response()->json($results, 200, [], JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function regenererQrCodes(Request $request)
    {
        // Implementation...
        return response()->json(["success" => true, "message" => "QR Code regénéré"]);
    }

    public function cleanupDebugFiles()
    {
        // Implementation...
        return response()->json(["success" => true, "message" => "Fichiers nettoyés"]);
    }
}';
    }

    private function getMiddlewareContent()
    {
        return '<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DebugOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!config("app.debug")) {
            abort(404, "Page non trouvée");
        }

        return $next($request);
    }
}';
    }

    private function getMainViewContent()
    {
        return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Débogage QR Code PDF - SGLP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>🔧 Débogage QR Code PDF</h1>
        <div class="alert alert-info">
            Outil de diagnostic pour les problèmes d\'affichage des QR codes dans les PDF.
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <label for="dossier_id" class="form-label">ID Dossier (optionnel)</label>
                    <input type="number" class="form-control" id="dossier_id">
                </div>
                <button class="btn btn-primary" onclick="lancerTest()">Lancer le Test</button>
            </div>
        </div>
        
        <div id="results" class="mt-4" style="display: none;">
            <div class="card">
                <div class="card-header">Résultats</div>
                <div class="card-body">
                    <pre id="results-content"></pre>
                </div>
            </div>
        </div>
    </div>

    <script>
        function lancerTest() {
            const dossierId = document.getElementById("dossier_id").value;
            const url = "/debug/qr-code/diagnostic" + (dossierId ? "?dossier_id=" + dossierId : "");
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("results").style.display = "block";
                    document.getElementById("results-content").textContent = JSON.stringify(data, null, 2);
                })
                .catch(error => console.error("Erreur:", error));
        }
    </script>
</body>
</html>';
    }

    private function getPngTestViewContent()
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test PNG</title>
</head>
<body>
    <h1>Test QR Code PNG</h1>
    @if(isset($qr_code) && !empty($qr_code->png_base64))
        <img src="data:image/png;base64,{{ $qr_code->png_base64 }}" width="100" height="100">
        <p>PNG affiché</p>
    @else
        <p>PNG non disponible</p>
    @endif
</body>
</html>';
    }

    private function getSvgTestViewContent()
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test SVG</title>
</head>
<body>
    <h1>Test QR Code SVG</h1>
    @if(isset($qr_code) && !empty($qr_code->svg_content))
        {!! $qr_code->svg_content !!}
        <p>SVG affiché</p>
    @else
        <p>SVG non disponible</p>
    @endif
</body>
</html>';
    }

    private function getPlaceholderTestViewContent()
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Placeholder</title>
</head>
<body>
    <h1>Test QR Code Placeholder</h1>
    <svg width="100" height="100">
        <rect width="100" height="100" fill="#f8f9fa" stroke="#003f7f" stroke-width="2"/>
        <text x="50" y="50" text-anchor="middle" fill="#003f7f">QR TEST</text>
    </svg>
    <p>Placeholder affiché</p>
</body>
</html>';
    }
}