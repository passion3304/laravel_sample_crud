<!-- select2 from ajax multiple -->
@php
    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();
    $current_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false;

    $response_entity = isset($field['response_entity']) ? $field['response_entity'] :
    ($crud->hasOperationSetting('ajaxEntities') ? (array_has($crud->getOperationSetting('ajaxEntities'), $field['entity']) ?
    $field['entity'] : array_key_first($crud->getOperationSetting('ajaxEntities'))) : '');

    $placeholder = isset($field['placeholder']) ? $field['placeholder'] : 'Select a ' . $field['entity'];

    if ($current_value !== false) {
        if(is_array($current_value)) {
            $current_value = $connected_entity->whereIn($connected_entity_key_name,$current_value)->pluck($field['attribute'],$connected_entity_key_name);
        }else{
            if(!$current_value->isEmpty()) {
                $current_value = $current_value->pluck($field['attribute'],$connected_entity_key_name)->toArray();
            }
        }
        $current_value = json_encode($current_value);
    }

$allows_null = $crud->model::isColumnNullable($field['name']) ?
        ((isset($field['allows_null']) && $field['allows_null'] != false) || !isset($field['allows_null']) ? true : false) :
        ((isset($field['allows_null']) && $field['allows_null'] != true) || !isset($field['allows_null']) ? false : true);

@endphp

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>
    @include('crud::inc.field_translatable_icon')
    <select
        name="{{ $field['name'] }}[]"
        style="width: 100%"
        id="select2_ajax_multiple_{{ $field['name'] }}"
        data-init-function="bpFieldInitSelect2FromAjaxMultipleElement"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(array_wrap($field['dependencies'])): json_encode([]) }}"
        data-placeholder="{{ $placeholder }}"
        data-data-source="{{isset($field['data_source']) ? $field['data_source'] : url($crud->route . '/fetch/' . $response_entity)}}"
        data-method="{{ $field['method'] ?? 'GET' }}"
        data-minimum-input-length="{{ isset($field['minimum_input_length']) ? $field['minimum_input_length'] : 2 }}"
        data-allows-null="{{var_export($allows_null)}}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-current-value="{{$current_value}}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-model-local-key="{{$crud->model->getKeyName()}}"
        data-include-all-form-fields="{{ $field['include_all_form_fields'] ?? 'true' }}"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control'])
        multiple>


    </select>

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
    @endpush

@endif

<!-- include field specific select2 js-->
@push('crud_fields_scripts')
<script>
document.styleSheets[0].addRule('.select2-selection__clear::after','content:  "{{ trans('backpack::crud.clear') }}";');
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

    function bpFieldInitSelect2FromAjaxMultipleElement(element) {
        var form = element.closest('form');
        var $placeholder = element.attr('data-placeholder');
        var $minimumInputLength = element.attr('data-minimum-input-length');
        var $dataSource = element.attr('data-data-source');
        var $method = element.attr('data-method');
        var $value = element.attr('data-current-value');
        var $item = false;
        if($value.length) {
            $item = true;
        }
        var $fieldAttribute = element.attr('data-field-attribute');
        var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
        var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
        var $allowClear = (element.attr('data-allows-null') == 'true' && $item) ? true : false;
        var $dependencies = JSON.parse(element.attr('data-dependencies'));


        var $modelKey = element.attr('data-model-local-key');

        var selectedOptions = [];

        if($item) {
            var $currentValue = JSON.parse(element.attr('data-current-value'));
        }else{
            var $currentValue = '';
        }

        for (const [key, value] of Object.entries($currentValue)) {
            selectedOptions.push(key);
            var $option = new Option(value, key);
            $(element).append($option);
        }
        $(element).val(selectedOptions);

        if(element.attr('data-allows-null') != 'true' && !$item) {
            fetchDefaultEntry(element).then(result => {
                refreshDefaultOption(element, $fieldAttribute, $modelKey);
            });
        }

        if (!$(element).hasClass("select2-hidden-accessible"))
        {
            $(element).select2({
                theme: 'bootstrap',
                multiple: true,
                placeholder: $placeholder,
                allowClear: $allowClear,
                minimumInputLength: $minimumInputLength,
                ajax: {
                    url: $dataSource,
                    type: $method,
                    dataType: 'json',
                    quietMillis: 250,
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

                        return {
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
                    },
                    cache: true
                },
            });
        }


                element.on('select2:unselect', function(e) {
                   e.preventDefault();
                    $elementVal = $(element).val();
                    if($elementVal == "") {
                        $(element).append('<option value="" >{{ $placeholder }}</option>');
                        $(element).trigger('change');
                    }
                    $(element).attr('data-current-value',JSON.stringify($elementVal));
                });

        // if any dependencies have been declared
        // when one of those dependencies changes value
        // reset the select2 value
        for (var i=0; i < $dependencies.length; i++) {
            $dependency = $dependencies[i];
            $('input[name='+$dependency+'], select[name='+$dependency+'], checkbox[name='+$dependency+'], radio[name='+$dependency+'], textarea[name='+$dependency+']').change(function () {
                element.val(null).trigger("change");
            });
        }
    }
</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
