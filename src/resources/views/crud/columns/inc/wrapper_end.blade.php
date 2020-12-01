@php
    // define the wrapper element
    $wrapperElement = $column['wrapper']['element'] ?? 'a';
    $wrapperElement = !is_string($wrapperElement) && is_callable($wrapperElement)
        ? $wrapperElement($crud, $column, $entry, $related_key)
        : $wrapperElement ?? 'a';
@endphp

</{{$wrapperElement}}>
