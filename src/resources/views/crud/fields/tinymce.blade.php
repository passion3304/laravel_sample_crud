<!-- Tiny MCE -->
@php
$defaultOptions = [
    'file_browser_callback' => 'elFinderBrowser',
    'selector' => 'textarea.tinymce',
    'plugins' => 'image,link,media,anchor',
];

$field['options'] = array_merge($defaultOptions, $field['options'] ?? []);
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <textarea
        name="{{ $field['name'] }}"
        data-init-function="bpFieldInitTinyMceElement"
        data-options="{{ trim(json_encode($field['options'])) }}"
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control tinymce'])
        >{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}</textarea>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include tinymce js-->
    <script src="{{ asset('packages/tinymce/tinymce.min.js') }}"></script>

    <script type="text/javascript">
    function bpFieldInitTinyMceElement(element) {
        // grab the configuration defined in PHP
        var configuration = element.data('options');

        // the target should be the element the function has been called on
        configuration['target'] = element;

        // automatically update the textarea value on focusout
        configuration['setup'] = (function (editor) {
            editor.on('change', function () {
                tinymce.triggerSave();
            });
        });

        // initialize the TinyMCE editor
        tinymce.init(element.data('options'));
    }

    function elFinderBrowser (field_name, url, type, win) {
      tinymce.activeEditor.windowManager.open({
        file: '{{ backpack_url('elfinder/tinymce4') }}',// use an absolute path!
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
    @endpush

@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
