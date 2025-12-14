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
                font-family: 'DejaVu Sans', Arial, sans-serif;
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
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => $format,
                'orientation' => $orientation,
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 55,      // Espace pour header
                'margin_bottom' => 45,    // Espace pour footer
                'margin_header' => 10,
                'margin_footer' => 10,
                'tempDir' => storage_path('app/temp')
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
            <table width="100%" style="font-family: Arial, sans-serif; font-size: 10px; border-bottom: 1px solid #ccc; padding-bottom: 5px;">
                <tr>
                    <td width="70%" style="vertical-align: top; padding: 3px;">
                        ' . $headerText . '
                    </td>
                    <td width="30%" style="text-align: right; vertical-align: top; padding: 3px;">
                        ' . ($logoBase64 ? '<img src="' . $logoBase64 . '" style="height: 35px; width: auto;" />' : '') . '
                    </td>
                </tr>
            </table>
            ';

            $mpdf->SetHTMLHeader($headerHtml);

            // ===== FOOTER FIXE (répété sur TOUTES les pages) =====
            $signatureText = $options['signature_text'] ?? '';
            $qrCodeBase64 = $options['qr_code_base64'] ?? '';

            // DEBUG
            \Log::info('PdfTemplateHelper Footer Debug', [
                'signature_text_length' => strlen($signatureText),
                'qr_code_base64_length' => strlen($qrCodeBase64),
                'signature_preview' => substr(strip_tags($signatureText), 0, 50),
            ]);

            $footerHtml = '
            <table width="100%" style="font-family: Arial, sans-serif; font-size: 9px; border-top: 1px solid #ccc; padding-top: 5px;">
                <tr>
                    <td width="30%" style="vertical-align: bottom; padding: 3px; text-align: left;">
                        ' . ($qrCodeBase64 ? '<img src="' . $qrCodeBase64 . '" style="width: 70px; height: 70px;" />' : '') . '
                    </td>
                    <td width="70%" style="vertical-align: bottom; padding: 3px; text-align: right;">
                        ' . $signatureText . '
                    </td>
                </tr>
            </table>
            ';

            $mpdf->SetHTMLFooter($footerHtml);

            // Écrire le contenu principal
            $mpdf->WriteHTML(self::getBaseStyle());
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
        return $mpdf->Output($filename, Destination::DOWNLOAD);
    }

    /**
     * Obtenir le PDF en string
     */
    public static function getPdfString(Mpdf $mpdf)
    {
        return $mpdf->Output('', Destination::STRING_RETURN);
    }
}
