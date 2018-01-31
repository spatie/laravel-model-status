<?php

namespace Spatie\Skeleton;

use Illuminate\Support\ServiceProvider;

class LaravelModelStatusServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');
        }
        if (! class_exists('CreateStatusesTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__ . '/../database/migrations/create_statuses_table.php'
                => database_path('migrations/'.$timestamp.'create_statuses_table.php')
                ], 'migrations');
        }
    }
}
