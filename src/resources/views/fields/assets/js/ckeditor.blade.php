<!-- include summernote js-->
<script src="{{ asset('vendor/backpack/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('vendor/backpack/ckeditor/adapters/jquery.js') }}"></script>
<script>
    jQuery(document).ready(function($) {
    	$('textarea.ckeditor' ).ckeditor({
    		"filebrowserBrowseUrl": "{{ url('admin/elfinder/ckeditor') }}",
    		"extraPlugins" : 'oembed,widget'
    	});
    });
</script>