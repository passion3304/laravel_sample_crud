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
@endphp
<span>
    @include('crud::columns.inc.column_wrapper')
</span>
