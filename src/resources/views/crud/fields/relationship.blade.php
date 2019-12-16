<!--  relationship  -->

@php
   $field['multiple'] = $field['multiple'] ?? $crud->relationAllowsMultiple($field['relation_type']);
   $field['ajax'] = $field['ajax'] ?? config('backpack.crud.relationships.default_ajax');
   //dd($field);
   $fieldOnTheFlyConfiguration = $field['on_the_fly'] ?? [];

   $current_value = old($field['name']) ?? $field['value'] ?? $field['default'] ?? '';

$related_model_instance = new $field['model']();




if ($crud->model::isColumnNullable($field['name'])) {
    $allows_null = isset($field['allows_null']) ? $field['allows_null'] : true;
}else {
    $allows_null = isset($field['allows_null']) ? $field['allows_null'] : false;
}


$options = [];
   if($field['ajax'] != true) {
    if (!isset($field['options'])) {

    $options = $field['model']::all()->pluck($field['attribute'],$related_model_instance->getKeyName());
} else {
    $options = call_user_func($field['options'], $field['model']::query()->pluck($field['attribute'],$related_model_instance->getKeyName()));
}
   }


//we make sure on_the_fly operation is setup and that user wants to allow field creation
$activeOnTheFlyCreate = $crud->has($crud->getOperation().'.on_the_fly') ?
isset($fieldOnTheFlyConfiguration['create']) ? $fieldOnTheFlyConfiguration['create'] : true : false;

if($activeOnTheFlyCreate) {
    //if user don't specify 'entity_route' we assume it's the same from $field['entity']
    $onTheFlyEntity = isset($fieldOnTheFlyConfiguration['entity_route']) ? $fieldOnTheFlyConfiguration['entity_route'] : $field['entity'];

if(!isset($onTheFly)) {
    $createRoute = route($onTheFlyEntity."-on-the-fly-create");

    $updateRoute = route($onTheFlyEntity."-on-the-fly-update");
    $createRouteEntity = explode('/',$crud->route)[1];

    $refreshRoute = route($createRouteEntity."-on-the-fly-refresh-options");

}else{
    $activeOnTheFlyCreate = false;
    $activeOnTheFlyUpdate = false;
}
}
@endphp

<div @include('crud::inc.field_wrapper_attributes') >

        <label>{!! $field['label'] !!}</label>
        @include('crud::inc.field_translatable_icon')
        @if($activeOnTheFlyCreate)
            @include('crud::inc.on_the_fly_create_button', ['name' => $field['name'], 'onTheFlyEntity' => $onTheFlyEntity])
        @endif
<select
        name="{{ $field['name'] }}"
        data-original-name="{{ $field['name'] }}"
        style="width: 100%"
        data-init-function="bpFieldInitRelationshipElement"
        data-is-on-the-fly="{{ $onTheFly ?? 'false' }}"
        data-field-multiple=""
        data-options-for-select="{{json_encode($options)}}"
        data-allows-null="{{var_export($allows_null)}}"
        data-current-value="{{$current_value}}"
        @if($activeOnTheFlyCreate)
        @include('crud::inc.on_the_fly_field_attributes')
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


// this function is responsible for reloading the option list uppon on-the-fly creation.
let refreshOptionList = function (element, $field, $refreshUrl) {
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


// this is the function responsible for displaying the select options.

function fillSelectOptions(element, $created = false, $multiple = false) {

    var $options = JSON.parse(element.attr('data-options-for-select'));

    var $allows_null = element.attr('data-allows-null');

    var $relatedKey = element.attr('data-on-the-fly-related-key');

    //used to check if after a related creation the created entity is still available in options
    var $createdIsOnOptions = false;

    //if this field is a select multiple we json parse the current value
    if ($multiple == true) {

        var $currentValue = JSON.parse(element.attr('data-current-value'));

        var selectedOptions = [];

        //if there are any selected options we re-select them
        for (const [key, value] of Object.entries($currentValue)) {
        selectedOptions.push(key);
    }

    //we add the options to the select and check if we have some created, if yes we append to selected options
    for (const [key, value] of Object.entries($options)) {
        var $option = new Option(value, key);

        if ($created) {
            if(key == $created[$relatedKey]) {
                $createdIsOnOptions = true;
                selectedOptions.push(key);
            }
        }

        $(element).append($option);
    }

    $(element).val(selectedOptions);

    }else{
        var $currentValue = element.attr('data-current-value');

        for (const [key, value] of Object.entries($options)) {

            var $option = new Option(value, key);
            $(element).append($option);
            if (key == $currentValue) {
                $(element).val(key);
            }
            if ($created) {
                //we check if created is presented in the available options, might not be based on some model constrain (like active() scope)
                if(key == $created[$relatedKey]) {
                    $createdIsOnOptions = true;
                    $(element).val(key);
                }

         }
    }
    }
    if ($allows_null == 'true' && $multiple == false && ($currentValue == '' || (Array.isArray($currentValue) && $currentValue.length)) && $createdIsOnOptions == false) {
        var $option = new Option('-', '');
        $(element).prepend($option);
        if (($currentValue == '' || (Array.isArray($currentValue) && $currentValue.length))) {
            $(element).val('');
        }
    }
    if($allows_null == 'false' && ($currentValue == '' || (Array.isArray($currentValue) && $currentValue.length)) && $createdIsOnOptions == false) {
        $(element).val(Object.keys($options)[0]);

    }

    $(element).trigger('change')
}

//this is just a trigger function that put's things in place, it checks if it is needed to refresh options or only display them.
function triggerSelectOptions(element, $created = false, $multiple = false) {
    var $fieldName = element.attr('data-original-name');




    var $onTheFlyRefreshRoute = element.attr('data-on-the-fly-refresh-route');

    $(element).empty();

    if ($created) {
        refreshOptionList(element, $fieldName, $onTheFlyRefreshRoute).then(result => {

            fillSelectOptions(element, $created, $multiple);
        }, result => {

        });
    } else {
        fillSelectOptions(element, $created, $multiple);
    }
}

function setupOnTheFlyButtons(element) {
    var $onTheFlyCreateButton = element.attr('data-on-the-fly-create-button');
    var $fieldEntity = element.attr('data-field-related-name');
    var $onTheFlyCreateButtonElement = $(document.getElementById($onTheFlyCreateButton));
    var $onTheFlyCreateRoute = element.attr('data-on-the-fly-create-route');

    $onTheFlyCreateButtonElement.on('click', function () {
        $(".loading_modal_dialog").show();
        $.ajax({
            url: $onTheFlyCreateRoute,
            data: {
                'entity': $fieldEntity
            },
            type: 'GET',
            success: function (result) {
                $('body').append(result);
                triggerModal(element, urls);

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
    var $multiple = (element.attr('data-field-multiple') === 'true');
    var modalName = '#'+$fieldName+'-on-the-fly-create-dialog';
    var $onTheFlyCreateRoute = element.attr('data-on-the-fly-create-route');
    var $modal = $(modalName);

    $modal.modal({ backdrop: 'static', keyboard: false });
    var $modalSaveButton = $modal.find('#saveButton');
    var $form = $(document.getElementById($fieldName+"-on-the-fly-create-form"));


    initializeFieldsWithJavascript($form);

    //when you hit save on modal save button.
    $modalSaveButton.on('click', function () {
        var $formData = new FormData(document.getElementById($fieldName+"-on-the-fly-create-form"));

        var loadingText = '<i class="fa fa-circle-o-notch fa-spin"></i> loading...';
        if ($modalSaveButton.html() !== loadingText) {
            $modalSaveButton.data('original-text', $(this).html());
            $modalSaveButton.html(loadingText);
            $modalSaveButton.prop('disabled', true);
        }


        $.ajax({
            url: $onTheFlyCreateRoute,
            data: $formData,
            processData: false,
            contentType: false,
            type: 'POST',
            success: function (result) {

                $createdEntity = result.data;
                triggerSelectOptions(element, $createdEntity,$multiple);

                $modal.modal('hide');
                swal({
                    title: "Related entity creation",
                    text: "Related entity created with success.",
                    icon: "success",
                    timer: 4000,
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
                    // element will be a jQuery wrapped DOM node
                    var $onTheFlyField = element.attr('data-is-on-the-fly');

                var $selectOptions = element.attr('data-options-for-select');

                triggerSelectOptions(element);

                //Checks if field is not beeing inserted in one on-the-fly modal and setup buttons
                if($onTheFlyField == "false") {

                    setupOnTheFlyButtons(element);

                }
                    if (!element.hasClass("select2-hidden-accessible")) {
                        element.select2({
                            theme: "bootstrap"
                        });
                    }
                }
            </script>
        @endpush

    @endif
    {{-- End of Extra CSS and JS --}}
    {{-- ########################################## --}}
