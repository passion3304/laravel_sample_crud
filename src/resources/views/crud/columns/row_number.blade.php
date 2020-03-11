{{-- row number --}}
@php
	$column['text'] = (array_key_exists('prefix', $column) ? $column['prefix'] : '').str_limit(strip_tags($rowNumber), array_key_exists('limit', $column) ? $column['limit'] : 40, "[...]").(array_key_exists('suffix', $column) ? $column['suffix'] : '');
@endphp

<span>
	@include('crud::columns.inc.column_wrapper')
</span>
