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
        $this->testUser->setStatus('pending', 'waiting on validation of email address');

        $this->assertDatabaseHas('statuses', [
            'name' => 'pending',
            'description' => 'waiting on validation of email address',
            ]);
    }

    /** @test */
    public function it_can_get_a_status_from_a_model()
    {
        $this->testUser->setStatus('pending', 'waiting on validation of email address');

        $user = TestModel::find(1);

        $testStatus = $user->getCurrentStatus()->name;

        $this->assertEquals('pending', $testStatus);
    }

    /** @test */
    public function it_returns_the_created_model()
    {
        $currentStatus = $this->testUser->setStatus('pending', 'waiting on validation of email address');

        $this->assertEquals('pending', $currentStatus->name);
    }

    /** @test */
    public function it_checks_if_the_status_is_valid()
    {
        $validationUser = ValidationTestModel::create([
            'name' => 'Thomas',
        ]);

        $this->expectException(InvalidStatus::class);

        $validationUser->setStatus('', '');
    }

    /** @test */
    public function it_can_find_the_last_status_by_name()
    {
        $this->testUser->setStatus('pending', 'waiting on validation of email address 1');

        $this->testUser->setStatus('validated', 'email address validated 1');

        $this->testUser->setStatus('pending', 'waiting on validation of email address 2');

        $foundStatus = $this->testUser->findLastStatus('validated');
        
        $this->assertEquals('email address validated 1', $foundStatus->description);
    }
}
