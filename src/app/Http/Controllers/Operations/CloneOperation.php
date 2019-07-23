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
     * @param  [type] $options    Route options (optional).
     */
    protected function setupCloneRoutes($name, $controller, $options)
    {
        Route::post($name.'/{id}/clone', [
            'as' => 'crud.'.$name.'.clone',
            'uses' => $controller.'@clone',
        ]);

        Route::post($name.'/bulk-clone', [
            'as' => 'crud.'.$name.'.bulkClone',
            'uses' => $controller.'@bulkClone',
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

    /**
     * Create duplicates of multiple entries in the datatabase.
     *
     * @param int $id
     *
     * @return Response
     */
    public function bulkClone()
    {
        $this->crud->hasAccessOrFail('clone');
        $this->crud->setOperation('clone');

        $entries = $this->request->input('entries');
        $clonedEntries = [];

        foreach ($entries as $key => $id) {
            if ($entry = $this->crud->model->find($id)) {
                $clonedEntries[] = $entry->replicate()->push();
            }
        }

        return $clonedEntries;
    }
}
