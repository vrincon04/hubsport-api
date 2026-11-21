<?php

namespace App\Http\Requests\Auth;

use App\Support\AgePolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'unique:users', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->letters()->symbols()],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'profile.first_name' => ['required', 'string', 'min:2', 'max:100'],
            'profile.last_name' => ['required', 'string', 'min:2', 'max:100'],
            'profile.country_id' => ['required', 'exists:countries,id'],
            'profile.city' => ['required', 'string', 'min:2', 'max:100'],
            'profile.sport_id' => ['required', 'exists:sports,id'],
            'profile.profile_type' => ['required', 'in:athlete,coach,recruiter,sponsor,event_organizer,fan'],
            'profile.phone_number' => ['sometimes', 'nullable', 'string', 'max:30'],
            'profile.birth_date' => ['required', 'date', 'before:today'],
            'profile.guardian_email' => ['sometimes', 'nullable', 'email:rfc', 'max:255'],
            'profile.position' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile.sport_level' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile.current_team' => ['sometimes', 'nullable', 'string', 'max:150'],
            'profile.organization_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'profile.rnc' => ['sometimes', 'nullable', 'string', 'max:40'],
            'profile.organization_document_url' => ['sometimes', 'nullable', 'url:http,https', 'max:2048'],
            'profile.organization_website' => ['sometimes', 'nullable', 'url:http,https', 'max:2048'],
            'profile.social_links' => ['sometimes', 'nullable', 'array'],
            'profile.social_links.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $birth = $this->input('profile.birth_date');
            if (! $birth) {
                return;
            }

            try {
                $date = Carbon::parse($birth)->startOfDay();
            } catch (\Throwable) {
                return;
            }

            if (AgePolicy::isUnderMinimum($date)) {
                $validator->errors()->add(
                    'profile.birth_date',
                    'Debes tener al menos 12 años para registrarte en HubSport.'
                );

                return;
            }

            $type = $this->input('profile.profile_type');
            if (AgePolicy::requiresGuardian($date, $type)) {
                $guardian = strtolower((string) $this->input('profile.guardian_email'));
                $email = strtolower((string) $this->input('email'));
                if ($guardian === '' || ! filter_var($guardian, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add(
                        'profile.guardian_email',
                        'Los atletas de 12 a 17 años deben indicar el correo del tutor.'
                    );
                } elseif ($guardian === $email) {
                    $validator->errors()->add(
                        'profile.guardian_email',
                        'El correo del tutor debe ser distinto al del menor.'
                    );
                }
            }

            if (in_array($type, ['recruiter', 'sponsor'], true)) {
                if (! filled($this->input('profile.organization_name'))) {
                    $validator->errors()->add(
                        'profile.organization_name',
                        'Indica el nombre de la organización.'
                    );
                }
                if (! filled($this->input('profile.rnc'))) {
                    $validator->errors()->add(
                        'profile.rnc',
                        'Indica el RNC o identificador fiscal.'
                    );
                }
                if (! filled($this->input('profile.organization_document_url'))) {
                    $validator->errors()->add(
                        'profile.organization_document_url',
                        'Adjunta un enlace al documento de respaldo.'
                    );
                }
            }
        });
    }
}
