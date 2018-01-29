<?php


namespace Spatie\LaravelEloquentStatus\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });

        include_once __DIR__.'/../database/migrations/create_statuses_table.php';
        (new \CreateStatusesTable())->up();
    }
}
