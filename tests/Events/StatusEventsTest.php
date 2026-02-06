<?php

use Illuminate\Support\Facades\Event;
use Spatie\ModelStatus\Events\StatusUpdated;
use Spatie\ModelStatus\Tests\Models\AttributedBackedStatusModel;
use Spatie\ModelStatus\Tests\Models\TestModel;
use Spatie\ModelStatus\Tests\Models\UserStatus;

beforeEach(function () {
    $this->testModel = TestModel::create([
        'name' => 'name',
    ]);
});

it('fires an event when status changes', function () {
    $this->testModel->setStatus('pending', 'waiting on action');

    Event::fake();

    $this->testModel->setStatus('status a', 'Reason a');

    Event::assertDispatched(
        StatusUpdated::class,
        function (StatusUpdated $event) {
            if ($event->model->id !== $this->testModel->id) {
                return false;
            }

            if ($event->newStatus->name !== 'status a') {
                return false;
            }

            if ($event->oldStatus->name !== 'pending') {
                return false;
            }

            return true;
        }
    );
});

it('fires an event when status changes using enum input', function () {
    $model = AttributedBackedStatusModel::create([
        'name' => 'name',
    ]);

    $model->setStatus(UserStatus::pending, 'waiting on action');

    Event::fake();

    $model->setStatus(UserStatus::accepted, 'accepted now');

    Event::assertDispatched(
        StatusUpdated::class,
        function (StatusUpdated $event) use ($model) {
            if ($event->model->id !== $model->id) {
                return false;
            }

            if ($event->newStatus->name !== 'accepted') {
                return false;
            }

            if ($event->oldStatus->name !== 'pending') {
                return false;
            }

            return true;
        }
    );
});
