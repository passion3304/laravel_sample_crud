<?php

use Illuminate\Database\Seeder;

class StrandsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
      DB::table('strands')->insert([
        'title' => 'Creatures of the Night',
        'color' => '#000000',
        'short_description' => 'Cult favourites and the best new horror, shown in those most magical of late-night hours.',
        'description' => 'Whether you’re a dedicated cinephile, a cultural explorer or a random insomniac, Creatures of the Night is dedicated to screening those truly unforgettable films, from the best new horror to revered and sometimes obscure cult classics, in those most magical of late-night hours.',
        'thumb' => '',
        'slug' => 'creatures'
      ]);
      DB::table('strands')->insert([
        'title' => 'Tuesday Wonders!',
        'color' => '#35BCCD',
        'short_description' => 'Shining a light on those hidden gems that you really shouldn’t miss.',
        'description' => 'With an increasing number of films being released into cinemas each and every year, it’s hard for some smaller titles to get the attention they deserve. That’s why we have Tuesday Wonders!, an ongoing strand where we shine a light on those hidden gems – from documentaries and artist films, to under-appreciated classics and world cinema – these are works which so often challenge and inspire, and that you really shouldn’t miss.',
        'thumb' => '',
        'slug' => 'tw'
      ]);
      DB::table('strands')->insert([
        'title' => 'Hyde & Seek',
        'color' => '#F9DC55',
        'short_description' => 'Discover a world of family-friendly films every Saturday at 12noon.',
        'description' => 'Discover a world of family friendly films every Saturday at 12noon. Tickets are just £1.50 for kids and £5 for adults. And held on the 3rd Sunday of every month, our Relaxed Screenings* offer a safe and relaxed environment for audiences who might otherwise find the cinema-going experience unsuitable – including those on the autism spectrum. These screenings involve prompt start times, slightly raised lighting and lower volume levels, and the ability for audiences to move freely around the auditorium during the film.',
        'thumb' => '',
        'slug' => 'hyde-seek'
      ]);
      DB::table('strands')->insert([
        'title' => 'Philosophy & Film',
        'color' => '#E790B5',
        'short_description' => 'Exploring philosophical themes in some of cinema’s most intriguing films.',
        'description' => 'Philosophy & Film is an ongoing series, held in partnership with the University of Leeds and Durham University, exploring philosophical questions raised by some of cinema’s most intriguing films.  Each film in this series is selected by an academic philosopher, who delivers a short talk after the film, exploring philosophical themes raised. Discussion then opens up to the audience.',
        'thumb' => '',
        'slug' => 'hyde-seek'
      ]);
    }
}
