@php
    // create a list of paths to column blade views
    // including the configured view_namespaces
    $columnPaths = array_map(function($item) use ($column) {
        return $item.'.'.$column['type'];
    }, \Backpack\CRUD\ViewNamespaces::getFor('columns'));

    // but always fall back to the stock 'text' column
    // if a view doesn't exist
    if (!in_array('crud::columns.text', $columnPaths)) {
        $columnPaths[] = 'crud::columns.text';
    }
@endphp
@includeFirst($columnPaths)