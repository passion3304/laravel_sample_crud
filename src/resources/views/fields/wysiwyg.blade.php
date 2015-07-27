<!-- default wysiwyg editor - summernote -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <textarea
    	class="form-control summernote"

    	@foreach ($field as $attribute => $value)
    		{{ $attribute }}="{{ $value }}"
    	@endforeach

    	>{{ (isset($field['value']))?$field['value']:'' }}</textarea>
  </div>