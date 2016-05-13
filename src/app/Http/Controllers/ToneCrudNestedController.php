<?php
/**
 * The Crud Controller
 */
namespace Backpack\Crud\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Backpack\Crud\Crud;

/**
 * The controller that handles all the crud actions
 */
class CrudNestedController extends BaseController
{
    protected $crud;
    protected $data;

    public function __construct()
    {
        $this->crud = new Crud();
        $this->data = [];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($parentId)
    {
        // dd($this->crud);
        $this->crud->checkPermission('list');

        return view('crud::layouts.list', $this->data + ['crud' => $this->crud]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($parentId)
    {
        // dd($this->crud);
        $this->crud->checkPermission('add');

        return view('crud::layouts.create', $this->data + ['crud' => $this->crud]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function crudStore(Request $request = null)
    {
        // $this->crud->checkPermission('add');
        // dd($request);
        $entity = $this->crud->save(\Request::all());

        // return \Redirect::to($this->crud->getRoute()."/{$entity->id}/edit")->with('status', trans('crud::crud.form.create_success'));
        return \Redirect::to($this->crud->getRoute())->with('status', trans('crud::crud.form.create_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($parentId, $id)
    {
        $this->crud->checkPermission('view');
        $this->crud->item($id);

        return view('crud::layouts.show', $this->data + ['crud' => $this->crud]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($parentId, $id)
    {
        $this->crud->checkPermission('edit');
        $this->crud->item($id);

        // dd($this->crud->item->toArray());

        return view('crud::layouts.edit', $this->data + ['crud' => $this->crud]);

    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function crudUpdate($parentId, $id, Request $request = null)
    {
        $this->crud->checkPermission('edit');
        $this->crud->item($id);

        $entity = $this->crud->update($id, \Request::all());

        return \Redirect::to($this->crud->getRoute())->with('status', trans('crud::crud.form.save_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($parentId, $id)
    {
        $this->crud->checkPermission('delete');

        return $this->crud->delete($id);
    }
}