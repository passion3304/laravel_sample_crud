@php
    // define the wrapper element
    $wrapperElement = isset($column['wrapper']['element']) ? $column['wrapper']['element'] : 'a';
    $wrapperElement = !is_string($wrapperElement) && is_callable($wrapperElement)
        ? $wrapperElement($crud, $column, $entry, $related_key)
        : $wrapperElement ?? 'a';
@endphp

</{{$wrapperElement}}>
