<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait CloneOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string $name       Name of the current entity (singular). Used as first URL segment.
     * @param  string $controller Name of the current CrudController.
     */
    protected function setupCloneRoutes($name, $controller)
    {
        Route::post($name.'/{id}/clone', [
            'as' => 'crud.'.$name.'.clone',
            'uses' => $controller.'@clone',
        ]);
    }

    /**
     * Create a duplicate of the current entry in the datatabase.
     *
     * @param int $id
     *
     * @return Response
     */
    public function clone($id)
    {
        $this->crud->hasAccessOrFail('clone');
        $this->crud->setOperation('clone');

        $clonedEntry = $this->crud->model->findOrFail($id)->replicate();

        return (string) $clonedEntry->push();
    }
}
