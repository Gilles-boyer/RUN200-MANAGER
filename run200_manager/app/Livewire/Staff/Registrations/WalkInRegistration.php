<?php

declare(strict_types=1);

namespace App\Livewire\Staff\Registrations;

use App\Infrastructure\Qr\QrTokenService;
use App\Models\Car;
use App\Models\CarCategory;
use App\Models\Payment;
use App\Models\Pilot;
use App\Models\Race;
use App\Models\RaceRegistration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class WalkInRegistration extends Component
{
    use WithFileUploads;

    // Step management
    public int $currentStep = 1;

    // Step 1: Select or create pilot
    public string $pilotMode = 'search'; // 'search' or 'create'

    public string $pilotSearch = '';

    public ?int $selectedPilotId = null;

    // New pilot data
    #[Validate('required_if:pilotMode,create|string|max:255')]
    public string $newPilotFirstName = '';

    #[Validate('required_if:pilotMode,create|string|max:255')]
    public string $newPilotLastName = '';

    #[Validate('required_if:pilotMode,create|email|max:255')]
    public string $newPilotEmail = '';

    #[Validate('required_if:pilotMode,create|string|max:20')]
    public string $newPilotLicense = '';

    #[Validate('required_if:pilotMode,create|date|before:today')]
    public string $newPilotBirthDate = '';

    #[Validate('required_if:pilotMode,create|string|max:255')]
    public string $newPilotBirthPlace = '';

    #[Validate('required_if:pilotMode,create|string|max:20')]
    public string $newPilotPhone = '';

    #[Validate('required_if:pilotMode,create|string|max:500')]
    public string $newPilotAddress = '';

    public string $newPilotCity = '';

    public string $newPilotPostalCode = '';

    public string $newPilotPermitNumber = '';

    public string $newPilotPermitDate = '';

    public string $newPilotEmergencyContactName = '';

    public string $newPilotEmergencyContactPhone = '';

    public bool $newPilotIsMinor = false;

    public string $newPilotGuardianFirstName = '';

    public string $newPilotGuardianLastName = '';

    public string $newPilotGuardianLicense = '';

    public string $newPilotGuardianPhone = '';

    public $newPilotPhoto = null;

    // Step 2: Select or create car
    public string $carMode = 'select'; // 'select' or 'create'

    public ?int $selectedCarId = null;

    // New car data
    #[Validate('required_if:carMode,create|integer|between:0,999')]
    public ?int $newCarRaceNumber = null;

    #[Validate('required_if:carMode,create|string|max:255')]
    public string $newCarMake = '';

    #[Validate('required_if:carMode,create|string|max:255')]
    public string $newCarModel = '';

    #[Validate('required_if:carMode,create|exists:car_categories,id')]
    public ?int $newCarCategoryId = null;

    // Step 3: Select race
    #[Validate('required|exists:races,id')]
    public ?int $selectedRaceId = null;

    // Step 4: Payment
    #[Validate('required|in:cash,bank_transfer,card_onsite')]
    public string $paymentMethod = 'cash';

    public bool $paymentReceived = true;

    public string $paymentNotes = '';

    // Result
    public ?RaceRegistration $createdRegistration = null;

    public ?string $errorMessage = null;

    public ?string $successMessage = null;

    #[Computed]
    public function searchResults()
    {
        if (strlen($this->pilotSearch) < 2) {
            return collect();
        }

        return Pilot::with('user')
            ->where(function ($query) {
                $query->where('first_name', 'like', '%'.$this->pilotSearch.'%')
                    ->orWhere('last_name', 'like', '%'.$this->pilotSearch.'%')
                    ->orWhere('license_number', 'like', '%'.$this->pilotSearch.'%')
                    ->orWhereHas('user', function ($q) {
                        $q->where('email', 'like', '%'.$this->pilotSearch.'%');
                    });
            })
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function selectedPilot()
    {
        if (! $this->selectedPilotId) {
            return null;
        }

        return Pilot::with(['user', 'cars.category'])->find($this->selectedPilotId);
    }

    #[Computed]
    public function pilotCars()
    {
        if (! $this->selectedPilotId) {
            return collect();
        }

        return Car::with('category')
            ->where('pilot_id', $this->selectedPilotId)
            ->get();
    }

    #[Computed]
    public function availableRaces()
    {
        return Race::where('status', 'OPEN')
            ->whereDate('race_date', '>=', now())
            ->orderBy('race_date')
            ->get();
    }

    #[Computed]
    public function selectedRace()
    {
        if (! $this->selectedRaceId) {
            return null;
        }

        return Race::find($this->selectedRaceId);
    }

    #[Computed]
    public function categories()
    {
        return CarCategory::whereActive()->ordered()->get();
    }

    public function selectPilot(int $pilotId): void
    {
        $this->selectedPilotId = $pilotId;
        $this->pilotSearch = '';
    }

    public function clearPilotSelection(): void
    {
        $this->selectedPilotId = null;
        $this->selectedCarId = null;
    }

    public function selectCar(int $carId): void
    {
        $this->selectedCarId = $carId;
    }

    public function generateRandomRaceNumber(): void
    {
        // Get all existing race numbers from cars
        $usedNumbers = Car::pluck('race_number')->toArray();

        // Generate available numbers (0-999)
        $allNumbers = range(0, 999);
        $availableNumbers = array_diff($allNumbers, $usedNumbers);

        if (empty($availableNumbers)) {
            $this->errorMessage = 'Aucun numéro de course disponible.';
            return;
        }

        // Pick a random available number
        $this->newCarRaceNumber = $availableNumbers[array_rand($availableNumbers)];
    }

    public function goToStep(int $step): void
    {
        // Validate current step before moving forward
        if ($step > $this->currentStep) {
            if (! $this->validateCurrentStep()) {
                return;
            }
        }

        $this->currentStep = $step;
    }

    public function nextStep(): void
    {
        if ($this->validateCurrentStep()) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    protected function validateCurrentStep(): bool
    {
        $this->errorMessage = null;

        switch ($this->currentStep) {
            case 1:
                if ($this->pilotMode === 'search' && ! $this->selectedPilotId) {
                    $this->errorMessage = 'Veuillez sélectionner un pilote existant ou créer un nouveau pilote.';

                    return false;
                }

                if ($this->pilotMode === 'create') {
                    $this->validate([
                        'newPilotFirstName' => 'required|string|max:255',
                        'newPilotLastName' => 'required|string|max:255',
                        'newPilotEmail' => 'required|email|max:255|unique:users,email',
                        'newPilotLicense' => 'required|digits_between:1,6|unique:pilots,license_number',
                        'newPilotBirthDate' => 'required|date|before:today',
                        'newPilotBirthPlace' => 'required|string|max:255',
                        'newPilotPhone' => 'required|string|max:20',
                        'newPilotAddress' => 'required|string|max:500',
                    ]);
                }

                return true;

            case 2:
                // Déterminer si on crée une nouvelle voiture
                // (soit mode explicite 'create', soit pas de voitures existantes pour ce pilote)
                $pilotHasCars = $this->pilotMode === 'search'
                    && $this->selectedPilotId
                    && $this->pilotCars->count() > 0;

                $isCreatingCar = $this->carMode === 'create' || ! $pilotHasCars;

                if (! $isCreatingCar && ! $this->selectedCarId) {
                    $this->errorMessage = 'Veuillez sélectionner une voiture ou en créer une nouvelle.';

                    return false;
                }

                if ($isCreatingCar) {
                    $this->validate([
                        'newCarRaceNumber' => 'required|integer|between:0,999|unique:cars,race_number',
                        'newCarMake' => 'required|string|max:255',
                        'newCarModel' => 'required|string|max:255',
                        'newCarCategoryId' => 'required|exists:car_categories,id',
                    ]);

                    // Forcer le mode create pour la suite du processus
                    $this->carMode = 'create';
                }

                return true;

            case 3:
                if (! $this->selectedRaceId) {
                    $this->errorMessage = 'Veuillez sélectionner une course.';

                    return false;
                }

                // Note: Un pilote PEUT inscrire plusieurs de ses voitures sur la même course
                // Seule contrainte: une même voiture ne peut pas être inscrite deux fois

                // Vérifier que la voiture n'est pas déjà inscrite
                $carId = $this->selectedCarId;
                if ($carId && RaceRegistration::where('race_id', $this->selectedRaceId)
                    ->where('car_id', $carId)
                    ->exists()) {
                    $this->errorMessage = 'Cette voiture est déjà inscrite à cette course.';

                    return false;
                }

                return true;

            default:
                return true;
        }
    }

    public function submit(): void
    {
        $this->errorMessage = null;

        if (! $this->validateCurrentStep()) {
            return;
        }

        try {
            DB::beginTransaction();

            // Step 1: Create pilot if needed
            $pilot = $this->getOrCreatePilot();

            // Step 2: Create car if needed
            $car = $this->getOrCreateCar($pilot);

            // Step 3: Create registration
            $registration = $this->createRegistration($pilot, $car);

            // Step 4: Create payment
            $this->createPayment($registration);

            // Generate QR token
            $qrService = new QrTokenService;
            $qrService->generate($registration);

            DB::commit();

            $this->createdRegistration = $registration->fresh(['pilot', 'car', 'race', 'payments']);
            $this->successMessage = 'Inscription créée avec succès !';
            $this->currentStep = 5; // Success step

        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage = 'Erreur lors de la création de l\'inscription : '.$e->getMessage();
        }
    }

    protected function getOrCreatePilot(): Pilot
    {
        if ($this->pilotMode === 'search' && $this->selectedPilotId) {
            return Pilot::findOrFail($this->selectedPilotId);
        }

        // Create new user
        $user = User::create([
            'name' => $this->newPilotFirstName.' '.$this->newPilotLastName,
            'email' => $this->newPilotEmail,
            'password' => Hash::make('password'), // Default password for walk-in registrations
            'email_verified_at' => now(),
        ]);

        $user->assignRole('PILOTE');

        // Create pilot profile
        $pilot = Pilot::create([
            'user_id' => $user->id,
            'first_name' => $this->newPilotFirstName,
            'last_name' => $this->newPilotLastName,
            'license_number' => $this->newPilotLicense,
            'birth_date' => $this->newPilotBirthDate,
            'birth_place' => $this->newPilotBirthPlace,
            'phone' => $this->newPilotPhone,
            'address' => $this->newPilotAddress,
            'city' => $this->newPilotCity ?: null,
            'postal_code' => $this->newPilotPostalCode ?: null,
            'permit_number' => $this->newPilotPermitNumber ?: null,
            'permit_date' => $this->newPilotPermitDate ?: null,
            'emergency_contact_name' => $this->newPilotEmergencyContactName ?: null,
            'emergency_contact_phone' => $this->newPilotEmergencyContactPhone ?: null,
            'is_minor' => $this->newPilotIsMinor,
            'guardian_first_name' => $this->newPilotIsMinor ? $this->newPilotGuardianFirstName : null,
            'guardian_last_name' => $this->newPilotIsMinor ? $this->newPilotGuardianLastName : null,
            'guardian_license_number' => $this->newPilotIsMinor ? $this->newPilotGuardianLicense : null,
            'guardian_phone' => $this->newPilotIsMinor ? $this->newPilotGuardianPhone : null,
        ]);

        // Handle photo upload
        if ($this->newPilotPhoto) {
            $path = $this->newPilotPhoto->store('pilots', 'public');
            $pilot->update(['photo_path' => $path]);
        }

        // Update selected pilot ID for car creation
        $this->selectedPilotId = $pilot->id;

        return $pilot;
    }

    protected function getOrCreateCar(Pilot $pilot): Car
    {
        if ($this->carMode === 'select' && $this->selectedCarId) {
            return Car::findOrFail($this->selectedCarId);
        }

        return Car::create([
            'pilot_id' => $pilot->id,
            'race_number' => $this->newCarRaceNumber,
            'make' => $this->newCarMake,
            'model' => $this->newCarModel,
            'car_category_id' => $this->newCarCategoryId,
        ]);
    }

    protected function createRegistration(Pilot $pilot, Car $car): RaceRegistration
    {
        $registration = RaceRegistration::create([
            'race_id' => $this->selectedRaceId,
            'pilot_id' => $pilot->id,
            'car_id' => $car->id,
            'status' => 'ACCEPTED', // Direct acceptance for walk-in
            'validated_at' => now(),
            'validated_by' => auth()->id(),
        ]);

        // Dispatch events for email notifications
        \App\Events\RegistrationCreated::dispatch($registration);
        \App\Events\RegistrationAccepted::dispatch($registration);

        return $registration;
    }

    protected function createPayment(RaceRegistration $registration): Payment
    {
        $race = Race::find($this->selectedRaceId);
        $amount = $race->entry_fee ?? 50.00; // Default fee if not set

        $payment = Payment::create([
            'race_registration_id' => $registration->id,
            'user_id' => $registration->pilot->user_id,
            'amount' => $amount,
            'amount_cents' => (int) ($amount * 100),
            'currency' => 'EUR',
            'method' => $this->paymentMethod,
            'status' => $this->paymentReceived ? 'paid' : 'pending',
            'paid_at' => $this->paymentReceived ? now() : null,
            'metadata' => [
                'registered_by' => auth()->user()->name,
                'registered_at' => now()->toIso8601String(),
                'walk_in' => true,
                'notes' => $this->paymentNotes,
            ],
        ]);

        // Dispatch PaymentConfirmed event if payment is already received
        if ($this->paymentReceived) {
            \App\Events\PaymentConfirmed::dispatch($payment);
        }

        return $payment;
    }

    public function createAnother(): void
    {
        $this->reset();
        $this->currentStep = 1;
    }

    public function render()
    {
        return view('livewire.staff.registrations.walk-in-registration')
            ->layout('layouts.app');
    }
}
