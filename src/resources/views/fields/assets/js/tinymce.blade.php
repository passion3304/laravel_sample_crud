<!-- include summernote js-->
<script src="{{ asset('dick/js/vendor/tinymce/tinymce.min.js') }}"></script>
{{-- <script src="{{ asset('admin/js/vendor/tinymce/jquery.tinymce.min.js') }}"></script> --}}

<script type="text/javascript">
tinymce.init({
    selector: "textarea.tinymce",
    skin: "dick-light",
    plugins: "image,link,media,anchor",
    file_browser_callback : elFinderBrowser,
 });

function elFinderBrowser (field_name, url, type, win) {
  tinymce.activeEditor.windowManager.open({
    file: '{{ url('admin/elfinder/tinymce4') }}',// use an absolute path!
    title: 'elFinder 2.0',
    width: 900,
    height: 450,
    resizable: 'yes'
  }, {
    setUrl: function (url) {
      win.document.getElementById(field_name).value = url;
    }
  });
  return false;
}
</script>