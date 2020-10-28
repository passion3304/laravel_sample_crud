{{-- Date Range Backpack CRUD filter --}}

@php
    $filterOptions = $filter->options['date_range_options'] ?? [];
    $filterOptions['locale'] = $filter->options['date_range_options']['locale'] ?? [];




    //initialize bare default configurations
    (isset($filterOptions['timePicker'])) ?: $filterOptions['timePicker'] = false;
    (isset($filterOptions['alwaysShowCalendars'])) ?: $filterOptions['alwaysShowCalendars'] = true;
    (isset($filterOptions['locale']['firstDay'])) ?: $filterOptions['locale']['firstDay'] = 0;
    (isset($filterOptions['autoUpdateInput'])) ?: $filterOptions['autoUpdateInput'] = true;
    (isset($filterOptions['locale']['format'])) ?: $filterOptions['locale']['format'] = config('backpack.base.default_date_format');
    (isset($filterOptions['ranges'])) ?: $filterOptions['ranges'] = [
        trans('backpack::crud.today') =>  "[moment().subtract(6, 'days'), moment()]",
        trans('backpack::crud.yesterday') => "[moment().subtract(1, 'days'), moment().subtract(1, 'days')]",
		trans('backpack::crud.last_7_days') => "[moment().subtract(6, 'days'), moment()]",
		trans('backpack::crud.last_30_days') => "[moment().subtract(29, 'days'), moment()]",
		trans('backpack::crud.this_month') => "[moment().startOf('month'), moment().endOf('month')]",
		trans('backpack::crud.last_month') => "[moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]"

    ];
    (isset($filterOptions['locale']['applyLabel'])) ?: $filterOptions['locale']['applyLabel'] = trans('backpack::crud.apply');
    (isset($filterOptions['locale']['cancelLabel'])) ?: $filterOptions['locale']['cancelLabel'] = trans('backpack::crud.cancel');
    (isset($filterOptions['locale']['fromLabel'])) ?: $filterOptions['locale']['fromLabel'] = trans('backpack::crud.from');
    (isset($filterOptions['locale']['toLabel'])) ?: $filterOptions['locale']['toLabel'] = trans('backpack::crud.to');
    (isset($filterOptions['locale']['customRangeLabel'])) ?: $filterOptions['locale']['customRangeLabel'] = trans('backpack::crud.custom_range');
    (isset($filterOptions['locale']['weekLabel'])) ?: $filterOptions['locale']['weekLabel'] = trans('backpack::crud.week_label');


    //we check if developer forced any start/end on field initialization
    //if it does he must provide a valid date or a carbon instance so we convert to YYYY-MM-DD

    (!isset($filterOptions['startDate']) || !$filterOptions['startDate'] instanceof \Carbon\CarbonInterface) ?:
    ($filterOptions['timePicker'] ?
        $filterOptions['startDate'] = $filterOptions['startDate']->toDateTimeString() :
            $filterOptions['startDate'] = $filterOptions['startDate']->toDateString());

    (!isset($filterOptions['endDate']) || !$filterOptions['endDate'] instanceof \Carbon\CarbonInterface) ?:
    ($filterOptions['timePicker'] ?
        $filterOptions['endDate'] = $filterOptions['endDate']->toDateTimeString() :
            $filterOptions['endDate'] = $filterOptions['endDate']->toDateString());

    //if filter is active we override developer init values
    if($filter->currentValue) {
	    $dates = (array)json_decode($filter->currentValue);
        $filterOptions['startDate'] = $dates['from'];
        $filterOptions['endDate'] = $dates['to'];
    }






@endphp


<li filter-name="{{ $filter->name }}"
    filter-type="{{ $filter->type }}"
    filter-key="{{ $filter->key }}"
	class="nav-item dropdown {{ Request::get($filter->name)?'active':'' }}">
	<a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{ $filter->label }} <span class="caret"></span></a>
	<div class="dropdown-menu p-0">
		<div class="form-group backpack-filter mb-0">
			<div class="input-group date">
		        <div class="input-group-prepend">
		          <span class="input-group-text"><i class="la la-calendar"></i></span>
		        </div>
		        <input class="form-control pull-right"
                        id="daterangepicker-{{ str_slug($filter->name) }}"
                        data-bs-datepicker="{{json_encode($filterOptions)}}"
                        type="text"
                        >
		        <div class="input-group-append daterangepicker-{{ str_slug($filter->name) }}-clear-button">
		          <a class="input-group-text" href=""><i class="fa fa-times"></i></a>
		        </div>
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
	<link rel="stylesheet" type="text/css" href="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.css') }}" />
	<style>
		.input-group.date {
			width: 320px;
			max-width: 100%; }
		.daterangepicker.dropdown-menu {
			z-index: 3001!important;
		}
	</style>
@endpush


{{-- FILTERS EXTRA JS --}}
{{-- push things in the after_scripts section --}}

@push('crud_list_scripts')
<script type="text/javascript" src="{{ asset('packages/moment/min/moment-with-locales.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('packages/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
  <script>

  		function applyDateRangeFilter{{camel_case($filter->name)}}(start, end) {

  			if (start && end) {


  				var dates = {
					'from': start.format('YYYY-MM-DD'),
					'to': end.format('YYYY-MM-DD')
                };

                var value = JSON.stringify(dates);

  			} else {
  				//this change to empty string,because addOrUpdateUriParameter method just judgment string
  				var value = '';
  			}
			var parameter = '{{ $filter->name }}';

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
			if (URI(new_url).hasQuery('{{ $filter->name }}', true)) {
				$('li[filter-name={{ $filter->name }}]').removeClass('active').addClass('active');
			} else {
				$('li[filter-name={{ $filter->name }}]').trigger('filter:clear');
			}
  		}

		jQuery(document).ready(function($) {
			var dateRangeInput = $('#daterangepicker-{{ str_slug($filter->name) }}');

            $config = dateRangeInput.data('bs-datepicker');

            $ranges = $config.ranges;
            $config.ranges = {};

            //the use of eval() is not always recommended, but this use case fits
            //the string beeing eval'd comes from a trusty source because is always hardcoded (dev or us).
            for (var key in $ranges) {
                if ($ranges.hasOwnProperty(key)) {
                    $config.ranges[key] = eval($ranges[key]);
                }
            }

            @if(!$filter->currentValue)

            //this is used mainly to avoid converting valid Carbon dates to other formats.

			const regex = /^([12]\d{3})-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])/;

            if ($config.startDate) {
                if (regex.exec($config.startDate) !== null) {
					$config.startDate = moment($config.startDate);
                }else{
					$config.startDate = moment($config.startDate, '{{$filterOptions['locale']['format']}}');
				}

                //start date can't be after end date
                if (moment().diff($config.startDate) < 0) {

                    $config.endDate = $config.startDate.format('YYYY-MM-DD HH:mm:ss');
                }

            }

            if ($config.endDate) {
				if (regex.exec($config.endDate) !== null) {
					$config.endDate = moment($config.endDate);
                }else{
                	$config.endDate = moment($config.endDate, '{{$filterOptions['locale']['format']}}');
            	}
			}
            @else
                $config.startDate = moment($config.startDate);
                $config.endDate = moment($config.endDate);
            @endif


            //set calendar localization
            moment.locale('{{ \App::getLocale() }}');

            dateRangeInput.daterangepicker($config);

			dateRangeInput.on('apply.daterangepicker', function(ev, picker) {
				applyDateRangeFilter{{$filter->key}}(picker.startDate, picker.endDate);
			});

			$('li[filter-key={{ $filter->key }}]').on('hide.bs.dropdown', function () {
				if($('.daterangepicker').is(':visible'))
			    return false;
			});

			$('li[filter-name={{ $filter->name }}]').on('filter:clear', function(e) {
				//if triggered by remove filters click just remove active class,no need to send ajax
				$('li[filter-key={{ $filter->key }}]').removeClass('active');
			});

			// datepicker clear button
			$(".daterangepicker-{{ $filter->key }}-clear-button").click(function(e) {
				e.preventDefault();
				applyDateRangeFilter{{$filter->key}}(null, null);
			})
		});
  </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
