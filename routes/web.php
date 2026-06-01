<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\NewuserController;
use App\Http\Controllers\AutoController;
use App\Http\Controllers\SubtitleAiController;

use App\Services\ModelApi;


// Route::get('/home', function () {
//     return view('hello',['prodid'=> 3, 'comntid' =>'sads']);
// })->name('welcomee');

Route::get('/news', function(){
    return view('news');
});
Route::get('/contact', function(){
    return view('contact');
});



Route::get('/data',[NewuserController::class,'show']);

Route::get('/single/{idd}',[NewuserController::class,'singleUser']);


Route::get('/add',[NewuserController::class,'addUser']);

Route::get('/update',[NewuserController::class,'updateUser']);



Route::get('/about', function(){
    return view('aboutus');
});


Route::get('/blog',[PageController::class,'showUser'])->name('blog');


// Route::get('/home',[PageController::class,'showHome'])->name('homee');


Route::get('/yoyo',AutoController::class)->name('homee');


function getUst(){
        return [
      1 => ['name' => 'malik', 'phone' => '123'],
      2 => ['name' => 'ustad', 'phone' => '456']  
    ];
}


Route::get('/test', function(){
    $name ="ustad";
   $nu=getUst();
    return view('test',['arr' => $nu]);
});



Route::get('/comment/{id}', function($id){
    $uu = getUst();
    $id = $uu[$id];
return "<h1> User Id  is :: ".$id['name']." </h1>";
})->name('bbnn');

// Route::get('abc', function () {
//     return view('hello');

// });

// Route::view('abc', 'hello');


// Route::get('abc/{id?}/comment/{comentid?}', function( string $id = null, string $comentid = null){

//     if($id){
    
//         return view('hello',['prodid'=> $id, 'comntid' =>$comentid]);
//     }else{
//         return  'hello ';
//     }
    
// })->where('id','[0-9]+')
// ->whereAlphaNumeric('comentid');


// Route::prefix('page')->group(function(){

// Route::get('/home', function () {
//     return view('welcome');
// });

// });

Route::get('/homelogin', function(){
    
    return view('login/dashboard',[]);
})->name('home');

Route::get('/register', function(){
    
    return view('login/register',[]);
})->name('register');


Route::post('registerSave',[UserController::class, 'register'])->name('registerSave');

Route::get('/logins', function(){
    
    return view('login/login',[]);
})->name('login');


 Route::post('loginMatch',[UserController::class, 'login'])->name('loginMatch');

Route::get('/dashboard',[UserController::class, 'dashboardview'])->name('login.home');

Route::post('/logout',[UserController::class, 'logout'])->name('logout');

Route::post('/verifyotp',[UserController::class, 'verifyotp'])->name('verifyotp')->middleware('web');

Route::get('/otp', function () {
    return view('login/otp');
})->name('otp.form');

//  Route::get('home', function(){
//     return view('login.home');
// })->name('dashboard');



/// Routes for the website

Route::get('/', function(){
    
    return view('home',[]);
})->name('homell');

Route::get('/work', function(){
    
    return view('work',[]);
})->name('work');


Route::get('/signup', function(){
    
    return view('login/signup',[]);
})->name('signup');

Route::get('/login', function(){
    
    return view('login/loginn',[]);
})->name('loginn');


Route::get('/logout', function(){
    Auth::logout();

    request()->session()->invalidate();
    request()->session()->regenerateToken(); 

    return redirect()->route('homell');
})->name('logout');


Route::get('/forget', function(){
    

    return view('login/forget',[]);
})->name('forget');

Route::post('/verifyotpp',[UserController::class, 'forgetpassword'])->name('forgetotp');

Route::get('/newpass', function(){
    
    return view('login/newpass',[]);
})->name('newpass');

Route::post('/savesrt',[WorkController::class, 'savesrt'])->name('savesrt');




///// subtitle 

Route::post('/subtitles', [WorkController::class, 'store'])->name('subtitles.store');

// 1 line card page
Route::get('/subtitles/{subtitle}/line/{seq}', [WorkController::class, 'line'])
    ->name('subtitles.line');



Route::get('/saved', [WorkController::class, 'fatchsubtitle']
)->name('saved');
    
Route::post('/towardssub',[WorkController::class, 'towardssub'])->name('towardssubss');



Route::get('/subtitle-test', function (ModelApi $modelApi) {
    return response()->json(
        $modelApi->analyzeSentence('Es freut mich, dich kennenzulernen', 'english')
    );
});





    // Route::post('/ai/analyze', [SubtitleAiController::class, 'analyze'])->name('ai.analyze');
    // Route::post('/ai/chat', [SubtitleAiController::class, 'chat'])->name('ai.chat');


   Route::post('/ai/analyze', [SubtitleAiController::class, 'analyze'])->name('ai.analyze');
    Route::post('/ai/chat', [SubtitleAiController::class, 'chat'])->name('ai.chat');

