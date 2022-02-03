@php
	$column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['columns'] = $column['columns'] ?? ['value' => 'Value'];

    if($column['value'] instanceof \Closure) {
        $column['value'] = $column['value']($entry);
    }

	// if this attribute isn't using attribute casting, decode it
	if (is_string($column['value'])) {
	    $column['value'] = json_decode($column['value'], true);
    }

	// always work with arrays in the html, so if it is an object, get an array back from it.
	if(is_object($column['value'])) {
		$column['value'] = (array)$column['value'];
	}

    // check if it is a multidimensional array, if not we turn $value into one
    if (is_array($column['value']) && !empty($column['value']) && !is_multidimensional_array($column['value'])) {
        $column['value'] = array($column['value']);
    }
@endphp

<span>
    @if (!empty($column['value']) && count($column['columns']))

    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')

    <table class="table table-bordered table-condensed table-striped m-b-0">
		<thead>
			<tr>
				@foreach($column['columns'] as $tableColumnKey => $tableColumnLabel)
				<th>{{ $tableColumnLabel }}</th>
				@endforeach
			</tr>
		</thead>
		<tbody>
			@foreach ($column['value'] as $tableRow)
			<tr>
				@foreach($column['columns'] as $tableColumnKey => $tableColumnLabel)
					<td>
                        {{ $tableRow[$tableColumnKey] ?? '' }}
					</td>
				@endforeach
			</tr>
			@endforeach
		</tbody>
    </table>

    @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
    
    @else
    
    {{ $column['default'] ?? '-' }}

	@endif
</span>
