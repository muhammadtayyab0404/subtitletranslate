<h1>

    ha ustad
</h1>




<h1>values are...</h1>

@foreach ($user as $item)

<h2>{{ $item->name }} |
    {{ $item->email }} |
    {{ $item->description }}|
</h2>
    
@endforeach