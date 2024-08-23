<?php

namespace App\Http\Requests\Post;

use App\Enums\PostStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => [
                'required'
            ],
            'body' => [
                'required'
            ],
            'status' => [
                'required',
                Rule::enum(PostStatusEnum::class)
                    ->only([PostStatusEnum::PUBLISHED, PostStatusEnum::PENDING, PostStatusEnum::DRAFT])
            ],
            'published_at' => [
                'required',
                'date'
            ],
            'gallery' => [
                'sometimes',
                'array'
            ],
            'gallery.*' => [
                'file',
                'mimetypes:image/jpeg,image/jpg,image/png,video/mp4',
                'max:20480',
            ],
        ];
    }
}
