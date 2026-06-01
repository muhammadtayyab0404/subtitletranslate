<html>

<head>

 <meta charset="UTF-8">
    <title>My Page</title>
<link rel="stylesheet"  href=" {{ asset('css/style.css') }}"> 


</head>


<body>


@include('pages.header',['variable'=>'ustad'])

<h1>Contact</h1>
<div class="mmnn">
<h1>hello</h1>
</div>

<p>


  @yield('newcontent')  
   
</p>


@section('heade')
    
<h1>contact</h1>
@endsection

<h1>footer</h1>