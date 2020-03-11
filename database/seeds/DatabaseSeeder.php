<?php

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
        factory(App\User::class, 5)->create()->each(function ($user) {

            $user->createdBoxes()->saveMany(
                factory(App\Box::class, 2)->create(['creator_id' => $user->id])->each(function ($box) {

                    $box->creator->boxes()->attach($box->id);

                    $box->cards()->saveMany(
                        factory(App\Card::class, 10)->make()
                    );
                    
                })
            );
            
        });
    }
}
