<?php

namespace Backpack\CRUD;

use Route;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class CrudServiceProvider extends ServiceProvider
{
    const VERSION = '3.4.26';

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
        $_SERVER['BACKPACK_CRUD_VERSION'] = $this::VERSION;

        // LOAD THE VIEWS

        // - first the published/overwritten views (in case they have any changes)
        $this->loadViewsFrom(resource_path('views/vendor/backpack/crud'), 'crud');
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
        $this->app->bind('CRUD', function ($app) {
            return new CRUD($app);
        });

        // register the artisan commands
        $this->commands($this->commands);

        // map the elfinder prefix
        if (! \Config::get('elfinder.route.prefix')) {
            \Config::set('elfinder.route.prefix', \Config::get('backpack.base.route_prefix').'/elfinder');
        }
    }

    public static function resource($name, $controller, array $options = [])
    {
        return new CrudRouter($name, $controller, $options);
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

    /**
     * Check if the application is running in normal conditions
     * (production env, not in console, not in unit tests).
     *
     * @return void
     */
    private function runningInProduction()
    {
        if ($this->app->environment('local')) {
            return false;
        }

        if ($this->app->runningInConsole()) {
            return false;
        }

        if ($this->app->runningUnitTests()) {
            return false;
        }
    }

    /**
     * Send usage statistics to the BackpackForLaravel.com website.
     * Used to track unlicensed usage and general usage statistics.
     *
     * No GDPR implications, since no client info is send, only server info.
     *
     * @return void
     */
    private function sendUsageStats()
    {
        // only do this in production
        if (! $this->runningInProduction()) {
            return;
        }

        // only send the stats with a 1/100 probability
        if (rand(1, 100) != 1) {
            return;
        }

        $stats = [];
        $stats['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? false;
        $stats['APP_URL'] = $_SERVER['APP_URL'] ?? false;
        $stats['APP_ENV'] = $this->app->environment() ?? false;
        $stats['APP_DEBUG'] = $_SERVER['APP_DEBUG'] ?? false;
        $stats['SERVER_ADDR'] = $_SERVER['SERVER_ADDR'] ?? false;
        $stats['SERVER_ADMIN'] = $_SERVER['SERVER_ADMIN'] ?? false;
        $stats['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? false;
        $stats['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? false;
        $stats['SERVER_PROTOCOL'] = $_SERVER['SERVER_PROTOCOL'] ?? false;
        $stats['SERVER_SOFTWARE'] = $_SERVER['SERVER_SOFTWARE'] ?? false;
        $stats['DB_CONNECTION'] = $_SERVER['DB_CONNECTION'] ?? false;
        $stats['LARAVEL_VERSION'] = $this->app->version() ?? false;
        $stats['BACKPACK_BASE_VERSION'] = $_SERVER['BACKPACK_BASE_VERSION'] ?? false;
        $stats['BACKPACK_CRUD_VERSION'] = $_SERVER['BACKPACK_CRUD_VERSION'] ?? false;
        $stats['BACKPACK_LICENSE'] = config('backpack.base.license_code') ?? false;

        // send this info to the main website and store it in the db
        $client = new \GuzzleHttp\Client();
        $res = $client->request('PUT', 'https://backpackforlaravel.com/api/stats', [
            'form_params' => $stats,
            'http_errors' => false,
        ]);
    }
}
