@if ($crud->hasAccess('clone') && $crud->bulk_actions)
	<a href="javascript:void(0)" onclick="bulkCloneEntries(this)" class="btn btn-sm btn-secondary bulk-button"><i class="fa fa-clone"></i> Clone</a>
@endif

@push('after_scripts')
<script>
	if (typeof bulkCloneEntries != 'function') {
	  function bulkCloneEntries(button) {

	      if (typeof crud.checkedItems === 'undefined' || crud.checkedItems.length == 0)
	      {
	      	PNotify.alert({
	              title: "{{ trans('backpack::crud.bulk_no_entries_selected_title') }}",
	              text: "{{ trans('backpack::crud.bulk_no_entries_selected_message') }}",
	              type: "notice"
	          });

	      	return;
	      }

	      var message = "Are you sure you want to clone these :number entries?";
	      message = message.replace(":number", crud.checkedItems.length);

	      // show confirm message
	      if (confirm(message) == true) {
	      		var ajax_calls = [];
	      		var clone_route = "{{ url($crud->route) }}/bulk-clone";

				// submit an AJAX delete call
				$.ajax({
					url: clone_route,
					type: 'POST',
					data: { entries: crud.checkedItems },
					success: function(result) {
					  // Show an alert with the result
					  PNotify.alert({
					      title: "Entries cloned",
					      text: crud.checkedItems.length+" new entries have been added.",
					      type: "success"
					  });

					  crud.checkedItems = [];
					  crud.table.ajax.reload();
					},
					error: function(result) {
					  // Show an alert with the result
					  PNotify.alert({
					      title: "Cloning failed",
					      text: "One or more entries could not be created. Please try again.",
					      type: "notice"
					  });
					}
				});
	      }
      }
	}
</script>
@endpush