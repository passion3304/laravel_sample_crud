<!-- hidden input -->
  <div class="form-group">
    <input
    	type="hidden"
    	class="form-control"

    	@foreach ($field as $attribute => $value)
			@if (is_string($attribute))
	    		{{ $attribute }}="{{ $value }}"
    		@endif
    	@endforeach
    	>
  </div>