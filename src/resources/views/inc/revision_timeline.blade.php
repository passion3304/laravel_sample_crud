<ul class="timeline">
@foreach($revisions as $revisionDate => $dateRevisions)
  <li class="time-label" data-date="{{ date('Y-m-d', strtotime($revisionDate)) }}">
      <span class="bg-red">
        {{-- @TODO: Display date in user's time format (.e.g dd/mm/yyyy vs mm/dd/yyyy) --}}
        {{ date('Y-m-d', strtotime($revisionDate)) }}
      </span>
  </li>
  
  @foreach($dateRevisions as $history)
  <li class="timeline-item-wrap">
    <i class="fa fa-calendar bg-blue"></i>
    <div class="timeline-item">
      <span class="time"><i class="fa fa-clock-o"></i> {{ date('h:ia', strtotime($history->created_at)) }}</span>
      @if($history->key == 'created_at' && !$history->old_value)
        <h3 class="timeline-header">{{ $history->userResponsible()->name }} {{ trans('backpack::crud.created_this') }} {{ $crud->entity_name }}</h3>
      @else
        <h3 class="timeline-header">{{ $history->userResponsible()->name }} {{ trans('backpack::crud.changed_the') }} {{ $history->fieldName() }}</h3>
        <div class="timeline-body">
          <b>{{ trans('backpack::crud.from') }}: {{ $history->oldValue() }}</b>
          <br><br>
          {{ trans('backpack::crud.to') }}: {{ $history->newValue() }}
        </div>
      @endif
      <div class="timeline-footer">
        {{-- @TODO: Implement form to submit revision restoration --}}
        {!! Form::open(array('url' => $crud->route.'/'.$entry->getKey().'/revisions/'.$history->id.'/restore', 'method' => 'post')) !!}
        <button type="submit" class="btn btn-primary btn-xs restore-btn" data-entry-id="{{ $entry->id }}" data-revision-id="{{ $history->id }}" onclick="onRestoreClick(event)">
          <i class="fa fa-history"></i> {{ trans('backpack::crud.restore_this_value') }}</button>
        {!! Form::close() !!}
      </div>
    </div>
  </li>
  @endforeach
@endforeach
</ul>

@section('after_scripts')
  <script type="text/javascript">
    $.ajaxPrefilter(function(options, originalOptions, xhr) {
        var token = $('meta[name="csrf_token"]').attr('content');

        if (token) {
              return xhr.setRequestHeader('X-XSRF-TOKEN', token);
        }
    });
    function onRestoreClick(e) {
      e.preventDefault();
      var entryId = $(e.target).attr('data-entry-id');
      var revisionId = $(e.target).attr('data-revision-id');
      $.ajax('/{{ $crud->route.'/' }}' + entryId + '/revisions/' +  revisionId + '/restore', {
        method: 'POST',
        data: {
          revision_id: revisionId
        },
        success: function(revisionTimeline) {
          // Replace the revision list with the updated revision list
          $('.timeline').replaceWith(revisionTimeline);

          // Animate the new revision in (by sliding)
          $('.timeline-item-wrap').first().addClass('fadein');
          new PNotify({
              title: '{{ trans('backpack::crud.revision_restored') }}'
          });
        }
        // @TODO: Implement error handling (i.e. - missing 'revision_id' param and error message)
      });
  }
  </script>
@endsection

@section('after_styles')
  {{-- Animations for new revisions after ajax calls --}}
  <style>
     .timeline-item-wrap.fadein {
      -webkit-animation: restore-fade-in 3s;
              animation: restore-fade-in 3s;
    }
    @-webkit-keyframes restore-fade-in {
      from {opacity: 0}
      to {opacity: 1}
    }
      @keyframes restore-fade-in {
        from {opacity: 0}
        to {opacity: 1}
    }
  </style>
@endsection
