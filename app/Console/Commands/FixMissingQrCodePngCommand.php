<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\QrCode;
use App\Services\QRCodeService;

/**
 * COMMANDE POUR REGÉNÉRER LES QR CODES MANQUANTS
 * 
 * Usage: php artisan qr:fix-missing-png
 */
class FixMissingQrCodePngCommand extends Command
{
    protected $signature = 'qr:fix-missing-png 
                           {--limit=50 : Nombre maximum de QR codes à traiter}
                           {--dry-run : Afficher ce qui serait fait sans l\'exécuter}
                           {--force : Forcer la regénération même si PNG existe}';

    protected $description = 'Regénère les PNG manquants pour les QR codes existants';

    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        parent::__construct();
        $this->qrCodeService = $qrCodeService;
    }

    public function handle()
    {
        $limit = $this->option('limit');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("🔍 Recherche des QR codes à corriger...");

        // Trouver les QR codes sans PNG ou avec PNG invalide
        $query = QrCode::where('is_active', true);
        
        if (!$force) {
            $query->where(function($q) {
                $q->whereNull('png_base64')
                  ->orWhere('png_base64', '')
                  ->orWhereRaw('LENGTH(png_base64) < 100');
            });
        }

        $qrCodes = $query->limit($limit)->get();

        if ($qrCodes->isEmpty()) {
            $this->info("✅ Aucun QR code à corriger trouvé.");
            return 0;
        }

        $this->info("📋 Trouvé {$qrCodes->count()} QR codes à traiter");

        if ($dryRun) {
            $this->warn("🔍 MODE DRY-RUN - Aucune modification ne sera effectuée");
        }

        $this->line('');
        
        // Tableau de progression
        $headers = ['ID', 'Code', 'Type', 'PNG Avant', 'SVG Avant', 'Status'];
        $rows = [];

        $success = 0;
        $errors = 0;
        $unchanged = 0;

        foreach ($qrCodes as $qrCode) {
            $beforePng = !empty($qrCode->png_base64) ? 'OUI ('.strlen($qrCode->png_base64).')' : 'NON';
            $beforeSvg = !empty($qrCode->svg_content) ? 'OUI ('.strlen($qrCode->svg_content).')' : 'NON';
            
            if ($dryRun) {
                $rows[] = [
                    $qrCode->id,
                    substr($qrCode->code, 0, 15).'...',
                    $qrCode->type ?? 'N/A',
                    $beforePng,
                    $beforeSvg,
                    '🔍 SERAIT TRAITÉ'
                ];
                continue;
            }

            try {
                // Regénérer le QR code
                $updated = $this->qrCodeService->regenerateQrCodeSvg($qrCode);
                
                if ($updated) {
                    $afterPng = !empty($updated->png_base64) ? 'OUI ('.strlen($updated->png_base64).')' : 'NON';
                    $success++;
                    
                    $rows[] = [
                        $qrCode->id,
                        substr($qrCode->code, 0, 15).'...',
                        $qrCode->type ?? 'N/A',
                        $beforePng,
                        $beforeSvg,
                        "✅ PNG: {$afterPng}"
                    ];
                } else {
                    $errors++;
                    $rows[] = [
                        $qrCode->id,
                        substr($qrCode->code, 0, 15).'...',
                        $qrCode->type ?? 'N/A',
                        $beforePng,
                        $beforeSvg,
                        '❌ ÉCHEC'
                    ];
                }
                
            } catch (\Exception $e) {
                $errors++;
                $rows[] = [
                    $qrCode->id,
                    substr($qrCode->code, 0, 15).'...',
                    $qrCode->type ?? 'N/A',
                    $beforePng,
                    $beforeSvg,
                    '❌ ERREUR: '.substr($e->getMessage(), 0, 30)
                ];
                
                $this->error("Erreur QR Code {$qrCode->id}: {$e->getMessage()}");
            }
        }

        // Afficher le tableau de résultats
        $this->table($headers, $rows);

        // Résumé
        $this->line('');
        if ($dryRun) {
            $this->info("🔍 DRY-RUN TERMINÉ - {$qrCodes->count()} QR codes auraient été traités");
            $this->line("Pour exécuter réellement: php artisan qr:fix-missing-png");
        } else {
            $this->info("📊 RÉSUMÉ:");
            $this->line("  ✅ Succès: {$success}");
            $this->line("  ❌ Erreurs: {$errors}");
            $this->line("  📝 Total traité: " . ($success + $errors));
            
            if ($success > 0) {
                $this->info("🎉 {$success} QR codes ont été regénérés avec succès!");
            }
            
            if ($errors > 0) {
                $this->warn("⚠️  {$errors} erreurs rencontrées. Consultez les logs pour plus de détails.");
            }
        }

        return $errors > 0 ? 1 : 0;
    }
}