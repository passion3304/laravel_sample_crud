{{-- Simple Backpack CRUD filter --}}

<li filter-name="{{ Str::slug($filter->name) }}"
	filter-type="{{ $filter->type }}"
	class="nav-item {{ Request::get(Str::slug($filter->name))?'active':'' }}">
    <a class="nav-link" href=""
		parameter="{{ Str::slug($filter->name) }}"
    	>{{ $filter->label }}</a>
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
			$("li[filter-name={{ Str::slug($filter->name) }}] a").click(function(e) {
				e.preventDefault();

				var parameter = $(this).attr('parameter');

		    	// behaviour for ajax table
				var ajax_table = $("#crudTable").DataTable();
				var current_url = ajax_table.ajax.url();

				if (URI(current_url).hasQuery(parameter)) {
					var new_url = URI(current_url).removeQuery(parameter, true);
				} else {
					var new_url = URI(current_url).addQuery(parameter, true);
				}

				new_url = normalizeAmpersand(new_url.toString());

				// replace the datatables ajax url with new_url and reload it
				ajax_table.ajax.url(new_url).load();

				// add filter to URL
				crud.updateUrl(new_url);

				// mark this filter as active in the navbar-filters
				if (URI(new_url).hasQuery('{{ Str::slug($filter->name) }}', true)) {
					$("li[filter-name={{ Str::slug($filter->name) }}]").removeClass('active').addClass('active');
                    $('#remove_filters_button').removeClass('invisible');
				}
				else
				{
					$("li[filter-name={{ Str::slug($filter->name) }}]").trigger("filter:clear");
				}
			});

			// clear filter event (used here and by the Remove all filters button)
			$("li[filter-name={{ Str::slug($filter->name) }}]").on('filter:clear', function(e) {
				// console.log('dropdown filter cleared');
				$("li[filter-name={{ Str::slug($filter->name) }}]").removeClass('active');
			});
		});
	</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
