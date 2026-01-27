<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePilotProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user owns the pilot profile
        return $this->user()->id === $this->route('pilot')->user_id
            || $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $pilotId = $this->route('pilot') ? $this->route('pilot')->id : null;

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'license_number' => [
                'required',
                'digits_between:1,6',
                Rule::unique('pilots', 'license_number')->ignore($pilotId),
            ],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_place' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_minor' => ['boolean'],
            'guardian_first_name' => ['required_if:is_minor,true', 'nullable', 'string', 'max:255'],
            'guardian_last_name' => ['required_if:is_minor,true', 'nullable', 'string', 'max:255'],
            'guardian_license_number' => ['required_if:is_minor,true', 'nullable', 'digits_between:1,6'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'license_number.digits_between' => 'Le numéro de licence doit contenir entre 1 et 6 chiffres.',
            'license_number.unique' => 'Ce numéro de licence est déjà utilisé.',
            'birth_date.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'guardian_first_name.required_if' => 'Le prénom du tuteur est obligatoire pour les mineurs.',
            'guardian_last_name.required_if' => 'Le nom du tuteur est obligatoire pour les mineurs.',
            'guardian_license_number.required_if' => 'Le numéro de licence du tuteur est obligatoire pour les mineurs.',
        ];
    }
}
