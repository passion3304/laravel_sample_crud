{{-- custom return value --}}
@php
    $value = $entry->{$column['function_name']}(...($column['function_parameters'] ?? []));
    $text = (array_key_exists('prefix', $column) ? $column['prefix'] : '').str_limit($value, array_key_exists('limit', $column) ? $column['limit'] : 40, "[...]").(array_key_exists('suffix', $column) ? $column['suffix'] : '');
    $column['escaped'] = $column['escaped'] ?? false;
@endphp

<span>
        @include('crud::columns.inc.column_wrapper',['text' => $text])
</span>
