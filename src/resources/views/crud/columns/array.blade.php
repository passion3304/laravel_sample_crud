{{-- enumerate the values in an array  --}}
@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    $column['escaped'] = $column['escaped'] ?? false;
    $column['prefix'] = $column['prefix'] ?? '';
    $column['suffix'] = $column['suffix'] ?? '';

    if(is_callable($column['value'])) {
        $column['value'] = $column['value']($entry);
    }

    // the value should be an array whether or not attribute casting is used
    if (!is_array($column['value'])) {
        $column['value'] = json_decode($column['value'], true);
    }
@endphp

<span>
    @if($column['value'] && count($column['value']))
        {{ $column['prefix'] }}
        @foreach($column['value'] as $key => $text)
            @php
                $column['text'] = $text;
                $related_key = $key;
            @endphp

            <span class="d-inline-flex">
                @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
                    @if($column['escaped'])
                        {{ $column['text'] }}
                    @else
                        {!! $column['text'] !!}
                    @endif
                @includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')

                @if(!$loop->last), @endif
            </span>
        @endforeach
        {{ $column['suffix'] }}
    @else
        {{ $column['default'] ?? '-' }}
    @endif
</span>
