@php
$multiple = array_get($field, 'multiple', true);
$sortable = array_get($field, 'sortable', false);
$value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';

if (!$multiple && is_array($value)) {
    $value = array_first($value);
}

if (!isset($field['wrapperAttributes']) || !isset($field['wrapperAttributes']['data-init-function']))
{
    $field['wrapperAttributes']['data-init-function'] = 'bpFieldInitBrowseMultipleElement';

    if ($multiple) {
        $field['wrapperAttributes']['data-popup-title'] = trans('backpack::crud.select_files');
        $field['wrapperAttributes']['data-multiple'] = "true";
    } else {
        $field['wrapperAttributes']['data-popup-title'] = trans('backpack::crud.select_file');
        $field['wrapperAttributes']['data-multiple'] = "false";
    }
    $field['wrapperAttributes']['data-only-mimes'] = json_encode($field['mime_types'] ?? []);

    if($sortable){
        $field['wrapperAttributes']['sortable'] = "true";
    }
}
@endphp

<div @include('crud::inc.field_wrapper_attributes') >

    <div><label>{!! $field['label'] !!}</label></div>
    @include('crud::inc.field_translatable_icon')
    <div class="list" data-field-name="{{ $field['name'] }}">
    @if ($multiple)

            @foreach( (array)$value as $v)
                @if ($v)
                    <div class="input-group input-group-sm">
                        <input type="text" name="{{ $field['name'] }}[]" value="{{ $v }}" data-marker="multipleBrowseInput"
                                @include('crud::inc.field_attributes') readonly>
                        <div class="input-group-btn">
                            <button type="button" class="browse remove btn btn-sm btn-light">
                                <i class="fa fa-trash"></i>
                            </button>
                            @if ($sortable)
                                <button type="button" class="browse move btn btn-sm btn-light"><span class="fa fa-sort"></span></button>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

    @else
        <input type="text" data-marker="multipleBrowseInput" name="{{ $field['name'] }}" value="{{ $value }}" @include('crud::inc.field_attributes') readonly>
    @endif
</div>
    <div class="btn-group" role="group" aria-label="..." style="margin-top: 3px;">
        <button type="button" class="browse popup btn btn-sm btn-light">
            <i class="fa fa-cloud-upload"></i>
            {{ trans('backpack::crud.browse_uploads') }}
        </button>
        <button type="button" class="browse clear btn btn-sm btn-light">
            <i class="fa fa-eraser"></i>
            {{ trans('backpack::crud.clear') }}
        </button>
    </div>

    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

    <script type="text/html" data-marker="browse_multiple_template">
        <div class="input-group input-group-sm">
            <input type="text" name="{{ $field['name'] }}[]" @include('crud::inc.field_attributes') readonly>
            <div class="input-group-btn">
                <button type="button" class="browse remove btn btn-sm btn-light">
                    <i class="fa fa-trash"></i>
                </button>
                @if($sortable)
                    <button type="button" class="browse move btn btn-sm btn-light"><span class="fa fa-sort"></span></button>
                @endif
            </div>
        </div>
    </script>
</div>


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- include browse server css -->
        <link rel="stylesheet" type="text/css" href="{{ asset('packages/jquery-ui-dist/jquery-ui.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('packages/barryvdh/elfinder/css/elfinder.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('packages/barryvdh/elfinder/css/theme.css') }}">
        <link href="{{ asset('packages/jquery-colorbox/example2/colorbox.css') }}" rel="stylesheet" type="text/css" />
        <style>
            #cboxContent, #cboxLoadedContent, .cboxIframe {
                background: transparent;
            }
        </style>
    @endpush

    @push('crud_fields_scripts')
        <!-- include browse server js -->
        <script src="{{ asset('packages/jquery-ui-dist/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('packages/jquery-colorbox/jquery.colorbox-min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('packages/barryvdh/elfinder/js/elfinder.min.js') }}"></script>
        {{-- <script type="text/javascript" src="{{ asset('packages/barryvdh/elfinder/js/extras/editors.default.min.js') }}"></script> --}}
        @if ( ($locale = \App::getLocale()) != 'en' )
            <script type="text/javascript" src="{{ asset("packages/barryvdh/elfinder/js/i18n/elfinder.{$locale}.js") }}"></script>
        @endif

        <script>
            function bpFieldInitBrowseMultipleElement(element) {
                var $template = element.find("[data-marker=browse_multiple_template]").html();
                var $list = element.find(".list");
                var $popupButton = element.find(".popup");
                var $clearButton = element.find(".clear");
                var $removeButton = element.find(".remove");
                var $input = element.find('input[data-marker=multipleBrowseInput]');
                var $inputName = $list.attr('data-field-name');
                var $popupTitle = element.attr('data-popup-title');
                var $onlyMimesArray = JSON.parse(element.attr('data-only-mimes'));
                var $multiple = element.attr('data-multiple');
                var $sortable = element.attr('sortable');

                if($sortable){
                    $list.sortable({
                        handle: 'button.move',
                        cancel: ''
                    });
                }

                element.on('click', 'button.popup', function (event) {
                    event.preventDefault();

                    var div = $('<div>');
                    div.elfinder({
                        lang: '{{ \App::getLocale() }}',
                        customData: {
                            _token: '{{ csrf_token() }}'
                        },
                        url: '{{ route("elfinder.connector") }}',
                        soundPath: '{{ asset('/packages/barryvdh/elfinder/sounds') }}',
                        dialog: {
                            width: 900,
                            modal: true,
                            title: $popupTitle,
                        },
                        resizable: false,
                        onlyMimes: $onlyMimesArray,
                        commandsOptions: {
                            getfile: {
                                multiple: $multiple,
                                oncomplete: 'destroy'
                            }
                        },
                        getFileCallback: function (files) {

                            if ($multiple === 'true') {

                                files.forEach(function (file) {
                                    var newInput = $($template);
                                    newInput.find('input').val(file.path);
                                    $list.append(newInput);
                                });

                                if($sortable){
                                    $list.sortable("refresh")
                                }
                            } else {

                                $input.val(files[0].path);

                            }
                            //after submit a file we delete the hidden input if it exists.
                            deleteHiddenInput($inputName, $list);

                            $.colorbox.close();
                        }
                    }).elfinder('instance');

                    // trigger the reveal modal with elfinder inside
                    $.colorbox({
                        href: div,
                        inline: true,
                        width: '80%',
                        height: '80%'
                    });
                });

                element.on('click', 'button.clear', function (event) {
                    event.preventDefault();

                    if($multiple === 'true') {

                        $('.input-group',$list).remove();
                        //when clearing all files we create an hidden input to be submited with the form empty
                        if($('.hidden_browse_multiple_'+$inputName,$list).length < 1) {
                            createHiddenInput($list,$inputName,$multiple);
                        }
                    }else{

                        $input.val('');

                    }
                });

                if ($multiple === 'true') {
                    element.on('click', 'button.remove', function (event) {
                        event.preventDefault();
                        $(this).closest('.input-group').remove();

                        //if we remove the last file we create an hidden input to be submited with the form empty.
                        if($('.input-group',$list).length < 1) {
                            createHiddenInput($list, $inputName, $multiple)
                        }
                    });
                }
            }

            //removes the hidden input from the field
            function deleteHiddenInput($fieldName, $list) {
                $('.hidden_browse_multiple_'+$fieldName,$list).remove()
            }

            //creates the hidden input and appends to field.
            function createHiddenInput($list, $fieldName, $multiple) {
                var input = document.createElement("input");

                input.setAttribute("type", "hidden");
                input.setAttribute("class", "hidden_browse_multiple_"+$fieldName);
                if($multiple === 'true') {
                    input.setAttribute("name", $fieldName+'[]');
                }else{
                    input.setAttribute("name", $fieldName);
                }

                input.setAttribute("value", '');

                $list.append(input);
            }
        </script>
    @endpush
@endif

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
