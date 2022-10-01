@php
    $column['value'] = $column['value'] ?? data_get($entry, $column['name']);
    if(function_exists('enum_exists') && !empty($column['value']) && $column['value'] instanceof \UnitEnum)  {
        $column['value'] = isset($column['enum_function']) ? $column['value']->{$column['enum_function']}() : ($column['value'] instanceof \BackedEnum ? $column['value']->value : $column['value']->name);
    }
@endphp

@include(isset($column['options']) ? 'crud::columns.select_from_array' : 'crud::columns.text')