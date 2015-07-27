@extends('admin.layout')

@section('head')
    <link href="{{ asset('dick/js/vendor/nestedSortable/nestedSortable.css') }}" rel="stylesheet" type="text/css" />
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
	    <li class="active">{{ trans('crud.reorder') }}</li>
	  </ol>
	</section>
@endsection

@section('content')
<?php
  function tree_element($entry, $key, $all_entries, $crud)
  {
    if (!isset($entry->tree_element_shown)) {
      // mark the element as shown
      $all_entries[$key]->tree_element_shown = true;
      $entry->tree_element_shown = true;

      // show the tree lement
      echo '<li id="list_'.$entry->id.'">';
      echo '<div><span class="disclose"><span></span></span>'.$entry->{$crud['reorder_label']}.'</div>';

      // see if this element has any children
      $children = [];
      foreach ($all_entries as $key => $subentry) {
        if ($subentry->parent_id == $entry->id) {
          $children[] = $subentry;
        }
      }

      // if it does have children, show them
      if (count($children)) {
        echo '<ol>';
        foreach ($children as $key => $child) {
          $children[$key] = tree_element($child, $key, $all_entries, $crud);
        }
        echo '</ol>';
      }
      echo '</li>';
    }

    return $entry;
  }

 ?>
<!-- Default box -->
  <div class="box">
    <div class="box-body">

      <p><br>{{ trans('crud.reorder_text') }}</p>

      <ol class="sortable">
        <?php
          $all_entries = $entries->all();
         ?>
        @foreach ($all_entries as $key => $entry)
          <?php
            $all_entries[$key] = tree_element($entry, $key, $all_entries, $crud);
          ?>
        @endforeach
      </ol>

      <button id="toArray" class="btn btn-success ladda-button" data-style="zoom-in"><span class="ladda-label"><i class="fa fa-save"></i> {{ trans('crud.save') }}</span></button>

    </div><!-- /.box-body -->
  </div><!-- /.box -->
@endsection

@section('scripts')
  <script src="https://code.jquery.com/ui/1.11.3/jquery-ui.min.js" type="text/javascript"></script>
  <script src="{{ url('dick/js/vendor/nestedSortable/jquery.mjs.nestedSortable2.js') }}" type="text/javascript"></script>

	<script type="text/javascript">
	  jQuery(document).ready(function($) {

      // initialize the nested sortable plugin
      $('.sortable').nestedSortable({
            forcePlaceholderSize: true,
            handle: 'div',
            helper: 'clone',
            items: 'li',
            opacity: .6,
            placeholder: 'placeholder',
            revert: 250,
            tabSize: 25,
            tolerance: 'pointer',
            toleranceElement: '> div',
            maxLevels: 3,

            isTree: true,
            expandOnHover: 700,
            startCollapsed: false
        });

      $('.disclose').on('click', function() {
        $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
      });

      $('#toArray').click(function(e){
        // get the current tree order
        arraied = $('ol.sortable').nestedSortable('toArray', {startDepthCount: 0});

        // log it
        console.log(arraied);

        // send it with POST
          $.ajax({
            url: '{{ Request::url() }}',
            type: 'POST',
            data: { tree: arraied },
          })
          .done(function() {
            console.log("success");
            new PNotify({
                        title: "{{ trans('crud.reorder_success_title') }}",
                        text: "{{ trans('crud.reorder_success_message') }}",
                        type: "success"
                    });
          })
          .fail(function() {
            console.log("error");
            new PNotify({
                        title: "{{ trans('crud.reorder_error_title') }}",
                        text: "{{ trans('crud.reorder_error_message') }}",
                        type: "danger"
                    });
          })
          .always(function() {
            console.log("complete");
          });

      });

      $.ajaxPrefilter(function(options, originalOptions, xhr) {
          var token = $('meta[name="csrf_token"]').attr('content');

          if (token) {
                return xhr.setRequestHeader('X-XSRF-TOKEN', token);
          }
      });

	  });
	</script>
@endsection
