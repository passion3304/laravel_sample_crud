@extends('backpack::layout')

@section('header')
  <section class="content-header">
    <h1>
      <span class="text-lowercase">{{ $crud->entity_name }}</span> {{ trans('backpack::crud.revisions') }}
    </h1>
    <ol class="breadcrumb">
      <li><a href="{{ url(config('backpack.base.route_prefix'),'dashboard') }}">{{ trans('backpack::crud.admin') }}</a></li>
      <li><a href="{{ url($crud->route) }}" class="text-capitalize">{{ $crud->entity_name_plural }}</a></li>
      <li class="active">{{ trans('backpack::crud.revisions') }}</li>
    </ol>
  </section>
@endsection

@section('content')
<div class="row">
  <div class="col-md-8 col-md-offset-2">
    <!-- Default box -->
    @if ($crud->hasAccess('list'))
      <a href="{{ url($crud->route) }}"><i class="fa fa-angle-double-left"></i> {{ trans('backpack::crud.back_to_all') }} <span class="text-lowercase">{{ $crud->entity_name_plural }}</span></a><br><br>
    @endif

    @if(!count($revisions))
      <div class="box">
        <div class="box-header with-border">
          <h3 class="box-title">{{ trans('backpack::crud.no_revisions') }}</h3>
        </div>
      </div>
    @else
      <ul class="timeline">
      @foreach($revisions as $revisionDate => $dateRevisions)
        <li class="time-label">
            <span class="bg-red">
              {{-- @TODO: Display date in user's time format (.e.g dd/mm/yyyy vs mm/dd/yyyy) --}}
              {{ date('Y-m-d', strtotime($revisionDate)) }}
            </span>
        </li>
        
        @foreach($dateRevisions as $history)
        <li>
          <i class="fa fa-calendar bg-blue"></i>
          <div class="timeline-item">
            <span class="time"><i class="fa fa-clock-o"></i> {{ date('h:ia', strtotime($history->created_at)) }}</span>
            @if($history->key == 'created_at' && !$history->old_value)
              <h3 class="timeline-header">{{ $history->userResponsible()->name }} {{ trans('backpack::crud.created_this') }} {{ $crud->entity_name }}</h3>
            @else
              <h3 class="timeline-header">{{ $history->userResponsible()->name }} {{ trans('backpack::crud.changed_the') }} {{ $history->fieldName() }}</h3>
              <div class="timeline-body">
                {{ trans('backpack::crud.from') }}: {{ $history->oldValue() }}
                <br><br>
                {{ trans('backpack::crud.to') }}: {{ $history->newValue() }}
              </div>
            @endif
            <div class="timeline-footer">
              {{-- @TODO: Implement form to submit revision restoration --}}
              <button type="submit" class="btn btn-primary btn-xs"><i class="fa fa-history"></i> {{ trans('backpack::crud.restore_this_value') }}</button>
            </div>
          </div>
        </li>
        @endforeach
      @endforeach
      </ul>
    @endif
  </div>
</div>
@endsection
