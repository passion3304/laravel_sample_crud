@php
    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();
    $field['multiple'] = $field['multiple'] ?? $crud->relationAllowsMultiple($field['relation_type']);
    $field['attribute'] = $field['attribute'] ?? $connected_entity->identifiableAttribute();
    $field['include_all_form_fields'] = $field['include_all_form_fields'] ?? true;
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
    // Note: isColumnNullable returns true if column is nullable in database, also true if column does not exist.

    // this field can be used as a pivot select for n-n relationships
    $field['is_pivot_select'] = $field['is_pivot_select'] ?? false;

    if (!isset($field['options'])) {
            $field['options'] = $connected_entity::all()->pluck($field['attribute'],$connected_entity_key_name);
        } else {
            $field['options'] = call_user_func($field['options'], $field['model']::query())->pluck($field['attribute'],$connected_entity_key_name);
    }

    // make sure the $field['value'] takes the proper value
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
                    $current_value = $current_value
                                    ->pluck($field['attribute'], $connected_entity_key_name);
                    }
            break;

            case 'NULL':
                $current_value = [];

            default:
                $current_value = $connected_entity
                                ->where($connected_entity_key_name, $current_value)
                                ->get()
                                ->pluck($field['attribute'], $connected_entity_key_name);
                break;
        }
    }

    $current_value = !is_array($current_value) ? $current_value->toArray() : $current_value;

@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    {{-- To make sure a value gets submitted even if the "select multiple" is empty, we need a hidden input --}}
    @if($field['multiple'])<input type="hidden" name="{{ $field['name'] }}" value="" @if(in_array('disabled', $field['attributes'] ?? [])) disabled @endif />@endif
    <select
        style="width:100%"
        name="{{ $field['name'].($field['multiple']?'[]':'') }}"
        data-init-function="bpFieldInitRelationshipSelectElement"
        data-field-is-inline="{{var_export($inlineCreate ?? false)}}"
        data-column-nullable="{{ var_export($field['allows_null']) }}"
        data-placeholder="{{ $field['placeholder'] }}"
        data-field-multiple="{{var_export($field['multiple'])}}"
        data-language="{{ str_replace('_', '-', app()->getLocale()) }}"
        data-is-pivot-select={{var_export($field['is_pivot_select'])}}

        @include('crud::fields.inc.attributes', ['default_class' =>  'form-control'])

        @if($field['multiple'])
        multiple
        @endif
        >

        @if ($field['allows_null'] && !$field['multiple'])
            <option value="">-</option>
        @endif

        @if (count($field['options']))
            @foreach ($field['options'] as $key => $option)
            @php
                $selected = '';
                if(!empty($current_value)) {
                    if(in_array($key, array_keys($current_value))) {
                        $selected = 'selected';
                    }
                }
            @endphp
                    <option value="{{ $key }}" {{$selected}}>{{ $option }}</option>
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
     *
     * This method gets called automatically by Backpack:
     *
     * @param  node element The jQuery-wrapped "select" element.
     * @return void
     */
    function bpFieldInitRelationshipSelectElement(element) {
        var $placeholder = element.attr('data-placeholder');
        var $multiple = element.attr('data-field-multiple')  == 'false' ? false : true;
        var $allows_null = (element.attr('data-column-nullable') == 'true') ? true : false;
        var $allowClear = $allows_null;
        var $isFieldInline = element.data('field-is-inline');
        var $isPivotSelect = element.data('is-pivot-select');
        
        const changePivotOptionState = function(pivot_selector, enable = true) {
            let pivots_container = pivot_selector.closest('div[data-repeatable-holder='+pivot_selector.data('repeatable-input-name')+']');
            
            $(pivots_container).children().each(function(i,container) {
                $(container).find('select').each(function(i, el) {
                    
                    if(typeof $(el).attr('data-is-pivot-select') !== 'undefined' && $(el).attr('data-is-pivot-select')) {
                        if(pivot_selector.val()) {
                            if(enable) {
                                $(el).find('option[value='+pivot_selector.val()+']').prop('disabled',false);   
                            }else{
                                if($(el).val() !== pivot_selector.val()) {
                                    $(el).find('option[value='+pivot_selector.val()+']').prop('disabled',true);
                                }
                            }
                        }
                    }
                });
            });
        };

        const disablePreviouslySelectedPivots = function(pivot_selector) {
            let pivots_container = pivot_selector.closest('div[data-repeatable-holder='+pivot_selector.data('repeatable-input-name')+']');
            let selected_values = [];
            let select_inputs = [];
            
            $(pivots_container).children().each(function(i,container) {
                $(container).find('select').each(function(i, el) {
                    if(typeof $(el).attr('data-is-pivot-select') !== 'undefined' && $(el).attr('data-is-pivot-select')) {
                        select_inputs.push(el);
                        if($(el).val()) {
                            selected_values.push($(el).val());
                        }
                    }
                });
            });

            select_inputs.forEach(function(input) {
                selected_values.forEach(function(value) {
                    if(value !== $(input).val()) {
                        $(input).find('option[value='+value+']').prop('disabled',true);
                    }
                });
            });
        };

        var $select2Settings = {
                theme: 'bootstrap',
                multiple: $multiple,
                placeholder: $placeholder,
                allowClear: $allowClear,
                dropdownParent: $isFieldInline ? $('#inline-create-dialog .modal-content') : document.body
            };
        if (!$(element).hasClass("select2-hidden-accessible"))
        {
            $(element).select2($select2Settings);
            disablePreviouslySelectedPivots($(element));
        }

        if($isPivotSelect) {
            $(element).on('select2:selecting', function(e) {
                if($(this).val()) {
                    changePivotOptionState($(this)); 
                }
                return true;
            });

            $(element).on('select2:select', function(e) {
                changePivotOptionState($(this), false);
                return true;
            });

            $(element).on('backpack_field.deleted', function(e) {
                changePivotOptionState($(this));
                    return true;
                });
        }

    }
</script>
@endpush
@endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
