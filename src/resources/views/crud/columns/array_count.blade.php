{{-- enumerate the values in an array  --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['escaped'] = $column['escaped'] ?? false;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? ' items';
    $column['text'] = $column['default'] ?? '-';

    if(is_callable($column['value'])) {
        $column['value'] = $column['value']($entry);
    }

    // the value should be an array whether or not attribute casting is used
    if (! is_array($column['value'])) {
        $column['value'] = json_decode($column['value'], true);
    }

    if($column['value'] && count($column['value'])) {
        $column['text'] = $column['prefix'].count($column['value']).$column['suffix'];
    }
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