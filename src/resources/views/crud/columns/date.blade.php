{{-- localized date using nesbot carbon --}}
@php
    $value = data_get($entry, $column['name']);
@endphp

<span data-order="{{ $value }}">
    @if (!empty($value))
    @php($text = \Carbon\Carbon::parse($value)
                    ->locale(App::getLocale())
                    ->isoFormat($column['format'] ?? config('backpack.base.default_date_format')))

            @include('crud::columns.inc.column_wrapper',['text' => $text])
    @else
        -
    @endif
</span>
