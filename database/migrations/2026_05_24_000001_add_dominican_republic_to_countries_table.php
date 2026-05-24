<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $country = Country::query()->where('code', 'DO')->first();

        if ($country === null) {
            $country = new Country();
            $country->id = '01KMTFVYH8955Z7RED16TKM94J';
            $country->code = 'DO';
        }

        $country->name = 'República Dominicana';
        $country->save();
    }

    public function down(): void
    {
        Country::query()->where('code', 'DO')->delete();
    }
};
