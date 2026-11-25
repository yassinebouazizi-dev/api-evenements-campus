<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'club_id' => ['required', 'integer', 'exists:clubs,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:events,slug'],
            'summary' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:255'],
            'max_seats' => ['required', 'integer', 'min:5'],
            'starts_at' => ['required', 'date'],
            'is_published' => ['sometimes', 'boolean'],
            'themes' => ['sometimes', 'array'],
            'themes.*' => ['integer', 'exists:themes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'club_id.required' => 'Le club est obligatoire.',
            'club_id.exists' => 'Le club sélectionné est invalide.',
            'title.required' => 'Le titre est obligatoire.',
            'slug.required' => 'Le slug est obligatoire.',
            'slug.unique' => 'Ce slug est déjà utilisé.',
            'summary.required' => 'Le résumé est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'location.required' => 'Le lieu est obligatoire.',
            'max_seats.required' => 'Le nombre de places est obligatoire.',
            'max_seats.integer' => 'Le nombre de places doit être un entier.',
            'max_seats.min' => 'Le nombre de places doit être au moins de :min.',
            'starts_at.required' => 'La date de début est obligatoire.',
            'starts_at.date' => 'La date de début doit être une date valide.',
            'themes.array' => 'Les thèmes doivent être envoyés sous forme de tableau.',
            'themes.*.exists' => 'Un des thèmes sélectionnés est invalide.',
        ];
    }
}
