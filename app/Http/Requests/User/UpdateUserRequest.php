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
            'profile.first_name' => ['required', 'string', 'min:2', 'max:100'],
            'profile.last_name' => ['required', 'string', 'min:2', 'max:100'],
            'profile.bio' => ['sometimes', 'string', 'max:500'],
            'profile.birth_date' => ['sometimes', 'date'],
        ];
    }
}
