<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Route;

trait OnTheFlyOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param string $segment    Name of the current entity (singular). Used as first URL segment.
     * @param string $routeName  Prefix of the route name.
     * @param string $controller Name of the current CrudController.
     */
    protected function setupOnTheFlyRoutes($segment, $routeName, $controller)
    {
        Route::get($segment.'/on-the-fly/create', [
            'as'        => $segment.'-on-the-fly-create',
            'uses'      => $controller.'@getInstantCreateModal',
            'operation' => 'OnTheFlyOperation',
        ]);
        Route::post($segment.'/on-the-fly/create', [
            'as'        => $segment.'-on-the-fly-create',
            'uses'      => $controller.'@storeOnTheFly',
            'operation' => 'OnTheFlyOperation',
        ]);
        Route::get($segment.'/on-the-fly/refresh', [
            'as'        => $segment.'-on-the-fly-refresh-options',
            'uses'      => $controller.'@refreshOptions',
            'operation' => 'OnTheFlyOperation',
        ]);
    }

    public function setupOnTheFlyDefaults()
    {
        $this->crud->setOperationSetting('on_the_fly', true);
    }

    public function getInstantCreateModal()
    {
        if (request()->has('entity')) {

            $this->setupOperationSettings();

            return $this->getInstantModal(request()->get('entity'), 'create', $this->crud->getCreateFields());
        }
    }

    public function setupOperationSettings() {
        if(method_exists($this,'setupCreateOperation')) {
            $this->setupCreateOperation();
        }else{
            $this->setup();
        }

        $this->crud->applyConfigurationFromSettings('create');
    }
    public function getInstantModal($entity, $action, $fields)
    {
        return view(
                'crud::inc.on_the_fly_modal',
                [
                    'fields' => $fields,
                    'action' => $action,
                    'crud' => $this->crud,
                    'entity' => $entity,
                ]
                );
    }

    public function refreshOptions()
    {
        $this->setupOperationSettings();

        if (request()->has('field')) {
            $field = $this->crud->fields()[request()->get('field')];
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

    public function storeOnTheFly()
    {
        $this->setupOperationSettings();
        return $this->store();
    }
}
