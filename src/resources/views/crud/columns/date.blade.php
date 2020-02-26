{{-- localized date using nesbot carbon --}}
@php
    $value = data_get($entry, $column['name']);
    if (!empty($value)) {
        $column['text'] = \Carbon\Carbon::parse($value)
                    ->locale(App::getLocale())
                    ->isoFormat($column['format'] ?? config('backpack.base.default_date_format'));
    }

@endphp

<span data-order="{{ ($value ?? '') }}">

            @include('crud::columns.inc.column_wrapper')

</span>
