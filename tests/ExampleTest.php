<?php

namespace Spatie\LaravelElequentStatus\Tests;

use Spatie\LaravelElequentStatus\Tests\Models\TestModel;

class ExampleTest extends TestCase
{
    /** @test */
    public function it_creates_a_test_model()
    {
        TestModel::create(['name'=> "iets"]);
        $this->assertDatabaseHas("test_models",['name'=> "iets"]);
    }
}
