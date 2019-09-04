<?php

namespace Backpack\CRUD;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CrudServiceProvider extends ServiceProvider
{
    use CrudUsageStats;

    protected $commands = [
        \Backpack\CRUD\app\Console\Commands\Install::class,
        \Backpack\CRUD\app\Console\Commands\Publish::class,
    ];

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $customViewsFolder = resource_path('views/vendor/backpack/crud');

        // LOAD THE VIEWS

        // - first the published/overwritten views (in case they have any changes)
        if (file_exists($customViewsFolder)) {
            $this->loadViewsFrom($customViewsFolder, 'crud');
        }
        // - then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'crud');

        // PUBLISH FILES

        // publish lang files
        $this->publishes([__DIR__.'/resources/lang' => resource_path('lang/vendor/backpack')], 'lang');

        // publish views
        $this->publishes([__DIR__.'/resources/views' => resource_path('views/vendor/backpack/crud')], 'views');

        // publish config file
        $this->publishes([__DIR__.'/config' => config_path()], 'config');

        // publish public Backpack CRUD assets
        $this->publishes([__DIR__.'/public' => public_path('vendor/backpack')], 'public');

        // publish custom files for elFinder
        $this->publishes([
            __DIR__.'/config/elfinder.php'      => config_path('elfinder.php'),
            __DIR__.'/resources/views-elfinder' => resource_path('views/vendor/elfinder'),
        ], 'elfinder');

        // AUTO PUBLISH
        if (\App::environment('local')) {
            if ($this->shouldAutoPublishPublic()) {
                \Artisan::call('vendor:publish', [
                    '--provider' => 'Backpack\CRUD\CrudServiceProvider',
                    '--tag' => 'public',
                ]);
            }
        }

        // use the vendor configuration file as fallback
        $this->mergeConfigFrom(
            __DIR__.'/config/backpack/crud.php',
            'backpack.crud'
        );

        $this->sendUsageStats();
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Bind the CrudPanel object to Laravel's service container
        $this->app->singleton('crud', function ($app) {
            return new CrudPanel($app);
        });

        // load a macro for Route,
        // for developers to be able to load all routes for a CRUD resource in one line
        if (! Route::hasMacro('crud')) {
            $this->addRouteMacro();
        }

        // register the helper functions
        $this->loadHelpers();

        // register the artisan commands
        $this->commands($this->commands);

        // map the elfinder prefix
        if (! \Config::get('elfinder.route.prefix')) {
            \Config::set('elfinder.route.prefix', \Config::get('backpack.base.route_prefix').'/elfinder');
        }
    }

    /**
     * The route macro allows developers to generate the routes for a CrudController,
     * for all operations, using a simple syntax: Route::crud().
     *
     * It will go to the given CrudController and get the setupRoutes() method on it.
     */
    private function addRouteMacro()
    {
        Route::macro('crud', function ($name, $controller) {
            // put together the route name prefix,
            // as passed to the Route::group() statements
            $routeName = '';
            if ($this->hasGroupStack()) {
                foreach ($this->getGroupStack() as $key => $groupStack) {
                    if (isset($groupStack['name'])) {
                        if (is_array($groupStack['name'])) {
                            $routeName = implode('', $groupStack['name']);
                        } else {
                            $routeName = $groupStack['name'];
                        }
                    }
                }
            }
            // add the name of the current entity to the route name prefix
            // the result will be the current route name (not ending in dot)
            $routeName .= $name;

            // get an instance of the controller
            $groupStack = $this->hasGroupStack() && isset($this->getGroupStack()[0]['namespace']) ? $this->getGroupStack()[0]['namespace'].'\\' : 'App\\';
            $namespacedController = $groupStack.$controller;
            $controllerInstance = new $namespacedController;

            return $controllerInstance->setupRoutes($name, $routeName, $controller);
        });
    }

    /**
     * Load the Backpack helper methods, for convenience.
     */
    public function loadHelpers()
    {
        require_once __DIR__.'/helpers.php';
    }

    /**
     * Checks to see if we should automatically publish
     * vendor files from the public tag.
     *
     * @return bool
     */
    private function shouldAutoPublishPublic()
    {
        $crudPubPath = public_path('vendor/backpack/crud');

        if (! is_dir($crudPubPath)) {
            return true;
        }

        return false;
    }
}
