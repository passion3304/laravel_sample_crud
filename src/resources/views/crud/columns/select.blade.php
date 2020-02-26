{{-- single relationships (1-1, 1-n) --}}
@php
    $attributes = $crud->getModelAttributeFromRelation($entry, $column['entity'], $column['attribute']);
@endphp

<span>
        @if(count($attributes))

        @php($lastKey = array_key_last($attributes))
        @foreach($attributes as $key => $attribute)

            @php($text = str_limit($attribute, array_key_exists('limit', $column) ? $column['limit'] : 40, '[...]'))
            @include('crud::columns.inc.column_wrapper',['text' => $text, 'related_model_key' => $key])@if($lastKey != $key),@endif
        @endforeach
        @else
            -
        @endif
</span>
