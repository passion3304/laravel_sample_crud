<?php

namespace Backpack\CRUD\app\Http\Controllers;

use Backpack\CRUD\CrudPanel;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Backpack\CRUD\app\Http\Controllers\Operations\SaveActions;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CloneOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\RevisionsOperation;

class CrudController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;
    use CreateOperation, CloneOperation, DeleteOperation, ListOperation, ReorderOperation, RevisionsOperation, SaveActions, ShowOperation, UpdateOperation;

    public $data = [];
    public $request;

    /**
     * @var CrudPanel
     */
    public $crud;

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
     * Load routes for all operations.
     * Allow developers to load extra routes by creating a  method that starts with setupRoutesFor.
     *
     * @param  string $name       Name of the current entity (singular).
     * @param  string $controller Name of the current controller.
     * @param  array  $options    Options for the route (optional).
     */
    public function routes($name, $controller, $options = [])
    {
        $setupRouteMethods = preg_grep('/^setupRoutesFor/', get_class_methods($this));

        if (count($setupRouteMethods)) {
            foreach ($setupRouteMethods as $methodName) {
                $this->{$methodName}($name, $controller, $options);
            }
        }
    }
}
