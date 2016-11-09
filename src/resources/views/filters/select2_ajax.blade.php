{{-- Select2 Ajax Backpack CRUD filter --}}

<li filter-name="{{ $filter->name }}"
	filter-type="{{ $filter->type }}"
	class="dropdown {{ Request::get($filter->name)?'active':'' }}">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
    <div class="dropdown-menu ajax-select padding-10">
	    <div class="form-group m-b-0">
	    	<input type="text" value="{{ Request::get($filter->name)?Request::get($filter->name):'' }}" id="filter_{{ $filter->name }}">
	    </div>
    </div>
  </li>

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

@push('crud_list_styles')
    <!-- include select2 css-->
    <link href="{{ asset('vendor/backpack/select2/select2.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('vendor/backpack/select2/select2-bootstrap-dick.css') }}" rel="stylesheet" type="text/css" />
    <style>
	  .form-inline .select2-container {
	    display: inline-block;
	  }
	  li[filter-type="{{ $filter->type }}"] .select2-container {
	  	display: block;
	  }
    </style>
@endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
	<!-- include select2 js-->
    <script src="{{ asset('vendor/backpack/select2/select2.js') }}"></script>
    <script>
        jQuery(document).ready(function($) {
            // trigger select2 for each untriggered select2 box
            $('#filter_{{ $filter->name }}').select2({
			    minimumInputLength: 2,
			    // tags: [],
			    ajax: {
			        url: "{{ $filter->values }}",
			        dataType: 'json',
			        type: "GET",
			        quietMillis: 50,
			        data: function (term) {
			            return {
			                term: term
			            };
			        },
			        results: function (data) {
			            return {
			                results: $.map(data, function (item, i) {
			                    return {
			                        text: item,
			                        id: i
			                    }
			                })
			            };
			        }
			    }
			}).on('change', function (evt) {
				var value = $(this).val();
				var parameter = '{{ $filter->name }}';

				@if (!$crud->ajaxTable())
					// behaviour for normal table
					var current_url = '{{ Request::fullUrl() }}'.replace("&amp;", "&");
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

					// mark this filter as active in the navbar-filters
					if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
						$("li[filter-name={{ $filter->name }}]").removeClass('active').addClass('active');
					}
					else
					{
						$("li[filter-name={{ $filter->name }}]").trigger("filter:clear");
					}
			    @endif
			});

			// clear filter event (used here and by the Remove all filters button)
			$("li[filter-name={{ $filter->name }}]").on('filter:clear', function(e) {
				// console.log('select2 filter cleared');
				$("li[filter-name={{ $filter->name }}]").removeClass('active');
				$("li[filter-name={{ $filter->name }}] .select2").select2("val", "");
			});
        });
    </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}

