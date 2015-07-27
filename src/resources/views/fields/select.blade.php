<!-- select -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <?php $entity_model = $crud['model']; ?>
    <select
    	class="form-control"

    	@foreach ($field as $attribute => $value)
    		{{ $attribute }}="{{ $value }}"
    	@endforeach
    	>

    	@if ($entity_model::isColumnNullable($field['name']))
            <option value="">-</option>
        @endif

	    	@if (isset($field['model']))
	    		@foreach ($field['model']::all() as $connected_entity_entry)
	    			<option value="{{ $connected_entity_entry->id }}"
						@if (isset($field['value']) && $connected_entity_entry->id==$field['value'])
							 selected
						@endif
	    			>{{ $connected_entity_entry->$field['attribute'] }}</option>
	    		@endforeach
	    	@endif
	</select>
  </div>