<!-- configurable color picker -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <div class="input-group colorpicker-component">

        <input
        	type="text"
        	name="{{ $field['name'] }}"
            value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
            data-javascript-function-for-field-initialisation="bpFieldInitColorPickerElement"
            @include('crud::inc.field_attributes')
        	>
        <div class="input-group-addon">
            <i class="color-preview-{{ $field['name'] }}"></i>
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
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.3.5/css/bootstrap-colorpicker.min.css" />
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.3.5/js/bootstrap-colorpicker.min.js"></script>
    <script>
        function bpFieldInitColorPickerElement(element) {
            // https://itsjaviaguilar.com/bootstrap-colorpicker/
            var config = jQuery.extend({}, {!! isset($field['color_picker_options']) ? json_encode($field['color_picker_options']) : '{}' !!});
            var picker = $('[name="{{ $field['name'] }}"]').parents('.colorpicker-component').colorpicker(config);

            element.on('focus', function(){
                picker.colorpicker('show');
            });
        }
    </script>
    @endpush

@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
