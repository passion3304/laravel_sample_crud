@php
    $column['escaped'] = $column['escaped'] ?? false;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = $entry->{$column['name']} ?? '';

    // turn the text into markdown
    $column['text'] = Illuminate\Mail\Markdown::parse($column['text']);

    if(!empty($column['text'])) {
        $column['text'] = $column['prefix'].$column['text'].$column['suffix'];
    }
@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    @if($column['escaped'])
        {{ $column['text'] }}
    @else
        {!! $column['text'] !!}
    @endif
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')

