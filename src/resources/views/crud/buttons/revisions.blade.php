@if ($crud->hasAccess('revisions') && count($entry->revisionHistory))
    <a href="{{ url($crud->route.'/'.$entry->getKey().'/revisions') }}" class="btn btn-sm btn-link"><i class="la la-history"></i> {{ trans('backpack::crud.revisions') }}</a>
@endif
