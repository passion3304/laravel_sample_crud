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
	              title: "No entries selected",
	              text: "Please check items in order to perform bulk actions on multiple entries.",
	              type: "warning"
	          }); // TODO: move these texts to language file

	      	return;
	      }

	      var message = "Are you sure you want to delete these "+crud.checkedItems.length+" entries?"; // TODO: move bulk delete message to languge file

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