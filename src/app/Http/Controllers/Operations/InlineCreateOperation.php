<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait InlineCreateOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupInlineCreateRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/inline/create', [
            'as'        => $segment.'-inline-create',
            'uses'      => $controller.'@getInlineCreateModal',
            'operation' => 'InlineCreate',
        ]);
        Route::post($segment.'/inline/create', [
            'as'        => $segment.'-inline-create-save',
            'uses'      => $controller.'@storeInlineCreate',
            'operation' => 'InlineCreate',
        ]);
        Route::get($segment.'/inline/refresh', [
            'as'        => $segment.'-inline-refresh-options',
            'uses'      => $controller.'@inlineRefreshOptions',
            'operation' => 'InlineCreate',
        ]);
    }

    /**
     * Setup operation default settings. We run setup() and setupCreateOperation() because those are run in middleware
     * and to get the fields we need them earlier in application lifecycle.
     */
    protected function setupInlineCreateDefaults()
    {
        // this only mark this operation as active
        $this->crud->setOperationSetting('inline_create', true);

        if (method_exists($this, 'setupCreateOperation')) {
            if (method_exists($this, 'setup')) {
                $this->setup();
            }
            $this->setupCreateOperation();
        } else {
            $this->setup();
        }
        $this->crud->applyConfigurationFromSettings('create');
    }

    /**
     * Returns the HTML of the create form. It's used by the CreateInline operation, to show that form
     * inside a popup (aka modal).
     */
    public function getInlineCreateModal()
    {
        if (request()->has('entity')) {
            return view(
                'crud::fields.relationship.modal',
                [
                    'fields' => $this->crud->getCreateFields(),
                    'action' => 'create',
                    'crud' => $this->crud,
                    'entity' => request()->get('entity'),
                ]
                );
        }
    }

    /**
     * This function is called after a related entity is added so we refresh the options in the select.
     * By query constrains the newly added option might not be available to select.
     *
     * This is not run by ajax fields.
     */
    public function inlineRefreshOptions()
    {
        if (request()->has('field')) {
            $field = $this->crud->fields()[request()->get('field')];

            $options = [];

            if (! empty($field)) {
                $relatedModelInstance = new $field['model']();

                if (! isset($field['options'])) {
                    $options = $field['model']::all()->pluck($field['attribute'], $relatedModelInstance->getKeyName());
                } else {
                    $options = call_user_func($field['options'], $field['model']::query()->pluck($field['attribute'], $relatedModelInstance->getKeyName()));
                }
            }

            return response()->json($options);
        }
    }

    /**
     * Runs the store() function in controller like a regular crud create form.
     * Developer might overwrite this if he wants some custom save behaviour when added on the fly.
     *
     * @return void
     */
    public function storeInlineCreate()
    {
        return $this->store();
    }
}
