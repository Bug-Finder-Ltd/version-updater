<?php

namespace BugFinder\Updater\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class VerifyAdminAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $guards = config('updater.auth_guards', ['admin', 'web']);
        $isAuthenticated = false;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $isAuthenticated = true;
                break;
            }
        }

        if (!$isAuthenticated) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated access to updater.'], 401);
            }

            // Try common admin login route names or fallback URL
            if (Route::has('admin.login')) {
                return redirect()->route('admin.login');
            }
            if (Route::has('login')) {
                return redirect()->route('login');
            }

            return redirect('/admin/login');
        }

        return $next($request);
    }
}
