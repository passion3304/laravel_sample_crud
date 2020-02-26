@php
    //this is passed by columns like select and select_multiple
    $related_model_key = $related_model_key ?? null;

    //if the wrapper attribute is callable we run the callback
    foreach($column['wrapper'] as $attribute => $value) {
        $attributeValue = is_callable($value) ? $value($crud, $column, $entry, $related_model_key) : ($value ?? '');
        $column['wrapper'][$attribute] = $attributeValue;
    }

    //setup defaults
    $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';

@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapperStart')
    @if($column['escaped'])
        {{ $text }}
    @else
        {!! $text !!}
    @endif
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapperEnd')
