@if ($crud->hasAccess('revisions'))
    <a href="{{ url($crud->route.'/'.$entry->getKey().'/revisions') }}" class="btn btn-xs btn-default"><i class="fa fa-clone"></i> {{ trans('backpack::crud.revisions') }}</a>
@endif
