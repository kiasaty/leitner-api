<?php

namespace Database\Factories;

use App\Box;
use App\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Card::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'box_id'    => Box::factory(),
            'front'     => $this->faker->word,
            'back'      => $this->faker->sentence,
        ];
    }
}
