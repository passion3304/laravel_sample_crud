@php
    //this is passed by columns like select and select_multiple
    $related_model_key = $related_model_key ?? null;

    //if the wrapper attribute is callable we run the callback
    foreach($column['wrapper'] as $attribute => $value) {
        $attributeValue = is_callable($value) ? $value($crud, $column, $entry, $related_model_key) : ($value ?? '');
        $column['wrapper'][$attribute] = $attributeValue;
    }

    //setup defaults if wrapper is not empty, case empty we just want to display the text.
    if(!empty($column['wrapper'])) {
        $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    }

    //we need this $text variable in case column is a select, because we include the wrapper in
    //all selected entries and not only once in the column.
    $column['text'] = $text ?? ($column['text'] ?? '-');

@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapperStart')
    @if($column['escaped'])
        {{ $column['text'] }}
    @else
        {!! $column['text'] !!}
    @endif
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapperEnd')
