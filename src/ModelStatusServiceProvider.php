<?php

namespace Spatie\LaravelModelStatus;

use Illuminate\Support\ServiceProvider;

class ModelStatusServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
        }

        if (! class_exists('CreateStatusesTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../database/migrations/create_statuses_table.php.stub.stub' => database_path('migrations/'.$timestamp.'create_statuses_table.php'),
            ], 'migrations');
        }

        $this->publishes([
            __DIR__.'/../config/model-status.php' => config_path('model-status.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/model-status.php', 'model-status');
    }
}
