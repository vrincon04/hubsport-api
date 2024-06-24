<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'unique:users','max:255'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->letters()->symbols()],
            'profile.first_name' => ['required', 'string', 'min:2', 'max:100'],
            'profile.last_name' => ['required', 'string', 'min:2', 'max:100']
        ];
    }
}
