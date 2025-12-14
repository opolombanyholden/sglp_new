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
        $logs = collect();

        if (class_exists('App\Models\ActivityLog')) {
            try {
                $logs = \App\Models\ActivityLog::latest()
                    ->with('user')
                    ->paginate(50);
            } catch (\Exception $e) {
                // Si une erreur se produit, utiliser une collection vide
                $logs = collect()->paginate(50);
            }
        } else {
            // Le modèle n'existe pas, utiliser une pagination vide
            $logs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 50);
        }

        $data = [
            'title' => 'Logs d\'activité',
            'logs' => $logs,
        ];

        return view('admin.analytics.activity-logs', $data);
    }

    /**
     * ✅ NOUVEAU : Rapport mensuel
     * Route: GET /admin/reports/monthly
     */
    public function monthlyReport(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $data = [
            'title' => 'Rapport Mensuel - ' . $startDate->format('F Y'),
            'period' => $startDate->format('F Y'),
            'month' => $month,
            'year' => $year,

            // Statistiques du mois
            'total_organisations' => Organisation::whereBetween('created_at', [$startDate, $endDate])->count(),
            'organisations_approuvees' => Organisation::where('statut', 'approuve')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'organisations_en_attente' => Organisation::whereIn('statut', ['soumis', 'en_validation'])
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'organisations_rejetees' => Organisation::where('statut', 'rejete')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),

            'total_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),

            // Statistiques globales pour comparaison
            'total_organisations_global' => Organisation::count(),
            'total_users_global' => User::count(),
        ];

        return view('admin.reports.monthly', $data);
    }

    /**
     * ✅ NOUVEAU : Rapport annuel
     * Route: GET /admin/reports/annual
     */
    public function annualReport(Request $request)
    {
        $year = $request->input('year', now()->year);

        $startDate = Carbon::create($year, 1, 1)->startOfYear();
        $endDate = Carbon::create($year, 12, 31)->endOfYear();

        // Données mensuelles pour l'année
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
            $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

            $monthlyData[$month] = [
                'label' => $monthStart->format('F'),
                'organisations' => Organisation::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'users' => User::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            ];
        }

        $data = [
            'title' => 'Rapport Annuel - ' . $year,
            'year' => $year,
            'period' => 'Année ' . $year,

            // Statistiques de l'année
            'total_organisations' => Organisation::whereBetween('created_at', [$startDate, $endDate])->count(),
            'organisations_approuvees' => Organisation::where('statut', 'approuve')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'organisations_en_attente' => Organisation::whereIn('statut', ['soumis', 'en_validation'])
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'organisations_rejetees' => Organisation::where('statut', 'rejete')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),

            'total_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),

            // Données mensuelles
            'monthly_data' => $monthlyData,

            // Statistiques globales
            'total_organisations_global' => Organisation::count(),
            'total_users_global' => User::count(),
        ];

        return view('admin.reports.annual', $data);
    }

    /**
     * ✅ NOUVEAU : Rapport personnalisé
     * Route: GET /admin/reports/custom
     */
    public function customReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $data = [
            'title' => 'Rapport Personnalisé',
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),

            // Statistiques de la période
            'total_organisations' => Organisation::whereBetween('created_at', [$startDate, $endDate])->count(),
            'organisations_approuvees' => Organisation::where('statut', 'approuve')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'organisations_en_attente' => Organisation::whereIn('statut', ['soumis', 'en_validation'])
                ->whereBetween('created_at', [$startDate, $endDate])->count(),
            'organisations_rejetees' => Organisation::where('statut', 'rejete')
                ->whereBetween('created_at', [$startDate, $endDate])->count(),

            'total_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),

            // Distribution par statut
            'status_distribution' => Organisation::whereBetween('created_at', [$startDate, $endDate])
                ->selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->pluck('count', 'statut'),

            // Statistiques globales
            'total_organisations_global' => Organisation::count(),
            'total_users_global' => User::count(),
        ];

        return view('admin.reports.custom', $data);
    }

    /**
     * ✅ NOUVEAU : Export des organisations
     * Route: GET /admin/exports/organisations
     */
    public function exportOrganisations(Request $request)
    {
        $format = $request->input('format', 'excel');

        $organisations = Organisation::all()->map(function ($org) {
            return [
                'ID' => $org->id,
                'Nom' => $org->nom_organisation ?? 'N/A',
                'Type' => $org->type_organisation ?? 'N/A',
                'Statut' => $org->statut ?? 'N/A',
                'Email' => $org->email ?? 'N/A',
                'Téléphone' => $org->telephone ?? 'N/A',
                'Adresse' => $org->adresse ?? 'N/A',
                'Ville' => $org->ville ?? 'N/A',
                'Date de création' => $org->created_at ? $org->created_at->format('d/m/Y') : 'N/A',
            ];
        });

        return $this->downloadExport($organisations, 'organisations', $format);
    }

    /**
     * ✅ NOUVEAU : Export des utilisateurs
     * Route: GET /admin/exports/users
     */
    public function exportUsers(Request $request)
    {
        $format = $request->input('format', 'excel');

        $users = User::all()->map(function ($user) {
            return [
                'ID' => $user->id,
                'Nom' => $user->name,
                'Email' => $user->email,
                'Rôle' => $user->role ?? 'user',
                'Statut' => $user->is_active ? 'Actif' : 'Inactif',
                'Date d\'inscription' => $user->created_at->format('d/m/Y'),
            ];
        });

        return $this->downloadExport($users, 'utilisateurs', $format);
    }

    /**
     * ✅ NOUVEAU : Export des dossiers
     * Route: GET /admin/exports/dossiers
     */
    public function exportDossiers(Request $request)
    {
        $format = $request->input('format', 'excel');

        // Note: Le modèle Dossier pourrait ne pas exister, utiliser Organisation comme fallback
        try {
            $dossiers = Organisation::all()->map(function ($org) {
                return [
                    'ID' => $org->id,
                    'Organisation' => $org->nom_organisation ?? 'N/A',
                    'Statut' => $org->statut ?? 'N/A',
                    'Type' => $org->type_organisation ?? 'N/A',
                    'Adresse' => $org->adresse ?? 'N/A',
                    'Ville' => $org->ville ?? 'N/A',
                    'Date de soumission' => $org->created_at ? $org->created_at->format('d/m/Y') : 'N/A',
                ];
            });
        } catch (\Exception $e) {
            $dossiers = collect([]);
        }

        return $this->downloadExport($dossiers, 'dossiers', $format);
    }

    /**
     * ✅ NOUVEAU : Export global (toutes les données)
     * Route: GET /admin/exports/global
     */
    public function exportGlobal(Request $request)
    {
        // Pour l'instant, retourner un message indiquant que la fonctionnalité est en développement
        return response()->json([
            'success' => false,
            'message' => 'L\'export global est en cours de développement. Utilisez les exports individuels pour le moment.',
            'available_exports' => [
                'organisations' => route('admin.exports.organisations'),
                'users' => route('admin.exports.users'),
                'dossiers' => route('admin.exports.dossiers'),
            ]
        ], 501);
    }

    /**
     * Méthode helper : Téléchargement des exports
     */
    private function downloadExport($data, $filename, $format)
    {
        $filename = $filename . '_' . date('Y-m-d_His');

        switch ($format) {
            case 'csv':
                return $this->exportCSV($data, $filename);
            case 'json':
                return response()->json($data);
            case 'pdf':
                return $this->exportPDF($data, $filename);
            case 'excel':
            default:
                // Excel export nécessite une librairie comme PhpSpreadsheet ou Laravel Excel
                return $this->exportCSV($data, $filename); // Fallback to CSV for now
        }
    }

    /**
     * Méthode helper : Export CSV
     */
    private function exportCSV($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // En-têtes
            if ($data->isNotEmpty()) {
                fputcsv($file, array_keys($data->first()));
            }

            // Données
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


    /**
     * Méthode helper : Export PDF
     */
    private function exportPDF($data, $filename)
    {
        // Générer HTML pour le PDF
        $html = '<html><head><meta charset="UTF-8">';
        $html .= '<style>';
        $html .= 'body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }';
        $html .= 'h1 { color: #333; font-size: 18px; margin-bottom: 20px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin-top: 10px; }';
        $html .= 'th { background-color: #4CAF50; color: white; padding: 8px; text-align: left; border: 1px solid #ddd; }';
        $html .= 'td { padding: 6px; border: 1px solid #ddd; }';
        $html .= 'tr:nth-child(even) { background-color: #f2f2f2; }';
        $html .= '</style></head><body>';

        $html .= '<h1>' . strtoupper(str_replace('_', ' ', $filename)) . '</h1>';
        $html .= '<p>Généré le ' . date('d/m/Y à H:i:s') . '</p>';

        if ($data->isNotEmpty()) {
            $html .= '<table>';

            // En-têtes
            $html .= '<thead><tr>';
            foreach (array_keys($data->first()) as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead>';

            // Données
            $html .= '<tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell ?? '') . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';

            $html .= '</table>';
        } else {
            $html .= '<p>Aucune donnée à exporter.</p>';
        }

        $html .= '</body></html>';

        // Générer le PDF avec DomPDF
        // Générer le PDF avec les backgrounds
        $htmlContent = \App\Helpers\PdfTemplateHelper::wrapContent(
            'Export ' . ucfirst($entity) . ' - ' . now()->format('d-m-Y'),
            $html
        );
        $pdf = \PDF::loadHTML($htmlContent);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download($filename . '.pdf');
    }


    /**
     * Méthode privée : Calculer le taux de croissance
     */
    private function calculateGrowthRate()
    {
        $thisMonth = Organisation::whereMonth('created_at', now()->month)->count();
        $lastMonth = Organisation::whereMonth('created_at', now()->subMonth()->month)->count();

        if ($lastMonth == 0)
            return 100;
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