{{-- single relationships (1-1, 1-n) --}}
@php
    $column['escaped'] = $column['escaped'] ?? true;
    $column['limit'] = $column['limit'] ?? 40;
    $column['attribute'] = $column['attribute'] ?? (new $column['model'])->identifiableAttribute();

    $attributes = $crud->getRelatedEntriesAttributes($entry, $column['entity'], $column['attribute']);
    foreach ($attributes as $key => $text) {
        $text = Str::limit($text, $column['limit'], '[...]');
    }
@endphp

<span>
    @if(count($attributes))
        @php
            $lastKey = array_key_last($attributes)
        @endphp

        @foreach($attributes as $key => $text)
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
                @if($lastKey != $key), @endif
        @endforeach
    @else
        -
    @endif
</span>
