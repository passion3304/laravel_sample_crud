<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Http\Controllers\CrudController;
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
    protected function setupInlineCreateOperationRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/inline/create', [
            'as'        => $segment.'-inline-create',
            'uses'      => $controller.'@getInlineCreateModal',
            'operation' => 'InlineCreateOperation',
        ]);
        Route::post($segment.'/inline/create', [
            'as'        => $segment.'-inline-create-save',
            'uses'      => $controller.'@storeInlineCreate',
            'operation' => 'InlineCreateOperation',
        ]);
        Route::get($segment.'/inline/refresh', [
            'as'        => $segment.'-inline-refresh-options',
            'uses'      => $controller.'@inlineRefreshOptions',
            'operation' => 'InlineCreateOperation',
        ]);
    }

    public function setupInlineCreateDefaults()
    {
        $this->crud->setOperationSetting('inline_create', true);
    }

    public function getInlineCreateModal()
    {
        if (request()->has('entity')) {
            $this->setupOperationSettings();

            return $this->getModalContent(request()->get('entity'), 'create', $this->crud->getCreateFields());
        }
    }

    public function setupOperationSettings()
    {
        if (method_exists($this, 'setupCreateOperation')) {
            $this->setupCreateOperation();
        } else {
            $this->setup();
        }

        $this->crud->applyConfigurationFromSettings('create');
    }

    public function getModalContent($entity, $action, $fields)
    {
        return view(
                'crud::fields.relationship.modal',
                [
                    'fields' => $fields,
                    'action' => $action,
                    'crud' => $this->crud,
                    'entity' => $entity,
                ]
                );
    }

    public function InlineRefreshOptions()
    {
        $this->setupOperationSettings();

        if (request()->has('field')) {
            $field = $this->crud->fields()[request()->get('field')];
            $field['entity'] = $field['entity'] ?? $field['name'];
            $field['model'] = $field['model'] ?? $this->crud->getRelationModel($field['entity']);
            $relatedModelInstance = new $field['model']();
            if ($field) {
                if (! isset($field['options'])) {
                    $options = $field['model']::all()->pluck($field['attribute'], $relatedModelInstance->getKeyName());
                } else {
                    $options = call_user_func($field['options'], $field['model']::query()->pluck($field['attribute'], $relatedModelInstance->getKeyName()));
                }
            }

            return response()->json($options);
        }
    }

    public function storeInlineCreate()
    {
        $this->setupOperationSettings();

        return $this->store();
    }
}
