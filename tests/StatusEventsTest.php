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
        $this->testModel->setStatus('pending', 'waiting on action');
        Event::fake(); // Fake after, in order not to get the initial event

        $this->testModel->setStatus('status a', 'Reason a');

        Event::assertDispatched(StatusUpdated::class,
            function (StatusUpdated $event) {
                return $event->getModel()->is($this->testModel)
                       && $event->getNewStatus() === 'status a'
                       && $event->getOldStatus() === 'pending';
            });
    }

    /** @test */
    public function it_does_not_fire_an_event_when_status_stays_the_same()
    {
        $this->testModel->setStatus('pending', 'waiting on action');
        Event::fake(); // Fake after, in order not to get the initial event

        $this->testModel->setStatus('pending', 'Still waiting');

        Event::assertNotDispatched(StatusUpdated::class);
    }
}
