<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     */
    public function handle($request, \Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $e) {
            // Log l'erreur pour debug
            \Log::warning('CSRF Token mismatch - régénération session', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => auth()->id(),
            ]);

            // Régénérer le token de session
            $request->session()->regenerateToken();

            // Si l'utilisateur est authentifié, rediriger vers la page précédente
            if (auth()->check()) {
                return redirect()->back()
                    ->withInput($request->except('_token'))
                    ->with('warning', 'Le formulaire a expiré. Vos données ont été conservées. Veuillez soumettre à nouveau.');
            }

            // Si non authentifié, rediriger vers la page demandée (GET)
            // pour rafraîchir le token
            $referer = $request->headers->get('referer');
            if ($referer) {
                return redirect($referer)
                    ->with('warning', 'Votre session a expiré. Veuillez réessayer.');
            }

            // Fallback: login
            return redirect()->route('login')
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }
    }
}
