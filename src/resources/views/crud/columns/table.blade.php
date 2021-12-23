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

    // check if it is a multidimensional array, if not we turn $value into one
    if (is_array($column['value']) && count($column['value']) === count($column['value'], COUNT_RECURSIVE)) {
        $column['value'] = array($column['value']);
    }
@endphp

<span>
    @if ($column['value'] && count($column['columns']))

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
						@if( is_array($tableRow) && isset($tableRow[$tableColumnKey]) )

                            {{ $tableRow[$tableColumnKey] }}

                        @elseif( is_object($tableRow) && property_exists($tableRow, $tableColumnKey) )

                            {{ $tableRow->{$tableColumnKey} }}

                        @endif
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
