<!--  relationship  -->

@php

    $field['multiple'] = $field['multiple'] ?? $crud->relationAllowsMultiple($field['relation_type']);
    $field['ajax'] = $field['ajax'] ?? config('backpack.crud.relationships.default_ajax', false);


    $fieldInlineConfiguration = $field['inline_create'] ?? [];

    if(!$field['multiple']) {
        $current_value = old($field['name']) ?? $field['value'] ?? $field['default'] ?? '';
    }else{
        $current_value = old(square_brackets_to_dots($field['name'])) ?? old($field['name']) ?? $field['value'] ?? $field['default'] ?? '';
    }

    $related_model_instance = new $field['model']();

    //this is to provide access to FetchOperation, it probably gona change as FetchOperation changes, does not break the field if not using the operation
    $response_entity = isset($field['response_entity']) ? $field['response_entity'] : $crud->hasOperationSetting('ajaxEntities') ? array_has($crud->getOperationSetting('ajaxEntities'), $field['entity']) ? $field['entity'] : array_key_first($crud->getOperationSetting('ajaxEntities')) : '';

    /*
     * if developer does not provide a placeholder we set one. If field is not nullable and developer did not setup the placeholder
     * we will get some default option for the select.
    */
    $autoFetch = false;
    if(!isset($field['placeholder'])) {
        $field['placeholder'] = 'Select an ' . $field['entity'];
        $autoFetch = true;
    }



    if ($current_value !== false) {
        if(is_array($current_value)) {
            $current_value = $related_model_instance->whereIn($related_model_instance->getKeyName(),$current_value)->pluck($field['attribute'],$related_model_instance->getKeyName());
        }else{
            if(is_object($current_value)) {
            if(!$current_value->isEmpty()) {
                $current_value = $current_value->pluck($field['attribute'],$related_model_instance->getKeyName())->toArray();
            }
        }

        }
        $current_value = json_encode($current_value);
    }


//this checks if column is nullable on database by default, but developer might overriden that property
$allows_null = isset($field['allows_null']) ? (bool)$field['allows_null'] : $crud->model::isColumnNullable($field['name']);

//dd($allows_null);

$options = [];
   if($field['ajax'] != true) {
    if (!isset($field['options'])) {

    $options = $related_model_instance::all()->pluck($field['attribute'],$related_model_instance->getKeyName());
} else {
    $options = call_user_func($field['options'], $field['model']::query()->pluck($field['attribute'],$related_model_instance->getKeyName()));
}
   }

//we make sure on_the_fly operation is setup and that user wants to allow field creation
if($crud->has($crud->getOperation().'.inline_create')) {

$activeInlineCreate = !empty($fieldInlineConfiguration) ? true : false;

if($activeInlineCreate) {
    //if user don't specify 'entity_route' we assume it's the same from $field['entity']
    $inlineCreateEntity = isset($fieldInlineConfiguration['entity']) ? $fieldInlineConfiguration['entity'] : $field['entity'];

if(!isset($inlineCreate)) {
    $createRoute = route($inlineCreateEntity."-inline-create");

    $createRouteEntity = last(explode('/', $crud->route));

    $refreshRoute = route($createRouteEntity."-inline-refresh-options");

}
}
}else{
    $activeInlineCreate = false;
}

@endphp

<div @include('crud::inc.field_wrapper_attributes') >

        <label>{!! $field['label'] !!}</label>
        @include('crud::inc.field_translatable_icon')

        @if($activeInlineCreate)
            @include('crud::fields.relationship.create_button', ['name' => $field['name'], 'inlineCreateEntity' => $inlineCreateEntity])
        @endif
<select
@if(!$field['multiple'])
        name="{{ $field['name'] }}"
        @else
        name="{{ $field['name'] }}[]"
        @endif
        data-original-name="{{ $field['name'] }}"
        style="width: 100%"
        data-auto-fetch="{{var_export($autoFetch)}}"
        data-init-function="bpFieldInitRelationshipElement"
        data-is-inline="{{ $inlineCreate ?? 'false' }}"
        data-field-multiple="{{var_export($field['multiple'])}}"
        data-options-for-select="{{json_encode($options)}}"
        data-allows-null="{{var_export($allows_null)}}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(array_wrap($field['dependencies'])): json_encode([]) }}"
        data-model-local-key="{{$crud->model->getKeyName()}}"
        data-placeholder="{{ $field['placeholder'] }}"

        @if($field['ajax'])
        data-data-source="{{isset($field['data_source']) ? $field['data_source'] : url($crud->route . '/fetch/' . $response_entity)}}"
        data-method="{{ $field['method'] ?? 'GET' }}"
        data-minimum-input-length="{{ isset($field['minimum_input_length']) ? $field['minimum_input_length'] : 2 }}"
        @endif
        data-field-attribute="{{ $field['attribute'] }}"
        data-connected-entity-key-name="{{ $related_model_instance->getKeyName() }}"
        data-include-all-form-fields="{{ $field['include_all_form_fields'] ?? 'true' }}"
        data-current-value="{{$current_value}}"
        data-field-ajax="{{var_export($field['ajax'])}}"
        data-item="{{ (isset($item) && !is_null($item) && !empty($item)) ? '{ "id":"'.$item->getKey().'","text":"'.$item->{$field['attribute']} .'"}' : json_encode(false) }}"
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

// this function is responsible for reloading the option list uppon inline creation.
if (!window.refreshOptionList) {
var refreshOptionList = function (element, $field, $refreshUrl) {
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: $refreshUrl,
            data: {
                'field': $field
            },
            type: 'GET',
            success: function (result) {
                $(element).attr('data-options-for-select', JSON.stringify(result));
                resolve(result);
            },
            error: function (result) {

                reject(result);
            }
        });
    });
};
}


  // this function is responsible for fetching some default option when developer don't allow null on field
  if (!window.fetchDefaultEntry) {
var fetchDefaultEntry = function (element) {
    var $fetchUrl = element.attr('data-data-source');
    return new Promise(function (resolve, reject) {
        $.ajax({
            url: $fetchUrl,
            data: {
                'q': ''
            },
            type: 'GET',
            success: function (result) {
                //if data is available here it means developer returned a collection and we want only the first.
                //when using the AjaxFetchOperation we will have here a single entity.
                if(result.data) {
                    var $return = result.data[0];
                }else{
                    $return = result;
                }

                $(element).attr('data-item', JSON.stringify($return));
                resolve(result);
            },
            error: function (result) {
                reject(result);
            }
        });
    });
};
  }

//this function is responsible by setting up a default option in ajax fields
if (typeof refreshDefaultOption !== "function") {
function refreshDefaultOption(element, $fieldAttribute, $modelKey) {
     var $item = JSON.parse(element.attr('data-item'));
     $(element).append('<option value="'+$item[$modelKey]+'">'+$item[$fieldAttribute]+'</option>');
     $(element).val($item[$modelKey]);
     $(element).trigger('change');
}
}


// this is the function responsible for displaying the select options.
if (typeof fillSelectOptions !== "function") {
function fillSelectOptions(element, $created = false) {

    var $multiple = element.attr('data-field-multiple') == 'true' ? true : false;
    var $options = JSON.parse(element.attr('data-options-for-select'));

    var $allows_null = element.attr('data-allows-null');
    var $autoFetch = element.attr('data-auto-fetch') == 'true' ? true : false;

    var $relatedKey = element.attr('data-connected-entity-key-name');

    var selectedOptions = [];
    //if this field is a select multiple we json parse the current value
    if ($multiple === true) {
        //if there are any selected options we re-select them
        $value = element.attr('data-current-value');
        if($value.length) {
            var $currentValue = JSON.parse(element.attr('data-current-value'));
        }else{
            var $currentValue = '';
        }

        if(!Array.isArray($currentValue) && (typeof $currentValue !== 'object' && $currentValue !== null)) {
            selectedOptions.push($currentValue);
        }else{

           for (let [key, value] of Object.entries($currentValue)) {

                selectedOptions.push(key);
            }
        }

    //we add the options to the select and check if we have some created, if yes we append to selected options
    for (const [key, value] of Object.entries($options)) {

        var $option = new Option(value, key);

        if ($created) {
            if(key == $created[$relatedKey]) {
                selectedOptions.push(key);
            }
        }

        $(element).append($option);
    }

    $(element).val(selectedOptions);

    $(element).attr('data-current-value',JSON.stringify(selectedOptions));

    }else{
        var $currentValue = element.attr('data-current-value');

        for (const [key, value] of Object.entries($options)) {

            var $option = new Option(value, key);
            $(element).append($option);
            if (key == $currentValue) {
                $(element).val(key);
            }
            if ($created) {
                //we select the created if it is on options
                if(key == $created[$relatedKey]) {
                    $(element).val(key);

                }
            }
        }
        $(element).attr('data-current-value',$(element).val());
    }

    $(element).trigger('change')
}
}

//this is just a trigger function that put's things in place, it checks if it is needed to refresh options or only display them.
if (typeof triggerSelectOptions !== "function") {
function triggerSelectOptions(element, $created = false) {
    var $fieldName = element.attr('data-original-name');

    var $inlineRefreshRoute = element.attr('data-inline-refresh-route');

    $(element).empty();

    if ($created) {
        refreshOptionList(element, $fieldName, $inlineRefreshRoute).then(result => {

            fillSelectOptions(element, $created);
        }, result => {

        });
    } else {
        fillSelectOptions(element);
    }
}
}

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

//this is the function called when button to add is pressed.

function triggerModal(element) {
    var $fieldName = element.attr('data-field-related-name');

    var modalName = '#'+$fieldName+'-inline-create-dialog';
    var $inlineCreateRoute = element.attr('data-inline-create-route');
    var $modal = $(modalName);

    $modal.modal({ backdrop: 'static', keyboard: false, focus: false });
    var $modalSaveButton = $modal.find('#saveButton');
    var $form = $(document.getElementById($fieldName+"-inline-create-form"));


    initializeFieldsWithJavascript($form);

    //when you hit save on modal save button.
    $modalSaveButton.on('click', function () {
        $form = document.getElementById($fieldName+"-inline-create-form");
        //this is needed otherwise fields like ckeditor don't post their value.
        $($form).trigger('form-pre-serialize');
        var $formData = new FormData($form);

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
                triggerSelectOptions(element, $createdEntity);

                $modal.modal('hide');
                swal({
                    title: "Related entity creation",
                    text: "Related entity created with success.",
                    icon: "success",
                    timer: 3000,
                    buttons: false,
                });
            },
            error: function (result) {
                // Show an alert with the result

                var $errors = result.responseJSON.errors;

                let message = '';
                for (var i in $errors) {
                    message += $errors[i] + ' \n';
                }

                swal({
                    title: "Creating related entity error",
                    text: message,
                    icon: "error",
                    timer: 4000,
                    buttons: false,
                });
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
                var $selectOptions = element.attr('data-options-for-select');

                var $autoFetch = element.attr('data-auto-fetch') == 'true' ? true : false;



                    //console.log($ajax);
                if(!$ajax) {
                    triggerSelectOptions(element);
                }else{
                    var $item = false;

                    var $value = JSON.parse(element.attr('data-current-value'))

                    if(Object.keys($value).length > 0) {
                        $item = true;
                    }
                    var selectedOptions = [];
                    if($item) {
                        var $currentValue = $value;
                    }else{
                        var $currentValue = '';
                    }
                    //we reselect the previously selected options if any.
                    for (const [key, value] of Object.entries($currentValue)) {
                        selectedOptions.push(key);
                        var $option = new Option(value, key);
                        $(element).append($option);
                    }

                    $(element).val(selectedOptions);

                    //if null not allowed, there is no item and developer did not provide a placeholder
                    if(element.attr('data-allows-null') != 'true' && $item === false && $autoFetch) {
                        fetchDefaultEntry(element).then(result => {
                            refreshDefaultOption(element, $fieldAttribute, $modelKey);
                        });
                    }
                }

                var $allowClear = (element.attr('data-allows-null') == 'true') ? true : false;
                //Checks if field is not beeing inserted in one inline create modal and setup buttons
                if($inlineField == "false") {

                    setupInlineCreateButtons(element);

                }

                    if (!element.hasClass("select2-hidden-accessible")) {
                        if(!$ajax) {

                        element.select2({
                            theme: "bootstrap",
                            placeholder: $placeholder,
                            allowClear: $allowClear,
                        });
                        }else{

                            element.select2({
                            theme: "bootstrap",
                            placeholder: $placeholder,
                            minimumInputLength: $minimumInputLength,
                            allowClear: $allowClear,
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

                        var result = {
                            results: $.map(data.data, function (item) {
                                textField = $fieldAttribute;
                                return {
                                    text: item[textField],
                                    id: item[$connectedEntityKeyName]
                                }
                            }),
                           pagination: {
                                 more: data.current_page < data.last_page
                           }
                        };

                        return result;
                    },
                    cache: true
                },
                        });
                        }
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
