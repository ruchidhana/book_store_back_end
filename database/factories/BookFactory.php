<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Str;
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = Book::class;

    public function definition()
    {
        return [
            'isbn' => Str::random(10),
            'title' => $this->faker->sentence($nbWords = 2, $variableNbWords = true),
            'description' => $this->faker->unique()->sentence($nbWords = 4, $variableNbWords = true),
            'price' => $this->faker->randomNumber(2),
            'cover_image' => '20590-166983470-1675531793.jpg',
            'created_at' => date("Y-m-d H:i:s")
        ];
    }
}
