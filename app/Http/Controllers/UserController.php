<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeMail;
use App\Models\User;
use App\Services\MailService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
class UserController extends Controller
{
    public function register(Request $request){
            $data =$request->validate([
                'name' =>'required',
                'email' => 'required|email',
                'password' =>'required|min:6|confirmed',
                'agree' => 'accepted',
            
        ],[
           'name.required' => 'Name is required!'  
        ]);
            
        
        try {
               $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password =bcrypt( $data['password']);
        $newotp=rand(1000,9999);
        $user->otp=$newotp;
        $user->otp_expires = now()->addMinutes(5);
        $user->save();



      $tomail =$data['email'];
      $message="HEllo welcome to our website";
    //   $subject="OTP VERIFICATION CODE";

    //     MailService::send($tomail,$subject,$message,$newotp);
   
        } catch (QueryException $th) {
                if ($th->getCode()==='23000'){
                    return back()->withErrors(['email' =>'This email is already Registered!'])->withInput();
                }else{
                    return back()->withInput();
                }
                }
        

        if($user){

        return redirect()->route('otp.form')->with('email', $user->email);
      
    }
      else{
            return back()->withInput();
      }


   
    }




    public function login(Request $request){

        $data =$request->validate([
                'email' => 'required|email',
                'password' =>'required',
            
        ]);

        if (Auth::attempt( $data))

    {
       if(!Auth::user()->otp_verified){
        Auth::logout();

        $newotp = rand(1000,9999);
        $otpexpires = now()->addMinutes(5);
        $usermail=$request->email;
        User::where('email', $usermail)->update([
            'otp' => $newotp,
            'otp_expires'  =>$otpexpires,
            'otp_verified' => false
        ]);

        $message="HEllo welcome to our website";
    //   $subject="OTP VERIFICATION CODE";

    //       MailService::send($usermail,$subject,$message,$newotp);
        

        return redirect()->route('otp.form')->with([
            'email'=> $usermail,
            'error' => 'please Enter your OTP First',
         
            
        ]);
       }else
       {
   
        return redirect()->route('work');


       }


        
    } else{
            return back()->withErrors([
            'email' => 'Invalid email or password.'
        ])->withInput();
    }

}

public function dashboardview(){
    if(Auth::check()){

         return view('login/home');
    }else{
     return redirect()->route('login');
}
}

public function logout(){
    if(Auth::check()){

        Auth::logout();
        
      return  redirect()->route('home');
    }else
    return redirect()->route('home');
}

public function verifyotp(Request $request){

  
    $request->validate([
    'otp' => 'required|digits:4',
    'flow' =>'required'

    ]);

   $flow= $request->flow;

    $user = User::where('email', $request->email)
    ->where('otp', intval($request->otp))
    ->where('otp_expires', '>', now())
    ->first();
    if ($user){

        
        if($flow==='forgot'){

            return redirect()->route('newpass')->with('email' , $request->email);
        }

        $user->otp = null;
        $user->otp_expires = null;
        $user->otp_verified = true;
        $user->save();


        Auth::login($user);

        return redirect()->route('work');
       
    }else{
        return redirect()->back()->withErrors(['otp' => 'Invalid, Please try again.'])->withInput();
    }


   

}

 public function forgetpassword(Request $request){
            $data =$request->validate([
                'email' => 'required|email|exists:users,email',
            
        ],[
           'email.exists' => 'This email is not registered with us.',  
        ]);




      $tomail =$data['email'];
      $message="HEllo welcome to our website";
      $subject="OTP VERIFICATION CODE";
      $newotp = rand(1000,9999);


      $user=User::where('email',$tomail)
      ->update(['otp'=>$newotp,
      'otp_expires' => now()->addMinutes(5)]);

      if($user){

    //    MailService::send($tomail,$subject,$message,$newotp);

        return redirect()->route('otp.form')->with([
            'email' => $tomail,
            'flow'  => 'forgot',   // 🔥 MUST
          ]);
      }

      else{
        return "<h1>No success </h1>";
      }
}


public function updatepass(Request $request){

    $data=$request->validate([

     'newpassword' =>'required|min:6|confirmed']);

    $email=$request->email;

    $user = User::where('email',$email)->first();
    
 
    if(!$user){

        return redirect()->back();
    }
    else
    {

    $user->password=bcrypt( $data['newpassword']);
    $user->otp = null;
    $user->otp_expires = null;
    $user->otp_verified=true;
    $user->save();
    
    Auth::login($user);
    session()->forget(['email','flow']);
        return redirect()->route('work');
    }
}





}