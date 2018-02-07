<?php

namespace Spatie\ModelStatus\Tests;

use Spatie\ModelStatus\Exceptions\InvalidStatusModel;
use Spatie\ModelStatus\Tests\Models\TestModel;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Spatie\ModelStatus\Tests\Models\ValidationTestModel;

class HasStatusesFunctionsTest extends TestCase
{
    protected $testUser;

    protected function setUp()
    {
        parent::setUp();

        $this->testUser = TestModel::create([
            'name' => 'my-name',
        ]);
    }

    /** @test */
    public function it_sets_a_status_to_a_model()
    {
        $this->testUser->setStatus('pending', 'waiting on action');

        $name = $this->testUser->statuses->first()->name;

        $reason = $this->testUser->statuses->first()->reason;

        $this->assertEquals('pending', $name);

        $this->assertEquals('waiting on action', $reason);
    }

    /** @test */
    public function it_can_get_a_status_from_a_model()
    {
        $this->testUser->setStatus('pending', 'waiting on action');

        $user = TestModel::find(1);

        $testStatus = $user->status()->name;

        $this->assertEquals('pending', $testStatus);
    }

    /** @test */
    public function it_returns_the_created_model()
    {
        $this->testUser->setStatus('pending', 'waiting on action');

        $this->assertEquals('pending', $this->testUser->status()->name);
    }

    /** @test */
    public function it_checks_if_the_status_is_valid()
    {
        $validationUser = ValidationTestModel::create([
            'name' => 'my-name',
        ]);

        $this->expectException(InvalidStatus::class);

        $validationUser->setStatus('');
    }

    /** @test */
    public function it_can_find_the_last_status_by_name()
    {
        $this->testUser->setStatus('pending', 'waiting on action 1');

        $this->testUser->setStatus('validated', 'validated action 1');

        $this->testUser->setStatus('pending', 'waiting on action 2');

        $foundStatus = $this->testUser->latestStatus('validated');

        $this->assertEquals('validated action 1', $foundStatus->reason);
    }

    /** @test */
    public function it_can_handle_getting_a_status_when_there_are_none_set()
    {
        $emptyCurrentStatus = $this->testUser->status();

        $this->assertNull($emptyCurrentStatus);
    }

    /** @test */
    public function it_can_handle_an_empty_reason_when_setting_a_status()
    {
        $this->testUser->setStatus('status');

        $this->assertEquals('status', $this->testUser->status()->name);
    }

    /** @test */
    public function it_can_handle_an_empty_latest_status()
    {
        $this->testUser->setStatus('status');

        $lateststatus = $this->testUser->latestStatus();

        $this->assertEquals('status', $lateststatus->name);
    }

    /** @test */
    public function it_can_accept_a_list_of_arguments_and_give_the_latest_status()
    {
        $this->testUser->setStatus('status 1');
        $this->testUser->setStatus('status 3');
        $this->testUser->setStatus('status 2');
        $this->testUser->setStatus('status 1');
        $this->testUser->setStatus('status 2');

        $latestStatusOfTwo = $this->testUser->latestStatus('status 1', 'status 3');

        $this->assertEquals('status 1', $latestStatusOfTwo->name);

        $latestStatusOfThree = $this->testUser->latestStatus('status 1', 'status 2', 'status 3');

        $this->assertEquals('status 2', $latestStatusOfThree->name);
    }

    /** @test */
    public function it_can_handle_getting_a_status_by_latest_status_when_there_are_none_set()
    {
        $this->assertNull($this->testUser->latestStatus());
    }

    /** @test */
    public function it_can_handle_a_different_status_model()
    {
        $this->app['config']->set(
            'model-status.status_model',
            \Spatie\ModelStatus\Tests\Models\StatusTestModel::class
        );

        $this->testUser->setStatus('pending', 'waiting on action');

        $name = $this->testUser->status()->name;

        $reason = $this->testUser->status()->reason;

        $this->assertEquals('pending', $name);

        $this->assertEquals('waiting on action', $reason);
    }

    /** @test */
    public function it_throws_an_exception_when_the_status_model_does_not_extend_the_status_model()
    {
        $this->app['config']->set(
            'model-status.status_model',
            \Spatie\ModelStatus\Tests\Models\InvalidExtendTestModel::class
        );

        $this->expectException(InvalidStatusModel::class);

        $this->testUser->setStatus('pending', 'waiting on action');
    }
}
