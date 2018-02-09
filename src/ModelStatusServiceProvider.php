<?php

namespace Spatie\ModelStatus;

use Illuminate\Support\ServiceProvider;
use Spatie\ModelStatus\Exceptions\InvalidStatusModel;

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
                __DIR__.'/../database/migrations/create_statuses_table.php.stub' => database_path('migrations/'.$timestamp.'_create_statuses_table.php'),
            ], 'migrations');
        }

        $this->publishes([
            __DIR__.'/../config/model-status.php' => config_path('model-status.php'),
        ], 'config');

        $this->guardAgainstInvalidStatusModel();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/model-status.php', 'model-status');
    }

    public function guardAgainstInvalidStatusModel()
    {
        $modelClassName = config('model-status.status_model');

        if (! is_a($modelClassName, Status::class, true)) {
            throw InvalidStatusModel::create($modelClassName);
        }
    }
}
