@extends('layouts.masterlayouts')








@section('newcontent')
<p>
    about us
</p>

@endsection









{{-- 

<html>

<head>

 <meta charset="UTF-8">
    <title>My Page</title>
<link rel="stylesheet"   href=" {{ asset('css/style.css') }}"> 


</head>


<body>



<?php

$pandwa= 'hello';

?>
@include('pages.header',['variable'=>'ustad'])
<div class="mmnn">
<h1>hello</h1>
</div>



@includeWhen(true,'pages.footer',['now'=>$pandwa])
<a href="/home"> Welcome Page</a>

@if ($prodid)
<p>yoyo</p>

{!!  "<h1> hello </h1>" !!}

@php

echo "Haan Ustad";

@endphp
    {{$prodid}}
    @endif


    @if ($comntid)
    <h2>{{ $comntid }}</h2>
@endif

@php
$user =['aa' =>2 , 'bb' =>3 ,'cc'=>4 ];
@endphp

@foreach ($user as $key=>$item)


@if ($loop->last)


<p style="background-color: aqua">{{ $loop->index }}  {{ $key. $item }}</p>
    
@else
    
<p>{{ $loop->index }}  {{ $key. $item }}</p>

@endif

@endforeach
</body>

</html> --}}