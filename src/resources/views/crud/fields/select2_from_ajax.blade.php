<!-- select2 from ajax -->
@php
    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();
    $old_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false;
    // by default set ajax query delay to 500ms
    // this is the time we wait before send the query to the search endpoint, after the user as stopped typing.
    $field['delay'] = $field['delay'] ?? 500;
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <?php $entity_model = $crud->model; ?>

    <select
        name="{{ $field['name'] }}"
        style="width: 100%"
        data-init-function="bpFieldInitSelect2FromAjaxElement"
        data-column-nullable="{{ $entity_model::isColumnNullable($field['name'])?'true':'false' }}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(Arr::wrap($field['dependencies'])): json_encode([]) }}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-minimum-input-length="{{ $field['minimum_input_length'] }}"
        data-data-source="{{ $field['data_source'] }}"
        data-method="{{ $field['method'] ?? 'GET' }}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ isset($field['include_all_form_fields']) ? ($field['include_all_form_fields'] ? 'true' : 'false') : 'true' }}"
        data-ajax-delay="{{ $field['delay'] }}"
        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control'])
        >

        @if ($old_value)
            @php
                $item = $connected_entity->find($old_value);
            @endphp
            @if ($item)

            {{-- allow clear --}}
            @if ($entity_model::isColumnNullable($field['name']))
            <option value="" selected>
                {{ $field['placeholder'] }}
            </option>
            @endif

            <option value="{{ $item->getKey() }}" selected>
                {{ $item->{$field['attribute']} }}
            </option>
            @endif
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')

{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        <!-- select2_from_ajax field type css -->
        @loadCssOnce('packages/select2/dist/css/select2.min.css')
        @loadCssOnce('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css')
        {{-- allow clear --}}
        @if ($entity_model::isColumnNullable($field['name']))
        <style type="text/css">
            .select2-selection__clear::after {
                content: ' {{ trans('backpack::crud.clear') }}';
            }
        </style>
        @endif
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <!-- select2_from_ajax field type js -->
        @loadJsOnce('packages/select2/dist/js/select2.full.min.js')
        @if (app()->getLocale() !== 'en')
            @loadJsOnce('packages/select2/dist/js/i18n/' . app()->getLocale() . '.js')
        @endif
        @loadOnce('bpFieldInitSelect2FromAjaxElement')
        <script>
            if (!window.fetchSingleEntry) {
                var fetchSingleEntry = function (element) {
                    var $fetchUrl = element.attr('data-data-source');
                    var $relatedAttribute = element.attr('data-field-attribute');
                    var $relatedKeyName = element.attr('data-connected-entity-key-name');
                    var $value = element.attr('data-value');
                    var $return = {};

                    return new Promise(function (resolve, reject) {
                        $.ajax({
                            url: $fetchUrl + '/single',
                            data: {
                                'q': $value
                            },
                            type: 'POST',
                            success: function (result) {

                                $key = result[$relatedKeyName];
                                $value = result[$relatedAttribute];
                                $pair = { [$relatedKeyName] : $key, [$relatedAttribute] : $value}
                                $return = {...$return, ...$pair};

                                $(element).attr('data-current-value', JSON.stringify($return));

                                resolve(result);
                            },
                            error: function (result) {

                                reject(result);
                            }
                        });
                    });
                };
            }
            function bpFieldInitSelect2FromAjaxElement(element) {
                var form = element.closest('form');
                var $placeholder = element.attr('data-placeholder');
                var $minimumInputLength = element.attr('data-minimum-input-length');
                var $dataSource = element.attr('data-data-source');
                var $method = element.attr('data-method');
                var $fieldAttribute = element.attr('data-field-attribute');
                var $connectedEntityKeyName = element.attr('data-connected-entity-key-name');
                var $includeAllFormFields = element.attr('data-include-all-form-fields')=='false' ? false : true;
                var $allowClear = element.attr('data-column-nullable') == 'true' ? true : false;
                var $dependencies = JSON.parse(element.attr('data-dependencies'));
                var $ajaxDelay = element.attr('data-ajax-delay');


                if (!$(element).hasClass("select2-hidden-accessible"))
                {
                    $(element).select2({
                        theme: 'bootstrap',
                        multiple: false,
                        placeholder: $placeholder,
                        minimumInputLength: $minimumInputLength,
                        allowClear: $allowClear,
                        ajax: {
                            url: $dataSource,
                            type: $method,
                            dataType: 'json',
                            delay: $ajaxDelay,
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
                $value = element.attr('data-value');
                if (typeof $value !== typeof undefined && $value !== false) {
                    fetchSingleEntry(element).then(result => {
                        var $item = JSON.parse(element.attr('data-current-value'));
                        $(element).append('<option value="'+$item[$connectedEntityKeyName]+'">'+$item[$fieldAttribute]+'</option>');
                        $(element).val($item[$connectedEntityKeyName]);
                        $(element).trigger('change');
                    });
                }
            }
        </script>
        @endLoadOnce

    @endpush

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
