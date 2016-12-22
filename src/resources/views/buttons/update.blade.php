@if ($crud->hasAccess('update'))
	@if (!$crud->model->translationEnabled())
	<a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}" class="btn btn-xs btn-default"><i class="fa fa-edit"></i> {{ trans('backpack::crud.edit') }}</a>
	@else
	<!-- Split button -->
	<div class="btn-group">
	  <a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}" class="btn btn-xs btn-default"><i class="fa fa-edit"></i> {{ trans('backpack::crud.edit') }}</a>
	  <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    <span class="caret"></span>
	    <span class="sr-only">Toggle Dropdown</span>
	  </button>
	  <ul class="dropdown-menu">
	  	@foreach (config('backpack.crud.locales') as $key => $locale)
		  	<li><a href="{{ url($crud->route.'/'.$entry->getKey().'/edit') }}?locale={{ $key }}">{{ trans('backpack::crud.edit').' '.$locale }}</a></li>
	  	@endforeach
	  </ul>
	</div>
	@endif
@endif