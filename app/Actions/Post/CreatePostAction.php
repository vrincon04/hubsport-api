<?php

namespace App\Actions\Post;

use App\Http\Requests\Post\StorePostRequest;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\Concerns\AsAction;

class CreatePostAction
{
    use AsAction;

    public function handle(StorePostRequest $request): Post
    {
        $user = $request->user();
        $post = $user->posts()->create($request->safe()->only(['title', 'body', 'status', 'published_at']));

        $this->attachGalleryFiles($post, $request);

        return $post;
    }

    /**
     * Acepta gallery (array o un archivo), image o photo (muchos clientes móviles no usan el nombre "gallery").
     */
    private function attachGalleryFiles(Post $post, StorePostRequest $request): void
    {
        $keys = ['gallery', 'image', 'photo'];

        foreach ($keys as $key) {
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
    }
}
