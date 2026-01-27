<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be a pilot and have a pilot profile
        return $this->user()->isPilot() && $this->user()->pilot !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'race_number' => ['required', 'integer', 'min:0', 'max:999', 'unique:cars,race_number'],
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'car_category_id' => ['required', 'exists:car_categories,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'race_number.unique' => 'Ce numéro de course est déjà utilisé.',
            'race_number.min' => 'Le numéro de course doit être entre 0 et 999.',
            'race_number.max' => 'Le numéro de course doit être entre 0 et 999.',
            'car_category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
        ];
    }
}
