<?php

namespace Backpack\CRUD;

use Route;

class CrudRouter
{
    protected $requiredInjectables = [];

    protected $name = null;
    protected $options = null;
    protected $controller = null;

    public function __construct($name, $controller, $options)
    {
        $this->name = $name;
        $this->options = $options;
        $this->controller = $controller;

        // CRUD routes
        Route::post($this->name.'/search', [
            'as' => 'crud.'.$this->name.'.search',
            'uses' => $this->controller.'@search',
        ]);

        Route::get($this->name.'/reorder', [
            'as' => 'crud.'.$this->name.'.reorder',
            'uses' => $this->controller.'@reorder',
        ]);

        Route::post($this->name.'/reorder', [
            'as' => 'crud.'.$this->name.'.save.reorder',
            'uses' => $this->controller.'@saveReorder',
        ]);

        Route::get($this->name.'/{id}/details', [
            'as' => 'crud.'.$this->name.'.showDetailsRow',
            'uses' => $this->controller.'@showDetailsRow',
        ]);

        Route::get($this->name.'/{id}/translate/{lang}', [
            'as' => 'crud.'.$this->name.'.translateItem',
            'uses' => $this->controller.'@translateItem',
        ]);

        Route::get($this->name.'/{id}/revisions', [
            'as' => 'crud.'.$this->name.'.listRevisions',
            'uses' => $this->controller.'@listRevisions',
        ]);

        Route::post($this->name.'/{id}/revisions/{revisionId}/restore', [
            'as' => 'crud.'.$this->name.'.restoreRevision',
            'uses' => $this->controller.'@restoreRevision',
        ]);

        $options_with_default_route_names = array_merge([
            'names' => [
                'index'     => 'crud.'.$this->name.'.index',
                'create'    => 'crud.'.$this->name.'.create',
                'store'     => 'crud.'.$this->name.'.store',
                'edit'      => 'crud.'.$this->name.'.edit',
                'update'    => 'crud.'.$this->name.'.update',
                'show'      => 'crud.'.$this->name.'.show',
                'destroy'   => 'crud.'.$this->name.'.destroy',
            ],
        ], $this->options);

        Route::resource($this->name, $this->controller, $options_with_default_route_names);
    }

    public function with($injectables)
    {
        if (is_string($injectables)) {
            $this->requiredInjectables[] = 'with'.ucwords($injectables);
        } elseif (is_array($injectables)) {
            foreach ($injectables as $injectable) {
                $this->requiredInjectables[] = 'with'.ucwords($injectable);
            }
        } else {
            $reflection = new \ReflectionFunction($injectables);

            if ($reflection->isClosure()) {
                $this->requiredInjectables[] = $injectables;
            }
        }

        return $this->inject();
    }

    private function inject()
    {
        foreach ($this->requiredInjectables as $injectable) {
            if (is_string($injectable)) {
                $this->{$injectable}();
            } else {
                $injectable();
            }
        }
    }

    public function __call($method, $parameters = null)
    {
        if (method_exists($this, $method)) {
            $this->{$method}($parameters);
        }
    }
}
