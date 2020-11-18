<?php

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'firstname'     => $this->faker->firstName,
            'lastname'      => $this->faker->lastName,
            'username'      => $this->faker->userName,
            'email'         => $this->faker->email,
            'password'      => app('hash')->make('secret'),
            'profile_photo' => $this->faker->imageUrl,
        ];
    }
}
