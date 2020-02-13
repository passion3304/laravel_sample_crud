{{-- localized date using nesbot carbon --}}
@php
    $value = data_get($entry, $column['name']);
@endphp

<span data-order="{{ $value }}">
    @if (!empty($value))
    @php($text = \Carbon\Carbon::parse($value)
                    ->locale(App::getLocale())
                    ->isoFormat($column['format'] ?? config('backpack.base.default_date_format')))
        @if(isset($column['anchor']['href']))
            @include('crud::inc.column_anchors',['text' => $text])
        @else
            {{ $text }}
        @endif
    @else
        -
    @endif
</span>
