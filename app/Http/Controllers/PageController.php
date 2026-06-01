<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function showUser(){
        return view('blog',[]) ;
    }

        public function showHome(){
        return view('hello') ;
    }

}
