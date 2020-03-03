<!--  relationship  -->

@php
    use Illuminate\Support\Str;

    if(isset($field['inline_create']) && !is_array($field['inline_create'])) {
        $field['inline_create'] = [true];
    }
    //dd($crud);

    $field['multiple'] = $field['multiple'] ?? false;
    $field['ajax'] = $field['ajax'] ?? false;
    $field['placeholder'] = $field['placeholder'] ?? ($field['multiple'] ? 'Select entries' : 'Select entry');
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
    // Note: isColumnNullable returns true if column is nullable in database, also true if column does not exist.

    //if field is not ajax but user wants to use InlineCreate
    //we make minimum_input_length = 0 so when user open we show the entries like a regular select
    $field['minimum_input_length'] = ($field['ajax'] !== true) ? 0 : ($field['minimum_input_length'] ?? 2);

   @endphp

   @if(isset($field['inline_create']))
        @include('crud::fields.relationship.fetch_or_create')
    @else
        @if($field['ajax'])
            @include('crud::fields.relationship.fetch')
        @else
            @include('crud::fields.relationship.select')
        @endif
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
        @stack('crud_fields_scripts')

        @endpush

    @endif
    {{-- End of Extra CSS and JS --}}
    {{-- ########################################## --}}
