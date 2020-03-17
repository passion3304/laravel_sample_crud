{{-- regular object attribute --}}
@php
    $value = data_get($entry, $column['name']);
    if ($value != null) {
    	$value = number_format($value,
		    array_key_exists('decimals', $column) ? $column['decimals'] : 0,
		    array_key_exists('dec_point', $column) ? $column['dec_point'] : '.',
		    array_key_exists('thousands_sep', $column) ? $column['thousands_sep'] : ','
		 );
    }
    $column['text'] = !is_null($value) ?
    (array_key_exists('prefix', $column) ? $column['prefix'] : '').$value.(array_key_exists('suffix', $column) ? $column['suffix'] : '') : '';

    $column['escaped'] = $column['escaped'] ?? true;

    if(!empty($column['wrapper'])) {
        $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
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
