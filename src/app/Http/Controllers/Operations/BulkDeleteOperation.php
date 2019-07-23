<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait BulkDeleteOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string $name       Name of the current entity (singular). Used as first URL segment.
     * @param  string $controller Name of the current CrudController.
     * @param  [type] $options    Route options (optional).
     */
    protected function setupBulkDeleteRoutes($name, $controller, $options)
    {
        Route::post($name.'/bulk-delete', [
            'as' => 'crud.'.$name.'.bulkDelete',
            'uses' => $controller.'@bulkDelete',
        ]);
    }

    /**
     * Delete multiple entries in one go.
     *
     * @return string
     */
    public function bulkDelete()
    {
        $this->crud->hasAccessOrFail('delete');
        $this->crud->setOperation('delete');

        $entries = $this->request->input('entries');
        $deletedEntries = [];

        foreach ($entries as $key => $id) {
            if ($entry = $this->crud->model->find($id)) {
                $deletedEntries[] = $entry->delete();
            }
        }

        return $deletedEntries;
    }
}
