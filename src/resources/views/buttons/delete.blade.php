@if ($crud->hasAccess('delete'))
	<a href="{{ Request::url().'/'.$entry->getKey() }}" class="btn btn-xs btn-default" data-button-type="delete"><i class="fa fa-trash"></i> {{ trans('backpack::crud.delete') }}</a>
@endif