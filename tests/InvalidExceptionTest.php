<?php

namespace Spatie\LaravelStatus\Tests;

use Spatie\LaravelStatus\Exception\StatusError;
use Spatie\LaravelStatus\HasStatuses;
use Spatie\LaravelStatus\Models\Status;
use Spatie\LaravelStatus\Tests\Models\TestModel;
use Spatie\LaravelStatus\Tests\Models\ValidationTestModel;

class InvalidExceptionTest extends TestCase
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

    /** @test */
    public function it_returns_the_created_model()
    {
        $testUser = TestModel::create(['name'=> 'Thomas']);

        $returnedStatus = $testUser->setStatus('pending', 'waiting on validation of email address');

        $this->assertEquals('pending', $returnedStatus->name);
    }

    /** @test */
    public function it_can_check_if_the_status_is_valid()
    {
        $this->expectException(StatusError::class);

        $this->expectExceptionMessage("The status is not valid, check the status or adjust the isValidStatus method. ");

        $testUser = ValidationTestModel::create(['name'=> 'Thomas']);

        $testUser->setStatus('', '');
    }

    /** @test */
    public function it_can_add_a_callback_and_execute()
    {
        $user = TestModel::create(['name'=> 'Thomas']);

        $user->setCallbackOnAdd(

            function ($name) {
                $this->assertEquals('pending', $name);
            }
        );

        $user->setStatus('pending', 'waiting on validation of email address');
    }
}
