{{-- converts 1/true or 0/false to yes/no/lang --}}
@php
    $value = data_get($entry, $column['name']);

    if($value === true || $value === 1 || $value === '1') {
        if ( isset( $column['options'][1] ) ) {
            $column['text'] = $column['options'][1];
            $column['escaped'] = false;
        }else{
            $column['text'] = Lang::has('backpack::crud.yes')?trans('backpack::crud.yes'):'Yes';
        }
    }else {
        if ( isset( $column['options'][0] ) ) {
            $column['text'] = $column['options'][0];
            $column['escaped'] = false;
        }else{
            $column['text'] = Lang::has('backpack::crud.no')?trans('backpack::crud.no'):'No';
        }
    }

@endphp

<span data-order="{{ $value }}">
	@include('crud::columns.inc.column_wrapper')
</span>
