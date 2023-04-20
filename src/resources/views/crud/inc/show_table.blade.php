@if(count($columns))
    <div class="card no-padding no-border mb-0">
        <table class="table table-striped mb-0">
            <tbody>
            @foreach($columns as $column)
                <tr>
                    <td>
                        <strong>{!! $column['label'] !!}:</strong>
                    </td>
                    <td>
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
                    </td>
                </tr>
            @endforeach
            @if($crud->buttons()->where('stack', 'line')->count())
                <tr>
                    <td>
                        <strong>{{ trans('backpack::crud.actions') }}</strong>
                    </td>
                    <td>
                        @include('crud::inc.button_stack', ['stack' => 'line'])
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endif