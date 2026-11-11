<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\Governorate;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Country => governorate => cities.
     *
     * Intentionally empty: no source for the locations exists in the project
     * documents. Fill it in, for example:
     * 'CountryName' => ['GovernorateName' => ['CityName', 'CityName']].
     *
     * @var array<string, array<string, array<int, string>>>
     */
    private const LOCATIONS = [];

    public function run(): void
    {
        foreach (self::LOCATIONS as $countryName => $governorates) {
            $country = Country::query()->firstOrCreate(['name' => $countryName]);

            foreach ($governorates as $governorateName => $cities) {
                $governorate = Governorate::query()->firstOrCreate([
                    'country_id' => $country->id,
                    'name' => $governorateName,
                ]);

                foreach ($cities as $cityName) {
                    City::query()->firstOrCreate([
                        'governorate_id' => $governorate->id,
                        'name' => $cityName,
                    ]);
                }
            }
        }
    }
}
