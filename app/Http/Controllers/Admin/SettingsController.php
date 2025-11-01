<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
 * Page principale des paramètres (REMPLACER votre méthode existante)
 */
public function index()
{
    try {
        // Informations système (conservées + étendues)
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => config('app.env'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            // ➕ NOUVELLES DONNÉES
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'timezone' => config('app.timezone')
        ];

        // ➕ PARAMÈTRES SYSTÈME CONFIGURABLES
        $systemSettings = $this->getSystemSettings();
        
        // ➕ PRÉFÉRENCES UTILISATEUR ÉTENDUES  
        $userPreferences = $this->getExtendedUserPreferences();
        
        // ➕ STATISTIQUES GÉNÉRALES
        $generalStats = $this->getGeneralStats();
        
        return view('admin.settings.index', compact(
            'systemInfo',
            'systemSettings', 
            'userPreferences',
            'generalStats'
        ));
        
    } catch (\Exception $e) {
        \Log::error('Erreur settings index: ' . $e->getMessage());
        return redirect()->route('admin.dashboard')->with('error', 'Erreur lors du chargement des paramètres.');
    }
}

    // =====================================================================
    // 🔧 NOUVELLES MÉTHODES - PARAMÈTRES SYSTÈME
    // =====================================================================

   


    // =====================================================================
    // 👤 NOUVELLES MÉTHODES - PRÉFÉRENCES UTILISATEUR
    // =====================================================================



    // =====================================================================
    // 🔒 NOUVELLES MÉTHODES - GESTION SÉCURITÉ
    // =====================================================================


    // =====================================================================
    // 📊 NOUVELLES MÉTHODES - STATISTIQUES ET MONITORING
    // =====================================================================

    // =====================================================================
    // 🛠️ MÉTHODES UTILITAIRES PRIVÉES
    // =====================================================================

    private function getMySQLVersion()
    {
        try {
            $result = DB::select('SELECT VERSION() as version');
            return $result[0]->version ?? 'Unknown';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getDiskUsage()
    {
        try {
            $bytes = disk_free_space('/');
            $total = disk_total_space('/');
            $used = $total - $bytes;
            $percent = round(($used / $total) * 100, 1);
            
            return [
                'used' => $this->formatBytes($used),
                'total' => $this->formatBytes($total),
                'percent' => $percent
            ];
        } catch (\Exception $e) {
            return ['used' => 'N/A', 'total' => 'N/A', 'percent' => 0];
        }
    }

    private function getMemoryUsage()
    {
        $used = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = ini_get('memory_limit');
        
        return [
            'current' => $this->formatBytes($used),
            'peak' => $this->formatBytes($peak),
            'limit' => $limit
        ];
    }

    private function getSystemUptime()
    {
        try {
            if (PHP_OS_FAMILY === 'Linux') {
                $uptime = file_get_contents('/proc/uptime');
                $seconds = (int) explode(' ', $uptime)[0];
                return $this->formatUptime($seconds);
            }
            return 'Non disponible';
        } catch (\Exception $e) {
            return 'Non disponible';
        }
    }

    private function countStorageFiles()
    {
        try {
            $count = 0;
            $directories = Storage::allDirectories();
            foreach ($directories as $dir) {
                $count += count(Storage::files($dir));
            }
            return $count;
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function getDatabaseSize()
    {
        try {
            $database = config('database.connections.mysql.database');
            $result = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.tables 
                WHERE table_schema = '{$database}'
            ");
            return ($result[0]->size_mb ?? 0) . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    private function formatUptime($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        return "{$days}j {$hours}h {$minutes}m";
    }

    /**
     * ➕ Méthode privée existante étendue
     */
    private function getUserPreferences()
    {
        // Conserver la méthode existante pour compatibilité
        return [
            'timezone' => 'Africa/Libreville',
            'language' => 'fr',
            'theme' => 'light',
            'notifications_email' => true,
            'sidebar_collapsed' => false
        ];
    }


    /**
     * ➕ Mettre à jour les paramètres système
     */
    public function updateSystemSettings(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'app_name' => 'required|string|max:255',
                'app_timezone' => 'required|string|max:50',
                'session_lifetime' => 'required|integer|min:30|max:1440',
                'delai_traitement_standard' => 'required|integer|min:1|max:365',
                'max_organisations_per_operator' => 'required|integer|min:1|max:50',
                'auto_assign_dossiers' => 'boolean',
                'notification_email_enabled' => 'boolean',
                'require_2fa_admin' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Sauvegarder les paramètres en cache ou base de données
            $settings = $request->only([
                'app_name', 'app_timezone', 'session_lifetime',
                'delai_traitement_standard', 'max_organisations_per_operator',
                'auto_assign_dossiers', 'notification_email_enabled', 'require_2fa_admin'
            ]);

            // Sauvegarder dans le cache
            foreach ($settings as $key => $value) {
                Cache::forever("system_setting_{$key}", $value);
            }

            // Log de l'action
            \Log::info('Mise à jour paramètres système', [
                'user_id' => auth()->id(),
                'settings' => $settings
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Paramètres système mis à jour avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur updateSystemSettings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres'
            ], 500);
        }
    }

    /**
     * ➕ Mettre à jour les préférences utilisateur
     */
    public function updateUserPreferences(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'theme' => 'required|in:light,dark',
                'language' => 'required|in:fr,en',
                'notifications_email' => 'boolean',
                'notifications_browser' => 'boolean',
                'items_per_page' => 'required|integer|min:10|max:100',
                'auto_logout' => 'required|integer|min:30|max:480'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $currentPreferences = $user->preferences ?? [];
            
            $newPreferences = array_merge($currentPreferences, $request->only([
                'theme', 'language', 'notifications_email',
                'notifications_browser', 'items_per_page', 'auto_logout'
            ]));

            $user->update(['preferences' => $newPreferences]);

            return response()->json([
                'success' => true,
                'message' => 'Préférences mises à jour avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur updateUserPreferences: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des préférences'
            ], 500);
        }
    }

    /**
     * ➕ Activer/Désactiver le mode maintenance
     */
    public function toggleMaintenanceMode(Request $request)
    {
        try {
            $message = $request->input('message', 'Le système est en maintenance temporaire.');
            
            if (app()->isDownForMaintenance()) {
                Artisan::call('up');
                $status = 'désactivé';
            } else {
                Artisan::call('down', [
                    '--message' => $message,
                    '--retry' => 60
                ]);
                $status = 'activé';
            }

            \Log::warning("Mode maintenance {$status} par " . auth()->user()->name, [
                'user_id' => auth()->id(),
                'message' => $message
            ]);

            return response()->json([
                'success' => true,
                'message' => "Mode maintenance {$status} avec succès",
                'maintenance_active' => $status === 'activé'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur toggleMaintenanceMode: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de mode maintenance'
            ], 500);
        }
    }

    /**
     * ➕ Vider les caches système
     */
    public function clearCaches(Request $request)
    {
        try {
            $cacheTypes = $request->input('types', ['application', 'config', 'route', 'view']);
            $cleared = [];

            if (in_array('application', $cacheTypes)) {
                Artisan::call('cache:clear');
                $cleared[] = 'Cache application';
            }

            if (in_array('config', $cacheTypes)) {
                Artisan::call('config:clear');
                $cleared[] = 'Cache configuration';
            }

            if (in_array('route', $cacheTypes)) {
                Artisan::call('route:clear');
                $cleared[] = 'Cache routes';
            }

            if (in_array('view', $cacheTypes)) {
                Artisan::call('view:clear');
                $cleared[] = 'Cache vues';
            }

            \Log::info('Caches vidés par ' . auth()->user()->name, [
                'user_id' => auth()->id(),
                'types' => $cacheTypes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Caches vidés: ' . implode(', ', $cleared)
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur clearCaches: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du vidage des caches'
            ], 500);
        }
    }

    /**
     * ➕ Méthodes utilitaires privées
     */
    private function getSystemSettings()
    {
        return [
            // Configuration application
            'app_name' => Cache::get('system_setting_app_name', config('app.name', 'SGLP')),
            'app_timezone' => Cache::get('system_setting_app_timezone', config('app.timezone', 'Africa/Libreville')),
            'session_lifetime' => Cache::get('system_setting_session_lifetime', config('session.lifetime', 120)),
            
            // Configuration SGLP spécifiques
            'delai_traitement_standard' => Cache::get('system_setting_delai_traitement_standard', 30),
            'max_organisations_per_operator' => Cache::get('system_setting_max_organisations_per_operator', 5),
            'auto_assign_dossiers' => Cache::get('system_setting_auto_assign_dossiers', false),
            'notification_email_enabled' => Cache::get('system_setting_notification_email_enabled', true),
            'require_2fa_admin' => Cache::get('system_setting_require_2fa_admin', false)
        ];
    }

    private function getExtendedUserPreferences()
    {
        $user = auth()->user();
        $preferences = $user->preferences ?? [];
        
        return array_merge([
            // Interface
            'theme' => 'light',
            'language' => 'fr',
            'items_per_page' => 25,
            
            // Notifications
            'notifications_email' => true,
            'notifications_browser' => true,
            
            // Sécurité
            'auto_logout' => 120
        ], $preferences);
    }

    private function getGeneralStats()
    {
        try {
            return [
                'users' => [
                    'total' => User::count(),
                    'active' => User::where('is_active', true)->count(),
                    'admins' => User::where('role', 'admin')->count(),
                    'agents' => User::where('role', 'agent')->count(),
                    'operators' => User::where('role', 'operator')->count()
                ]
            ];
        } catch (\Exception $e) {
            \Log::error('Erreur getGeneralStats: ' . $e->getMessage());
            return ['users' => []];
        }
    }

    // Ajoutez ces méthodes à votre SettingsController :
    
    /**
     * 🔒 Mettre à jour les paramètres de sécurité
     */
    public function updateSecuritySettings(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'require_2fa_admin' => 'boolean',
                'max_login_attempts' => 'required|integer|min:3|max:10',
                'session_timeout' => 'integer|min:30|max:1440',
                'password_expiry_days' => 'integer|min:30|max:365'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Sauvegarder les paramètres de sécurité
            $settings = $request->only([
                'require_2fa_admin', 'max_login_attempts', 
                'session_timeout', 'password_expiry_days'
            ]);
    
            foreach ($settings as $key => $value) {
                Cache::forever("security_setting_{$key}", $value);
            }
    
            // Log de sécurité important
            \Log::warning('Paramètres de sécurité modifiés', [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name,
                'settings' => $settings,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Paramètres de sécurité mis à jour avec succès'
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Erreur updateSecuritySettings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour des paramètres de sécurité'
            ], 500);
        }
    }
    
    /**
     * 🔒 Forcer la 2FA pour tous les administrateurs
     */
    public function force2FAForAdmins(Request $request)
    {
        try {
            // Compter les admins sans 2FA
            $adminsWithout2FA = User::where('role', 'admin')
                ->where('two_factor_enabled', '!=', true)
                ->count();
    
            if ($adminsWithout2FA === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tous les administrateurs ont déjà la 2FA activée'
                ]);
            }
    
            // Activer 2FA pour tous les admins qui ne l'ont pas
            $updated = User::where('role', 'admin')
                ->where('two_factor_enabled', '!=', true)
                ->update([
                    'two_factor_enabled' => true,
                    'updated_at' => now()
                ]);
    
            // Log de sécurité critique
            \Log::critical('2FA forcée pour tous les administrateurs', [
                'forced_by' => auth()->user()->name,
                'user_id' => auth()->id(),
                'admins_updated' => $updated,
                'ip' => request()->ip()
            ]);
    
            return response()->json([
                'success' => true,
                'message' => "2FA activée pour {$updated} administrateur(s)"
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Erreur force2FAForAdmins: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'activation forcée de la 2FA'
            ], 500);
        }
    }
    
    /**
     * 🔒 Nettoyer les logs anciens
     */
    public function clearOldLogs(Request $request)
    {
        try {
            $days = $request->input('days', 30); // Garder logs des 30 derniers jours par défaut
            
            $logPath = storage_path('logs');
            $deletedFiles = 0;
            $totalSize = 0;
    
            if (!is_dir($logPath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Répertoire des logs non trouvé'
                ], 404);
            }
    
            // Parcourir les fichiers de logs
            $files = glob($logPath . '/*.log');
            $cutoffDate = now()->subDays($days);
    
            foreach ($files as $file) {
                $fileModified = \Carbon\Carbon::createFromTimestamp(filemtime($file));
                
                if ($fileModified->lt($cutoffDate) && basename($file) !== 'laravel.log') {
                    $fileSize = filesize($file);
                    if (unlink($file)) {
                        $deletedFiles++;
                        $totalSize += $fileSize;
                    }
                }
            }
    
            // Nettoyer aussi les lignes anciennes du fichier principal
            $mainLogFile = $logPath . '/laravel.log';
            if (file_exists($mainLogFile)) {
                $this->cleanMainLogFile($mainLogFile, $days);
            }
    
            \Log::info('Nettoyage des logs effectué', [
                'cleaned_by' => auth()->user()->name,
                'files_deleted' => $deletedFiles,
                'size_freed' => $this->formatBytes($totalSize),
                'days_kept' => $days
            ]);
    
            return response()->json([
                'success' => true,
                'message' => "Nettoyage effectué: {$deletedFiles} fichier(s) supprimé(s), " . 
                            $this->formatBytes($totalSize) . " libérés"
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Erreur clearOldLogs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du nettoyage des logs'
            ], 500);
        }
    }
    
    /**
     * 🔒 Réinitialiser toutes les sessions utilisateur (sauf la session actuelle)
     */
    public function resetAllSessions(Request $request)
    {
        try {
            $currentSessionId = session()->getId();
            $affectedUsers = 0;
    
            // Si vous avez une table user_sessions
            if (\Schema::hasTable('user_sessions')) {
                $affectedUsers = \DB::table('user_sessions')
                    ->where('session_id', '!=', $currentSessionId)
                    ->update([
                        'logout_at' => now(),
                        'is_active' => false
                    ]);
            }
    
            // Forcer la régénération des tokens remember_me
            User::where('id', '!=', auth()->id())
                ->update(['remember_token' => null]);
    
            // Vider le cache des sessions Laravel
            if (config('session.driver') === 'file') {
                $sessionPath = storage_path('framework/sessions');
                if (is_dir($sessionPath)) {
                    $files = glob($sessionPath . '/*');
                    $deletedSessions = 0;
                    foreach ($files as $file) {
                        if (is_file($file) && !str_contains($file, $currentSessionId)) {
                            unlink($file);
                            $deletedSessions++;
                        }
                    }
                }
            }
    
            // Log de sécurité critique
            \Log::critical('Toutes les sessions utilisateur réinitialisées', [
                'reset_by' => auth()->user()->name,
                'user_id' => auth()->id(),
                'affected_users' => $affectedUsers,
                'ip' => request()->ip(),
                'reason' => $request->input('reason', 'Réinitialisation de sécurité')
            ]);
    
            return response()->json([
                'success' => true,
                'message' => 'Toutes les sessions utilisateur ont été réinitialisées. ' .
                            'Les utilisateurs devront se reconnecter.'
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Erreur resetAllSessions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réinitialisation des sessions'
            ], 500);
        }
    }
    
    /**
     * 🔒 Méthodes utilitaires privées pour la sécurité
     */
    private function cleanMainLogFile($filePath, $days)
    {
        try {
            if (!file_exists($filePath)) return;
    
            $lines = file($filePath, FILE_IGNORE_NEW_LINES);
            $cutoffDate = now()->subDays($days);
            $keptLines = [];
    
            foreach ($lines as $line) {
                // Extraire la date du log Laravel [YYYY-MM-DD HH:MM:SS]
                if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
                    $logDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $matches[1]);
                    if ($logDate->gt($cutoffDate)) {
                        $keptLines[] = $line;
                    }
                } else {
                    // Conserver les lignes sans date (continuations, etc.)
                    $keptLines[] = $line;
                }
            }
    
            // Réécrire le fichier avec seulement les lignes récentes
            file_put_contents($filePath, implode("\n", $keptLines));
    
        } catch (\Exception $e) {
            \Log::error('Erreur cleanMainLogFile: ' . $e->getMessage());
        }
    }
    
    public function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }

}