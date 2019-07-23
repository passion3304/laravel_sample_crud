<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait BulkDeleteOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string $segment       Name of the current entity (singular). Used as first URL segment.
     * @param  string $routeName    Prefix of the route name.
     * @param  string $controller Name of the current CrudController.
     */
    protected function setupBulkDeleteRoutes($segment, $routeName, $controller)
    {
        Route::post($segment.'/bulk-delete', [
            'as' => $routeName.'bulkDelete',
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
