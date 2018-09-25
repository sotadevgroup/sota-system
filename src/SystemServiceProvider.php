<?php

namespace Sota\System;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Dotenv\Dotenv;
use Sota\System\Logging\RequestIdProcessor;
use Sota\System\Middleware\SentryContext;
use Sota\System\Commands\SetEnvCommand;

class SystemServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {

        $this->app['router']->prependMiddlewareToGroup('api', SentryContext::class);

        // load builenv
        if (!App::environment('local')) {
            
            // Load buildenv
            $dotenv = new Dotenv(base_path(), '.buildenv');
            $dotenv->load();

            // Tag sentry with Build
            if (app()->bound('sentry')) {
                app('sentry')->tags_context([
                    'build' => env('BUILD')
                ]);
            }
        }
        
        // register backup drive
        config([ 'filesystems.disks.backup' => [
            'driver' => 's3',
            'key' => env('BACKUPS_AWS_KEY'),
            'secret' => env('BACKUPS_AWS_SECRET'),
            'region' => env('BACKUPS_AWS_REGION'),
            'bucket' => env('BACKUPS_AWS_BUCKET'),
        ]]);

        config([ 'backup.backup.destination.disks' => [ 'backup' ]]);

        // Register the logging processor
        $monolog = logger();
        $processor = new RequestIdProcessor(request());
        $monolog->pushProcessor($processor);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sota');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {

            // Publishing the configuration file.
            //$this->publishes([
            //    __DIR__.'/../config/sota-system.php' => config_path('sota-system.php'),
            //], 'sota-system.config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/sota'),
            ], 'sota-system.views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/sota'),
            ], 'sota-system.views');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/sota'),
            ], 'sota-system.views');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/backup.php', 'backup');
        $this->mergeConfigFrom(__DIR__.'/../config/deploy.php', 'deploy');
        $this->mergeConfigFrom(__DIR__.'/../config/json-api-paginate.php', 'json-api-paginate');
        $this->mergeConfigFrom(__DIR__.'/../config/query-builder.php', 'query-builder');
        $this->mergeConfigFrom(__DIR__.'/../config/sentry.php', 'sentry');
        
        $this->app->bind('command.env:set', SetEnvCommand::class);

        $this->commands([
            'command.env:set'
        ]);
        
        $this->app->singleton(
            Illuminate\Contracts\Debug\ExceptionHandler::class,
            Sota\System\Exceptions\Handler::class
        );
        
    }
}