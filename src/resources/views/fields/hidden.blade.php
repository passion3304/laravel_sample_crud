<!-- hidden input -->
  <div class="form-group">
    <input
    	type="hidden"
    	class="form-control"

    	@foreach ($field as $attribute => $value)
    		{{ $attribute }}="{{ $value }}"
    	@endforeach
    	>
  </div>