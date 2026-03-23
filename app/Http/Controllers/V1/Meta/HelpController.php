<?php

namespace App\Http\Controllers\V1\Meta;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HelpController extends Controller
{
    /**
     * Topics / FAQ structure for the help screens (extend via CMS or DB later).
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'message' => 'Help topics',
            'data' => [
                'sections' => [
                    [
                        'id' => 'getting-started',
                        'title' => 'Primeros pasos',
                        'articles' => [],
                    ],
                    [
                        'id' => 'account',
                        'title' => 'Cuenta y perfil',
                        'articles' => [],
                    ],
                ],
            ],
        ]);
    }
}
