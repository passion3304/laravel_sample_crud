<?php
namespace Dick\CRUD;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Route;

class CrudServiceProvider extends ServiceProvider
{
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
        // use this if your package has views
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'crud');

        // use this if your package has routes
        // $this->setupRoutes($this->app->router);

        // use this if your package needs a config file
        // $this->publishes([
        //         __DIR__.'/config/config.php' => config_path('CRUD.php'),
        // ]);

        // use the vendor configuration file as fallback
        // $this->mergeConfigFrom(
        //     __DIR__.'/config/config.php', 'CRUD'
        // );
    }
    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function setupRoutes(Router $router)
    {
        $router->group(['namespace' => 'Dick\CRUD\Http\Controllers'], function($router)
        {
            require __DIR__.'/Http/routes.php';
        });
    }
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerCRUD();

        // use this if your package has a config file
        // config([
        //         'config/CRUD.php',
        // ]);
    }

    private function registerCRUD()
    {
        $this->app->bind('CRUD',function($app){
            return new CRUD($app);
        });
    }

    public static function resource($name, $controller, array $options = [])
    {
        // CRUD routes
        Route::get($name.'/reorder', $controller.'@reorder');
        Route::get($name.'/reorder/{lang}', $controller.'@reorder');
        Route::post($name.'/reorder', $controller.'@saveReorder');
        Route::post($name.'/reorder/{lang}', $controller.'@saveReorder');
        Route::get($name.'/{id}/details', $controller.'@showDetailsRow');
        Route::get($name.'/{id}/translate/{lang}', $controller.'@translateItem');
        Route::resource($name, $controller, $options);
    }

}