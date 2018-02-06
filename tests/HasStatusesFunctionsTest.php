<?php

namespace Spatie\LaravelModelStatus\Tests;

use Spatie\LaravelModelStatus\Exceptions\InvalidStatus;
use Spatie\LaravelModelStatus\Tests\Models\TestModel;
use Spatie\LaravelModelStatus\Tests\Models\ValidationTestModel;

class HasStatusesFunctionsTest extends TestCase
{
    protected $testUser;

    protected function setUp()
    {
        parent::setUp();

        $this->testUser = TestModel::create([
            'name' => 'my-name'
        ]);
    }

    /** @test */
    public function it_sets_a_status_to_a_model()
    {
        $this->testUser->setStatus('pending', 'waiting on action');

        $name = $this->testUser->statuses->first()->name;

        $description = $this->testUser->statuses->first()->description;

        $this->assertEquals('pending', $name);

        $this->assertEquals('waiting on action', $description);
    }

    /** @test */
    public function it_can_get_a_status_from_a_model()
    {
        $this->testUser->setStatus('pending', 'waiting on action');

        $user = TestModel::find(1);

        $testStatus = $user->currentStatus()->name;

        $this->assertEquals('pending', $testStatus);
    }

    /** @test */
    public function it_returns_the_created_model()
    {
        $currentStatus = $this->testUser->setStatus('pending', 'waiting on action');

        $this->assertEquals('pending', $currentStatus->name);
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

    //** @test */
    public function it_can_find_the_last_status_by_name()
    {
        $this->testUser->setStatus('pending', 'waiting on action 1');

        $this->testUser->setStatus('validated', 'validated action 1');

        $this->testUser->setStatus('pending', 'waiting on action 2');

        $foundStatus = $this->testUser->latestStatus('validated');

        $this->assertEquals('validated action 1', $foundStatus->description);
    }

    /** @test */
    public function it_can_handle_getting_a_status_when_there_are_none_set()
    {
        $emptyCurrentStatus = $this->testUser->currentStatus();

        $this->assertNull($emptyCurrentStatus);
    }

    /** @test */
    public function it_can_handle_an_empty_description_when_setting_a_status()
    {
        $status = $this->testUser->setStatus('status');

        $this->assertEquals('status', $status->name);
    }

    //** @test */
    public function it_can_handle_an_empty_latest_status()
    {
        $this->testUser->setStatus('status');

        $lateststatus = $this->testUser->latestStatus();

        $this->assertEquals('status', $lateststatus->name);
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

        $latestStatusOfThree = $this->testUser->latestStatus(['status 3', 'status 1', 'status 2']);

        $this->assertEquals('status 2', $latestStatusOfThree->name);
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
    public function it_can_gives_the_last_status_when_given_wrong_values_in_latest_status()
    {
        $this->testUser->setStatus('status 1');
        $this->testUser->setStatus('status 3');
        $this->testUser->setStatus('status 2');
        $this->testUser->setStatus('status 1');
        $this->testUser->setStatus('status 2');

        $latestStatus = $this->testUser->latestStatus('wrong', 'status');

        $this->assertEquals('status 2', $latestStatus->name);
    }

    /** @test */
    public function it_can_handle_getting_a_status_by_latest_status_when_there_are_none_set()
    {
        $this->assertNull($this->testUser->latestStatus());
    }
}
