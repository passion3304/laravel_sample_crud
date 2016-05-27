<?php

namespace Backpack\CRUD;

use Illuminate\Support\ServiceProvider;
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
        // LOAD THE VIEWS
        // - first the published/overwritten views (in case they have any changes)
        $this->loadViewsFrom(resource_path('views/vendor/backpack/crud'), 'crud');
        // - then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'crud');

        $this->loadTranslationsFrom(realpath(__DIR__.'/resources/lang'), 'backpack');

        // PUBLISH FILES
        // publish lang files
        $this->publishes([__DIR__.'/resources/lang' => resource_path('lang/vendor/backpack')], 'lang');
        // publish views
        $this->publishes([__DIR__.'/resources/views' => resource_path('views/vendor/backpack/crud')], 'views');
        // publish public Backpack CRUD assets
        $this->publishes([__DIR__.'/public' => public_path('vendor/backpack')], 'public');
        // publish custom files for elFinder
        $this->publishes([
                            __DIR__.'/config/elfinder.php'      => config_path('elfinder.php'),
                            __DIR__.'/resources/views-elfinder' => resource_path('views/vendor/elfinder'),
                            ], 'elfinder');
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
        $this->app->bind('CRUD', function ($app) {
            return new CRUD($app);
        });
    }

    public static function resource($name, $controller, array $options = [])
    {
        // CRUD routes
        Route::get($name.'/reorder', $controller.'@reorder');
        Route::post($name.'/reorder', $controller.'@saveReorder');
        Route::get($name.'/{id}/details', $controller.'@showDetailsRow');
        Route::get($name.'/{id}/translate/{lang}', $controller.'@translateItem');
        Route::resource($name, $controller, $options);
    }
}
