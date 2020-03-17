{{-- relationships with pivot table (n-n) --}}
@php
    $results = data_get($entry, $column['name']);
    $results_array = [];
    if(!$results->isEmpty()) {
        $related_key = $results->first()->getKeyName();
        $results_array = $results->pluck($column['attribute'],$related_key)->toArray();
        $lastKey = array_key_last($results_array);
    }
    $column['escaped'] = $column['escaped'] ?? true;

     // if the wrapper "element" is not defined, set it to the default - an anchor
    if(!empty($column['wrapper'])) {
        $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    }
@endphp

<span>

    @if (!empty($results_array))
        @foreach ($results_array as $key => $attribute)
        @php
            $related_key = $key;
            $text = str_limit($attribute, array_key_exists('limit', $column) ? $column['limit'] : 40, '[...]');
        @endphp
            @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
                @if($column['escaped'])
                    {{ $text }}
                @else
                    {!! $text !!}
                @endif

            @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')

            @if($lastKey != $key),@endif
        @endforeach
    @else
        -
    @endif

</span>
