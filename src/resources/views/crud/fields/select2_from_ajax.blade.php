@php
    $connected_entity = new $field['model'];

    $connected_entity_key_name = $connected_entity->getKeyName();
    $old_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false;
    //dd($crud->hasOperationSetting('ajaxEntities'));
    $response_entity = isset($field['response_entity']) ? $field['response_entity'] : $crud->hasOperationSetting('ajaxEntities') ? array_has($crud->getOperationSetting('ajaxEntities'), $field['entity']) ? $field['entity'] : array_key_first($crud->getOperationSetting('ajaxEntities')) : '';
    $placeholder = isset($field['placeholder']) ? $field['placeholder'] : 'Select a ' . $field['entity'];

    //this checks if column is nullable on database by default, but developer might overriden that property
    $allows_null = $crud->model::isColumnNullable($field['name']) ?
        ((isset($field['allows_null']) && $field['allows_null'] != false) || !isset($field['allows_null']) ? true : false) :
        ((isset($field['allows_null']) && $field['allows_null'] != true) || !isset($field['allows_null']) ? false : true);

    if ($old_value) {
        if(!is_object($old_value)) {
            $item = $connected_entity->find($old_value);
        }else{
            $item = $old_value;
        }

    }
@endphp

<div @include('crud::inc.field_wrapper_attributes') >
    <label>{!! $field['label'] !!}</label>

    <select
        name="{{ $field['name'] }}"
        style="width: 100%"
        data-init-function="bpFieldInitSelect2FromAjaxElement"
        data-column-nullable="{{ var_export($allows_null) }}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(array_wrap($field['dependencies'])): json_encode([]) }}"
        data-model-local-key="{{$crud->model->getKeyName()}}"
        data-placeholder="{{ $placeholder }}"
        data-minimum-input-length="{{ isset($field['minimum_input_length']) ? $field['minimum_input_length'] : 2 }}"
        data-method="{{ $field['method'] ?? 'GET' }}"
        data-data-source="{{isset($field['data_source']) ? $field['data_source'] : url($crud->route . '/fetch/' . $response_entity)}}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-item="{{ (isset($item) && !is_null($item)) ? '{ "id":"'.$item->getKey().'","text":"'.$item->{$field['attribute']} .'"}' : json_encode(false) }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ $field['include_all_form_fields'] ?? 'true' }}"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control'])
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
  // this function is responsible for fetching some default option when developer don't allow null on field
let fetchDefaultEntry = function (element) {
    var $fetchUrl = element.attr('data-data-source');

    return new Promise(function (resolve, reject) {

        $.ajax({
            url: $fetchUrl,
            data: {
                'q': ''
            },
            type: 'GET',
            success: function (result) {
                $(element).attr('data-item', JSON.stringify(result));
                resolve(result);
            },
            error: function (result) {

                reject(result);
            }
        });
    });
};

function refreshDefaultOption(element, $fieldAttribute, $modelKey) {
     var $item = JSON.parse(element.attr('data-item'));
     $(element).append('<option value="'+$item[$modelKey]+'">'+$item[$fieldAttribute]+'</option>');
}
    function bpFieldInitSelect2FromAjaxElement(element) {
        var form = element.closest('form');
        var $placeholder = element.attr('data-placeholder');
        var $minimumInputLength = element.attr('data-minimum-input-length');
        var $dataSource = element.attr('data-data-source');
        var $modelKey = element.attr('data-model-local-key');
        var $method = element.attr('data-method');
        var $fieldAttribute = element.attr('data-field-attribute');
        var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
        var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
        var $allowClear = element.attr('data-column-nullable') == 'true' ? true : false;
        var $dependencies = JSON.parse(element.attr('data-dependencies'));
    if(element.attr('data-column-nullable') != 'true') {
            fetchDefaultEntry(element).then(result => {
                refreshDefaultOption(element, $fieldAttribute, $modelKey);
            });
        }

        var $item = JSON.parse(element.attr('data-item'));

        var $allowClear = (element.attr('data-column-nullable') == true && !$item == false) ? true : false;
        var $select2Settings = {
                theme: 'bootstrap',
                multiple: false,
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
            if($item) {
                $(element).append('<option value="'+$item.id+'">'+$item.text+'</option>');
            }else if ($allowClear && !$item) {
                $(element).append('<option value="" >{{ $placeholder }}</option>');
            }
                element.on('select2:unselect', function(e) {
                   e.preventDefault();
                   $(element).append(new Option('{{ $placeholder }}', '',true,true));
                   $(element).trigger('change');
                });

            if($allowClear && !$item) {
                document.styleSheets[0].addRule('.select2-selection__clear::after','content:  "{{ trans('backpack::crud.clear') }}";');
            }
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
