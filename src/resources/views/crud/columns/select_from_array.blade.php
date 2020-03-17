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

    $column['escaped'] = $column['escaped'] ?? true;

     // if the wrapper "element" is not defined, set it to the default - an anchor
    if(!empty($column['wrapper'])) {
        $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    }
@endphp

<span>
    @if(!empty($list))
        @foreach($list as $key => $text)
        @php
            $related_key = $key;
        @endphp
        @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
            @if($column['escaped'])
                {{ $text }}
            @else
                {!! $text !!}
            @endif
        @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
        @if($lastKey != $key),@endif
        @endforeach
    @endif
</span>
