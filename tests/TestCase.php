<?php

namespace Spatie\ModelStatus\Tests;

use CreateStatusesTable;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\ModelStatus\ModelStatusServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [
            ModelStatusServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        tap($this->app['db']->connection()->getSchemaBuilder(), function ($schema) {
            $schema->create('test_models', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });

            $schema->create('validation_test_models', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });

            $schema->create('custom_model_key_statuses', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->text('reason')->nullable();

                $table->string('model_type');
                $table->unsignedBigInteger('model_custom_fk');
                $table->index(['model_type', 'model_custom_fk']);

                $table->timestamps();
            });
        });

        include_once __DIR__.'/../database/migrations/create_statuses_table.php.stub';

        (new CreateStatusesTable())->up();
    }
}
