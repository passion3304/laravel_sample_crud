<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();

        DB::table('users')->insert([[
            'id' => 1,
            'name' => $faker->name,
            'email' => $faker->safeEmail,
            'password' => bcrypt('secret'),
            'remember_token' => str_random(10),
        ]]);
    }
}
