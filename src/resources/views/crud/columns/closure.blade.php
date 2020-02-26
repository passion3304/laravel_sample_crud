{{-- closure function column type --}}
@php
    $column['escaped'] = $column['escaped'] ?? false;
    $text = $column['function']($entry);
@endphp
<span>

    @include('crud::columns.inc.column_wrapper',['text' => $text])

</span>
