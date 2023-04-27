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