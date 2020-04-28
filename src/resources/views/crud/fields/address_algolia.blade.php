<!-- text input -->

<?php

// the field should work whether or not Laravel attribute casting is used
if (isset($field['value']) && (is_array($field['value']) || is_object($field['value']))) {
    $field['value'] = json_encode($field['value']);
}

$field['wrapper']['algolia-wrapper'] = $field['wrapper']['algolia-wrapper'] ?? 'true';

?>

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <input type="hidden" 
        value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}" 
        name="{{ $field['name'] }}" 
        data-algolia-hidden-input="{{ $field['name'] }}">

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <div class="input-group-addon">{!! $field['prefix'] !!}</div> @endif
        @if(isset($field['store_as_json']) && $field['store_as_json'])
        <input
            type="text"
            data-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;, &quot;full&quot;: {{isset($field['store_as_json']) && $field['store_as_json'] ? 'true' : 'false'}} }"
            data-init-function="bpFieldInitAddressAlgoliaElement"
            @include('crud::fields.inc.attributes')
        >
        @else
        <input
            type="text"
            data-address="{&quot;field&quot;: &quot;{{$field['name']}}&quot;, &quot;full&quot;: {{isset($field['store_as_json']) && $field['store_as_json'] ? 'true' : 'false'}} }"
            data-init-function="bpFieldInitAddressAlgoliaElement"
            name="{{ $field['name'] }}"
            value="{{ old($field['name']) ? old($field['name']) : (isset($field['value']) ? $field['value'] : (isset($field['default']) ? $field['default'] : '' )) }}"
            @include('crud::fields.inc.attributes')
        >
        @endif
        @if(isset($field['suffix'])) <div class="input-group-addon">{!! $field['suffix'] !!}</div> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- Note: you can use  to only load some CSS/JS once, even though there are multiple instances of it --}}

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

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
    <script src="{{ asset('packages/places.js/dist/cdn/places.min.js') }}"></script>
    <script>
            window.AlgoliaPlaces = window.AlgoliaPlaces || {};

            function bpFieldInitAddressAlgoliaElement(element) {
                $addressConfig = element.data('address');
                // console.log(element);
                // console.log(element.prev("[data-algolia-hidden-input]"));
                // console.log(element.siblings("[data-algolia-hidden-input]"));
                console.log($('[name="'+$addressConfig.field+'"]'));
                console.log(element.parent("[algolia-wrapper]").find('[data-algolia-hidden-input="'+$addressConfig.field+'"]'));
                // console.log(element.parent("[algolia-wrapper]").children("[data-algolia-hidden-input]").first());
                $hiddenInput = element.parent("[algolia-wrapper]").find('[data-algolia-hidden-input="'+$addressConfig.field+'"]');
                $place = places({
                    container: element[0]
                });

                function clearInput() {
                    if( !element.val().length ){
                        $hiddenInput.val('');
                    }
                }

                if( $addressConfig.full ){

                    $place.on('change', function(e){
                        var result = JSON.parse(JSON.stringify(e.suggestion));
                        delete(result.highlight); delete(result.hit); delete(result.hitIndex);
                        delete(result.rawAnswer); delete(result.query);
                        $hiddenInput.val( JSON.stringify(result) );
                    });

                    element.on('change blur', clearInput);
                    $place.on('clear', clearInput);

                    if( $hiddenInput.val().length ){
                        var existingData = JSON.parse($hiddenInput.val());
                        element.val(existingData.value);
                    }
                }

                window.AlgoliaPlaces[ $addressConfig.field ] = $place;
            }
    </script>
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
