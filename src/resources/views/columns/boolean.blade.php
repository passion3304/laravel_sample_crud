{{-- converts 1/true or 0/false to yes/no/lang --}}
<td data-order="{{ $entry->{$column['name']} }}">
	@if ($entry->{$column['name']} === true || $entry->{$column['name']} === 1 || $entry->{$column['name']} === '1')
    {{ trans('backpack::crud.yes') }}
    @else
    {{ trans('backpack::crud.no') }}
    @endif
</td>
