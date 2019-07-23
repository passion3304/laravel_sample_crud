<?php

namespace Backpack\CRUD\app\Http\Controllers;

use Backpack\CRUD\CrudPanel;
use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

class CrudController extends BaseController
{
    use DispatchesJobs, ValidatesRequests;

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
     * @param  string $segment       Name of the current entity (singular).
     * @param  string  $routeName     Route name prefix (ends with .).
     * @param  string $controller    Name of the current controller.
     */
    public function routes($segment, $routeName, $controller)
    {
        preg_match_all('/(?<=^|;)setup([^;]+?)Routes(;|$)/', implode(';', get_class_methods($this)), $matches);

        if (count($matches[1])) {
            foreach ($matches[1] as $methodName) {
                $this->{'setup'.$methodName.'Routes'}($segment, $routeName, $controller);
            }
        }
    }
}
