<?php

namespace Spatie\LaravelEloquentStatus\Tests;

use Spatie\LaravelEloquentStatus\Models\Status;
use Spatie\LaravelEloquentStatus\Tests\Models\TestModel;
use Spatie\LaravelEloquentStatus\Tests\Models\ValidationTestModel;

class ExampleTest extends TestCase
{
    /** @test */
    public function it_creates_a_test_model()
    {
        TestModel::create(['name'=> 'Thomas']);
        $this->assertDatabaseHas('test_models', ['name'=> 'Thomas']);
    }

    /** @test */
    public function it_sets_a_status_to_a_model()
    {
        TestModel::create(['name'=> 'Thomas']);
        Status::create(['name'=> 'pending','explanation'=> 'waiting on validation of email address','status_id'=> 1,'status_type'=> 'TestModel']);
        $this->assertDatabaseHas('statuses', ['name'=> 'pending','explanation'=> 'waiting on validation of email address','status_id'=> 1,'status_type'=> 'TestModel']);
    }

    /** @test */
    public function it_adds_a_status_to_a_user_record()
    {
        $testUser = TestModel::create(['name'=> 'Thomas']);
        $testUser->setStatus('pending', 'waiting on validation of email address');
        $user = TestModel::find(1);
        $testStatus = $user->getStatus()->name;
        $this->assertEquals('pending', $testStatus);
    }

    /** @test **/
    public function it_returns_the_created_model()
    {
        $testUser = TestModel::create(['name'=> 'Thomas']);
        $returnedStatus = $testUser->setStatus('pending', 'waiting on validation of email address');
        $this->assertEquals('pending', $returnedStatus->name);
    }

    /** @test **/
    public function it_can_check_if_the_status_is_valid()
    {
        $testUser = ValidationTestModel::create(['name'=> 'Thomas']);
        $returnedStatus = $testUser->setStatus('', '');
        $this->assertNull($returnedStatus);
    }
}
