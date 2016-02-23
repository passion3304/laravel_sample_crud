<!-- include select2 js-->
<script src="{{ asset('dick/js/vendor/select2/select2.js') }}"></script>
<script>
	jQuery(document).ready(function($) {
		// trigger select2 for each untriggered select box
		$('.select2').each(function (i, obj) {
            if ($(obj).attr("data-select2") != 'true')
            {
                $(obj).select2();
                $(obj).attr('select2', 'true');
            }
        });
	});
</script>