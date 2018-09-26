<?php

namespace Sota\System;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Dotenv\Dotenv;
use Sota\System\Logging\RequestIdProcessor;
use Sota\System\Middleware\SentryContext;
use Sota\System\Commands\EnvMakeCommand;
use Sota\System\Commands\EnvSetCommand;

class SystemServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Add Sentry Middleware
        $this->app['router']->prependMiddlewareToGroup('api', SentryContext::class);

        // Load builenv
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
        
        // Register backup drive
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
        
        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'system');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/routes.php');
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
        
        $this->app->bind('command.env:make', EnvMakeCommand::class);
        $this->app->bind('command.env:set', EnvSetCommand::class);

        $this->commands([
            'command.env:make',
            'command.env:set'
        ]);
        
    }
}