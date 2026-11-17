<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class OptVerificationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'lowercase'],
            'code' => ['string', 'min:4', 'max:8', 'required'],
        ];
    }
}
