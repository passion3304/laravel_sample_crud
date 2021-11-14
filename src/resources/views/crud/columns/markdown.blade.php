@php
    $column['text'] = Illuminate\Mail\Markdown::parse($entry->{$column['name']} ?? '');
    $column['escaped'] = $column['escaped'] ?? false;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';

    if(!empty($column['text'])) {
        $column['text'] = $column['prefix'].$column['text'].$column['suffix'];
    }else{
        $column['text'] = $column['default'] ?? null;
    }
@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    @if($column['escaped'])
        {{ $column['text'] }}
    @else
        {!! $column['text'] !!}
    @endif
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')

