<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
            'url' => $this->absoluteUrl(),
        ];
    }

    /**
     * URL absoluta usando la base del disco (MEDIA_URL / APP_URL), evita localhost en JSON y rutas mal resueltas en apps móviles.
     */
    private function absoluteUrl(): string
    {
        $diskName = $this->disk;
        $base = rtrim((string) (config("filesystems.disks.{$diskName}.url") ?? ''), '/');

        if ($base !== '') {
            $path = ltrim(str_replace('\\', '/', $this->getPathRelativeToRoot()), '/');

            return $base.'/'.$path;
        }

        return $this->getFullUrl();
    }
}
