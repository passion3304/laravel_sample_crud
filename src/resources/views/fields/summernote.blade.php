<!-- summernote editor -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <textarea
    	class="form-control summernote"

    	@foreach ($field as $attribute => $value)
    		@if (is_string($attribute) && is_string($value))
	    		{{ $attribute }}="{{ $value }}"
    		@endif
    	@endforeach

    	>
        @if(old($field['name']))
            {{ old($field['name']) }}
        @else
            {{ ( isset($field['value']))?$field['value']:'' }}
        @endif
        </textarea>
  </div>