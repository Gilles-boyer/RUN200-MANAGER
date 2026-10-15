<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();

        // Fortify owns this route and loads it after application providers boot.
        Route::matched(function (RouteMatched $event): void {
            if ($event->route->getName() !== 'register.store') {
                return;
            }

            // Fortify lowercases email before validation and cannot accept an array here.
            if ($event->request->has('email') && ! is_string($event->request->input('email'))) {
                $event->request->merge(['email' => '']);
            }

            if (! in_array('throttle:registration', $event->route->middleware(), true)) {
                $event->route->middleware('throttle:registration');
            }
        });
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn () => view('pages::auth.login'));
        Fortify::verifyEmailView(fn () => view('pages::auth.verify-email'));
        Fortify::twoFactorChallengeView(fn () => view('pages::auth.two-factor-challenge'));
        Fortify::confirmPasswordView(fn () => view('pages::auth.confirm-password'));
        Fortify::registerView(fn () => view('pages::auth.register'));
        Fortify::resetPasswordView(fn () => view('pages::auth.reset-password'));
        Fortify::requestPasswordResetLinkView(fn () => view('pages::auth.forgot-password'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('registration', function (Request $request) {
            $tooManyAttempts = function (Request $request, array $headers) {
                $message = 'Trop de tentatives d’inscription. Veuillez patienter une minute avant de réessayer.';

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 429, $headers);
                }

                return redirect()->route('register')
                    ->withErrors(['registration' => $message])
                    ->withInput($request->only('first_name', 'last_name', 'email', 'phone', 'license_number', 'terms'))
                    ->withHeaders($headers);
            };

            $submittedEmail = $request->input('email');
            $email = is_string($submittedEmail) ? Str::lower(trim($submittedEmail)) : '';

            return [
                Limit::perMinute(30)->by('registration-ip:'.$request->ip())->response($tooManyAttempts),
                Limit::perMinute(6)->by('registration-email:'.$email.'|'.$request->ip())->response($tooManyAttempts),
            ];
        });
    }
}
