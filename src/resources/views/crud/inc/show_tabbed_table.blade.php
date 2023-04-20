@if(count($columns))
    <div class="card no-padding no-border mb-0">
        <table class="table table-striped mb-0">
            <tbody>
            @foreach ($columns as $column)
                @include('crud::inc.show_tabbed_column')
            @endforeach
            @if ($crud->buttons()->where('stack', 'line')->count())
                <tr>
                    <td><strong>{{ trans('backpack::crud.actions') }}</strong></td>
                    <td>
                        @include('crud::inc.button_stack', ['stack' => 'line'])
                    </td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endif