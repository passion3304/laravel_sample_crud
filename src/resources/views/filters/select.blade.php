{{-- TODO: make this work --}}
{{-- important variables in here: $filter --}}

<div class="form-group">
	<select name="filter_{{ $filter->name }}" class="form-control">
		<option value="">-</option>

		@if (is_array($filter->values) && count($filter->values))
			@foreach($filter->values as $key => $value)
				<option value="{{ $key }}"
					@if($filter->isActive() && $filter->currentValue == $key)
						selected
					@endif
					>
					{{ $value }}
				</option>
			@endforeach
		@endif
	</select>
</div>

@section('after_scripts')
<script>
	function updateQueryStringParameter(uri, key, value) {
	  var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
	  var separator = uri.indexOf('?') !== -1 ? "&" : "?";
	  if (uri.match(re)) {
	    return uri.replace(re, '$1' + key + "=" + value + '$2');
	  }
	  else {
	    return uri + separator + key + "=" + value;
	  }
	}

	jQuery(document).ready(function($) {
		$("select[name=filter_{{ $filter->name }}]").change(function() {
			var value = $(this).val();
			var current_name = '{{ $filter->name }}';
			var current_url = '{{ Request::url() }}';
			var new_url = '';

			new_url = updateQueryStringParameter(current_url, current_name, value);
			if (value == '') {
				new_url = new_url.replace(current_name+"=", "");
			}
			window.location.href = new_url;
		})
	});
</script>
@endsection