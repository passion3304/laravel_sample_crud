<!-- html5 date input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <input
    	type="time"
    	class="form-control"

    	@foreach ($field as $attribute => $value)
    		{{ $attribute }}="{{ $value }}"
    	@endforeach
    	>
  </div>