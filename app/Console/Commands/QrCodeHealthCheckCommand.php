<?php

/**
 * COMMANDE COMPLÉMENTAIRE : VÉRIFIER LA SANTÉ DES QR CODES
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QrCode;

class QrCodeHealthCheckCommand extends Command
{
    protected $signature = 'qr:health-check 
                           {--detailed : Afficher les détails de chaque QR code}';

    protected $description = 'Vérifier la santé de tous les QR codes';

    public function handle()
    {
        $this->info("🏥 Vérification de la santé des QR codes...");

        $totalQrCodes = QrCode::count();
        $activeQrCodes = QrCode::where('is_active', true)->count();
        $withPng = QrCode::where('is_active', true)
            ->whereNotNull('png_base64')
            ->where('png_base64', '!=', '')
            ->whereRaw('LENGTH(png_base64) > 100')
            ->count();
        $withSvg = QrCode::where('is_active', true)
            ->whereNotNull('svg_content')
            ->where('svg_content', '!=', '')
            ->whereRaw('LENGTH(svg_content) > 100')
            ->count();
        $withUrl = QrCode::where('is_active', true)
            ->whereNotNull('verification_url')
            ->where('verification_url', '!=', '')
            ->count();

        // Statistiques globales
        $this->info("📊 STATISTIQUES GLOBALES:");
        $this->table(
            ['Métrique', 'Valeur', 'Pourcentage'],
            [
                ['QR codes total', $totalQrCodes, '100%'],
                ['QR codes actifs', $activeQrCodes, $totalQrCodes > 0 ? round(($activeQrCodes/$totalQrCodes)*100, 1).'%' : '0%'],
                ['Avec PNG valide', $withPng, $activeQrCodes > 0 ? round(($withPng/$activeQrCodes)*100, 1).'%' : '0%'],
                ['Avec SVG valide', $withSvg, $activeQrCodes > 0 ? round(($withSvg/$activeQrCodes)*100, 1).'%' : '0%'],
                ['Avec URL', $withUrl, $activeQrCodes > 0 ? round(($withUrl/$activeQrCodes)*100, 1).'%' : '0%'],
            ]
        );

        // Problèmes identifiés
        $missingPng = $activeQrCodes - $withPng;
        $missingSvg = $activeQrCodes - $withSvg;
        $missingUrl = $activeQrCodes - $withUrl;

        if ($missingPng > 0 || $missingSvg > 0 || $missingUrl > 0) {
            $this->warn("⚠️  PROBLÈMES IDENTIFIÉS:");
            if ($missingPng > 0) {
                $this->line("  🖼️  {$missingPng} QR codes sans PNG valide");
            }
            if ($missingSvg > 0) {
                $this->line("  🎨 {$missingSvg} QR codes sans SVG valide");
            }
            if ($missingUrl > 0) {
                $this->line("  🔗 {$missingUrl} QR codes sans URL de vérification");
            }
            
            $this->line('');
            $this->info("💡 SOLUTION:");
            $this->line("  Pour corriger les PNG manquants: php artisan qr:fix-missing-png");
            $this->line("  Pour prévisualiser: php artisan qr:fix-missing-png --dry-run");
        } else {
            $this->info("✅ Tous les QR codes actifs sont en bonne santé!");
        }

        // Détails si demandé
        if ($this->option('detailed')) {
            $this->line('');
            $this->info("🔍 DÉTAILS PAR QR CODE:");
            
            $problematicQrCodes = QrCode::where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('png_base64')
                          ->orWhere('png_base64', '')
                          ->orWhereRaw('LENGTH(png_base64) < 100')
                          ->orWhereNull('svg_content')
                          ->orWhere('svg_content', '')
                          ->orWhereRaw('LENGTH(svg_content) < 100');
                })
                ->limit(20)
                ->get();

            if ($problematicQrCodes->isNotEmpty()) {
                $detailHeaders = ['ID', 'Code', 'PNG', 'SVG', 'URL', 'Créé le'];
                $detailRows = [];

                foreach ($problematicQrCodes as $qr) {
                    $pngStatus = !empty($qr->png_base64) && strlen($qr->png_base64) > 100 ? '✅' : '❌';
                    $svgStatus = !empty($qr->svg_content) && strlen($qr->svg_content) > 100 ? '✅' : '❌';
                    $urlStatus = !empty($qr->verification_url) ? '✅' : '❌';
                    
                    $detailRows[] = [
                        $qr->id,
                        substr($qr->code, 0, 15).'...',
                        $pngStatus,
                        $svgStatus,
                        $urlStatus,
                        $qr->created_at->format('d/m/Y H:i')
                    ];
                }

                $this->table($detailHeaders, $detailRows);
                
                if ($problematicQrCodes->count() >= 20) {
                    $this->line("... (affichant les 20 premiers problèmes)");
                }
            }
        }

        return 0;
    }
}