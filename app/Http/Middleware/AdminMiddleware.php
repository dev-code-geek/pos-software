<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Use Spatie's hasRole() - NOT isAdmin()
        if (!$user->hasRole('super_admin') && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized access. Admin privileges required.');
        }

        return $next($request);
    }
}