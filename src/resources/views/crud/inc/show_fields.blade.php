{{-- Show the inputs --}}
@foreach ($fields as $field)
    <!-- load the view from type and view_namespace attribute if set -->
    @php
        $fieldsViewNamespace = $field['view_namespace'] ?? 'crud::fields';
    @endphp

    @include($fieldsViewNamespace.'.'.$field['type'], ['field' => $field])
@endforeach

@push('crud_scripts_pre')
@stack('crud_fields_scripts')
@endpush

@push('crud_styles_pre')
@stack('crud_fields_styles')
@endpush
