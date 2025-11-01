<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DebugOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (!config("app.debug")) {
            abort(404, "Page non trouvée");
        }

        return $next($request);
    }
}