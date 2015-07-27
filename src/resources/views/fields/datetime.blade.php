<!-- html5 datetime input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <input
    	type="datetime-local"
    	class="form-control"

    	@foreach ($field as $attribute => $value)
    		{{ $attribute }}="{{ $value }}"
    	@endforeach
    	>
  </div>