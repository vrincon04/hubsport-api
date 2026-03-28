<?php

namespace App\Support;

/**
 * URL pública absoluta para archivos en el disco local "public" (storage/app/public).
 */
class PublicDiskUrl
{
    public static function forRelativePath(string $pathWithinPublicDisk): string
    {
        $base = rtrim((string) config('filesystems.disks.public.url'), '/');
        $path = ltrim(str_replace('\\', '/', $pathWithinPublicDisk), '/');

        return $base !== '' ? $base.'/'.$path : '/storage/'.$path;
    }
}
