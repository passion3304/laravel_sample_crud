<?php
namespace Backpack\CRUD;

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
        // LOAD THE VIEWS
        // - first the published/overwritten views (in case they have any changes)
        $this->loadViewsFrom(resource_path('views/vendor/backpack/crud'), 'crud');
        // - then the stock views that come with the package, in case a published view might be missing
        $this->loadViewsFrom(realpath(__DIR__.'/resources/views'), 'crud');

        $this->loadTranslationsFrom(realpath(__DIR__.'/resources/lang'), 'backpack');

        // PUBLISH FILES
        // publish lang files
        $this->publishes([__DIR__.'/resources/lang' => resource_path('lang/vendor/backpack'), ], 'lang');
        // publish views
        $this->publishes([__DIR__.'/resources/views' => resource_path('views/vendor/backpack/crud'), ], 'views');
        // publish public Backpack CRUD assets
        $this->publishes([__DIR__.'/public' => public_path('vendor/backpack'), ], 'public');
        // publish custom files for elFinder
        $this->publishes([
                            __DIR__.'/config/elfinder.php' => config_path('elfinder.php'),
                            __DIR__.'/resources/views-elfinder' => resource_path('views/vendor/elfinder'),
                            ], 'elfinder');
        // TODO: publish demo resources:
            // - Auto Models: Article, Category, Tag
            // - Auto Requests: Article, Category, Tag
            // - Auto Controllers: Crud/ArticleController, Crud/CategoryController, Crud/TagController
            // - MANUAL routes
            // - MANUAL migration for those entities
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
        $this->app->bind('CRUD', function($app) {
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

        // Implicit controller for that entity
        // - makes any new function in that controller available without defining an extra route
        // - ex: EntityCrudController@getPreview will be available at /entity/preview through GET
        // - ex: EntityCrudController@postPreview will be available at /entity/preview through POST
        Route::controller($name, $controller);
    }

}