@extends('layouts.masterlayouts')

<h1>helalo</h1>

@php

$user = ["ss","dsd","ssdsd"];
@endphp

@push('javascript')


@endpush



@prepend('css')

<style>
    body{
        background-color: transparent;
    }
</style>
    
@endprepend

@section('newcontent')
   



@endsection
   



@push('css')
<link rel="stylesheet"  href=" {{ asset('css/style.css') }}"> 
@endpush


@dump($arr)


@foreach ($arr as $key => $value)

<h1> {{ $key  }} | {{ $value['name']  }} |
    </h1>
    <a href="{{route('bbnn', $key)}}"> SHOW</a>

<h1>  </h1>

    
@endforeach
<h1>
    

    </h1>
<script>

    var data = @JSON($user );
    console.log(data);
</script>