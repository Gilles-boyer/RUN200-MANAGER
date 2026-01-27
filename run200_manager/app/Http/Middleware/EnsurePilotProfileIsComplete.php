<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePilotProfileIsComplete
{
    /**
     * Routes that should be excluded from profile completion check.
     *
     * @var array<string>
     */
    protected array $excludedRoutes = [
        'pilot.profile.show',
        'pilot.profile.edit',
        'pilot.cars.index',
        'pilot.cars.create',
        'pilot.cars.store',
        'logout',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Skip if user doesn't have PILOTE role
        if (! $user || ! $user->hasRole('PILOTE')) {
            return $next($request);
        }

        // Skip if on excluded routes
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && in_array($currentRoute, $this->excludedRoutes)) {
            return $next($request);
        }

        $pilot = $user->pilot;

        // If no pilot profile exists, redirect to create it
        if (! $pilot) {
            session()->flash('warning', 'Veuillez créer votre profil pilote pour continuer.');

            return redirect()->route('pilot.profile.edit');
        }

        // If profile is not complete, redirect to complete it
        if (! $pilot->isProfileComplete()) {
            $percentage = $pilot->getProfileCompletionPercentage();
            session()->flash('warning', "Votre profil est complété à {$percentage}%. Veuillez le compléter à 100% pour accéder à toutes les fonctionnalités.");

            return redirect()->route('pilot.profile.edit');
        }

        return $next($request);
    }
}
