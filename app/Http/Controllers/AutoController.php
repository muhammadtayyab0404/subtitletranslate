<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AutoController extends Controller
{
    public function __invoke(){
        return view('blog',[]) ;
    }


}
