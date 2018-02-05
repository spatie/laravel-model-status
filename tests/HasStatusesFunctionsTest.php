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
            'name' => 'Thomas',
        ]);
    }

    /** @test */
    public function it_sets_a_status_to_a_model()
    {
        $this->testUser->setStatus('pending', 'waiting on action');

        $this->assertDatabaseHas('statuses', [
            'name' => 'pending',
            'description' => 'waiting on action',
            ]);
    }

    /** @test */
    public function it_can_get_a_status_from_a_model()
    {
        $this->testUser->setStatus('pending', 'waiting on action');

        $user = TestModel::find(1);

        $testStatus = $user->getCurrentStatus()->name;

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
            'name' => 'Thomas',
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

        $this->assertEquals('validated action 1', $foundStatus->description);
    }

    /** @test */
    public function it_can_handle_getting_a_status_when_there_are_none_set()
    {
        $emptyCurrentStatus = $this->testUser->getCurrentStatus();

        $this->assertNull($emptyCurrentStatus);
    }

    /** @test */
    public function it_can_handle_an_empty_description_when_setting_a_status()
    {
        $status = $this->testUser->setStatus('status');

        $this->assertEquals('status', $status->name);
    }

    /** @test */
    public function it_can_handle_an_empty_latest_status()
    {
        $this->testUser->setStatus('status');
        $lateststatus = $this->testUser->latestStatus();

        $this->assertEquals('status', $lateststatus->name);
    }
}
