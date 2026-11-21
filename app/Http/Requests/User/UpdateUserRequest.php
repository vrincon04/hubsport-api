<?php

namespace App\Http\Requests\User;

use App\Support\AgePolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id === $this->route('user')->id;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'profile' => ['sometimes', 'array'],
            'profile.first_name' => ['sometimes', 'string', 'min:2', 'max:100'],
            'profile.last_name' => ['sometimes', 'string', 'min:2', 'max:100'],
            'profile.country_id' => ['sometimes', 'exists:countries,id'],
            'profile.city' => ['sometimes', 'string', 'min:2', 'max:100'],
            'profile.sport_id' => ['sometimes', 'exists:sports,id'],
            'profile.profile_type' => ['sometimes', 'in:athlete,coach,recruiter,sponsor,event_organizer,fan'],
            'profile.phone_number' => ['sometimes', 'nullable', 'string', 'max:30'],
            'profile.bio' => ['sometimes', 'string', 'max:500'],
            'profile.birth_date' => ['sometimes', 'date', 'before:today'],
            'profile.guardian_email' => ['sometimes', 'nullable', 'email:rfc', 'max:255'],
            'profile.height_cm' => ['sometimes', 'nullable', 'numeric', 'min:50', 'max:280'],
            'profile.weight_kg' => ['sometimes', 'nullable', 'numeric', 'min:20', 'max:400'],
            'profile.wingspan_cm' => ['sometimes', 'nullable', 'numeric', 'min:50', 'max:300'],
            'profile.laterality' => ['sometimes', 'nullable', 'in:left,right,both,left_hand,right_hand,left_foot,right_foot'],
            'profile.physical_extras' => ['sometimes', 'nullable', 'array'],
            'profile.position' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile.sport_level' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile.current_team' => ['sometimes', 'nullable', 'string', 'max:150'],
            'profile.social_links' => ['sometimes', 'nullable', 'array'],
            'profile.social_links.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (! $this->filled('profile.sport_id')) {
                return;
            }

            $current = $this->user()->profile?->sport_id;
            $incoming = $this->input('profile.sport_id');

            if ($current && (string) $current !== (string) $incoming) {
                $validator->errors()->add(
                    'profile.sport_id',
                    'El deporte no se puede cambiar desde el perfil. Solicita el cambio al equipo HubSport.'
                );
            }
        });

        $validator->after(function (Validator $validator) {
            if (! $this->filled('profile.birth_date')) {
                return;
            }

            try {
                $date = Carbon::parse($this->input('profile.birth_date'))->startOfDay();
            } catch (\Throwable) {
                return;
            }

            if (AgePolicy::isUnderMinimum($date)) {
                $validator->errors()->add(
                    'profile.birth_date',
                    'Debes tener al menos 12 años para usar HubSport.'
                );
            }
        });
    }
}
