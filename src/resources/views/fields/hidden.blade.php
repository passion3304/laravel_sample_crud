<!-- hidden input -->
  <div class="form-group">
    <input
    	type="hidden"
    	class="form-control"

    	@foreach ($field as $attribute => $value)
			@if (is_string($attribute) && is_string($value))
	    		{{ $attribute }}="{{ $value }}"
    		@endif
    	@endforeach
    	>
  </div>