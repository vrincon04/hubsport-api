<?php

namespace App\Http\Controllers\V1\Meta;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use Symfony\Component\HttpFoundation\Response;

class CountryController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'message' => 'Countries List',
            'data' => CountryResource::collection(Country::all()),
        ], Response::HTTP_OK);
    }
}
