<?php

namespace CrudPanel;

use Illuminate\Database\Eloquent\Builder;

class CrudPanelTest extends BaseCrudPanelTest
{
    /** @test */
    public function it_sets_the_provided_model()
    {
        $this->crudPanel->setModel(\Model::class);
        $this->assertInstanceOf(\Model::class, $this->crudPanel->model);
        $this->assertInstanceOf(Builder::class, $this->crudPanel->query);

        $this->crudPanel->setModel('\Model');
        $this->assertInstanceOf('\Model', $this->crudPanel->model);
        $this->assertInstanceOf(Builder::class, $this->crudPanel->query);
    }

    /** @test */
    public function it_throws_exception_if_provided_model_does_not_exist()
    {
        $this->setExpectedException(\Exception::class);
        $this->crudPanel->setModel('\Foo\Bar');
    }
}
