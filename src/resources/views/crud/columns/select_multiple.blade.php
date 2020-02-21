{{-- relationships with pivot table (n-n) --}}
@php
    $results = data_get($entry, $column['name']);

    if(!$results->isEmpty()) {
        $related_model_key = $results->first()->getKeyName();
    }
@endphp

<span>

        @if ($results && $results->count())
            @php($results_array = $results->pluck($column['attribute'],$related_model_key)->toArray())
            @php($lastKey = array_key_last($results_array))
            @foreach ($results_array as $key => $result)

            @include('crud::inc.column_anchors',['text' => $result, 'related_model_key' => $key])@if($lastKey != $key),@endif
            @endforeach
            @else
            -
            @endif
</span>
