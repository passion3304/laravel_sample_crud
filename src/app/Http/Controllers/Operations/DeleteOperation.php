<?php

namespace Backpack\CRUD\app\Http\Controllers\Operations;

use Illuminate\Support\Facades\Route;

trait DeleteOperation
{
    /**
     * Define which routes are needed for this operation.
     *
     * @param  string $segment       Name of the current entity (singular). Used as first URL segment.
     * @param  string $routeName    Prefix of the route name.
     * @param  string $controller Name of the current CrudController.
     */
    protected function setupDeleteRoutes($segment, $routeName, $controller)
    {
        Route::delete($segment.'/{id}', [
            'as' => $routeName.'destroy',
            'uses' => $controller.'@destroy',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return string
     */
    public function destroy($id)
    {
        $this->crud->hasAccessOrFail('delete');
        $this->crud->setOperation('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        return $this->crud->delete($id);
    }
}
