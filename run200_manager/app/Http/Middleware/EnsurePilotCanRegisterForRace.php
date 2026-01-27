<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePilotCanRegisterForRace
{
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

        $pilot = $user->pilot;

        // If no pilot profile exists
        if (! $pilot) {
            session()->flash('error', 'Vous devez créer votre profil pilote avant de vous inscrire à une course.');

            return redirect()->route('pilot.profile.edit');
        }

        // Check if pilot can register for races
        if (! $pilot->canRegisterForRace()) {
            $reasons = $pilot->getRegistrationBlockingReasons();
            $message = implode(' ', $reasons);
            session()->flash('error', $message);

            // Redirect based on what's missing
            if (! $pilot->isProfileComplete()) {
                return redirect()->route('pilot.profile.edit');
            }

            if ($pilot->cars()->count() === 0) {
                return redirect()->route('pilot.cars.index')
                    ->with('warning', 'Vous devez enregistrer au moins une voiture pour vous inscrire aux courses.');
            }

            return redirect()->route('pilot.dashboard');
        }

        return $next($request);
    }
}
