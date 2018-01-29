<?php

namespace Spatie\LaravelElequentStatus\Tests;

use Spatie\LaravelElequentStatus\Models\User;
use Spatie\LaravelElequentStatus\Tests\Models\TestModel;

class ExampleTest extends TestCase
{
    /** @test */
    public function it_creates_a_test_model()
    {
        TestModel::create(['name'=> "pending"]);
        $this->assertDatabaseHas("test_models",['name'=> "pending"]);
    }

    /** @test */
    public function it_sets_a_status_to_a_model(){

    }

}
