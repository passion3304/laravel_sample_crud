{{-- relationships with pivot table (n-n) --}}
@php
    $results = data_get($entry, $column['name']);

    if(!$results->isEmpty()) {
        $related_key = $results->first()->getKeyName();
        $results_array = $results->pluck($column['attribute'],$related_key)->toArray();
        $lastKey = array_key_last($results_array);
    }else{
        $results_array = [];
        $column['text'] = '-';
    }
@endphp

<span>

    @if (!empty($results_array))
        @foreach ($results_array as $key => $result)
            @include('crud::columns.inc.column_wrapper',['text' => $result, 'related_key' => $key])@if($lastKey != $key),@endif
        @endforeach
    @else
        @include('crud::columns.inc.column_wrapper')
    @endif

</span>
