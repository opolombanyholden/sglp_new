<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (!$request->expectsJson()) {
            // Debug: Log quand un utilisateur non authentifié essaie d'accéder
            Log::warning('Utilisateur non authentifié redirigé vers login', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'session_id' => session()->getId(),
                'has_session' => session()->isStarted(),
            ]);

            return route('login');
        }
    }
}
