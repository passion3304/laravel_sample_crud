{{-- single relationships (1-1, 1-n) --}}
@php
    $attributes = $crud->getRelatedEntriesAttributes($entry, $column['entity'], $column['attribute']);

    if(count($attributes)) {
        $lastKey = array_key_last($attributes);
    }
    $column['escaped'] = $column['escaped'] ?? true;
@endphp

<span>
        @if(count($attributes))

        @foreach($attributes as $key => $attribute)
            @php

            $related_key = $key;
            $text = str_limit($attribute, array_key_exists('limit', $column) ? $column['limit'] : 40, '[...]');
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
        @else
            -
        @endif
</span>
