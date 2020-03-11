{{-- regular object attribute --}}
@php
    $value = data_get($entry, $column['name']);
    $column['text'] = is_string($value) ? $value : ''; // don't try to show arrays/object if the column was autoSet
    $column['escaped'] = false;
@endphp

<span>
    @include('crud::columns.inc.column_wrapper')
</span>
