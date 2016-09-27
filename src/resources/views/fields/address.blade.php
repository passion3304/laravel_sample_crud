<!-- text input -->
<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif
        @if(isset($field['address_full']) && $field['address_full'])
        <input
            type="text"
            data-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;, &quot;full&quot;: {{isset($field['address_full']) && $field['address_full'] ? 'true' : 'false'}} }"
            @include('crud::inc.field_attributes')
        >
        <input type="hidden" value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}" name="{{ $field['name'] }}">
        @else
        <input
            type="text"
            data-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;, &quot;full&quot;: {{isset($field['address_full']) && $field['address_full'] ? 'true' : 'false'}} }"
            name="{{ $field['name'] }}"
            value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
            @include('crud::inc.field_attributes')
        >
        @endif
        @if(isset($field['suffix'])) <div class="input-group-addon">{!! $field['suffix'] !!}</div> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>

{{-- Note: you can use  to only load some CSS/JS once, even though there are multiple instances of it --}}

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->checkIfFieldIsFirstOfItsType($field, $fields))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <style>
            .ap-input-icon.ap-icon-pin {
                right: 5px !important; }
            .ap-input-icon.ap-icon-clear {
                right: 10px !important; }
        </style>
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <script src="https://cdn.jsdelivr.net/places.js/1/places.min.js"></script>
    <script>
        jQuery(document).ready(function($){
            window.AlgoliaPlaces = window.AlgoliaPlaces || {};

            $('[data-address]').each(function(){

                var $this      = $(this),
                $addressConfig = $this.data('address'),
                $field = $('[name="'+$addressConfig.field+'"]'),
                $place = places({
                    container: $this[0]
                });

                if( $addressConfig.full ){

                    $place.on('change', function(e){
                        var result = JSON.parse(JSON.stringify(e.suggestion));
                        delete(result.highlight); delete(result.hit); delete(result.hitIndex);
                        delete(result.rawAnswer); delete(result.query);
                        $field.val( JSON.stringify(result) );
                    });

                    var existingData = JSON.parse($field.val());
                    $this.val(existingData.value);
                }

                window.AlgoliaPlaces[ $addressConfig.field ] = $place;
            });
        });
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
