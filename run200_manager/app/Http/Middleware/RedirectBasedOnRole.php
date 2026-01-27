<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // If accessing root or dashboard without specific role routing
            if ($request->is('/') || $request->is('dashboard')) {
                if ($user->isAdmin()) {
                    return redirect()->route('admin.dashboard');
                }

                if ($user->isStaff()) {
                    return redirect()->route('staff.dashboard');
                }

                if ($user->isPilot()) {
                    return redirect()->route('pilot.dashboard');
                }
            }
        }

        return $next($request);
    }
}
