@php

    //in case entity is superNews we want the url friendly super-news
    $routeEntity = strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $field['entity']));

    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();

    $field['multiple'] = $field['multiple'] ?? $crud->relationAllowsMultiple($field['relation_type']);
    $field['data_source'] = $field['data_source'] ?? url($crud->route.'/fetch/'.$routeEntity);
    $field['attribute'] = $field['attribute'] ?? $connected_entity->getIdentifiableName();
    $field['placeholder'] = $field['placeholder'] ?? $field['multiple'] ? 'Select entities' : 'Select an entity';
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
    // Note: isColumnNullable returns true if column is nullable in database, also true if column does not exist.

    $current_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '';

    if ($current_value != false) {
        if(is_array($current_value)) {
            $current_value = $connected_entity
                                    ->whereIn($connected_entity_key_name, $current_value)
                                    ->pluck($field['attribute'], $connected_entity_key_name);
        } elseif (is_object($current_value)) {
            if(! $current_value->isEmpty()) 
            {
                $current_value = $current_value
                                    ->pluck($field['attribute'], $connected_entity_key_name)
                                    ->toArray();
            }
        } else {
            $current_value = $connected_entity
                                ->where($connected_entity_key_name, $current_value)
                                ->pluck($field['attribute'], $connected_entity_key_name);
        }
    }

    $current_value = json_encode($current_value);
@endphp

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>

    <select
    @if(!$field['multiple'])
        name="{{ $field['name'] }}"
        @else
        name="{{ $field['name'] }}[]"
        @endif

        data-init-function="bpFieldInitFetchElement"
        data-column-nullable="{{ var_export($field['allows_null']) }}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(array_wrap($field['dependencies'])): json_encode([]) }}"
        data-model-local-key="{{$crud->model->getKeyName()}}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-minimum-input-length="{{ isset($field['minimum_input_length']) ? $field['minimum_input_length'] : 2 }}"
        data-method="{{ $field['method'] ?? 'POST' }}"
        data-data-source="{{ $field['data_source']}}"
        data-field-attribute="{{ $field['attribute'] ?? $field['model']::getIdentifiableName() }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ $field['include_all_form_fields'] ?? 'true' }}"
        data-current-value="{{$current_value}}"
        data-field-multiple="{{var_export($field['multiple'])}}"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control'])
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
                type: 'POST',
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
    function bpFieldInitFetchElement(element) {
        var form = element.closest('form');
        var $placeholder = element.attr('data-placeholder');
        var $minimumInputLength = element.attr('data-minimum-input-length');
        var $dataSource = element.attr('data-data-source');
        var $modelKey = element.attr('data-model-local-key');
        var $method = element.attr('data-method');
        var $fieldAttribute = element.attr('data-field-attribute');
        var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
        var $includeAllFormFields = element.attr('data-include-all-form-fields') == 'false' ? false : true;
        var $dependencies = JSON.parse(element.attr('data-dependencies'));
        var $multiple = element.attr('data-field-multiple')  == 'false' ? false : true;

        var $item = false;

        var $value = JSON.parse(element.attr('data-current-value'))

        if(Object.keys($value).length > 0) {
            $item = true;
        }


        if($item) {
            var $currentValue = $value;
        }else{
            var $currentValue = '';
        }

        //we reselect the previously selected options if any.
        var selectedOptions = [];

        for (const [key, value] of Object.entries($currentValue)) {
            selectedOptions.push(key);
            var $option = new Option(value, key);
            $(element).append($option);
        }

        $(element).val(selectedOptions);


        if(element.attr('data-column-nullable') != 'true' && $item === false) {
            fetchDefaultEntry(element).then(result => {
                refreshDefaultOption(element, $fieldAttribute, $modelKey);
            });
        }

        var $allowClear = (element.attr('data-column-nullable') == 'true') ? true : false;

        var $select2Settings = {
                theme: 'bootstrap',
                multiple: $multiple,
                placeholder: $placeholder,
                minimumInputLength: $minimumInputLength,
                allowClear: $allowClear,
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
            };
        if (!$(element).hasClass("select2-hidden-accessible"))
        {
            $(element).select2($select2Settings);
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
    }
</script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
