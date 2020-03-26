<?php

namespace Backpack\CRUD\app\Http\Controllers;

use Backpack\CRUD\app\Library\Widget;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    protected $data = []; // the information we send to the view

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(backpack_middleware());
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $this->data['title'] = trans('backpack::base.dashboard'); // set the page title
        $this->data['breadcrumbs'] = [
            trans('backpack::crud.admin')     => backpack_url('dashboard'),
            trans('backpack::base.dashboard') => false,
        ];

        Widget::add('thirdWidget')
            ->type('alert')
            ->group('before_content')
            ->class('alert alert-warning bg-success border-0 mb-2')
            ->heading('Widgets Fluent Syntax Works')
            ->content('This widget was added in <span class="badge badge-warning">AdminController::dashboard()</span>, using the fluent syntax. If you can see this, it means that works.')
            ->close_button(true);

        // dd(WidgetsCollection::all());

        return view(backpack_view('dashboard'), $this->data);
    }

    /**
     * Redirect to the dashboard.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        // The '/admin' route is not to be used as a page, because it breaks the menu's active state.
        return redirect(backpack_url('dashboard'));
    }
}
