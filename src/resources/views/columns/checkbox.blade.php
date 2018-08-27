{{-- checkbox with loose false/null/0 checking --}}
<span>
    <input type="checkbox"
    		class="crud_bulk_actions_row_checkbox"
    		data-primary-key-value="{{ $entry->getKey() }}"
    		onClick="addOrRemoveCrudCheckedItem(this)"
    		>
</span>

<script>
	if (typeof addOrRemoveCrudCheckedItem != 'function') {
	  function addOrRemoveCrudCheckedItem(element) {
		var checked = element.checked;
		var primaryKeyValue = $(element).attr('data-primary-key-value');
		// console.log(element.checked);
		// console.log(primaryKeyValue);

		if (typeof crud.checkedItems === 'undefined') {
			crud.checkedItems = [];
		}

		if (checked) {
			crud.checkedItems.push(primaryKeyValue);
		} else {
			var index = crud.checkedItems.indexOf(primaryKeyValue);
			if (index > -1) {
			  crud.checkedItems.splice(index, 1);
			}
		}
	  }
	}

	if (typeof markCheckboxAsCheckedIfPreviouslySelected != 'function') {
	  function markCheckboxAsCheckedIfPreviouslySelected() {
	  	$('#crudTable input[type=checkbox][data-primary-key-value]').each(function(i, element) {
			var checked = element.checked;
			var primaryKeyValue = $(element).attr('data-primary-key-value');

	  		if (typeof crud.checkedItems !== 'undefined' && crud.checkedItems.length > 0)
	  		{
	  			var index = crud.checkedItems.indexOf(primaryKeyValue);
				if (index > -1) {
					element.checked = true;
				}
			}
	  	});
	  }
	}

	if (typeof addBulkActionMainCheckboxesFunctionality != 'function') {
      function addBulkActionMainCheckboxesFunctionality() {
      	$(".crud_bulk_actions_main_checkbox").prop('checked', false);

        // when the crud_bulk_actions_main_checkbox is selected, toggle all visible checkboxes
        $("input.crud_bulk_actions_main_checkbox").click(function(event) {
          if (this.checked) { // if checked, check all visible checkboxes
              $("input.crud_bulk_actions_row_checkbox:not(:checked)").trigger('click');
              // make sure the other checkbox has the same checked status
              $("input.crud_bulk_actions_main_checkbox").prop('checked', true);
          } else { // if not checked, uncheck all visible checkboxes
              $("input.crud_bulk_actions_row_checkbox:checked").trigger('click');
              // make sure the other checkbox has the same checked status
              $("input.crud_bulk_actions_main_checkbox").prop('checked', false);
          }
        });
      }
    }

	// activate checkbox if the page reloaded and the item is remembered as selected
	// make it so that the function above is run after each DataTable draw event
	crud.addFunctionToDataTablesDrawEventQueue('markCheckboxAsCheckedIfPreviouslySelected');
	crud.addFunctionToDataTablesDrawEventQueue('addBulkActionMainCheckboxesFunctionality');
	crud.addFunctionToDataTablesDrawEventQueue('addBulkActionMainCheckboxesFunctionality');
</script>