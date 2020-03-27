{{-- enumerate the values in an array  --}}
@php
    $array = data_get($entry, $column['name']);
    $list[$column['visible_key']] = [];

    // if the isn't using attribute casting, decode it
    if (is_string($array)) {
        $array = json_decode($array);
    }

    if (is_array($array) && count($array)) {
        $list = [];
        foreach ($array as $item) {
            if (isset($item->{$column['visible_key']})) {
                $list[$column['visible_key']][] = $item->{$column['visible_key']};
            } elseif (is_array($item) && isset($item[$column['visible_key']])) {
                $list[$column['visible_key']][] = $item[$column['visible_key']];
            }
        }
        $lastKey = array_key_last($list[$column['visible_key']]);
    }

    $column['escaped'] = $column['escaped'] ?? true;
@endphp

<span>
    @if(!empty($list))
        @foreach($list[$column['visible_key']] as $key => $text)
            @php
                $column['text'] = $text;
                $related_key = $key;
            @endphp
            @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
                @if($column['escaped'])
                    {{ $column['text'] }}
                @else
                    {!! $column['text'] !!}
                @endif
            @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
        @if($lastKey != $key),@endif
        @endforeach
    @else
        -
    @endif
</span>
