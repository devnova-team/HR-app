<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Asset> */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $item = fake()->randomElement([
            ['Laptop Macbook Air M1 2020', 'Apple'],
            ['UltraSharp 27 Monitor', 'Dell'],
            ['MX Keys Keyboard', 'Logitech'],
            ['ThinkPad T14', 'Lenovo'],
            ['iPhone 13', 'Apple'],
        ]);

        return [
            'name' => $item[0],
            'brand' => $item[1],
            'image_url' => 'https://placehold.co/600x400?text='.rawurlencode($item[1]),
            'is_under_warranty' => fake()->boolean(70),
            'purchase_date' => fake()->dateTimeBetween('-3 years', '-6 months')->format('Y-m-d'),
        ];
    }
}
