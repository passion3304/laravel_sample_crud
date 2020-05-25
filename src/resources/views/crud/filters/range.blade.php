{{-- Example Backpack CRUD filter --}}
<li filter-name="{{ Str::slug($filter->name) }}"
	filter-type="{{ $filter->type }}"
	class="nav-item dropdown {{ Request::get(Str::slug($filter->name))?'active':'' }}">
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
    <div class="dropdown-menu p-0">

			<div class="form-group backpack-filter mb-0">
					<?php
                        $from = '';
                        $to = '';
                        if ($filter->currentValue) {
                            $range = (array) json_decode($filter->currentValue);
                            $from = $range['from'];
                            $to = $range['to'];
                        }
                    ?>
					<div class="input-group">
				        <input class="form-control pull-right from"
				        		type="number"
									@if($from)
										value = "{{ $from }}"
									@endif
									@if(array_key_exists('label_from', $filter->options))
										placeholder = "{{ $filter->options['label_from'] }}"
									@else
										placeholder = "min value"
									@endif
				        		>
								<input class="form-control pull-right to"
				        		type="number"
									@if($to)
										value = "{{ $to }}"
									@endif
									@if(array_key_exists('label_to', $filter->options))
										placeholder = "{{ $filter->options['label_to'] }}"
									@else
										placeholder = "max value"
									@endif
				        		>
				        <div class="input-group-append range-filter-{{ Str::slug($filter->name) }}-clear-button">
				          <a class="input-group-text" href=""><i class="la la-times"></i></a>
				        </div>
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


{{-- FILTER JAVASCRIPT CHECKLIST

- redirects to a new URL for standard DataTables
- replaces the search URL for ajax DataTables
- users have a way to clear this filter (and only this filter)
- filter:clear event on li[filter-name], which is called by the "Remove all filters" button, clears this filter;

END OF FILTER JAVSCRIPT CHECKLIST --}}

@push('crud_list_scripts')
	<script>
		jQuery(document).ready(function($) {
			$("li[filter-name={{ Str::slug($filter->name) }}] .from, li[filter-name={{ Str::slug($filter->name) }}] .to").change(function(e) {
				e.preventDefault();
				var from = $("li[filter-name={{ Str::slug($filter->name) }}] .from").val();
				var to = $("li[filter-name={{ Str::slug($filter->name) }}] .to").val();
				if (from || to) {
					var range = {
						'from': from,
						'to': to
					};
					var value = JSON.stringify(range);
				} else {
					//this change to empty string,because addOrUpdateUriParameter method just judgment string
					var value = '';
				}
				var parameter = '{{ Str::slug($filter->name) }}';

				// behaviour for ajax table
				var ajax_table = $('#crudTable').DataTable();
				var current_url = ajax_table.ajax.url();
				var new_url = addOrUpdateUriParameter(current_url, parameter, value);

				// replace the datatables ajax url with new_url and reload it
				new_url = normalizeAmpersand(new_url.toString());
				ajax_table.ajax.url(new_url).load();

				// add filter to URL
				crud.updateUrl(new_url);

				// mark this filter as active in the navbar-filters
				if (URI(new_url).hasQuery('{{ Str::slug($filter->name) }}', true)) {
					$('li[filter-name={{ Str::slug($filter->name) }}]').removeClass('active').addClass('active');
				}
			});

			$('li[filter-name={{ Str::slug($filter->name) }}]').on('filter:clear', function(e) {
				$('li[filter-name={{ Str::slug($filter->name) }}]').removeClass('active');
				$("li[filter-name={{ Str::slug($filter->name) }}] .from").val("");
				$("li[filter-name={{ Str::slug($filter->name) }}] .to").val("");
				$("li[filter-name={{ Str::slug($filter->name) }}] .to").trigger('change');
			});

			// range clear button
			$(".range-filter-{{ Str::slug($filter->name) }}-clear-button").click(function(e) {
				e.preventDefault();

				$('li[filter-name={{ Str::slug($filter->name) }}]').trigger('filter:clear');
			})

		});
	</script>
@endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
