<?php

namespace App\Http\Requests\Post;

use App\Enums\PostStatusEnum;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePostRequest extends FormRequest
{
    private const IMAGE_MIMES = 'mimetypes:image/jpeg,image/jpg,image/png,image/webp,image/heic,image/heif,video/mp4';

    protected function prepareForValidation(): void
    {
        if (! $this->filled('published_at')) {
            return;
        }

        try {
            $this->merge([
                'published_at' => Carbon::parse($this->input('published_at'))->format('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable) {
            // Let the date validation rule return the user-facing error.
        }
    }

    public function rules(): array
    {
        $oneFile = ['sometimes', 'file', self::IMAGE_MIMES, 'max:25600'];
        $eachFile = ['file', self::IMAGE_MIMES, 'max:25600'];

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
            'gallery.*' => $eachFile,
            'images' => ['sometimes', 'array'],
            'images.*' => $eachFile,
            'photos' => ['sometimes', 'array'],
            'photos.*' => $eachFile,
            'files' => ['sometimes', 'array'],
            'files.*' => $eachFile,
            'image' => $oneFile,
            'photo' => $oneFile,
            'picture' => $oneFile,
            'file' => $oneFile,
            'media' => $oneFile,
            'attachment' => $oneFile,
            'upload' => $oneFile,
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
