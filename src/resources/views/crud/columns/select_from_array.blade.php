{{-- select_from_array column --}}
@php
    $values = data_get($entry, $column['name']);
    $list = [];
    if ($values !== null) {
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    if (! is_null($value)) {
                        $list[$key] = $column['options'][$value] ?? $value;
                    }
                }
            } else {
                $value = $column['options'][$values] ?? $values;
                $list[$values] = $value;
            }
            $lastKey = array_key_last($list);
        }
@endphp

<span>
    @if(!empty($list))
        @foreach($list as $key => $text)
            @include('crud::columns.inc.column_wrapper',['text' => $text, 'related_key' => $key])@if($lastKey != $key),@endif
        @endforeach
    @endif
</span>
