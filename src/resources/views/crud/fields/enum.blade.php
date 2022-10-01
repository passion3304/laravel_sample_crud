{{-- enum --}}
@php
    $entity_model = $field['model'] ?? $crud->model;
    $field['value'] = old_empty_or_null($field['name'], '') ??  $field['value'] ?? $field['default'] ?? '';

    $possible_values = (function() use ($entity_model, $field) {
        // if developer provided the options, use them, no ned to guess.
        if(isset($field['options'])) {
            return $field['options'];
        }

        // if we are in a PHP version where PHP enums are not available, it can only be a database enum
        if(! function_exists('enum_exists')) {
            $possibilities = $entity_model::getPossibleEnumValues($field['name']);
            return array_combine($possibilities, $possibilities);
        }

        // developer can provide the enum class so that we extract the available options from it
        if(isset($field['enum_class'])) {
            if($field['enum_class'] instanceof \BackedEnum) {
                return array_column($field['enum_class']::cases(), 'value', 'name');
            }
            return array_column($field['enum_class']::cases(), 'name');
        }

        // check for model casting, in this case it must be a BakedEnum to work with Laravel casting
        $possibleEnumCast = (new $entity_model)->getCasts()[$field['name']] ?? false;
        if($possibleEnumCast && class_exists($possibleEnumCast)) {
            return array_column((new $entity_model)->getCasts()[$field['name']]::cases(), 'value', 'name');
        }

        $possibilities = $entity_model::getPossibleEnumValues($field['name']);
        return array_combine($possibilities, $possibilities);
    })();
    

    if(function_exists('enum_exists') && !empty($field['value']) && $field['value'] instanceof \UnitEnum)  {
        $field['value'] = $field['value'] instanceof \BackedEnum ? $field['value']->value : $field['value']->name;
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
                @foreach ($possible_values as $key => $possible_value)
                    <option value="{{ $key }}"
                        @if ($field['value']==$key)
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
