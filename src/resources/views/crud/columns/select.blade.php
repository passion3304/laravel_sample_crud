{{-- single relationships (1-1, 1-n) --}}
@php
    $attributes = $crud->getRelatedEntriesAttributes($entry, $column['entity'], $column['attribute']);
    $list = [];
    if(count($attributes)) {
        $lastKey = array_key_last($attributes);
        foreach($attributes as $key => $attr) {
            $list[$key] = $attr;
        }
    }
@endphp

<span>
        @if(!empty($list))

        @foreach($list as $key => $attribute)
            @php($text = str_limit($attribute, array_key_exists('limit', $column) ? $column['limit'] : 40, '[...]'))
            @include('crud::columns.inc.column_wrapper',['text' => $text, 'related_key' => $key])@if($lastKey != $key),@endif
        @endforeach
        @else
            -
        @endif
</span>
