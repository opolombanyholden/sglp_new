<?php

namespace App\Services;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentGenerationService
{
    /**
     * Génère un document HTML à partir d'une vue Blade
     *
     * @param string $view
     * @param array  $data
     * @return string
     *
     * @throws \Exception
     */
    public function generateHtml(string $view, array $data = []): string
    {
        if (!View::exists($view)) {
            throw new \Exception("La vue Blade '{$view}' n'existe pas.");
        }

        return View::make($view, $data)->render();
    }

    /**
     * Sauvegarde un contenu HTML dans un fichier
     *
     * @param string $html
     * @param string $directory
     * @param string|null $filename
     * @return string Chemin du fichier généré
     */
    public function saveHtmlToFile(
        string $html,
        string $directory = 'documents',
        ?string $filename = null
    ): string {
        $filename = $filename ?? Str::uuid() . '.html';
        $path = $directory . '/' . $filename;

        Storage::disk('local')->put($path, $html);

        return $path;
    }

    /**
     * Génère et sauvegarde un document HTML depuis une vue Blade
     *
     * @param string $view
     * @param array  $data
     * @param string $directory
     * @return string
     */
    public function generateAndSave(
        string $view,
        array $data = [],
        string $directory = 'documents'
    ): string {
        $html = $this->generateHtml($view, $data);

        return $this->saveHtmlToFile($html, $directory);
    }
}
