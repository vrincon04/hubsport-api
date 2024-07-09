<?php

namespace App\Http\Controllers\V1\Role;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'message' => 'Roles list',
            'data' => Role::all(),
        ], Response::HTTP_OK);
    }
}
