<?php

use App\Http\Controllers\RaceBoardController;
use App\Http\Controllers\Staff\ExportEngagedCsvController;
use App\Http\Controllers\Webhook\StripeWebhookController;
use App\Http\Middleware\EnsurePilotCanRegisterForRace;
use App\Infrastructure\Pdf\EngagedListPdfService;
use App\Infrastructure\Pdf\EngagementFormPdfService;
use App\Livewire\Admin\Championship;
use App\Livewire\Admin\PaddockSpots\PaddockMap;
use App\Livewire\Admin\Races\Documents;
use App\Livewire\Admin\Races\Notifications;
use App\Livewire\Admin\Seasons\PointsRules;
use App\Livewire\Pilot\Cars\Form;
use App\Livewire\Pilot\Cars\Index;
use App\Livewire\Pilot\ChampionshipStanding;
use App\Livewire\Pilot\Dashboard;
use App\Livewire\Pilot\Profile\Edit;
use App\Livewire\Pilot\Profile\Show;
use App\Livewire\Pilot\RaceResults;
use App\Livewire\Pilot\Registrations\Create;
use App\Livewire\Pilot\Registrations\Ecard;
use App\Livewire\Pilot\Registrations\PaddockSelection;
use App\Livewire\Pilot\Registrations\Payment;
use App\Livewire\Pilot\Registrations\PaymentCancel;
use App\Livewire\Pilot\Registrations\PaymentSuccess;
use App\Livewire\Public\BoardIndex;
use App\Livewire\Public\ChampionshipStandings;
use App\Livewire\Public\RaceBoard;
use App\Livewire\Public\RaceCalendar;
use App\Livewire\Staff\Cars\TechInspectionHistory;
use App\Livewire\Staff\Paddock\ManagePaddock;
use App\Livewire\Staff\Registrations\CheckpointsManager;
use App\Livewire\Staff\Registrations\EngagementSign;
use App\Livewire\Staff\Registrations\PaymentManager;
use App\Livewire\Staff\Registrations\TechInspectionForm;
use App\Livewire\Staff\Registrations\WalkInRegistration;
use App\Livewire\Staff\Results\ResultsManager;
use App\Livewire\Staff\Scan\Scanner;
use App\Models\EngagementForm;
use App\Models\Race;
use Illuminate\Support\Facades\Route;

// Design System Preview (dev only)
if (app()->environment('local')) {
    Route::get('/design-system', fn () => view('design-system'))->name('design-system');
}

// Stripe Webhook (must be outside auth middleware)
Route::post('/stripe/webhook', StripeWebhookController::class)
    ->name('stripe.webhook')
    ->withoutMiddleware(['web', 'csrf']);

// =========================================================================
// Public Routes (no authentication required)
// =========================================================================
Route::prefix('public')->name('public.')->group(function () {
    Route::get('/calendrier', RaceCalendar::class)->name('calendar');
    Route::get('/classement', ChampionshipStandings::class)->name('standings');
    Route::get('/resultats/{race}', RaceResults::class)->name('results.race');
    Route::get('/tableaux-affichage', BoardIndex::class)->name('boards');
});

// =========================================================================
// Legal Pages (public - no authentication required)
// =========================================================================
Route::get('/mentions-legales', fn () => view('pages.legal'))->name('legal');
Route::get('/confidentialite', fn () => view('pages.privacy'))->name('privacy');

// =========================================================================
// Tableau d'affichage numérique (Public - accessible sans authentification)
// =========================================================================
Route::prefix('board')->name('board.')->middleware('throttle:60,1')->group(function () {
    Route::get('/{race:slug}', RaceBoard::class)->name('show');
    Route::get('/doc/{slug}', [RaceBoardController::class, 'view'])->name('view');
    Route::get('/doc/{slug}/download', [RaceBoardController::class, 'download'])->name('download');
});

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->isStaff()) {
            return redirect()->route('staff.dashboard');
        }

        if ($user->isPilot()) {
            return redirect()->route('pilot.dashboard');
        }

        return redirect()->route('dashboard');
    }

    return view('welcome');
})->name('home');

Route::get('dashboard', function () {
    $user = auth()->user();

    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->isStaff()) {
        return redirect()->route('staff.dashboard');
    }

    if ($user->isPilot()) {
        return redirect()->route('pilot.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Pilot Routes
Route::middleware(['auth', 'role:PILOTE'])->prefix('pilot')->name('pilot.')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // Profile Routes (always accessible for pilots)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', Show::class)->name('show');
        Route::get('/edit', Edit::class)->name('edit');
    });

    // Cars Routes (always accessible for pilots)
    Route::prefix('cars')->name('cars.')->group(function () {
        Route::get('/', Index::class)->name('index');
        Route::get('/create', Form::class)->name('create');
        Route::get('/{car}/edit', Form::class)->name('edit');
    });

    // Races Routes (browse open races)
    Route::prefix('races')->name('races.')->group(function () {
        Route::get('/', App\Livewire\Pilot\Races\Index::class)->name('index');
    });

    // Existing registrations and Stripe returns remain accessible if profile details change.
    Route::prefix('registrations')->name('registrations.')->group(function () {
        Route::get('/', App\Livewire\Pilot\Registrations\Index::class)->name('index');
        Route::get('/create/{race}', Create::class)
            ->middleware(EnsurePilotCanRegisterForRace::class)
            ->name('create');
        Route::get('/{registration}/ecard', Ecard::class)->name('ecard');
        Route::get('/{registration}/payment', Payment::class)->name('payment');
        Route::get('/{registration}/payment/success', PaymentSuccess::class)->name('payment.success');
        Route::get('/{registration}/payment/cancel', PaymentCancel::class)->name('payment.cancel');
        Route::get('/{registration}/paddock', PaddockSelection::class)->name('paddock.select');
    });

    // Results Routes (public published results)
    Route::prefix('results')->name('results.')->group(function () {
        Route::get('/{race}', RaceResults::class)->name('race');
    });

    // Championship Routes
    Route::get('/championship', ChampionshipStanding::class)->name('championship');
    Route::get('/championship/{season}', ChampionshipStanding::class)->name('championship.season');
});

// Staff Routes
Route::middleware(['auth', 'role:ADMIN|STAFF_ADMINISTRATIF|CONTROLEUR_TECHNIQUE|STAFF_ENTREE|STAFF_SONO'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', App\Livewire\Staff\Dashboard::class)->name('dashboard');

    // Registration management
    Route::prefix('registrations')->name('registrations.')->group(function () {
        Route::get('/', App\Livewire\Staff\Registrations\Index::class)->name('index');
        Route::get('/walk-in', WalkInRegistration::class)
            ->middleware('permission:registration.manage')
            ->name('walk-in');
        Route::get('/engagement', EngagementSign::class)
            ->middleware('permission:registration.manage')
            ->name('engagement');
        Route::get('/engagement/{registration}', EngagementSign::class)
            ->middleware('permission:registration.manage')
            ->name('engagement.registration');
        Route::get('/engagement-pdf/{engagement}', function (EngagementForm $engagement) {
            $pdfService = new EngagementFormPdfService;

            return $pdfService->stream($engagement);
        })->middleware('permission:registration.manage')->name('engagement-pdf');
        Route::get('/engagement-pdf/{engagement}/download', function (EngagementForm $engagement) {
            $pdfService = new EngagementFormPdfService;

            return $pdfService->download($engagement);
        })->middleware('permission:registration.manage')->name('engagement-pdf-download');
        Route::get('/{registration}/checkpoints', CheckpointsManager::class)
            ->name('checkpoints');
        Route::get('/{registration}/tech', TechInspectionForm::class)
            ->middleware('permission:tech_inspection.manage')
            ->name('tech');
        Route::get('/{registration}/payments', PaymentManager::class)
            ->middleware('permission:payment.manage')
            ->name('payments');
    });

    // Car management and tech inspection history
    Route::get('/cars', App\Livewire\Staff\Cars\Index::class)
        ->middleware('permission:tech_inspection.manage')
        ->name('cars.index');
    Route::get('/cars/{car}/tech-history', TechInspectionHistory::class)
        ->middleware('permission:tech_inspection.manage')
        ->name('cars.tech-history');

    // Race management
    Route::prefix('races')->name('races.')->group(function () {
        Route::get('/', App\Livewire\Staff\Races\Index::class)->name('index');
        Route::get('/{race}/engaged-pdf', function (Race $race) {
            $pdfService = new EngagedListPdfService;

            return $pdfService->download($race);
        })->name('engaged-pdf');
        Route::get('/{race}/engaged-csv', ExportEngagedCsvController::class)
            ->name('engaged-csv');
        Route::get('/{race}/results', ResultsManager::class)
            ->middleware('permission:race.manage')
            ->name('results');
    });

    // Checkpoint Scanners
    Route::prefix('scan')->name('scan.')->middleware('throttle:scan')->group(function () {
        Route::get('/admin', Scanner::class)
            ->defaults('checkpointCode', 'ADMIN_CHECK')
            ->name('admin');
        Route::get('/tech', Scanner::class)
            ->defaults('checkpointCode', 'TECH_CHECK')
            ->name('tech');
        Route::get('/entry', Scanner::class)
            ->defaults('checkpointCode', 'ENTRY')
            ->name('entry');
        Route::get('/bracelet', Scanner::class)
            ->defaults('checkpointCode', 'BRACELET')
            ->name('bracelet');
        Route::get('/assistance', Scanner::class)
            ->defaults('checkpointCode', 'ASSISTANCE')
            ->name('assistance');
    });

    // Pilots management
    Route::prefix('pilots')->name('pilots.')->group(function () {
        Route::get('/', App\Livewire\Staff\Pilots\Index::class)->name('index');
        Route::get('/create', App\Livewire\Staff\Pilots\Create::class)->name('create');
        Route::get('/{pilot}/edit', App\Livewire\Staff\Pilots\Edit::class)->name('edit');
    });

    // Paddock management
    Route::get('/paddock', ManagePaddock::class)
        ->middleware('permission:registration.manage')
        ->name('paddock.manage');
});

// Admin Routes
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/home', App\Livewire\Admin\Dashboard::class)->name('dashboard');

    // Seasons management
    Route::prefix('seasons')->name('seasons.')->group(function () {
        Route::get('/', App\Livewire\Admin\Seasons\Index::class)->name('index');
        Route::get('/create', App\Livewire\Admin\Seasons\Form::class)->name('create');
        Route::get('/{season}/edit', App\Livewire\Admin\Seasons\Form::class)->name('edit');
        Route::get('/{season}/points-rules', PointsRules::class)->name('points-rules');
    });

    // Races management
    Route::prefix('races')->name('races.')->group(function () {
        Route::get('/', App\Livewire\Admin\Races\Index::class)->name('index');
        Route::get('/create', App\Livewire\Admin\Races\Form::class)->name('create');
        Route::get('/{race}/edit', App\Livewire\Admin\Races\Form::class)->name('edit');
        Route::get('/{race}/notifications', Notifications::class)->name('notifications');
        Route::get('/{race}/documents', Documents::class)->name('documents');
    });

    // Users management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', App\Livewire\Admin\Users\Index::class)->name('index');
    });

    // Car Categories management
    Route::prefix('car-categories')->name('car-categories.')->group(function () {
        Route::get('/', App\Livewire\Admin\CarCategories\Index::class)->name('index');
    });

    // Checkpoints management
    Route::prefix('checkpoints')->name('checkpoints.')->group(function () {
        Route::get('/', App\Livewire\Admin\Checkpoints\Index::class)->name('index');
    });

    // Paddock Spots management
    Route::prefix('paddock-spots')->name('paddock-spots.')->group(function () {
        Route::get('/', App\Livewire\Admin\PaddockSpots\Index::class)->name('index');
        Route::get('/map', PaddockMap::class)->name('map');
    });

    // Registrations management (Admin can register pilots manually)
    Route::prefix('registrations')->name('registrations.')->group(function () {
        Route::get('/walk-in', WalkInRegistration::class)->name('walk-in');
    });

    // Championship Routes
    Route::get('/championship/{season}', Championship::class)->name('championship');
});

require __DIR__.'/settings.php';
