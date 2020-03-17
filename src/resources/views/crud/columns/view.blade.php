@php
    if(!empty($column['wrapper'])) {
        $column['wrapper']['element'] = $column['wrapper']['element'] ?? 'a';
    }
@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
    @include($column['view'])
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')
