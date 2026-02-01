<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Events\PilotRegistered;
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
            'terms' => ['accepted'],
        ], [
            // Messages personnalisés pour le numéro de licence
            'license_number.required' => 'Le numéro de licence FFSA est obligatoire.',
            'license_number.min' => 'Le numéro de licence doit contenir au moins 1 chiffre.',
            'license_number.max' => 'Le numéro de licence ne peut pas dépasser 6 chiffres.',
            'license_number.regex' => 'Le numéro de licence doit contenir uniquement des chiffres (ex: 123456).',
            'license_number.unique' => 'Ce numéro de licence est déjà enregistré. Contactez-nous si vous pensez qu\'il s\'agit d\'une erreur.',

            // Messages personnalisés pour l'email
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide (ex: nom@domaine.com).',
            'email.unique' => 'Cette adresse email est déjà utilisée. Essayez de vous connecter ou utilisez une autre adresse.',

            // Messages personnalisés pour le mot de passe
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',

            // Messages personnalisés pour les informations personnelles
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',

            // Message pour les conditions
            'terms.accepted' => 'Vous devez accepter les mentions légales et la politique de confidentialité.',
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
            $pilot = Pilot::create([
                'user_id' => $user->id,
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'phone' => $input['phone'],
                'license_number' => $input['license_number'],
            ]);

            // Dispatch welcome email event
            PilotRegistered::dispatch($pilot);

            return $user;
        });
    }
}
