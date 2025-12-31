<?php

namespace App\Helpers;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Helper pour la génération de PDF avec mPDF
 * Version avec header/footer fixes sur chaque page
 */
class PdfTemplateHelper
{
    /**
     * Chemin vers le logo du ministère
     */
    private static function getLogoPath()
    {
        return public_path('storage/images/logo-ministere.png');
    }

    /**
     * Obtenir le logo en base64
     */
    private static function getLogoBase64()
    {
        $path = self::getLogoPath();
        if (file_exists($path)) {
            $data = base64_encode(file_get_contents($path));
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            return "data:image/{$ext};base64,{$data}";
        }
        return '';
    }

    /**
     * Obtenir le style CSS de base pour les PDFs
     */
    private static function getBaseStyle()
    {
        return "
        <style>
            body {
                font-family: 'garamond', serif;
                font-size: 11pt;
                line-height: 1.6;
                color: #000;
            }
            h1 {
                color: #009e3f;
                font-size: 16pt;
                text-align: center;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .font-bold { font-weight: bold; }
        </style>
        ";
    }

    /**
     * Générer un PDF avec mPDF et header/footer fixes répétés sur chaque page
     * 
     * @param string $html HTML du contenu
     * @param string $orientation 'P' (portrait) ou 'L' (paysage)
     * @param string $format Format A4, Letter, etc.
     * @param array $options Options : header_text, signature_text, qr_code_base64
     * @return Mpdf
     */
    public static function generatePdf($html, $orientation = 'P', $format = 'A4', $options = [])
    {
        try {
            // Ajuster les marges selon les options
            $marginTop = 55;  // Par défaut avec header
            $marginFooter = 10; // Par défaut
            $marginBottom = 45; // Par défaut

            if (!empty($options['header_first_page_only'])) {
                $marginTop = 15;  // Réduire si header seulement sur 1ère page
            }

            if (!empty($options['bg_in_footer'])) {
                $marginFooter = 0;  // Aucune marge pour coller l'image au bas
                $marginBottom = 30; // Réduire légèrement pour laisser place au footer
            }

            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => $format,
                'orientation' => $orientation,
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => $marginTop,      // Espace pour header (ajusté dynamiquement)
                'margin_bottom' => $marginBottom,    // Espace pour footer
                'margin_header' => 10,
                'margin_footer' => $marginFooter,
                'tempDir' => storage_path('app/temp'),
                'fontDir' => [storage_path('fonts')],
                'fontdata' => [
                    'garamond' => [
                        'R' => 'EBGaramond-Regular.ttf',
                        'B' => 'EBGaramond-Bold.ttf',
                        'I' => 'EBGaramond-Italic.ttf',
                        'BI' => 'EBGaramond-BoldItalic.ttf',
                    ],
                    // Variantes supplémentaires (optionnel)
                    'garamond-medium' => [
                        'R' => 'EBGaramond-Medium.ttf',
                        'I' => 'EBGaramond-MediumItalic.ttf',
                    ],
                    'garamond-semibold' => [
                        'R' => 'EBGaramond-SemiBold.ttf',
                        'I' => 'EBGaramond-SemiBoldItalic.ttf',
                    ],
                    'garamond-extrabold' => [
                        'R' => 'EBGaramond-ExtraBold.ttf',
                        'I' => 'EBGaramond-ExtraBoldItalic.ttf',
                    ],
                ],
                'default_font' => 'garamond'
            ]);

            // Configuration
            $mpdf->SetAuthor('PNGDI - Ministère de l\'Intérieur');
            $mpdf->SetCreator('PNGDI Platform');

            // ===== HEADER FIXE (répété sur TOUTES les pages) =====
            $headerText = $options['header_text'] ?? '';
            $logoBase64 = self::getLogoBase64();

            // DEBUG
            \Log::info('PdfTemplateHelper Header Debug', [
                'header_text_length' => strlen($headerText),
                'logo_base64_length' => strlen($logoBase64),
                'header_preview' => substr(strip_tags($headerText), 0, 50),
            ]);

            $headerHtml = '
            <div>
                <table width="100%" style="font-family: Arial, sans-serif; font-size: 10px;">
                    <tr>
                        <td width="70%" style="vertical-align: top; padding: 3px;">
                            ' . $headerText . '
                        </td>
                        <td width="30%" style="text-align: right; vertical-align: top; padding: 3px;">
                            ' . ($logoBase64 ? '<img src="' . $logoBase64 . '" style="height: 80px; width: auto;" />' : '') . '
                        </td>
                    </tr>
                </table>
            </div>
            ';

            // Si header uniquement sur première page
            if (!empty($options['header_first_page_only'])) {
                // Ne pas utiliser SetHTMLHeader, on intègrera le header directement dans le HTML
                // et on désactivera le header après la première page
            } else {
                $mpdf->SetHTMLHeader($headerHtml);
            }

            // ===== FOOTER FIXE (répété sur TOUTES les pages) =====
            $signatureText = $options['signature_text'] ?? '';
            $qrCodeBase64 = $options['qr_code_base64'] ?? '';

            // DEBUG
            \Log::info('PdfTemplateHelper Footer Debug', [
                'signature_text_length' => strlen($signatureText),
                'qr_code_base64_length' => strlen($qrCodeBase64),
                'is_qr_code_empty' => empty($qrCodeBase64),
                'signature_preview' => substr(strip_tags($signatureText), 0, 50),
            ]);

            // Footer avec QR Code en bas à gauche
            $footerHtml = '';

            // Charger l'image de fond
            $bgImageBase64 = '';
            $bgImagePath = public_path('storage/images/bg-pied-page.png');
            if (file_exists($bgImagePath)) {
                $imageData = file_get_contents($bgImagePath);
                $bgImageBase64 = 'data:image/png;base64,' . base64_encode($imageData);
            }

            // FOOTER avec image de fond (pour récépissé définitif)
            if (!empty($options['bg_in_footer']) && $bgImageBase64) {
                // QR code par-dessus l'image de fond avec la structure spéciale
                if ($qrCodeBase64) {
                    $qrCodeBase64 = trim($qrCodeBase64);
                    $footerHtml = '
                    <table style="width: 80px; margin-left: -20px; margin-bottom: -740px; background-color: white; padding: 5px; position: relative; z-index: 999;">
                        <tr><td style="width: 70px;"><img src="' . $qrCodeBase64 . '" style="width: 60px; height: 60px;" /></td></tr>
                    </table>
                    <div style="margin-left: -15mm; margin-right: -15mm;">
                        <img src="' . $bgImageBase64 . '" style="width: 100%; height: auto; display: block;" />
                    </div>';
                } else {
                    // Juste l'image de fond sans QR code
                    $footerHtml = '<div style="margin-left: -15mm; margin-right: -15mm; margin-bottom: -20mm;"><img src="' . $bgImageBase64 . '" style="width: 100%; height: auto; display: block; position: relative; z-index: 1;" /></div>';
                }
            } else {
                // FOOTER simple pour autres documents (récépissé provisoire, accusé, etc.)
                if ($qrCodeBase64) {
                    $qrCodeBase64 = trim($qrCodeBase64);
                    // QR code simple en bas à gauche
                    $footerHtml = '<div style="text-align: left; padding: 5px;"><img src="' . $qrCodeBase64 . '" style="width: 60px; height: 60px;" /></div>';
                }
            }

            $mpdf->SetHTMLFooter($footerHtml);

            // Écrire le contenu principal
            $mpdf->WriteHTML(self::getBaseStyle());

            // Si header uniquement sur première page, on l'ajoute directement dans le HTML
            if (!empty($options['header_first_page_only'])) {
                $mpdf->WriteHTML($headerHtml);
            }

            // Écrire le contenu principal
            $mpdf->WriteHTML($html);

            return $mpdf;

        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la génération du PDF: ' . $e->getMessage());
        }
    }

    /**
     * Pour compatibilité - ancienne méthode wrapContent (désormais header/footer intégrés dans generatePdf)
     */
    public static function wrapContent($title, $content)
    {
        return $content; // Le wrapping est maintenant géré par SetHTMLHeader/Footer
    }

    /**
     * Télécharger le PDF
     */
    public static function downloadPdf(Mpdf $mpdf, $filename = 'document.pdf')
    {
        // Obtenir le contenu PDF en string
        $pdfContent = $mpdf->Output('', Destination::STRING_RETURN);

        // Retourner une réponse Laravel avec les bons headers
        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($pdfContent))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Obtenir le PDF en string
     */
    public static function getPdfString(Mpdf $mpdf)
    {
        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
