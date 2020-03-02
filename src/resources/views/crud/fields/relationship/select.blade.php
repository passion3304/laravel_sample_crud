<!--  relationship  -->

@php
   @endphp

   @if($field['multiple'])
        @include('crud::fields.relationship.select2_multiple')
        @else
        @include('crud::fields.relationship.select2')
   @endif


        @if ($crud->fieldTypeNotLoaded($field))
        @php
            $crud->markFieldTypeAsLoaded($field);
        @endphp

        {{-- FIELD CSS - will be loaded in the after_styles section --}}
        @push('crud_fields_styles')


        @endpush

        {{-- FIELD JS - will be loaded in the after_scripts section --}}
        @push('crud_fields_scripts')



        @endpush

    @endif
    {{-- End of Extra CSS and JS --}}
    {{-- ########################################## --}}
