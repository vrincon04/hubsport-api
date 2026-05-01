<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

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
            'profile.first_name' => ['sometimes', 'string', 'min:2', 'max:100'],
            'profile.last_name' => ['sometimes', 'string', 'min:2', 'max:100'],
            'profile.country_id' => ['sometimes', 'exists:countries,id'],
            'profile.city' => ['sometimes', 'string', 'min:2', 'max:100'],
            'profile.sport_id' => ['sometimes', 'exists:sports,id'],
            'profile.profile_type' => ['sometimes', 'in:athlete,coach,recruiter,sponsor,event_organizer,fan'],
            'profile.phone_number' => ['sometimes', 'nullable', 'string', 'max:30'],
            'profile.bio' => ['sometimes', 'string', 'max:500'],
            'profile.birth_date' => ['sometimes', 'date'],
            'profile.position' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile.sport_level' => ['sometimes', 'nullable', 'string', 'max:100'],
            'profile.current_team' => ['sometimes', 'nullable', 'string', 'max:150'],
            'profile.social_links' => ['sometimes', 'nullable', 'array'],
            'profile.social_links.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
