<?php

namespace Spatie\ModelStatus\Tests;

use Illuminate\Support\Facades\Event;
use Spatie\ModelStatus\Events\StatusUpdated;
use Spatie\ModelStatus\Tests\Models\TestModel;

class StatusEventsTest extends TestCase
{
    protected $testModel;

    protected function setUp()
    {
        parent::setUp();

        $this->testModel = TestModel::create([
            'name' => 'name',
        ]);
    }

    /** @test */
    public function it_fires_an_event_when_status_changes()
    {
        // Prepare
        $this->testModel->setStatus('pending', 'waiting on action');
        Event::fake(); // Fake after, in order not to get the initial event

        // Act
        $this->testModel->setStatus('status a', 'Reason a');

        // Assert
        Event::assertDispatched(StatusUpdated::class,
            function (StatusUpdated $e) {
                return $e->getModel()->is($this->testModel)
                       && $e->getNewStatus() === 'status a'
                       && $e->getOldStatus() === 'pending';
            });
    }

    /** @test */
    public function it_does_not_fire_an_event_when_status_stays_same()
    {
        // Prepare
        $this->testModel->setStatus('pending', 'waiting on action');
        Event::fake(); // Fake after, in order not to get the initial event

        // Act
        $this->testModel->setStatus('pending', 'Still waiting');

        // Assert
        Event::assertNotDispatched(StatusUpdated::class);
    }
}
