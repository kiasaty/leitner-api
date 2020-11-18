<?php

namespace Database\Seeders;

use App\Box;
use App\Card;
use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->count(5)->create()->each(function ($user) {
            $user->createdBoxes()->saveMany(
                Box::factory()->count(2)->create(['creator_id' => $user->id])->each(function ($box) {
                    $box->creator->boxes()->attach($box->id);

                    $box->cards()->saveMany(
                        Card::factory()->count(10)->make()
                    );
                })
            );
        });
    }
}
