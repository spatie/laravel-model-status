<?php


namespace Spatie\LaravelElequentStatus\Tests;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Spatie\LaravelElequentStatus\HasStatus;
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


    }
}
