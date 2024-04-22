<?php

use Illuminate\Support\Facades\Event;
use Spatie\ModelStatus\Events\StatusUpdated;
use Spatie\ModelStatus\Tests\Models\TestEnum;
use Spatie\ModelStatus\Tests\Models\TestModel;

beforeEach(function () {
    $this->testModel = TestModel::create([
        'name' => 'name',
    ]);
});

it('fires an event when status changes', function () {
    $this->testModel->setStatus(TestEnum::Pending, 'waiting on action');

    Event::fake();

    $this->testModel->setStatus(TestEnum::Approved, 'Reason a');

    Event::assertDispatched(
        StatusUpdated::class,
        function (StatusUpdated $event) {
            if ($event->model->id !== $this->testModel->id) {
                return false;
            }

            if ($event->newStatus->name !== TestEnum::Approved->value) {
                return false;
            }

            if ($event->oldStatus->name !== TestEnum::Pending->value) {
                return false;
            }

            return true;
        }
    );
});
