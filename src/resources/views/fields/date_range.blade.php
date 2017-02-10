<!-- bootstrap daterange picker input -->

<?php
    // if the column has been cast to Carbon or Date (using attribute casting)
    // get the value as a date string
    if (isset($field['value']) && ( $field['value'] instanceof \Carbon\Carbon || $field['value'] instanceof \Jenssegers\Date\Date )) {
        $field['value'] = $field['value']->format( 'Y-m-d H:i:s' );
    }

    //Do the same as the above but for the range end field
    if ( isset($entry) && ($entry->{$field['name_end']} instanceof \Carbon\Carbon || $entry->{$field['name_end']} instanceof \Jenssegers\Date\Date) ) {
        $name_end = $entry->{$field['name_end']}->format( 'Y-m-d H:i:s' );
    } else {
        $name_end = null;
    }
?>

<div @include('crud::inc.field_wrapper_attributes') >
    <input class="datepicker-range-start" type="hidden" name="{{ $field['name'] }}" value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}">
    <input class="datepicker-range-end" type="hidden" name="{{ $field['name_end'] }}" value="{{ old($field['name_end']) ? old($field['name_end']) : (!empty($name_end) ? $name_end : (isset($field['default_end']) ? $field['default_end'] : '' )) }}">
    <label>{!! $field['label'] !!}</label>
    <div class="input-group date">
        <input
            data-bs-daterangepicker="{{ isset($field['date_range_options']) ? json_encode($field['date_range_options']) : '{}'}}"
            type="text"
            @include('crud::inc.field_attributes')
            >
        <div class="input-group-addon">
            <span class="glyphicon glyphicon-calendar"></span>
        </div>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    <link rel="stylesheet" href="{{ asset('/vendor/adminlte/plugins/daterangepicker/daterangepicker.css') }}">
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script src="{{ asset('/vendor/adminlte/plugins/daterangepicker/moment.min.js') }}"></script>
    <script src="{{ asset('/vendor/adminlte/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script>
        jQuery(document).ready(function($){
            $('[data-bs-daterangepicker]').each(function(){

                var $fake = $(this),
                $start = $fake.parents('.form-group').find('.datepicker-range-start'),
                $end = $fake.parents('.form-group').find('.datepicker-range-end'),
                $customConfig = $.extend({
                    format: 'dd/mm/yyyy',
                    autoApply: true,
                    startDate: moment($start.val()),
                    endDate: moment($end.val())
                }, $fake.data('bs-daterangepicker'));

                $fake.daterangepicker($customConfig);
                $picker = $fake.data('daterangepicker');

                $fake.on('keydown', function(e){
                    e.preventDefault();
                    return false;
                });

                $fake.on('apply.daterangepicker hide.daterangepicker', function(e, picker){
                    $start.val( picker.startDate.format('YYYY-MM-DD HH:mm:ss') );
                    $end.val( picker.endDate.format('YYYY-MM-DD H:mm:ss') );
                });

            });
        });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
