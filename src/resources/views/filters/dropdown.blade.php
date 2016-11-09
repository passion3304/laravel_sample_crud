{{-- TODO: make this work --}}
{{-- important variables in here: $filter --}}


<li class="dropdown {{ Request::get($filter->name)?'active':'' }}">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
    <div class="dropdown-menu padding-10">

		<div class="form-group backpack-filter">
			<div class="filter-content">
				<select name="filter_{{ $filter->name }}" class="form-control input-sm">
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
		</div>

    </div>
  </li>


{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

    {{-- @push('crud_list_styles')
        <!-- no css -->
    @endpush --}}


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

    @push('crud_list_scripts')
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

						// set this filter as active in the navbar-filters
						if (URI(new_url).hasQuery('{{ $filter->name }}')) {
							$("li[filter-name={{ $filter->name }}]").removeClass('active').addClass('active');
						}
				    @endif
				})
			});
		</script>
    @endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}