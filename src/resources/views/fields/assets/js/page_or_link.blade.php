<script>

	jQuery(document).ready(function($) {

		$("#page_or_link_select").change(function(e) {
			$(".page_or_link_value input").attr('disabled', 'disabled');
			$(".page_or_link_value select").attr('disabled', 'disabled');
			$(".page_or_link_value").removeClass("hidden").addClass("hidden");


			switch($(this).val()) {
			    case 'external_link':
			        $("#page_or_link_external_link input").removeAttr('disabled');
			        $("#page_or_link_external_link").removeClass('hidden');
			        break;

			    case 'internal_link':
			        $("#page_or_link_internal_link input").removeAttr('disabled');
			        $("#page_or_link_internal_link").removeClass('hidden');
			        break;

			    default: // page_link
			        $("#page_or_link_page select").removeAttr('disabled');
			        $("#page_or_link_page").removeClass('hidden');
			}
		});

	});
</script>