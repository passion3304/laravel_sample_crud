{{-- Date Range Backpack CRUD filter --}}

<li filter-name="{{ $filter->name }}"
	filter-type="{{ $filter->type }}"
	class="dropdown {{ Request::get($filter->name)?'active':'' }}">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
	<div class="dropdown-menu">
		<div class="form-group backpack-filter m-b-0">
			<div class="input-group date">
        <div class="input-group-addon">
          <i class="fa fa-calendar"></i>
        </div>
        <input class="form-control pull-right" id="datepicker-{{ str_slug($filter->name) }}" type="text">
      </div>
		</div>
	</div>
</li>

{{-- ########################################### --}}
{{-- Extra CSS and JS for this particular filter --}}

{{-- FILTERS EXTRA CSS  --}}
{{-- push things in the after_styles section --}}

@push('crud_list_styles')
    <!-- include select2 css-->
    <link href="{{ asset('vendor/adminlte/plugins/daterangepicker/daterangepicker.css') }}" rel="stylesheet" type="text/css" />
		<style>
			.input-group.date {
				width: 320px;
				max-width: 100%; }
		</style>
@endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
	<script type="text/javascript" src="{{ asset('vendor/adminlte/plugins/daterangepicker/moment.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('vendor/adminlte/plugins/daterangepicker/daterangepicker.js') }}"></script>
  <script>
		jQuery(document).ready(function($) {
			var dateInput = $('#datepicker-{{ str_slug($filter->name) }}');
			dateInput.daterangepicker({
				timePicker: true, timePickerIncrement: 30
			},
			function (start, end) {
				var dates = {
					'from': start.format('YYYY-MM-DD H:mm'),
					'to': end.format('YYYY-MM-DD H:mm')
				};
				var value = JSON.stringify(dates);
				var parameter = '{{ $filter->name }}';

				@if (!$crud->ajaxTable())
					// behaviour for normal table
					var current_url = normalizeAmpersand('{{ Request::fullUrl() }}');
					var new_url = addOrUpdateUriParameter(current_url, parameter, value);

					// refresh the page to the new_url
					new_url = normalizeAmpersand(new_url.toString());
			    	window.location.href = new_url;
		    @else
			    	// behaviour for ajax table
					var ajax_table = $('#crudTable').DataTable();
					var current_url = ajax_table.ajax.url();
					var new_url = addOrUpdateUriParameter(current_url, parameter, value);

					// replace the datatables ajax url with new_url and reload it
					new_url = normalizeAmpersand(new_url.toString());
					ajax_table.ajax.url(new_url).load();

					// mark this filter as active in the navbar-filters
					if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
						$('li[filter-name={{ $filter->name }}]').removeClass('active').addClass('active');
					}
					else
					{
						$('li[filter-name={{ $filter->name }}]').trigger('filter:clear');
					}
		    @endif
			});

			$('li[filter-name={{ $filter->name }}]').on('hide.bs.dropdown', function () {
				if($('.daterangepicker').is(':visible'))
			    return false;
			});

			$('li[filter-name={{ $filter->name }}]').on('filter:clear', function(e) {
				// console.log('select2 filter cleared');
				$('li[filter-name={{ $filter->name }}]').removeClass('active');
				$('#datepicker-{{ str_slug($filter->name) }}').val('');
			});
		});
  </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
