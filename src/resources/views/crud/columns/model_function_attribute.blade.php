{{-- custom return value via attribute --}}
@php
	$model_function = $entry->{$column['function_name']}(...($column['function_parameters'] ?? []));

	if ($model_function) {
	    $value = $model_function->{$column['attribute']};
	} else {
		$value = '';
    }
    $text = (array_key_exists('prefix', $column) ? $column['prefix'] : '').str_limit($value, array_key_exists('limit', $column) ? $column['limit'] : 40, "[...]").(array_key_exists('suffix', $column) ? $column['suffix'] : '');
@endphp
<span>
	@if(isset($column['anchor']['href']))
        @include('crud::inc.column_anchors',['text' => $text])
    @else
        {{ $text }}
    @endif
</span>
