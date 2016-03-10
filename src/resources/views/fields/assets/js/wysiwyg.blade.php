<!-- include summernote js-->
<script src="{{ asset('vendor/backpack/summernote/summernote.min.js') }}"></script>
<script>
	jQuery(document).ready(function($) {
		$('.summernote').summernote({
			height: 200
		});
	});
</script>