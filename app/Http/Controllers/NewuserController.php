<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewuserController extends Controller
{
    //

    public function show(){

        $users =DB::table('newusers')->where('newcity','KHI')->ORwhere('id',2)->get();
        return view('datafile', ['user' => $users]) ;

        
        
    }


    public function singleUser(string $idd){

        $users =DB::table('newusers')->find($idd);
        // return view('datafile', ['user' => $users]) ;

        

            return "<h1> $users->name </h1>";

        
    }

    public function addUser(){
        $user= DB::table('newusers')
        ->insert([
            'name' =>'abc',
            'email'=>fake()->unique()->email(),
            'description'=>fake()->paragraph(),
            'newcity'=>fake()->city(),
            'created_at' => now(),
            'updated_at' => now()

        ]);
    }

        public function updateUser(){
        $user= DB::table('newusers')
        ->where('id',1)
         ->delete();
    }
}
