@php
    // this is passed by columns like select and select_multiple
    $related_key = $related_key ?? null;

    // each wrapper attribute can be a callback or a string
    // for those that are callbacks, run the callbacks to get the final string to use
    foreach($column['wrapper'] as $attribute => $value) {
        $column['wrapper'][$attribute] = is_callable($value) ? $value($crud, $column, $entry, $related_key) : $value ?? '';
    }

    // if the wrapper "element" is not defined, set it to the default - an anchor
    if(!empty($column['wrapper'])) {
        $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    }

    // we need this $text variable in case column is a select, because we include the wrapper in
    // all selected entries and not only once in the column.
    $column['text'] = $text ?? $column['text'] ?? '-';

@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapperStart')
    @if($column['escaped'])
        {{ $column['text'] }}
    @else
        {!! $column['text'] !!}
    @endif
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapperEnd')
