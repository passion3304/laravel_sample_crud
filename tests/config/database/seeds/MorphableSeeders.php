<?php

namespace Backpack\CRUD\Tests\Config\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MorphableSeeders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        $now = \Carbon\Carbon::now();

        DB::table('recommends')->insert([[
            'title' => $faker->title,
            'created_at'     => $now,
            'updated_at'     => $now,
        ], [
            'title' => $faker->title,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]]);

        DB::table('bills')->insert([[
            'title' => $faker->title,
            'created_at'     => $now,
            'updated_at'     => $now,
        ], [
            'title' => $faker->title,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]]);
    }
}
