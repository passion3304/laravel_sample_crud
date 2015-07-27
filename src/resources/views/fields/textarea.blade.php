<!-- textarea -->
  <div class="form-group">
    <label>{{ $field['title'] }}</label>
    <textarea
    	class="form-control"

    	@foreach ($field as $attribute => $value)
    		{{ $attribute }}="{{ $value }}"
    	@endforeach

    	>{{ (isset($field['value']))?$field['value']:'' }}</textarea>
  </div>