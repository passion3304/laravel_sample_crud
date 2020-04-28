@php
$multiple = Arr::get($field, 'multiple', true);
$sortable = Arr::get($field, 'sortable', false);
$value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';

if (!$multiple && is_array($value)) {
    $value = Arr::first($value);
}

$field['wrapper'] = $field['wrapperAttributes'] ?? [];
$field['wrapper']['data-init-function'] = $field['wrapper']['data-init-function'] ?? 'bpFieldInitBrowseMultipleElement';
$field['wrapper']['data-elfinder-trigger-url'] = $field['wrapper']['data-elfinder-trigger-url'] ?? url(config('elfinder.route.prefix').'/popup/'.$field['name'].'?multiple=1');

if (isset($field['mime_types'])) {
    $field['wrapper']['data-elfinder-trigger-url'] .= '&mimes='.urlencode(serialize($field['mime_types']));
}

if ($multiple) {
    $field['wrapper']['data-multiple'] = "true";
} else {
    $field['wrapper']['data-multiple'] = "false";
}

if($sortable){
    $field['wrapper']['sortable'] = "true";
}
@endphp

@include('crud::fields.inc.wrapper_start')

    <div><label>{!! $field['label'] !!}</label></div>
    @include('crud::fields.inc.translatable_icon')
    <div class="list" data-field-name="{{ $field['name'] }}">
    @if ($multiple)

            @foreach( (array)$value as $v)
                @if ($v)
                    <div class="input-group input-group-sm">
                        <input type="text" name="{{ $field['name'] }}[]" value="{{ $v }}" data-marker="multipleBrowseInput"
                                @include('crud::fields.inc.attributes') readonly>
                        <div class="input-group-btn">
                            <button type="button" class="browse remove btn btn-sm btn-light">
                                <i class="la la-trash"></i>
                            </button>
                            @if ($sortable)
                                <button type="button" class="browse move btn btn-sm btn-light"><span class="la la-sort"></span></button>
                            @endif
                        </div>
                    </div>
                @endif
            @endforeach

    @else
        <input type="text" data-marker="multipleBrowseInput" name="{{ $field['name'] }}" value="{{ $value }}" @include('crud::fields.inc.attributes') readonly>
    @endif
</div>
    <div class="btn-group" role="group" aria-label="..." style="margin-top: 3px;">
        <button type="button" class="browse popup btn btn-sm btn-light">
            <i class="la la-cloud-upload"></i>
            {{ trans('backpack::crud.browse_uploads') }}
        </button>
        <button type="button" class="browse clear btn btn-sm btn-light">
            <i class="la la-eraser"></i>
            {{ trans('backpack::crud.clear') }}
        </button>
    </div>

    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif

    <script type="text/html" data-marker="browse_multiple_template">
        <div class="input-group input-group-sm">
            <input type="text" name="{{ $field['name'] }}[]" @include('crud::fields.inc.attributes') readonly>
            <div class="input-group-btn">
                <button type="button" class="browse remove btn btn-sm btn-light">
                    <i class="la la-trash"></i>
                </button>
                @if($sortable)
                    <button type="button" class="browse move btn btn-sm btn-light"><span class="la la-sort"></span></button>
                @endif
            </div>
        </div>
    </script>
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')        
        <link href="{{ asset('packages/jquery-colorbox/example2/colorbox.css') }}" rel="stylesheet" type="text/css" />
        <style>
            #cboxContent, #cboxLoadedContent, .cboxIframe {
                background: transparent;
            }
        </style>
    @endpush

    @push('crud_fields_scripts')
        
        <script src="{{ asset('packages/jquery-ui-dist/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('packages/jquery-colorbox/jquery.colorbox-min.js') }}"></script>
        <script>
            // this global variable is used to remember what input to update with the file path
            // because elfinder is actually loaded in an iframe by colorbox
            var elfinderTarget = false;

            // function to update the file selected by elfinder
            function processSelectedMultipleFiles(files, requestingField) {
                elfinderTarget.trigger('processFiles', [files]);                
                elfinderTarget = false;
            }

            function bpFieldInitBrowseMultipleElement(element) {
                var $triggerUrl = element.data('elfinder-trigger-url');
                var $template = element.find("[data-marker=browse_multiple_template]").html();
                var $list = element.find(".list");
                var $popupButton = element.find(".popup");
                var $clearButton = element.find(".clear");
                var $removeButton = element.find(".remove");
                var $input = element.find('input[data-marker=multipleBrowseInput]');
                var $inputName = $list.attr('data-field-name');
                var $multiple = element.attr('data-multiple');
                var $sortable = element.attr('sortable');

                if($sortable){
                    $list.sortable({
                        handle: 'button.move',
                        cancel: ''
                    });
                }

                element.on('processFiles', element, function(event, files) {
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
                    // after submit a file we delete the hidden input if it exists.
                    deleteHiddenInput($inputName, $list);
                });

                element.on('click', 'button.popup', function (event) {
                    event.preventDefault();

                    // remember which element the elFinder was triggered by
                    elfinderTarget = element;

                    // trigger the elFinder modal
                    $.colorbox({
                        href: $triggerUrl,
                        fastIframe: true,
                        iframe: true,
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
