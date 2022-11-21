<?php

namespace Backpack\CRUD\Tests\Unit\CrudPanel;

use Backpack\CRUD\Tests\Unit\Models\User;
use Backpack\CRUD\Tests\Unit\Http\Requests\UserRequest;

/**
 * @covers Backpack\CRUD\app\Library\CrudPanel\Traits\Validation
 */
class CrudPanelValidationTest extends BaseDBCrudPanelTest
{
    /**
     * Undocumented function
     *
     * @group fail
     */
    public function testItCanAddValidationFromRequest()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setValidation(UserRequest::class);

        $request = request()->create('users/', 'POST', [
            'email' => 'test@test.com',
            'password' => 'test'
        ]);
       
        $this->crudPanel->setRequest($request);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $validatedRequest = $this->crudPanel->validateRequest();
    }

    /**
     * Undocumented function
     *
     * @group fail
     */
    public function testItMergesFieldValidationWithRequestValidation()
    {
        $this->crudPanel->setModel(User::class);
        $this->crudPanel->setValidation(UserRequest::class);

        $request = app()->handle(request()->create('users/', 'POST', [
            'name' => 'test name',
            'email' => 'test@test.com',
            'password' => 'test'
        ]));

        $this->crudPanel->addField([
            'name' => 'email',
            'validationRules' => 'required'
        ]);

        $this->crudPanel->addField([
            'name' => 'name',
        ]);

        $this->crudPanel->addField([
            'name' => 'password',
        ]);
       
        $this->crudPanel->setRequest($request);

        $validatedRequest = $this->crudPanel->validateRequest();
        dump(get_class($validatedRequest));
        $this->assertEquals(['name', 'email'], array_keys($validatedRequest->validated()));
    }

    /**
     * Undocumented function
     *
     * @group fail
     */
    public function testItMergesAllKindsOfValidation()
    {
        $this->crudPanel->setModel(User::class);
        
        $this->crudPanel->setOperation('create');
        $this->crudPanel->setValidation([
            'password' => 'required'
        ]);
        $this->crudPanel->setValidation(UserRequest::class);
        $request = request()->create('users/', 'POST', [
            'name' => 'test name',
            'email' => 'test@test.com',
            'password' => 'test'
        ]);

        $request->setRouteResolver(function () use ($request) {
            return (new Route('POST', 'users', ['UserCrudController', 'create']))->bind($request);
        });

        $this->crudPanel->addField([
            'name' => 'email',
            'validationRules' => 'required'
        ]);

        $this->crudPanel->addField([
            'name' => 'name',
        ]);

        $this->crudPanel->addField([
            'name' => 'password',
        ]);
       //dd($request);
        $this->crudPanel->setRequest($request);

        $validatedRequest = $this->crudPanel->validateRequest();
        dd(get_class($validatedRequest));
        $this->assertEquals(['name', 'email', 'password'], array_keys($validatedRequest->validated()));
    }
}
