<?php

namespace Backpack\CRUD\app\Http\Controllers;

use Backpack\CRUD\CrudPanel;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Backpack\CRUD\app\Http\Controllers\Operations\Show;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Backpack\CRUD\app\Http\Controllers\Operations\Create;
use Backpack\CRUD\app\Http\Controllers\Operations\Update;
use Backpack\CRUD\app\Http\Controllers\Operations\Reorder;
use Backpack\CRUD\app\Http\Controllers\Operations\Revisions;
use Backpack\CRUD\app\Http\Controllers\Operations\ListEntries;
use Backpack\CRUD\app\Http\Controllers\Operations\SaveActions;

class CrudController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;
    use Create, Update, ListEntries, Show, Reorder, Revisions, SaveActions;

    public $data = [];

    /**
     * @var CrudPanel
     */
    public $crud;

    public $request;

    public function __construct()
    {
        if (! $this->crud) {
            $this->crud = app()->make(CrudPanel::class);

            // call the setup function inside this closure to also have the request there
            // this way, developers can use things stored in session (auth variables, etc)
            $this->middleware(function ($request, $next) {
                $this->request = $request;
                $this->crud->request = $request;
                $this->setup();

                return $next($request);
            });
        }
    }

    /**
     * Allow developers to set their configuration options for a CrudPanel.
     */
    public function setup()
    {
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

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        return $this->crud->delete($id);
    }
}
