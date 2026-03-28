<?php

namespace App\Actions\Post;

use App\Http\Requests\Post\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePostAction
{
    use AsAction;

    public function handle(StorePostRequest $request): Post
    {
        return DB::transaction(function () use ($request) {
            $user = $request->user();
            $post = $user->posts()->create($request->safe()->only(['title', 'body', 'status', 'published_at']));

            $this->attachGalleryFiles($post, $request);

            return $post;
        });
    }

    /**
     * Nombres habituales en apps (Flutter/Dio, etc.). Si ninguno coincide, el post queda sin galería.
     */
    private function attachGalleryFiles(Post $post, StorePostRequest $request): void
    {
        $singleFileKeys = [
            'gallery',
            'image',
            'photo',
            'picture',
            'file',
            'media',
            'attachment',
            'upload',
        ];

        foreach ($singleFileKeys as $key) {
            if (! $request->hasFile($key)) {
                continue;
            }

            $files = $request->file($key);
            $files = is_array($files) ? $files : [$files];

            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $post->addMedia($file)->toMediaCollection('gallery');
                }
            }
        }

        foreach (['images', 'photos', 'files'] as $arrayKey) {
            if (! $request->hasFile($arrayKey)) {
                continue;
            }
            $batch = $request->file($arrayKey);
            if (! is_array($batch)) {
                $batch = [$batch];
            }
            foreach ($batch as $file) {
                if ($file instanceof UploadedFile) {
                    $post->addMedia($file)->toMediaCollection('gallery');
                }
            }
        }
    }
}
