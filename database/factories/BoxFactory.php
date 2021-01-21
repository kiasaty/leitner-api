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

    /**
     * Set the creator of the box.
     *
     * @param  \App\Model\User|int
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function creator($creator)
    {
        return $this->state(function () use ($creator) {
            return [
                'creator_id' => is_numeric($creator) ? $creator : $creator->id,
            ];
        });
}

}
