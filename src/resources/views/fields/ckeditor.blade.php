<!-- CKeditor -->
  <div class="form-group">
    <label>{{ $field['label'] }}</label>
    <textarea
    	class="form-control ckeditor"
    	id="ckeditor-{{ $field['name'] }}"

    	@foreach ($field as $attribute => $value)
            @if (is_string($attribute) && is_string($value))
    		  @if($attribute == 'value')
                    {{ $attribute }}="{{ old($field['name']) ? old($field['name']) : $value }}"
                @else
                    {{ $attribute }}="{{ $value }}"
                @endif
            @endif
    	@endforeach

    	>{{ old($field['name']) ? old($field['name']) : ((isset($field['value']))?$field['value']:'')  }}</textarea>
  </div>