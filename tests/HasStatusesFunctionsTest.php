<?php

namespace Spatie\ModelStatus\Tests;

use Spatie\ModelStatus\Tests\Models\TestModel;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Spatie\ModelStatus\Exceptions\InvalidStatusModel;
use Spatie\ModelStatus\Tests\Models\ValidationTestModel;
use DB;

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
    public function it_can_accept_an_array_and_give_the_latest_status()
    {
        $this->testUser->setStatus('status 1');
        $this->testUser->setStatus('status 3');
        $this->testUser->setStatus('status 2');
        $this->testUser->setStatus('status 1');
        $this->testUser->setStatus('status 2');

        $latestStatusOfTwo = $this->testUser->latestStatus(['status 1', 'status 3']);

        $this->assertEquals('status 1', $latestStatusOfTwo->name);

        $latestStatusOfThree = $this->testUser->latestStatus(['status 1', 'status 2', 'status 3']);

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

    /** @test */
    public function it_can_give_back_all_the_models_that_have_as_a_last_status_the_given_name()
    {
        $testUser2 = TestModel::create(['name' => 'second-user']);
        $testUser3 = TestModel::create(['name' => 'third-user']);
        $testUser4 = TestModel::create(['name' => 'fourth-user']);
        $testUser5 = TestModel::create(['name' => 'last-user']);

        $this->testUser->setStatus('status-A');

        $testUser2->setStatus('status-B');

        $testUser3->setStatus('status-C');

        $testUser4->setStatus('status-B');

        $testUser5->setStatus('status-A');

        $testUser2->setStatus('status-C');

        $testUser2->setStatus('status-B');

        $modelsWithStatus = TestModel::hasStatus('status-B')->get()->pluck('name');
        $this->assertContains('fourth-user', $modelsWithStatus);
        $this->assertContains('second-user', $modelsWithStatus);
        $this->assertCount(2, $modelsWithStatus);
    }

    /** @test */
    public function it_can_return_a_string_when_calling_the_attribute()
    {
        $this->testUser->setStatus('free');
        $this->testUser->setStatus('pending', 'waiting for a change');

        $this->assertEquals('pending', $this->testUser->status);

        $this->assertEquals('pending', $this->testUser->status()->name);

        $this->assertEquals('waiting for a change', $this->testUser->status()->reason);
    }
}
