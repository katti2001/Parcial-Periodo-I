<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlmacenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || (!Auth::user()->esAlmacen() && !Auth::user()->esAdmin())) {
            abort(403, 'Acceso restringido. Se requiere rol almacén o administrador.');
        }

        return $next($request);
    }
}
