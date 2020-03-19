{{-- telephone link --}}
@php
    $value = data_get($entry, $column['name']);
    $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    $column['wrapper']['href'] = $column['wrapper']['href'] ?? 'tel:'.$value;
    $column['text'] =  str_limit(strip_tags($value), array_key_exists('limit', $column) ? $column['limit'] : 40, "[...]");
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
