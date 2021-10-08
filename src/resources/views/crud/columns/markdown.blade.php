@php
    $column['escaped'] = $column['escaped'] ?? true;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';
    $column['text'] = $entry->{$column['name']} ?? '';

    // if needed, strip out HTML from the text
    $column['text'] = $column['escaped'] ? strip_tags($column['text']) : $column['text'];

    // turn the text into markdown
    $column['text'] = Illuminate\Mail\Markdown::parse($column['text']);

    if(!empty($column['text'])) {
        $column['text'] = $column['prefix'].$column['text'].$column['suffix'];
    }
@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    {!! $column['text'] !!}
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')

