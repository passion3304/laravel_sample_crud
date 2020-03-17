@php
	$column['key'] = $column['key'] ?? $column['name'];
	$entryValue = data_get($entry, $column['key']);
    $column['text'] = $column['options'][$entryValue] ?? '';

    if(!empty($column['wrapper'])) {
        $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    }

    $column['escaped'] = $column['escaped'] ?? true;
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
