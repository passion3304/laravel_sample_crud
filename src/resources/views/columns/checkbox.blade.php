{{-- checkbox with loose false/null/0 checking --}}
<span>
    <input type="checkbox"
    		class="dt_row_checkbox"
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
</script>