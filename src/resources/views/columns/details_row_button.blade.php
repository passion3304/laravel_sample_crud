<!-- expand/minimize button column -->
<span class="details-control text-center cursor-pointer m-r-5">
	<i class="fa fa-plus-square-o details-row-button cursor-pointer" data-entry-id="{{ $entry->getKey() }}"></i>
</span>

<script>
	if (typeof registerDetailsRowButtonAction != 'function') {
		function registerDetailsRowButtonAction() {
	        // var crudTable = $('#crudTable tbody');
	        // Remove any previously registered event handlers from draw.dt event callback
	        $('#crudTable tbody').off('click', 'td .details-row-button');

	        // Make sure the ajaxDatatables rows also have the correct classes
	        $('#crudTable tbody td .details-row-button').parent('td')
	          .removeClass('details-control').addClass('details-control')
	          .removeClass('text-center').addClass('text-center')
	          .removeClass('cursor-pointer').addClass('cursor-pointer');

	        // Add event listener for opening and closing details
	        $('#crudTable tbody td .details-control').on('click', function (e) {
	        	e.stopPropagation();

	            var tr = $(this).closest('tr');
	            var btn = $(this).find('.details-row-button');
	            var row = crud.table.row( tr );

	            if ( row.child.isShown() ) {
	                // This row is already open - close it
	                btn.removeClass('fa-minus-square-o').addClass('fa-plus-square-o');
	                $('div.table_row_slider', row.child()).slideUp( function () {
	                    row.child.hide();
	                    tr.removeClass('shown');
	                } );
	            }
	            else {
	                // Open this row
	                btn.removeClass('fa-plus-square-o').addClass('fa-minus-square-o');
	                // Get the details with ajax
	                $.ajax({
	                  url: '{{ url($crud->route) }}/'+btn.data('entry-id')+'/details',
	                  type: 'GET',
	                  // dataType: 'default: Intelligent Guess (Other values: xml, json, script, or html)',
	                  // data: {param1: 'value1'},
	                })
	                .done(function(data) {
	                  // console.log("-- success getting table extra details row with AJAX");
	                  row.child("<div class='table_row_slider'>" + data + "</div>", 'no-padding').show();
	                  tr.addClass('shown');
	                  $('div.table_row_slider', row.child()).slideDown();
	                  // register_delete_button_action();
	                })
	                .fail(function(data) {
	                  // console.log("-- error getting table extra details row with AJAX");
	                  row.child("<div class='table_row_slider'>{{ trans('backpack::crud.details_row_loading_error') }}</div>").show();
	                  tr.addClass('shown');
	                  $('div.table_row_slider', row.child()).slideDown();
	                })
	                .always(function(data) {
	                  // console.log("-- complete getting table extra details row with AJAX");
	                });
	            }
	        } );
	      }
	  }

	// make it so that the function above is run after each DataTable draw event
	crud.addFunctionToDataTablesDrawEventQueue('registerDetailsRowButtonAction');
</script>