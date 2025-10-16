<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BasicAuthMiddleware
{

    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('is_logged_in')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
