<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'profile.first_name' => ['required', 'string', 'min:2', 'max:100'],
            'profile.last_name' => ['required', 'string', 'min:2', 'max:100'],
            'profile.bio' => ['sometimes', 'string', 'max:500'],
            'profile.dob' => ['sometimes', 'date'],
        ];
    }
}
