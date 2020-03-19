{{-- regular object attribute --}}
@php
    $value = data_get($entry, $column['name']);
    $column['text'] = is_string($value) ? $value : ''; // don't try to show arrays/object if the column was autoSet
    $column['escaped'] = $column['escaped'] ?? false;
@endphp

<span>
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
        @if($column['escaped'])
            {{ $column['text'] }}
        @else
            {!! $column['text'] !!}
        @endif
    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
</span>
