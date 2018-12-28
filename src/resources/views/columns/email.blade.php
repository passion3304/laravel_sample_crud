{{-- email link --}}
@php
    $value = data_get($entry, $column['name']);
@endphp

<span><a href="mailto:{{ $value }}">{{ $value }}</a></span>