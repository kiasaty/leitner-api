<?php

namespace Database\Factories;

use App\Box;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoxFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Box::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'creator_id'    => User::factory(),
            'title'         => $this->faker->word,
            'description'   => $this->faker->sentence,
        ];
    }
}
