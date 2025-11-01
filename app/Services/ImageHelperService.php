<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * SERVICE HELPER POUR GESTION D'IMAGES DANS LES PDF
 * 
 * Ce service centralise la conversion d'images en base64
 * pour une utilisation optimale avec DomPDF
 */
class ImageHelperService
{
    /**
     * Convertit une image en base64 pour les PDF
     * 
     * @param string $path Chemin vers l'image
     * @param string $disk Disque de stockage (par défaut 'public')
     * @return string|null Base64 data URI ou null si échec
     */
    public function getImageAsBase64(string $path, string $disk = 'public'): ?string
    {
        try {
            // Méthode 1: Via Storage facade (recommandé)
            if (Storage::disk($disk)->exists($path)) {
                $imageContent = Storage::disk($disk)->get($path);
                $fullPath = Storage::disk($disk)->path($path);
                $mimeType = $this->getMimeType($fullPath);
                
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
            }
            
            // Méthode 2: Chemin absolu si Storage échoue
            $absolutePath = storage_path("app/{$disk}/" . ltrim($path, '/'));
            if (file_exists($absolutePath)) {
                $imageContent = file_get_contents($absolutePath);
                $mimeType = $this->getMimeType($absolutePath);
                
                return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
            }
            
            Log::warning('Image non trouvée pour PDF', ['path' => $path, 'disk' => $disk]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Erreur conversion image base64', [
                'path' => $path,
                'disk' => $disk,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Convertit un QR code en base64 pour PDF
     * 
     * @param \App\Models\QrCode $qrCode
     * @return string|null
     */
    public function getQrCodeAsBase64($qrCode): ?string
    {
        // Priorité 1: PNG base64 déjà en base
        if (!empty($qrCode->png_base64)) {
            return 'data:image/png;base64,' . $qrCode->png_base64;
        }
        
        // Priorité 2: Fichier PNG
        if (!empty($qrCode->file_path)) {
            $base64 = $this->getImageAsBase64($qrCode->file_path);
            if ($base64) {
                return $base64;
            }
        }
        
        // Priorité 3: Générer depuis URL si possible
        if (!empty($qrCode->verification_url)) {
            return $this->generateQrCodeBase64($qrCode->verification_url);
        }
        
        return null;
    }
    
    /**
     * Génère un QR code en base64 à la volée
     * 
     * @param string $url URL à encoder
     * @return string|null
     */
    private function generateQrCodeBase64(string $url): ?string
    {
        try {
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                return null;
            }
            
            $qrData = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(120)
                ->margin(2)
                ->color(0, 62, 127)
                ->backgroundColor(255, 255, 255)
                ->errorCorrection('H')
                ->generate($url);
            
            return 'data:image/png;base64,' . base64_encode($qrData);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération QR code base64', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Détermine le MIME type d'un fichier
     * 
     * @param string $filePath
     * @return string
     */
    private function getMimeType(string $filePath): string
    {
        if (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath);
            if ($mimeType) {
                return $mimeType;
            }
        }
        
        // Fallback basé sur l'extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp'
        ];
        
        return $mimeTypes[$extension] ?? 'image/png';
    }
    
    /**
     * Valide qu'une image peut être utilisée dans un PDF
     * 
     * @param string $path
     * @param string $disk
     * @return array ['valid' => bool, 'error' => string|null, 'size' => int]
     */
    public function validateImageForPdf(string $path, string $disk = 'public'): array
    {
        try {
            if (!Storage::disk($disk)->exists($path)) {
                return [
                    'valid' => false,
                    'error' => 'Fichier non trouvé',
                    'size' => 0
                ];
            }
            
            $size = Storage::disk($disk)->size($path);
            $maxSize = 2 * 1024 * 1024; // 2MB max pour PDF
            
            if ($size > $maxSize) {
                return [
                    'valid' => false,
                    'error' => 'Fichier trop volumineux (max 2MB)',
                    'size' => $size
                ];
            }
            
            $fullPath = Storage::disk($disk)->path($path);
            $mimeType = $this->getMimeType($fullPath);
            
            $allowedTypes = ['image/png', 'image/jpeg', 'image/gif'];
            if (!in_array($mimeType, $allowedTypes)) {
                return [
                    'valid' => false,
                    'error' => 'Type de fichier non supporté: ' . $mimeType,
                    'size' => $size
                ];
            }
            
            return [
                'valid' => true,
                'error' => null,
                'size' => $size
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'error' => 'Erreur validation: ' . $e->getMessage(),
                'size' => 0
            ];
        }
    }
    
    /**
     * Crée un placeholder SVG pour les images manquantes
     * 
     * @param string $text Texte à afficher
     * @param int $width Largeur
     * @param int $height Hauteur
     * @return string SVG en base64
     */
    public function createPlaceholderSvg(string $text = 'Image', int $width = 100, int $height = 100): string
    {
        $svg = "<?xml version='1.0' encoding='UTF-8'?>
        <svg width='{$width}' height='{$height}' xmlns='http://www.w3.org/2000/svg'>
            <rect width='{$width}' height='{$height}' fill='#f8f9fa' stroke='#003f7f' stroke-width='2' stroke-dasharray='4,4'/>
            <text x='50%' y='50%' font-family='Arial' font-size='12' text-anchor='middle' 
                  dominant-baseline='middle' fill='#666'>{$text}</text>
        </svg>";
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}