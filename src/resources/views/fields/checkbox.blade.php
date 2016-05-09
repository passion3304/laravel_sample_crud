<!-- checkbox field -->
<div class="checkbox">
	<label>
	  <input type="hidden" name="{{ $field['name'] }}" value="0">
	  <input type="checkbox" @foreach ($field as $attribute => $value)
        @if (is_string($attribute) && is_string($value))
    		@if( $attribute == 'value' )
    			@if((int) $value == 1)
    			checked = "checked"
    			@endif
    			{{ $attribute }}= "1"
    		@else
    			{{ $attribute }}="{{ $value }}"
    		@endif
        @endif
	@endforeach> {{ $field['label'] }}
	</label>
</div>
