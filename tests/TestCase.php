<?php

namespace Spatie\LaravelModelStatus\Tests;

use CreateStatusesTable;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        $this->app['db']->connection()->getSchemaBuilder()->create('validation_test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        include_once __DIR__.'/../database/migrations/create_statuses_table.php';

        (new CreateStatusesTable())->up();
    }
}
