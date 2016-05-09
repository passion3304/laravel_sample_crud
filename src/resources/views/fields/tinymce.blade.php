<!-- Tiny MCE -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <textarea
    	class="form-control tinymce"
    	id="tinymce-{{ $field['name'] }}"

    	@foreach ($field as $attribute => $value)
            @if (is_string($attribute) && is_string($value))
        		{{ $attribute }}="{{ $value }}"
            @endif
    	@endforeach

    	>{{ (isset($field['value']))?$field['value']:'' }}</textarea>
  </div>