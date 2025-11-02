<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'admin']);
    }

    /**
     * Dashboard analytics
     */
    public function index()
    {
        $data = [
            'title' => 'Analytics & Statistiques Avancées',
            'organisations_count' => Organisation::count(),
            'users_count' => User::count(),
            'pending_count' => Organisation::whereIn('statut', ['soumis', 'en_validation'])->count(),
            'approved_count' => Organisation::where('statut', 'approuve')->count(),
            'growth_rate' => $this->calculateGrowthRate(),
            'charts_data' => $this->getChartsData()
        ];

        return view('admin.analytics.index', $data);
    }

    /**
     * ✅ MÉTHODE AJOUTÉE : Page des rapports
     * Route: GET /admin/reports
     */
    public function reports(Request $request)
    {
        $data = [
            'title' => 'Rapports & Statistiques',
            'total_organisations' => Organisation::count(),
            'organisations_actives' => Organisation::where('statut', 'approuve')->count(),
            'organisations_en_attente' => Organisation::whereIn('statut', ['soumis', 'en_validation'])->count(),
            'total_users' => User::count(),
        ];

        return view('admin.analytics.reports', $data);
    }

    /**
     * ✅ MÉTHODE AJOUTÉE : Page des exports
     * Route: GET /admin/exports
     */
    public function exports(Request $request)
    {
        $data = [
            'title' => 'Exports de données',
            'available_exports' => [
                'organisations' => 'Export des organisations',
                'users' => 'Export des utilisateurs',
                'dossiers' => 'Export des dossiers',
                'statistiques' => 'Export des statistiques',
            ],
        ];

        return view('admin.analytics.exports', $data);
    }

    /**
     * ✅ MÉTHODE AJOUTÉE : Logs d'activité
     * Route: GET /admin/activity-logs
     */
    public function activityLogs(Request $request)
    {
        // Vérifier si le modèle ActivityLog existe, sinon créer une collection vide
        try {
            $logs = ActivityLog::latest()
                ->with('user')
                ->paginate(50);
        } catch (\Exception $e) {
            // Si la table n'existe pas, retourner une collection vide
            $logs = collect()->paginate(50);
        }

        $data = [
            'title' => 'Logs d\'activité',
            'logs' => $logs,
        ];

        return view('admin.analytics.activity-logs', $data);
    }

    /**
     * Méthode privée : Calculer le taux de croissance
     */
    private function calculateGrowthRate()
    {
        $thisMonth = Organisation::whereMonth('created_at', now()->month)->count();
        $lastMonth = Organisation::whereMonth('created_at', now()->subMonth()->month)->count();
        
        if ($lastMonth == 0) return 100;
        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    /**
     * Méthode privée : Récupérer les données pour graphiques
     */
    private function getChartsData()
    {
        return [
            'monthly_registrations' => Organisation::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('month')
                ->pluck('count', 'month'),
            'status_distribution' => Organisation::selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->pluck('count', 'statut')
        ];
    }
}