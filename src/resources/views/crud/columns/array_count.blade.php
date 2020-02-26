{{-- enumerate the values in an array  --}}
@php
    $array = data_get($entry, $column['name']);
    $suffix = isset($column['suffix']) ? $column['suffix'] : 'items';

    // the value should be an array wether or not attribute casting is used
    if (! is_array($array)) {
        $array = json_decode($array, true);
    }
    if($array && count($array))

    $column['text'] = count($array).' '.$suffix);

@endphp

<span>
        @include('crud::columns.inc.column_wrapper')
</span>
