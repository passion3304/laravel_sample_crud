{{-- TODO: make this work --}}
{{-- important variables in here: $filter --}}

<div class="form-group backpack-filter">
	<label for="filter_{{ $filter->name }}">{{ $filter->label }}</label>
	<select id="filter_{{ $filter->name }}" name="filter_{{ $filter->name }}" class="form-control select2" placeholder="{{ $filter->placeholder }}">
		<option></option>
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

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

    @push('crud_list_styles')
        <!-- include select2 css-->
        <link href="{{ asset('vendor/backpack/select2/select2.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('vendor/backpack/select2/select2-bootstrap-dick.css') }}" rel="stylesheet" type="text/css" />
    @endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

    @push('crud_list_scripts')
    	<!-- include select2 js-->
        <script src="{{ asset('vendor/backpack/select2/select2.js') }}"></script>
        <script>
            jQuery(document).ready(function($) {
                // trigger select2 for each untriggered select2 box
                $('.select2').each(function (i, obj) {
                    if (!$(obj).data("select2"))
                    {
                        $(obj).select2();
                    }
                });
            });
        </script>

        <script>
			jQuery(document).ready(function($) {
				$("select[name=filter_{{ $filter->name }}]").change(function() {
					var value = $(this).val();
					var parameter = '{{ $filter->name }}';

					@if (!$crud->ajaxTable())
						// behaviour for normal table
						var current_url = '{{ Request::fullUrl() }}';
						var new_url = addOrUpdateUriParameter(current_url, parameter, value);

						// refresh the page to the new_url
				    	window.location.href = new_url;
				    @else
				    	// behaviour for ajax table
						var ajax_table = $("#crudTable").DataTable();
						var current_url = ajax_table.ajax.url();
						var new_url = addOrUpdateUriParameter(current_url, parameter, value);

						// replace the datatables ajax url with new_url and reload it
						ajax_table.ajax.url(new_url).load();

						// also set any duplicate filters to the same value
						$("select[name=filter_{{ $filter->name }}]").val(value);
				    @endif
				})
			});
		</script>
    @endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}