<?php

use App\Models\CarCategory;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\Season;
use App\Models\SeasonCategoryStanding;
use App\Models\SeasonStanding;
use App\Models\User;

describe('Public Race Calendar', function () {
    it('can access calendar page without authentication', function () {
        $this->get(route('public.calendar'))
            ->assertOk();
    });

    it('shows message when no active season', function () {
        // Ensure no active season
        Season::query()->update(['is_active' => false]);

        $this->get(route('public.calendar'))
            ->assertOk()
            ->assertSee('Aucune saison active');
    });

    it('displays upcoming races for active season', function () {
        $season = Season::factory()->create(['is_active' => true]);

        $upcomingRace = Race::create([
            'season_id' => $season->id,
            'name' => 'Course Future Test',
            'race_date' => now()->addDays(7),
            'location' => 'CIRCUIT TEST',
            'status' => 'OPEN',
        ]);

        $this->get(route('public.calendar'))
            ->assertOk()
            ->assertSee('Course Future Test')
            ->assertSee('CIRCUIT TEST')
            ->assertSee('Inscriptions ouvertes');
    });

    it('displays past races for active season', function () {
        $season = Season::factory()->create(['is_active' => true]);

        $pastRace = Race::create([
            'season_id' => $season->id,
            'name' => 'Course Passee Test',
            'race_date' => now()->subDays(7),
            'location' => 'ANCIEN CIRCUIT',
            'status' => 'PUBLISHED',
        ]);

        $this->get(route('public.calendar'))
            ->assertOk()
            ->assertSee('Course Passee Test')
            ->assertSee('Résultats publiés');
    });

    it('shows season statistics', function () {
        $season = Season::factory()->create(['is_active' => true]);

        Race::create([
            'season_id' => $season->id,
            'name' => 'Course 1',
            'race_date' => now()->addDays(7),
            'location' => 'CIRCUIT 1',
            'status' => 'OPEN',
        ]);

        Race::create([
            'season_id' => $season->id,
            'name' => 'Course 2',
            'race_date' => now()->subDays(7),
            'location' => 'CIRCUIT 2',
            'status' => 'PUBLISHED',
        ]);

        $this->get(route('public.calendar'))
            ->assertOk()
            ->assertSee('Courses au total')
            ->assertSee('Courses terminées')
            ->assertSee('Courses à venir');
    });

    it('shows registration CTA for guests', function () {
        $season = Season::factory()->create(['is_active' => true]);

        $this->get(route('public.calendar'))
            ->assertOk()
            ->assertSee('Rejoignez le championnat');
    });

    it('hides registration CTA for authenticated users', function () {
        $season = Season::factory()->create(['is_active' => true]);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('public.calendar'))
            ->assertOk()
            ->assertDontSee('Rejoignez le championnat');
    });
});

describe('Public Championship Standings', function () {
    it('can access standings page without authentication', function () {
        $this->get(route('public.standings'))
            ->assertOk();
    });

    it('shows message when no active season', function () {
        Season::query()->update(['is_active' => false]);

        $this->get(route('public.standings'))
            ->assertOk()
            ->assertSee('Aucune saison active');
    });

    it('displays general standings', function () {
        $season = Season::factory()->create(['is_active' => true]);
        $user = User::factory()->create();
        $pilot = Pilot::factory()->create(['user_id' => $user->id]);

        SeasonStanding::create([
            'season_id' => $season->id,
            'pilot_id' => $pilot->id,
            'races_count' => 3,
            'base_points' => 75,
            'bonus_points' => 20,
            'total_points' => 95,
            'rank' => 1,
            'computed_at' => now(),
        ]);

        $this->get(route('public.standings'))
            ->assertOk()
            ->assertSee($pilot->first_name)
            ->assertSee($pilot->last_name)
            ->assertSee('95');
    });

    it('displays category standings when selected', function () {
        $season = Season::factory()->create(['is_active' => true]);
        $category = CarCategory::create([
            'name' => 'SPORT',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $user = User::factory()->create();
        $pilot = Pilot::factory()->create(['user_id' => $user->id]);

        SeasonCategoryStanding::create([
            'season_id' => $season->id,
            'car_category_id' => $category->id,
            'pilot_id' => $pilot->id,
            'races_count' => 2,
            'base_points' => 50,
            'bonus_points' => 0,
            'total_points' => 50,
            'rank' => 1,
            'computed_at' => now(),
        ]);

        $this->get(route('public.standings', ['view' => $category->id]))
            ->assertOk()
            ->assertSee('SPORT')
            ->assertSee($pilot->first_name);
    });

    it('shows ranking rules information', function () {
        $season = Season::factory()->create(['is_active' => true]);

        $this->get(route('public.standings'))
            ->assertOk()
            ->assertSee('Règles du championnat')
            ->assertSee('courses')
            ->assertSee('points');
    });

    it('displays category tabs', function () {
        $season = Season::factory()->create(['is_active' => true]);

        CarCategory::create([
            'name' => 'CATEGORIE A',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        CarCategory::create([
            'name' => 'CATEGORIE B',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $this->get(route('public.standings'))
            ->assertOk()
            ->assertSee('Classement Général')
            ->assertSee('CATEGORIE A')
            ->assertSee('CATEGORIE B');
    });

    it('shows NC for pilots with insufficient races', function () {
        $season = Season::factory()->create(['is_active' => true]);
        $user = User::factory()->create();
        $pilot = Pilot::factory()->create(['user_id' => $user->id]);

        SeasonStanding::create([
            'season_id' => $season->id,
            'pilot_id' => $pilot->id,
            'races_count' => 1, // Less than minimum
            'base_points' => 25,
            'bonus_points' => 0,
            'total_points' => 25,
            'rank' => null, // Not ranked
            'computed_at' => now(),
        ]);

        $this->get(route('public.standings'))
            ->assertOk()
            ->assertSee('NC');
    });

    it('shows bonus badge for pilots with bonus points', function () {
        $season = Season::factory()->create(['is_active' => true]);
        $user = User::factory()->create();
        $pilot = Pilot::factory()->create(['user_id' => $user->id]);

        SeasonStanding::create([
            'season_id' => $season->id,
            'pilot_id' => $pilot->id,
            'races_count' => 5,
            'base_points' => 100,
            'bonus_points' => 20,
            'total_points' => 120,
            'rank' => 1,
            'computed_at' => now(),
        ]);

        $this->get(route('public.standings'))
            ->assertOk()
            ->assertSee('+20');
    });

    it('shows registration CTA for guests', function () {
        $season = Season::factory()->create(['is_active' => true]);

        $this->get(route('public.standings'))
            ->assertOk()
            ->assertSee('Rejoignez le championnat');
    });
});

describe('Public Routes Navigation', function () {
    it('calendar link is accessible from standings', function () {
        $this->get(route('public.standings'))
            ->assertOk()
            ->assertSee('Calendrier');
    });

    it('standings link is accessible from calendar', function () {
        $this->get(route('public.calendar'))
            ->assertOk()
            ->assertSee('Classement');
    });

    it('shows login and register links for guests', function () {
        $this->get(route('public.calendar'))
            ->assertOk()
            ->assertSee('Connexion')
            ->assertSee('Inscription'); // Le bouton utilise "Inscription"
    });

    it('shows dashboard link for authenticated users', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('public.calendar'))
            ->assertOk()
            ->assertSee('Mon Espace'); // Avec majuscule sur Espace
    });
});
