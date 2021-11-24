<!-- configurable color picker -->
{{-- https://farbelous.io/bootstrap-colorpicker/ --}}
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <div class="input-group colorpicker-component">
        <input
        	type="text"
        	name="{{ $field['name'] }}"
            value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
            data-init-function="bpFieldInitColorPickerElement"
            @include('crud::fields.inc.attributes')
        	>
        <span class="input-group-append">
            <span class="input-group-text colorpicker-input-addon"><i></i></span>
        </span>
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        @loadOnce('packages/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css')
		@loadOnce('bpFieldInitColorPickerElement-custom-style')
		<style>
			.input-group>.input-group-append>.input-group-text {
				border: 1px solid rgba(0,40,100,.12);
			}
			.input-group>.input-group-append>.input-group-text:focus {
				outline: 0;
				border-color: #9080f1;
				box-shadow: 0 0 0 2px #e1dcfb;
			}
		</style>
        @endLoadOnce
    @endpush

{{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
    @loadOnce('packages/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js')
    @loadOnce('bpFieldInitColorPickerElement')
    <script>
        function bpFieldInitColorPickerElement(element) {
            // https://itsjavi.com/bootstrap-colorpicker/
            var config = jQuery.extend({}, {!! isset($field['color_picker_options']) ? json_encode($field['color_picker_options']) : '{}' !!});
            var picker = element.parents('.colorpicker-component').colorpicker(config);

            element.on('focus', function(){
                picker.colorpicker('show');
            });
        }
    </script>
    @endLoadOnce
@endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
