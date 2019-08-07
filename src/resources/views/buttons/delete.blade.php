@if ($crud->hasAccess('delete'))
	<a href="javascript:void(0)" onclick="deleteEntry(this)" data-route="{{ url($crud->route.'/'.$entry->getKey()) }}" class="btn btn-sm btn-link" data-button-type="delete"><i class="fa fa-trash"></i> {{ trans('backpack::crud.delete') }}</a>
@endif

<script>
	if (typeof deleteEntry != 'function') {
	  $("[data-button-type=delete]").unbind('click');

	  function deleteEntry(button) {
		// ask for confirmation before deleting an item
		// e.preventDefault();
		var button = $(button);
		var route = button.attr('data-route');
		var row = $("#crudTable a[data-route='"+route+"']").closest('tr');

		var notice = PNotify.error({
		  // title: 'Confirmation Needed',
		  text: '{{ trans('backpack::crud.delete_confirm') }}<br><br>',
		  textTrusted: true,
		  icon: 'fa fa-question-circle',
		  hide: false,
		  stack: {
		    'dir1': 'down',
		    'modal': true,
		    'firstpos1': 25
		  },
		  modules: {
            // Animate: {
            //   animate: true,
            //   inClass: 'bounceIn',
            //   outClass: 'bounceOut'
            // },
		    Confirm: {
		      confirm: true,
		      align: 'center',
		      buttons: [{
		          text: '{{ trans('backpack::crud.delete') }}',
		          primary: true,
		          click: function(notice) {
		            $.ajax({
					      url: route,
					      type: 'DELETE',
					      success: function(result) {
					          if (result != 1) {
					          	// Show an error alert
					              PNotify.alert({
					                  title: "{{ trans('backpack::crud.delete_confirmation_not_title') }}",
					                  text: "{{ trans('backpack::crud.delete_confirmation_not_message') }}",
					                  type: "notice"
					              });
					          } else {
					              // Show a success alert with the result
					              PNotify.alert({
					                  title: "{{ trans('backpack::crud.delete_confirmation_title') }}",
					                  text: "{{ trans('backpack::crud.delete_confirmation_message') }}",
					                  type: "success"
					              });

					              // Hide the modal, if any
					              $('.modal').modal('hide');

					              // Remove the details row, if it is open
					              if (row.hasClass("shown")) {
					                  row.next().remove();
					              }

					              // Remove the row from the datatable
					              row.remove();
					          }
					      },
					      error: function(result) {
					          // Show an alert with the result
					          PNotify.alert({
					              title: "{{ trans('backpack::crud.delete_confirmation_not_title') }}",
					              text: "{{ trans('backpack::crud.delete_confirmation_not_message') }}",
					              type: "notice"
					          });
					      }
					  });
		          }
		        },
		        {
		          text: '{{ trans('backpack::crud.cancel') }}',
		          click: function(notice) {
		            notice.update({
		              title: '{{ trans('backpack::crud.delete_confirmation_not_deleted_title') }}',
		              text: '{{ trans('backpack::crud.delete_confirmation_not_deleted_message') }}',
		              icon: true,
		              type: 'info',
		              delay: '1000',
		              hide: true,
		              modules: {
		                Confirm: {
		                  confirm: false
		                },
		                Buttons: {
		                  closer: true,
		                  sticker: true
		                }
		              }
		            });
		          }
		        }
		      ]
		    },
		    Buttons: {
		      closer: false,
		      sticker: false
		    },
		    History: {
		      history: false
		    }
		  }
		});
      }
	}

	// make it so that the function above is run after each DataTable draw event
	// crud.addFunctionToDataTablesDrawEventQueue('deleteEntry');
</script>