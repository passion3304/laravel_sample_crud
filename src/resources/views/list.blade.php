@extends('admin.layout')

@section('head')
	<!-- DATA TABLES -->
    <link href="{{ asset('AdminLTE/plugins/datatables/dataTables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content-header')
	<section class="content-header">
	  <h1>
	    <span class="text-capitalize">{{ $crud['entity_name_plural'] }}</span>
	    <small>{{ trans('crud.all') }} <span class="text-lowercase">{{ $crud['entity_name_plural'] }}</span> {{ trans('crud.in_the_database') }}.</small>
	  </h1>
	  <ol class="breadcrumb">
	    <li><a href="{{ url('admin/dashboard') }}">Admin</a></li>
	    <li><a href="{{ url($crud['route']) }}" class="text-capitalize">{{ $crud['entity_name_plural'] }}</a></li>
	    <li class="active">{{ trans('crud.list') }}</li>
	  </ol>
	</section>
@endsection

@section('content')
<!-- Default box -->
  <div class="box">
    @if (!(isset($crud['add_permission']) && !$crud['add_permission']))
      <div class="box-header with-border">
    		<a href="{{ url($crud['route'].'/create') }}" class="btn btn-primary ladda-button" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-plus"></i> {{ trans('crud.add') }} {{ $crud['entity_name'] }}</span></a>
      </div>
    @endif
    <div class="box-body">

		<table id="crudTable" class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        {{-- Table columns --}}
                        @foreach ($crud['columns'] as $column)
                          <th>{{ $column['label'] }}</th>
                        @endforeach

                        @if ( !( isset($crud['edit_permission']) && $crud['edit_permission'] === false && isset($crud['delete_permission']) && $crud['delete_permission'] === false ) )
                          <th>{{ trans('crud.actions') }}</th>
                        @endif
                      </tr>
                    </thead>
                    <tbody>

                      @foreach ($entries as $k => $entry)
                      <tr>
                        @foreach ($crud['columns'] as $column)
                          @if (isset($column['type']) && $column['type']=='select_multiple')
                            {{-- relationships with pivot table (n-n) --}}
                            <td><?php
                            $results = $entry->{$column['entity']}()->getResults();
                            if ($results && $results->count()) {
                                $results_array = $results->lists($column['attribute'], 'id');
                                echo implode(', ', $results_array->toArray());
                              }
                              else
                              {
                                echo '-';
                              }
                             ?></td>
                          @elseif (isset($column['type']) && $column['type']=='select')
                            {{-- single relationships (1-1, 1-n) --}}
                            <td><?php
                            if ($entry->{$column['entity']}()->getResults()) {
                                echo $entry->{$column['entity']}()->getResults()->{$column['attribute']};
                              }
                             ?></td>
                          @else
                            {{-- regular object attribute --}}
                            <td>{{ str_limit(strip_tags($entry->$column['name']), 80, "[...]") }}</td>
                          @endif

                        @endforeach

                        @if ( !( isset($crud['edit_permission']) && $crud['edit_permission'] === false && isset($crud['delete_permission']) && $crud['delete_permission'] === false ) )
                        <td>
                          {{-- <a href="{{ Request::url().'/'.$entry->id }}" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> {{ trans('crud.preview') }}</a> --}}
                          @if (!(isset($crud['edit_permission']) && !$crud['edit_permission']))
                            <a href="{{ Request::url().'/'.$entry->id }}/edit" class="btn btn-xs btn-default"><i class="fa fa-edit"></i> {{ trans('crud.edit') }}</a>
                          @endif
                           @if (!(isset($crud['delete_permission']) && !$crud['delete_permission']))
                          <a href="{{ Request::url().'/'.$entry->id }}" class="btn btn-xs btn-default" data-button-type="delete"><i class="fa fa-trash"></i> {{ trans('crud.delete') }}</a>
                          @endif
                        </td>
                        @endif
                      </tr>
                      @endforeach

                    </tbody>
                    <tfoot>
                      <tr>
                        {{-- Table columns --}}
                        @foreach ($crud['columns'] as $column)
                          <th>{{ $column['label'] }}</th>
                        @endforeach

                        @if ( !( isset($crud['edit_permission']) && $crud['edit_permission'] === false && isset($crud['delete_permission']) && $crud['delete_permission'] === false ) )
                          <th>{{ trans('crud.actions') }}</th>
                        @endif
                      </tr>
                    </tfoot>
                  </table>

    </div><!-- /.box-body -->
  </div><!-- /.box -->
@endsection

@section('scripts')
	<!-- DATA TABES SCRIPT -->
    <script src="{{ url('AdminLTE/plugins/datatables/jquery.dataTables.js') }}" type="text/javascript"></script>
    <script src="{{ url('AdminLTE/plugins/datatables/dataTables.bootstrap.js') }}" type="text/javascript"></script>

	<script type="text/javascript">
	  jQuery(document).ready(function($) {
	  	$("#crudTable").dataTable({
        "language": {
              "emptyTable":     "{{ trans('crud.emptyTable') }}",
              "info":           "{{ trans('crud.info') }}",
              "infoEmpty":      "{{ trans('crud.infoEmpty') }}",
              "infoFiltered":   "{{ trans('crud.infoFiltered') }}",
              "infoPostFix":    "{{ trans('crud.infoPostFix') }}",
              "thousands":      "{{ trans('crud.thousands') }}",
              "lengthMenu":     "{{ trans('crud.lengthMenu') }}",
              "loadingRecords": "{{ trans('crud.loadingRecords') }}",
              "processing":     "{{ trans('crud.processing') }}",
              "search":         "{{ trans('crud.search') }}",
              "zeroRecords":    "{{ trans('crud.zeroRecords') }}",
              "paginate": {
                  "first":      "{{ trans('crud.paginate.first') }}",
                  "last":       "{{ trans('crud.paginate.last') }}",
                  "next":       "{{ trans('crud.paginate.next') }}",
                  "previous":   "{{ trans('crud.paginate.previous') }}"
              },
              "aria": {
                  "sortAscending":  "{{ trans('crud.aria.sortAscending') }}",
                  "sortDescending": "{{ trans('crud.aria.sortDescending') }}"
              }
          }
      });

      $.ajaxPrefilter(function(options, originalOptions, xhr) {
          var token = $('meta[name="csrf_token"]').attr('content');

          if (token) {
                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
          }
    });

      // CRUD Delete
      // ask for confirmation before deleting an item
      $("[data-button-type=delete]").click(function(e) {
        e.preventDefault();
        var delete_button = $(this);
        var delete_url = $(this).attr('href');

        if (confirm("{{ trans('crud.delete_confirm') }}") == true) {
            $.ajax({
                url: delete_url,
                type: 'DELETE',
                success: function(result) {
                    // Show an alert with the result
                    new PNotify({
                        title: "{{ trans('crud.delete_confirmation_title') }}",
                        text: "{{ trans('crud.delete_confirmation_message') }}",
                        type: "success"
                    });
                    // delete the row from the table
                    delete_button.parentsUntil('tr').parent().remove();
                },
                error: function(result) {
                    // Show an alert with the result
                    new PNotify({
                        title: "{{ trans('crud.delete_confirmation_not_title') }}",
                        text: "{{ trans('crud.delete_confirmation_not_message') }}",
                        type: "warning"
                    });
                }
            });
        } else {
            new PNotify({
                title: "{{ trans('crud.delete_confirmation_not_deleted_title') }}",
                text: "{{ trans('crud.delete_confirmation_not_deleted_message') }}",
                type: "info"
            });
        }
      });
	  });
	</script>
@endsection
