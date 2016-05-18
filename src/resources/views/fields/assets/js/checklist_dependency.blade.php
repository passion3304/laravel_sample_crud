<!-- include checklist_dependency js-->
<script>
	jQuery(document).ready(function($) {
		
		$('.checklist_dependency').each(function(index, item){

			var unique_name = $(this).data('entity');
			var dependencyJson = window[unique_name];
			
			var thisField = $(this);
			thisField.find('.primary_list').change(function(){
				
				var idCurrent = $(this).data('id');
				if($(this).is(':checked')){

					//add hidden field with this value
					var nameInput = thisField.find('.hidden_fields_primary').data('name');
					var inputToAdd = $('<input type="hidden" class="primary_hidden" name="'+nameInput+'[]" value="'+idCurrent+'">');
					
					thisField.find('.hidden_fields_primary').append(inputToAdd);

					$.each(dependencyJson[idCurrent], function(key, value){
						//check and disable secondies checkbox
						thisField.find('input.secondary_list[value="'+value+'"]').prop( "checked", true );
						thisField.find('input.secondary_list[value="'+value+'"]').prop( "disabled", true );
						//remove hidden fields with secondary dependency if was setted
						var hidden = thisField.find('input.secondary_hidden[value="'+value+'"]');
						if(hidden)
							hidden.remove();
					});
					
				}else{
					//remove hidden field with this value
					thisField.find('input.primary_hidden[value="'+idCurrent+'"]').remove();

					// uncheck and active secondary checkboxs if are not in other selected primary.

					var secondary = dependencyJson[idCurrent];
					
					var selected = [];
					thisField.find('input.primary_hidden').each(function (index, input){
						selected.push( $(this).val() );
					});

					$.each(secondary, function(index, secondaryItem){
						var ok = 1;
						
						$.each(selected, function(index2, selectedItem){
							if( dependencyJson[selectedItem].indexOf(secondaryItem) != -1 ){
								ok =0;
							}
						});

						if(ok){
							thisField.find('input.secondary_list[value="'+secondaryItem+'"]').prop('checked', false);
							thisField.find('input.secondary_list[value="'+secondaryItem+'"]').prop('disabled', false);
						}
					});

				}
			});


			thisField.find('.secondary_list').click(function(){
				
				var idCurrent = $(this).data('id');
				if($(this).is(':checked')){
					//add hidden field with this value
					var nameInput = thisField.find('.hidden_fields_secondary').data('name');
					var inputToAdd = $('<input type="hidden" class="secondary_hidden" name="'+nameInput+'[]" value="'+idCurrent+'">');
					
					thisField.find('.hidden_fields_secondary').append(inputToAdd);
					
				}else{
					//remove hidden field with this value
					thisField.find('input.secondary_hidden[value="'+idCurrent+'"]').remove();
				}
			});

		});
	});
</script>