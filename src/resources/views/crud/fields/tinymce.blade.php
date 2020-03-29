<!-- Tiny MCE -->
@php
$defaultOptions = [
    'file_browser_callback' => 'elFinderBrowser',
    'selector' => 'textarea.tinymce',
    'plugins' => 'image,link,media,anchor',
];

$field['options'] = array_merge($defaultOptions, $field['options'] ?? []);
@endphp

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <textarea
        name="{{ $field['name'] }}"
        data-init-function="bpFieldInitTinyMceElement"
        data-options="{{ trim(json_encode($field['options'])) }}"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control tinymce'])
        >{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}</textarea>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
</div>


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
        console.log('bpFieldInitTinyMceElement got called');

        var configuration = element.data('options');

        configuration['target'] = element;
        configuration['setup'] = (function (editor) {
            editor.on('change', function () {
                tinymce.triggerSave();
            });
        });

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
