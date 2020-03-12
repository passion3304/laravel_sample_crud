<!--  relationship  -->

@php
    use Illuminate\Support\Str;

    //in case entity is superNews we want the url friendly super-news
    $routeEntity = Str::kebab($field['entity']);

    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();

    //we need to explicitly define field type to load correct assets
    //this is needed because the field type `relationship` is a switch to
    //other field types like fetch_or_create
    $field['type'] = 'fetch_or_create';

    // make sure the $field['value'] takes the proper value
    // and format it to JSON, so that select2 can parse it
    $current_value = old(square_brackets_to_dots($field['name'])) ?? old($field['name']) ?? $field['value'] ?? $field['default'] ?? '';

    if ($current_value != false) {
        switch (gettype($current_value)) {
            case 'array':
                $current_value = $connected_entity
                                    ->whereIn($connected_entity_key_name, $current_value)
                                    ->pluck($field['attribute'], $connected_entity_key_name)
                                    ->toArray();
                break;
            case 'object':
                if(! $current_value->isEmpty())  {
                    $current_value = $current_value
                                    ->pluck($field['attribute'], $connected_entity_key_name)
                                    ->toArray();
                }
                break;
            default:
                $current_value = $connected_entity
                                ->where($connected_entity_key_name, $current_value)
                                ->pluck($field['attribute'], $connected_entity_key_name)
                                ->toArray();
                break;
        }
    }

    $field['value'] = json_encode($current_value);

    //by default, when creating an entity we want it to be selected/added to selection.
    $field['inline_create']['force_select'] = $field['inline_create']['force_select'] ?? true;

    $field['data_source'] = $field['data_source'] ?? url($crud->route.'/fetch/'.$routeEntity);



$activeInlineCreate = !empty($field['inline_create']) ? true : false;

if($activeInlineCreate) {
    //if user don't specify a different entity in inline_create we assume it's the same from $field['entity'] kebabed
    $field['inline_create']['entity'] = $field['inline_create']['entity'] ?? $routeEntity;

    //we check if this field is not beeing requested in some InlineCreate operation.
    //this variable is setup by InlineCreate modal when loading the fields.
    if(!isset($inlineCreate)) {

        //route to create a new entity
        $field['inline_create']['create_route'] = route($field['inline_create']['entity']."-inline-create");
    }
}

@endphp

<div @include('crud::inc.field_wrapper_attributes') >

        <label>{!! $field['label'] !!}</label>
        @include('crud::inc.field_translatable_icon')

        @if($activeInlineCreate)
            @include('crud::fields.relationship.create_button', ['name' => $field['name'], 'inlineCreateEntity' => $field['inline_create']['entity']])
        @endif
<select
        name="{{ $field['name'].($field['multiple']?'[]':'') }}"
        data-original-name="{{ $field['name'] }}"
        style="width: 100%"
        data-force-select="{{ var_export($field['inline_create']['force_select']) }}"
        data-init-function="bpFieldInitRelationshipElement"
        data-is-inline="{{ $inlineCreate ?? 'false' }}"
        data-field-multiple="{{var_export($field['multiple'])}}"
        data-allows-null="{{var_export($field['allows_null'])}}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(array_wrap($field['dependencies'])): json_encode([]) }}"
        data-model-local-key="{{$crud->model->getKeyName()}}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-data-source="{{ $field['data_source'] }}"
        data-method="{{ $field['method'] ?? 'POST' }}"
        data-minimum-input-length="{{ $field['minimum_input_length'] }}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ $field['include_all_form_fields'] ?? 'true' }}"
        data-current-value="{{ $field['value'] }}"
        data-field-ajax="{{var_export($field['ajax'])}}"

        @if($activeInlineCreate)
            @include('crud::fields.relationship.field_attributes')
        @endif

        @include('crud::inc.field_attributes', ['default_class' =>  'form-control select2_field'])

        @if($field['multiple'])
        multiple
        @endif
        >

</select>
 {{-- HINT --}}
 @if (isset($field['hint']))
 <p class="help-block">{!! $field['hint'] !!}</p>
@endif

</div>

        @if ($crud->fieldTypeNotLoaded($field))
        @php
            $crud->markFieldTypeAsLoaded($field);
        @endphp

        {{-- FIELD CSS - will be loaded in the after_styles section --}}
        @push('crud_fields_styles')

            <!-- include select2 css-->
            <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
            <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        @endpush

        {{-- FIELD JS - will be loaded in the after_scripts section --}}
        @push('crud_fields_scripts')

            <!-- include select2 js-->
            <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
            @if (app()->getLocale() !== 'en')
            <script src="{{ asset('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js') }}"></script>
            @endif
            <script>

document.styleSheets[0].addRule('.select2-selection__clear::after','content:  "{{ trans('backpack::crud.clear') }}";');

// this is the function responsible for querying the ajax endpoint with our query string, emulating the select2
// ajax search mechanism.
var performAjaxSearch = function (element, $searchString) {
    var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
    var $refreshUrl = element.attr('data-data-source');
    var $method = element.attr('data-method');
    var form = element.closest('form')

    return new Promise(function (resolve, reject) {
        $.ajax({
            url: $refreshUrl,
            data: (function() {
                if ($includeAllFormFields) {
                            return {
                                q: $searchString, // search term
                                form: form.serializeArray() // all other form inputs
                            };
                        } else {
                            return {
                                q: $searchString, // search term
                            };
                        }
            })(),
            type: $method,
            success: function (result) {

                resolve(result);
            },
            error: function (result) {

                reject(result);
            }
        });
    });
};


  // this function is responsible for fetching some default option when developer don't allow null on field
if (!window.fetchDefaultEntry) {
var fetchDefaultEntry = function (element) {
    var $relatedAttribute = element.attr('data-field-attribute');
    var $relatedKeyName = element.attr('data-connected-entity-key-name');
    var $fetchUrl = element.attr('data-data-source');
    var $return = {};
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: $fetchUrl,
            data: {
                'q': ''
            },
            type: 'POST',
            success: function (result) {
                // if data is available here it means a paginated collection has been returned.
                // we want only the first to be default.
                if (typeof result.data !== "undefined"){
                    $key = result.data[0][$relatedKeyName];
                    $value = result.data[0][$relatedAttribute];
                }else{
                    $key = result[0][$relatedKeyName];
                    $value = result[0][$relatedAttribute];
                }

                $pair = { [$relatedKeyName] : $key, [$relatedAttribute] : $value}
                $return = {...$return, ...$pair};

                $(element).attr('data-current-value', JSON.stringify($return));
                resolve($return);
            },
            error: function (result) {
                reject(result);
            }
        });
    });
};
}


//parses the current value for selects. we can have a single, multiple { key : attr } or multiple ids [1,2,3,4]
// we allways return [1,2,3,4]
function parseValueForSelectMultipleOptions(element) {
    var $currentValue = JSON.parse(element.attr('data-current-value'));
    var selectedOptions = [];
        //we parse the current value to append those options values to the selected options.
        if(Number.isInteger(+$currentValue)) {
            selectedOptions.push($currentValue);
        }else{

           for (let [key, value] of Object.entries($currentValue)) {
                    selectedOptions.push(key);
            }
        }
        return selectedOptions;
}

//this setup the "+Add" button in page with corresponding click handler.
//when clicked, fetches the html for the modal to show

function setupInlineCreateButtons(element) {
    var $inlineCreateButton = element.attr('data-inline-create-button');
    var $fieldEntity = element.attr('data-field-related-name');
    var $inlineCreateButtonElement = $(document.getElementById($inlineCreateButton));
    var $inlineCreateRoute = element.attr('data-inline-create-route');

    $inlineCreateButtonElement.on('click', function () {
        $(".loading_modal_dialog").show();
        $.ajax({
            url: $inlineCreateRoute,
            data: {
                'entity': $fieldEntity
            },
            type: 'GET',
            success: function (result) {
                $('body').append(result);
                triggerModal(element);

            },
            error: function (result) {
                // Show an alert with the result
                swal({
                    title: "error",
                    text: "error",
                    icon: "error",
                    timer: 4000,
                    buttons: false,
                });
            }
        });
    });

}

// when an entity is created we query the ajax endpoint to check if the created option is returned.
function ajaxSearch(element, created) {
    var $relatedAttribute = element.attr('data-field-attribute');
    var $relatedKeyName = element.attr('data-connected-entity-key-name');
    var $searchString = created[$relatedAttribute];

    //we run the promise with ajax call to search endpoint to check if we got the created entity back
    //in case we do, we add it to the selected options.
    performAjaxSearch(element, $searchString).then(result => {
        var inCreated = $.map(result.data, function (item) {
            if(item[$relatedKeyName] == created[$relatedKeyName]) {
                                return {
                                    text: item[$relatedAttribute],
                                    id: item[$relatedKeyName]
                                }
            }
                            });

                            if(inCreated.length) {
                                selectOption(element, created);
                            }
                    });
    }

//this is the function called when button to add is pressed,
//it triggers the modal on page and initialize the fields

function triggerModal(element) {
    var $fieldName = element.attr('data-field-related-name');
    var $modal = $('#'+$fieldName+'-inline-create-dialog');
    var $modalSaveButton = $modal.find('#saveButton');
    var $form = $(document.getElementById($fieldName+"-inline-create-form"));
    var $inlineCreateRoute = element.attr('data-inline-create-route');
    var $ajax = element.attr('data-field-ajax') == 'true' ? true : false;
    var $force_select = (element.attr('data-force-select') == 'true') ? true : false;

    $modal.modal({ backdrop: 'static', keyboard: false, focus: false });


    initializeFieldsWithJavascript($form);

    //when you hit save on modal save button.
    $modalSaveButton.on('click', function () {

        $form = document.getElementById($fieldName+"-inline-create-form");

        //this is needed otherwise fields like ckeditor don't post their value.
        $($form).trigger('form-pre-serialize');

        var $formData = new FormData($form);

        //we change button state so users know something is happening.
        //we also disable it to prevent double form submition
        var loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> loading...';
        if ($modalSaveButton.html() !== loadingText) {
            $modalSaveButton.data('original-text', $(this).html());
            $modalSaveButton.html(loadingText);
            $modalSaveButton.prop('disabled', true);
        }


        $.ajax({
            url: $inlineCreateRoute,
            data: $formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (result) {

                $createdEntity = result.data;

                if(!$force_select) {
                    //if developer did not force the created entity to be selected we first try to
                    //check if created is still available upon model re-search.
                    ajaxSearch(element, result.data);

                }else{
                    selectOption(element, result.data);
                }

                $modal.modal('hide');
                //TODO: We should create translation string for this ?
                new Noty({
                    type: "info",
                    text: 'Related entry created.',
                }).show();
            },
            error: function (result) {

                var $errors = result.responseJSON.errors;

                let message = '';
                for (var i in $errors) {
                    message += $errors[i] + ' \n';
                }

                new Noty({
                    type: "error",
                    text: '<strong>Error creating related entry.</strong><br> '+message,
                }).show();

                //revert save button back to normal
                $modalSaveButton.prop('disabled', false);
                $modalSaveButton.html($modalSaveButton.data('original-text'));
            }
        });
    });

    $modal.on('hidden.bs.modal', function (e) {
        $modal.remove();
    });


    $modal.on('shown.bs.modal', function (e) {
        $(".loading_modal_dialog").hide();
    });
}

//function responsible for adding an option to the select
function selectOption(element, option) {
    var $currentValue = JSON.parse(element.attr('data-current-value'));
    var $relatedAttribute = element.attr('data-field-attribute');
    var $relatedKeyName = element.attr('data-connected-entity-key-name');
    var $multiple = (element.attr('data-field-multiple') == 'true') ? true : false;
    var $option = new Option(option[$relatedAttribute], option[$relatedKeyName]);
        $(element).append($option);

        if($multiple) {
        //we check if any options are previously selected
        var selectedOptions = parseValueForSelectMultipleOptions(element);
        selectedOptions.push(option[$relatedKeyName]);
            $(element).val(selectedOptions);
        }else{
            $(element).val(option[$relatedKeyName]);
        }
        $(element).trigger('change');

}



                function bpFieldInitRelationshipElement(element) {

                var form = element.closest('form');
                var $inlineField = element.attr('data-is-inline');
                var $ajax = element.attr('data-field-ajax') == 'true' ? true : false;
                var $placeholder = element.attr('data-placeholder');
                var $minimumInputLength = element.attr('data-minimum-input-length');
                var $dataSource = element.attr('data-data-source');
                var $method = element.attr('data-method');
                var $fieldAttribute = element.attr('data-field-attribute');
                var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
                var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
                var $dependencies = JSON.parse(element.attr('data-dependencies'));
                var $modelKey = element.attr('data-model-local-key');
                var $allows_null = (element.attr('data-allows-null') == 'true') ? true : false;


                // we catch select/unselect events so we don't lose our selections
                // if we create a related entity before saving the actual selection
                element.on('select2:select', function (e) {
                    $(element).attr('data-current-value', JSON.stringify(element.val()));
                });

                element.on('select2:unselect', function (e) {
                    $(element).attr('data-current-value', JSON.stringify(element.val()));
                });

                //if field is not ajax we fetch the defaults for select.


                    var $item = false;

                    var $value = JSON.parse(element.attr('data-current-value'))

                    if(Object.keys($value).length > 0) {
                        $item = true;
                    }
                    var selectedOptions = [];
                    var $currentValue = $item ? $value : '';

                    //we reselect the previously selected options if any.
                    for (const [key, value] of Object.entries($currentValue)) {
                        selectedOptions.push(key);
                        var $option = new Option(value, key);
                        $(element).append($option);
                    }

                    $(element).val(selectedOptions);

                    //null is not allowed we fetch some default entry
                    if(!$allows_null && !$item) {
                        fetchDefaultEntry(element).then(result => {
                            $(element).append('<option value="'+result[$modelKey]+'">'+result[$fieldAttribute]+'</option>');
                            $(element).val(result[$modelKey]);
                            $(element).trigger('change');
                        });
                    }



                //Checks if field is not beeing inserted in one inline create modal and setup buttons
                if($inlineField == "false") {
                    setupInlineCreateButtons(element);
                }

                    if (!element.hasClass("select2-hidden-accessible")) {


                            element.select2({
                            theme: "bootstrap",
                            placeholder: $placeholder,
                            minimumInputLength: $minimumInputLength,
                            allowClear: $allows_null,
                            ajax: {
                            url: $dataSource,
                            type: $method,
                            dataType: 'json',
                            quietMillis: 500,
                            data: function (params) {
                            if ($includeAllFormFields) {
                            return {
                                q: params.term, // search term
                                page: params.page, // pagination
                                form: form.serializeArray() // all other form inputs
                            };
                        } else {
                            return {
                                q: params.term, // search term
                                page: params.page, // pagination
                            };
                        }
                    },
                    processResults: function (data, params) {
                        params.page = params.page || 1;
                        //if we have data.data here it means we returned a paginated instance from controller.
                        //otherwise we returned one or more entries unpaginated.
                        if(data.data) {
                        var result = {
                            results: $.map(data.data, function (item) {
                                return {
                                    text: item[$fieldAttribute],
                                    id: item[$connectedEntityKeyName]
                                }
                            }),
                           pagination: {
                                 more: data.current_page < data.last_page
                           }
                        };
                        }else {
                            var result = {
                                results: $.map(data, function (item) {
                                    return {
                                        text: item[$fieldAttribute],
                                        id: item[$connectedEntityKeyName]
                                    }
                                }),
                                pagination: {
                                    more: false,
                                }
                            }
                        }

                        return result;
                    },
                    cache: true
                },
                        });
                    }

                for (var i=0; i < $dependencies.length; i++) {
                $dependency = $dependencies[i];
                $('input[name='+$dependency+'], select[name='+$dependency+'], checkbox[name='+$dependency+'], radio[name='+$dependency+'], textarea[name='+$dependency+']').change(function () {
                    element.val(null).trigger("change");
                });
            }

                }
            </script>
        @endpush

    @endif
    {{-- End of Extra CSS and JS --}}
    {{-- ########################################## --}}
