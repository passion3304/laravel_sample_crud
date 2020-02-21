{{-- custom return value --}}
@php
    $value = $entry->{$column['function_name']}(...($column['function_parameters'] ?? []));
    $text = (array_key_exists('prefix', $column) ? $column['prefix'] : '').str_limit($value, array_key_exists('limit', $column) ? $column['limit'] : 40, "[...]").(array_key_exists('suffix', $column) ? $column['suffix'] : '');
@endphp

<span>
        @include('crud::inc.column_anchors',['text' => $text, 'escaped' => false])
</span>
