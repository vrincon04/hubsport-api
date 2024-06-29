<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OptVerificationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['string', 'min:6', 'max:8', 'required'],
        ];
    }
}
