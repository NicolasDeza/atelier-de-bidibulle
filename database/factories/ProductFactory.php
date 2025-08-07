<?php

namespace Database\Factories;


use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

      protected $model = Product::class;


    public function definition(): array
    {

        $name = $this->faker->words(2, true);

        return [
     'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . $this->faker->unique()->numberBetween(100, 999),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 5, 150),
            'discount_type' => null,
            'discount_value' => null,
            'stock' => $this->faker->numberBetween(0, 50),
            'image' => 'default.jpg',
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
        ];
    }
}
