<?php

namespace Backpack\CRUD\Tests\Unit\Http;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\Tests\BaseTest;

class CrudControllerTest extends BaseTest
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $controller = '\Backpack\CRUD\Tests\Unit\Http\Controllers\UserCrudController';

        $app['router']->get('users/{id}/edit', "$controller@edit");
        $app['router']->put('users/{id}', "$controller@update");

        $app->singleton('crud', function ($app) {
            return new CrudPanel($app);
        });
    }

    public function testCrudRequestUpdatesOnEachRequest()
    {
        $crud = app('crud');

        // create a first request
        $firstRequest = request()->create('/users/1/edit', 'GET');
        app()->handle($firstRequest);

        // see if the first global request has been passed to the CRUD object
        $this->assertSame($crud->getRequest(), $firstRequest);

        // create a second request
        $secondRequest = request()->create('/users/1', 'PUT', ['name' => 'foo']);
        app()->handle($secondRequest);

        // see if the second global requesst has been passed to the CRUD object
        $this->assertSame($crud->getRequest(), $secondRequest);

        // the CRUD object's request should no longer hold the first request, but the second one
        $this->assertNotSame($crud->getRequest(), $firstRequest);
    }
}
