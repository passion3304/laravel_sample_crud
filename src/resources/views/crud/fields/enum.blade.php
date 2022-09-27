{{-- enum --}}
@php
    $entity_model = $crud->model;
    $field['value'] = old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '';

    // check if enums are enabled in PHP version. 
    if (function_exists('enum_exists')) {
        if(!empty($field['value']) && $field['value'] instanceof \UnitEnum)  {
            $possible_values = array_column($field['value']->cases(), 'value', 'name');
            $field['value'] = isset($field['enum_function']) ? $field['value']->{$field['enum_function']}() : ($field['value'] instanceof \BackedEnum ? $field['value']->value : $field['value']->name);
        }else{
            // check if enum field is casted to a class otherwise it's a database enum
            $possibleEnumCast = (new $entity_model)->getCasts()[$field['name']] ?? false;
            if($possibleEnumCast && class_exists($possibleEnumCast)) {
                $possible_values = array_column((new $entity_model)->getCasts()[$field['name']]::cases(), 'value', 'name');
            }else{
                $possible_values = $entity_model::getPossibleEnumValues($field['name']);
            }
        }
    }else{
        $possible_values = $entity_model::getPossibleEnumValues($field['name']);
    }
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <select
        name="{{ $field['name'] }}"
        @include('crud::fields.inc.attributes')
        >

        @if ($entity_model::isColumnNullable($field['name']))
            <option value="">-</option>
        @endif

            @if (count($possible_values))
                @foreach ($possible_values as $possible_value)
                    <option value="{{ $possible_value }}"
                        @if ($field['value']==$possible_value)
                            selected
                        @endif
                    >{{ $possible_value }}</option>
                @endforeach
            @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')
