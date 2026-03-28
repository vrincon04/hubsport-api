<?php

namespace App\Http\Requests\Post;

use App\Enums\PostStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => [
                'required',
            ],
            'body' => [
                'required',
            ],
            'status' => [
                'required',
                Rule::enum(PostStatusEnum::class)
                    ->only([PostStatusEnum::PUBLISHED, PostStatusEnum::PENDING, PostStatusEnum::DRAFT]),
            ],
            'published_at' => [
                'required',
                'date',
            ],
            'gallery' => [
                'sometimes',
            ],
            'gallery.*' => [
                'file',
                'mimetypes:image/jpeg,image/jpg,image/png,image/webp,video/mp4',
                'max:25600',
            ],
            'image' => [
                'sometimes',
                'file',
                'mimetypes:image/jpeg,image/jpg,image/png,image/webp,video/mp4',
                'max:25600',
            ],
            'photo' => [
                'sometimes',
                'file',
                'mimetypes:image/jpeg,image/jpg,image/png,image/webp,video/mp4',
                'max:25600',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            if (! $this->hasFile('gallery')) {
                return;
            }
            $raw = $this->file('gallery');
            if (is_array($raw)) {
                return;
            }
            if (! $raw instanceof UploadedFile || ! $raw->isValid()) {
                $v->errors()->add('gallery', 'El archivo de galería no es válido.');

                return;
            }
            if ($raw->getSize() > 25600 * 1024) {
                $v->errors()->add('gallery', 'El archivo supera 25MB.');
            }
        });
    }
}
