<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\Pilot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['required', 'string', 'max:20'],
            'license_number' => ['required', 'string', 'min:1', 'max:6', 'regex:/^[0-9]+$/', 'unique:pilots,license_number'],
            'password' => $this->passwordRules(),
        ], [
            'license_number.unique' => 'Ce numéro de licence est déjà utilisé.',
            'license_number.regex' => 'Le numéro de licence doit contenir uniquement des chiffres.',
        ])->validate();

        return DB::transaction(function () use ($input) {
            // Create the user
            $user = User::create([
                'name' => $input['first_name'].' '.$input['last_name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            // Assign PILOTE role by default
            $user->assignRole('PILOTE');

            // Create associated pilot profile
            Pilot::create([
                'user_id' => $user->id,
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'phone' => $input['phone'],
                'license_number' => $input['license_number'],
            ]);

            return $user;
        });
    }
}
