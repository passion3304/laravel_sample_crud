{{-- Show the inputs --}}
@foreach ($fields as $field)
    <!-- load the view from the view attribute if set -->
    @if(isset($field['view_namespace']))
        @include($field['view_namespace'].'.'.$field['type'], ['field' => $field])
    @else
        <!-- load the view from the application if it exists, otherwise load the one in the package -->
        @if(view()->exists('vendor.backpack.crud.fields.'.$field['type']))
            @include('vendor.backpack.crud.fields.'.$field['type'], ['field' => $field])
        @else
            @include('crud::fields.'.$field['type'], ['field' => $field])
        @endif
    @endif
@endforeach
