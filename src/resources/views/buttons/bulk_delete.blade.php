@if ($crud->hasAccess('delete'))
	<a href="javascript:void(0)" onclick="bulkDeleteEntries(this)" class="btn btn-default"><i class="fa fa-trash"></i> {{ trans('backpack::crud.delete') }}</a>
@endif

@push('after_scripts')
<script>
	if (typeof bulkDeleteEntries != 'function') {
	  function bulkDeleteEntries(button) {

	      if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
	      {
	      	new PNotify({
	              title: "{{ trans('backpack::crud.bulk_no_entries_selected_title') }}",
	              text: "{{ trans('backpack::crud.bulk_no_entries_selected_message') }}",
	              type: "warning"
	          });

	      	return;
	      }
	      var message = "{{ trans('backpack::crud.bulk_delete_are_you_sure') }}";
	      message = message.replace(":number", crud.checkedItems.length);

	      // show confirm message
	      if (confirm(message) == true) {
	      		var ajax_calls = [];

		        // for each crud.checkedItems
		        crud.checkedItems.forEach(function(item) {
	      		  var delete_route = "{{ url($crud->route) }}/"+item;

		      	  // submit an AJAX delete call
	      		  ajax_calls.push($.ajax({
		              url: delete_route,
		              type: 'DELETE',
		              success: function(result) {
		                  // Show an alert with the result
		                  new PNotify({
		                      title: "{{ trans('backpack::crud.delete_confirmation_title') }}",
		                      text: "{{ trans('backpack::crud.delete_confirmation_message') }}",
		                      type: "success"
		                  });
		              },
		              error: function(result) {
		                  // Show an alert with the result
		                  new PNotify({
		                      title: "{{ trans('backpack::crud.delete_confirmation_not_title') }}",
		                      text: "{{ trans('backpack::crud.delete_confirmation_not_message') }}",
		                      type: "warning"
		                  });
		              }
		          }));

		      });

		      $.when.apply(this, ajax_calls).then(function ( ajax_calls ) {
		      		crud.checkedItems = [];
		      		crud.table.ajax.reload();
				});
	      }
      }
	}
</script>
@endpush