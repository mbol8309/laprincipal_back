<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        for ($i=1; $i <= 1000; $i++) {
            DB::table('bs_book')->insert([
                'title' => $faker->sentence(4),
                'author' => $faker->name(),
                'genre' => $faker->word(),
                'publication_date' => $faker->date(),
                'publisher' => $faker->company(),
                'description' => $faker->paragraph(3)
            ]);
        }
    }
}
