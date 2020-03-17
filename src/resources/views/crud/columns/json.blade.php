@php
    $column['escaped'] = $column['escaped'] ?? true;

    $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'pre';

    $column['text'] = is_string($entry->{$column['name']}) ?
        json_encode(json_decode($entry->{$column['name']}, true), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) :
        json_encode($entry->{$column['name']}, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)

@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    @if($column['escaped'])
        {{ $column['text'] }}
    @else
        {!! $column['text'] !!}
    @endif
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
