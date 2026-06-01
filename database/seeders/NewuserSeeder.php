<?php

namespace Database\Seeders;

use App\Models\Newuser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class NewuserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(path: 'database/json/newusers.json');
        $students = collect(json_decode($json));

        $students->each(function($lmop){
           
        Newuser::create([

            'name' =>fake()->name(),
            'email'=>fake()->unique()->email(),
            'description'=>fake()->paragraph(),
            'newcity'=>fake()->city()

        ]);
        
           });

        
        
    }
}
