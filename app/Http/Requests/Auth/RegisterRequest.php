<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'unique:users','max:255'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->letters()->symbols()],
            'avatar' => ['sometimes', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'profile.first_name' => ['required', 'string', 'min:2', 'max:100'],
            'profile.last_name' => ['required', 'string', 'min:2', 'max:100'],
            'profile.country_id' => ['required', 'exists:countries,id'],
            'profile.city' => ['required', 'string', 'min:2', 'max:100'],
            'profile.sport_id' => ['required', 'exists:sports,id'],
            'profile.profile_type' => ['required', 'in:athlete,coach,recruiter,sponsor,event_organizer,fan'],
            'profile.phone_number' => ['sometimes', 'nullable', 'string', 'max:30'],
            'profile.birth_date' => ['sometimes', 'nullable', 'date'],
            'profile.position' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile.sport_level' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile.current_team' => ['sometimes', 'nullable', 'string', 'max:150'],
            'profile.social_links' => ['sometimes', 'nullable', 'array'],
            'profile.social_links.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
