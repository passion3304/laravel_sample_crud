@php

    //in case entity is superNews we want the url friendly super-news
    $entityWithoutAttribute = $crud->getOnlyRelationEntity($field);
    $routeEntity = Str::kebab(str_replace('_', '-', $entityWithoutAttribute));

    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();

    // we need to re-ensure field type here because relationship is a `switchboard` and not actually
    // a crud field like this one.
    $field['type'] = 'fetch';

    // this field can be used as a pivot selector for n-n relationships
    $field['is_pivot_select'] = $field['is_pivot_select'] ?? false;

    $field['multiple'] = $field['multiple'] ?? $crud->guessIfFieldHasMultipleFromRelationType($field['relation_type']);
    $field['data_source'] = $field['data_source'] ?? url($crud->route.'/fetch/'.$routeEntity);
    $field['attribute'] = $field['attribute'] ?? $connected_entity->identifiableAttribute();
    $field['placeholder'] = $field['placeholder'] ?? ($field['multiple'] ? trans('backpack::crud.select_entries') : trans('backpack::crud.select_entry'));
    $field['include_all_form_fields'] = $field['include_all_form_fields'] ?? true;

    // Note: isColumnNullable returns true if column is nullable in database, also true if column does not exist.
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);

    // this is the time we wait before send the query to the search endpoint, after the user as stopped typing.
    $field['delay'] = $field['delay'] ?? 500;

    // make sure the $field['value'] takes the proper value
    // and format it to JSON, so that select2 can parse it
    $current_value = old_empty_or_null($field['name'], []) ??  $field['value'] ?? $field['default'] ?? [];
    if (!empty($current_value) || is_int($current_value)) {
        switch (gettype($current_value)) {
            case 'array':
                $current_value = $connected_entity
                                    ->whereIn($connected_entity_key_name, $current_value)
                                    ->get()
                                    ->pluck($field['attribute'], $connected_entity_key_name);
                break;

            case 'object':
            if (is_subclass_of(get_class($current_value), 'Illuminate\Database\Eloquent\Model') ) {
                    $current_value = [$current_value->{$connected_entity_key_name} => $current_value->{$field['attribute']}];
                }else{
                    if(! $current_value->isEmpty())  {
                    $current_value = $current_value
                                    ->pluck($field['attribute'], $connected_entity_key_name)
                                    ->toArray();
                    }
                }
                break;

            default:
                $current_value = $connected_entity
                                ->where($connected_entity_key_name, $current_value)
                                ->get()
                                ->pluck($field['attribute'], $connected_entity_key_name);
                break;
        }
    }
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    {{-- To make sure a value gets submitted even if the "select multiple" is empty, we need a hidden input --}}
    @if($field['multiple'])<input type="hidden" name="{{ $field['name'] }}" value="" @if(in_array('disabled', $field['attributes'] ?? [])) disabled @endif />@endif
    <select
        style="width:100%"
        name="{{ $field['name'].($field['multiple']?'[]':'') }}"
        data-init-function="bpFieldInitFetchElement"
        data-field-is-inline="{{ var_export($inlineCreate ?? false) }}"
        data-column-nullable="{{ var_export($field['allows_null']) }}"
        data-dependencies="{{ isset($field['dependencies'])?json_encode(Arr::wrap($field['dependencies'])): json_encode([]) }}"
        data-model-local-key="{{$crud->model->getKeyName()}}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-minimum-input-length="{{ isset($field['minimum_input_length']) ? $field['minimum_input_length'] : 2 }}"
        data-method="{{ $field['method'] ?? 'POST' }}"
        data-data-source="{{ $field['data_source']}}"
        data-field-attribute="{{ $field['attribute'] }}"
        data-connected-entity-key-name="{{ $connected_entity_key_name }}"
        data-include-all-form-fields="{{ var_export($field['include_all_form_fields']) }}"
        data-app-current-lang="{{ app()->getLocale() }}"
        data-ajax-delay="{{ $field['delay'] }}"
        data-language="{{ str_replace('_', '-', app()->getLocale()) }}"
        data-is-pivot-select="{{ var_export($field['is_pivot_select']) }}"

        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control'])

        @if($field['multiple'])
        multiple
        @endif
        >

        @if ($field['allows_null'] && !$field['multiple'])
            <option value="">-</option>
        @endif

        @if (!empty($current_value))
            @foreach ($current_value as $key => $item)
                <option value="{{ $key }}" selected>
                    {{ $item }}
                </option>
            @endforeach
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
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
    <!-- include select2 css-->
    <link href="{{ asset('packages/select2/dist/css/select2.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('packages/select2-bootstrap-theme/dist/select2-bootstrap.min.css') }}" rel="stylesheet" type="text/css" />

    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
    <!-- include select2 js-->
    <script src="{{ asset('packages/select2/dist/js/select2.full.min.js') }}"></script>
    @if (app()->getLocale() !== 'en')
    <script src="{{ asset('packages/select2/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js') }}"></script>
    @endif
    @endpush



<!-- include field specific select2 js-->
@push('crud_fields_scripts')
<script>
    // if nullable, make sure the Clear button uses the translated string
    document.styleSheets[0].addRule('.select2-selection__clear::after','content:  "{{ trans('backpack::crud.clear') }}";');
    /**
     * Initialize Select2 on an element that wants the "Fetch" functionality.
     * This method gets called automatically by Backpack:
     * - after the Create/Update page loads
     * - after a Fetch is inserted with JS somewhere (ex: in a modal)
     *
     * @param  node element The jQuery-wrapped "select" element.
     * @return void
     */
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
        var $allows_null = element.attr('data-column-nullable') == 'true' ? true : false;
        var $multiple = element.prop('multiple');
        var $ajaxDelay = element.attr('data-ajax-delay');
        var $isFieldInline = element.data('field-is-inline');
        var $isPivotSelect = element.data('is-pivot-select');

        var $select2Settings = {
                theme: 'bootstrap',
                multiple: $multiple,
                placeholder: $placeholder,
                minimumInputLength: $minimumInputLength,
                allowClear: $allows_null,
                dropdownParent: $isFieldInline ? $('#inline-create-dialog .modal-content') : document.body,
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

                        // if field is a pivot select, we are gonna get other pivot values,so we can disable them from selection.
                        if ($isPivotSelect) {
                            let pivots_container = $(element).closest('div[data-repeatable-holder='+$(element).data('repeatable-input-name')+']');
                            var selected_values = [];

                            $(pivots_container).children().each(function(i,container) {
                                $(container).find('select').each(function(i, el) {
                                    if(typeof $(el).attr('data-is-pivot-select') !== 'undefined' && $(el).attr('data-is-pivot-select') && $(el).val()) {
                                        selected_values.push($(el).val());
                                    }
                                });
                            });
                        }

                        //if we have data.data here it means we returned a paginated instance from controller.
                        //otherwise we returned one or more entries unpaginated.
                        if (data.data) {
                        var result = {
                            results: $.map(data.data, function (item) {
                                var $itemText = processItemText(item, $fieldAttribute);
                                let disabled = false;

                                if (selected_values && selected_values.some(e => e == item[$connectedEntityKeyName])) {
                                    disabled = true;
                                }

                                return {
                                    text: $itemText,
                                    id: item[$connectedEntityKeyName],
                                    disabled: disabled
                                }
                            }),
                           pagination: {
                                 more: data.current_page < data.last_page
                           }
                        };
                        } else {
                            var result = {
                                results: $.map(data, function (item) {
                                    var $itemText = processItemText(item, $fieldAttribute);
                                    let disabled = false;
                                    if(selected_values.some(e => e == item[$connectedEntityKeyName])) {
                                        disabled = true;
                                    }

                                    return {
                                        text: $itemText,
                                        id: item[$connectedEntityKeyName],
                                        disabled: disabled
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
            };
        if (!$(element).hasClass("select2-hidden-accessible"))
        {
            $(element).select2($select2Settings);

            // if any dependencies have been declared
            // when one of those dependencies changes value
            // reset the select2 value
            for (var i=0; i < $dependencies.length; i++) {
                var $dependency = $dependencies[i];
                //if element does not have a custom-selector attribute we use the name attribute
                if (typeof element.attr('data-custom-selector') == 'undefined') {
                    form.find('[name="'+$dependency+'"], [name="'+$dependency+'[]"]').change(function(el) {
                            $(element.find('option:not([value=""])')).remove();
                            element.val(null).trigger("change");
                    });
                } else {
                    // we get the row number and custom selector from where element is called
                    let rowNumber = element.attr('data-row-number');
                    let selector = element.attr('data-custom-selector');

                    // replace in the custom selector string the corresponding row and dependency name to match
                    selector = selector
                        .replaceAll('%DEPENDENCY%', $dependency)
                        .replaceAll('%ROW%', rowNumber);

                    $(selector).change(function (el) {
                        $(element.find('option:not([value=""])')).remove();
                        element.val(null).trigger("change");
                    });
                }
            }
        }
    }

    if (typeof processItemText !== 'function') {
        function processItemText(item, $fieldAttribute) {
            var $appLang = '{{ app()->getLocale() }}';
            var $appLangFallback = '{{ Lang::getFallback() }}';
            var $emptyTranslation = '{{ trans("backpack::crud.empty_translations") }}';
            var $itemField = item[$fieldAttribute];

            // try to retreive the item in app language; then fallback language; then first entry; if nothing found empty translation string
            return typeof $itemField === 'object' && $itemField !== null
                ? $itemField[$appLang] ? $itemField[$appLang] : $itemField[$appLangFallback] ? $itemField[$appLangFallback] : Object.values($itemField)[0] ? Object.values($itemField)[0] : $emptyTranslation
                : $itemField;
        }
    }
</script>
@endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
