<?php

namespace Backpack\CRUD\app\Http\Controllers;

use Facade\FlareClient\Flare;
use Facade\Ignition\ErrorPage\ErrorPageHandler;
use Illuminate\Http\Request;
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

    /**
     * Show the error.
     *
     * @return \Illuminate\Http\Response
     */
    public function errorFrame(Request $request)
    {
        $handler = app(ErrorPageHandler::class);
        $client = app()->make(Flare::class);

        $exception = new $request->exception($request->message, 0, 1, $request->file, $request->line);

        $report = $client->createReport($exception);

        $handler->handleReport($report);
    }
}
