@php

    if($column['anchor'] !== false) {
        $related_model_key = $related_model_key ?? null;
        $column['anchor']['href'] = isset($column['anchor']['href']) ? (is_callable($column['anchor']['href']) ? $column['anchor']['href']($crud, $column, $entry, $related_model_key) : ($column['anchor']['href'] ?? '')) : '';
        $column['anchor']['class'] = isset($column['anchor']['class']) ? (is_callable($column['anchor']['class']) ? $column['anchor']['class']($crud, $column, $entry, $related_model_key) : ($column['anchor']['class'] ?? '')) : '';
        $column['anchor']['target'] = $column['anchor']['target'] ?? '';
    }
@endphp

@if($column['anchor'] !== false)
<a href="{{ $column['anchor']['href'] }}" target="{{$column['anchor']['target']}}" class="{{$column['anchor']['class']}}">
    @if(isset($escaped) && $escaped === false)
    {!! $text !!}
    @else
    {{ $text }}
    @endif
</a>
@else
@if(isset($escaped) && $escaped === false)
    {!! $text !!}
    @else
    {{ $text }}
    @endif
@endif
