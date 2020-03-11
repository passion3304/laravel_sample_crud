@php
    $column['text'] = $column['value']?? ' ';
    $column['escaped'] = false;
@endphp
<span>
	@include('crud::columns.inc.column_wrapper')
</span>
