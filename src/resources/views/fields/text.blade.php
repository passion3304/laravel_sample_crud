<!-- text input -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <input
    	type="text"
    	class="form-control"

    	@foreach ($field as $attribute => $value)
    		{{ $attribute }}="{{ $value }}"
    	@endforeach
    	>
  </div>