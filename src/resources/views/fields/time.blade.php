    <!-- html5 date input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <input
    	type="time"
    	class="form-control"

    	@foreach ($field as $attribute => $value)
            @if (is_string($attribute))
        		@if($attribute == 'value')
                    {{ $attribute }}="{{ old($field['name']) ? old($field['name']) : $value }}"
                @else
                    {{ $attribute }}="{{ $value }}"
                @endif
            @endif
    	@endforeach
    	>
  </div>