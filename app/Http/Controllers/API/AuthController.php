<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function signup(Request $request){

        $crendentail =$request->validate([
                'name' =>'required',
                'email' => 'required|email|unique:user,email',
                'password' =>'required',
            
        ]);
    if(Auth::attempt($crendentail)){
        return view('success');
            };
        }
    public function login(Request $request){
        
    }


    public function logout(Request $request){
        
    }
}
