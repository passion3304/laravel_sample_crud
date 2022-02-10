{{-- Show the inputs --}}
@foreach ($fields as $field)
    @php
        // if the namespace is given, use that, no questions asked, otherwise
        // load it from the first view_namespace that holds that field
        if (isset($field['view_namespace'])) {
            $fieldPaths = [$field['view_namespace'].'.'.$field['type']];
        } else {
            $fieldPaths = array_map(function($item) use ($field) {
                return $item.'.'.$field['type'];
            }, config('backpack.crud.view_namespaces.fields'));
        }
    @endphp

    @includeFirst($fieldPaths, ['field' => $field])
@endforeach

