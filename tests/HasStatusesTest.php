<?php

use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\ModelStatus\Exceptions\InvalidEnumType;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Spatie\ModelStatus\Exceptions\InvalidEnumClass;
use Spatie\ModelStatus\Tests\Models\AlternativeStatusModel;
use Spatie\ModelStatus\Tests\Models\CustomModelKeyStatusModel;
use Spatie\ModelStatus\Tests\Models\TestEnum;
use Spatie\ModelStatus\Tests\Models\TestEnum2;
use Spatie\ModelStatus\Tests\Models\TestEnumNotBacked;
use Spatie\ModelStatus\Tests\Models\TestModel;
use Spatie\ModelStatus\Tests\Models\TestModelInvalidEnumType;
use Spatie\ModelStatus\Tests\Models\ValidationTestModel;

beforeEach(function () {
    $this->testModel = TestModel::create([
        'name' => 'name',
    ]);
});

it('can get and set a status', function () {
    $this->testModel->setStatus(TestEnum::Pending, 'waiting on action');

    $name = $this->testModel->statuses->first()->name;
    $reason = $this->testModel->statuses->first()->reason;

    expect($name)->toEqual(TestEnum::Pending->value)
        ->and($reason)->toEqual('waiting on action');
});

test('a reason can be set')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::Pending, 'waiting on action'))
    ->expect(fn () => $this->testModel->status()->reason)
    ->toEqual('waiting on action');

it('throws an exception when setting an invalid status', function () {
    $validationUser = ValidationTestModel::create([
        'name' => 'name',
    ]);

    $validationUser->setStatus(TestEnum::InvalidStatus);
})->throws(InvalidStatus::class);

it('can force set an invalid status', function () {
    $validationUser = ValidationTestModel::create([
        'name' => 'name',
    ]);

    $validationUser->forceSetStatus(TestEnum::InvalidStatus);

    $status = $validationUser->statuses->first()->name;

    expect($status)->toEqual(TestEnum::InvalidStatus->value);
});

it('throws an exception if status enum instance was from a different enum', function () {
    $this->testModel->setStatus(TestEnum2::TestStatus, 'test');
})->throws(InvalidEnumClass::class);;


it('can find the last status by enum', function () {
    $this->testModel
        ->setStatus(TestEnum::Pending, 'reason 1')
        ->setStatus(TestEnum::Approved, 'reason 2')
        ->setStatus(TestEnum::Pending, 'reason 3');

    expect(
        $this->testModel->latestStatus(TestEnum::Pending)->reason
    )->toEqual('reason 3')
        ->and(
            $this->testModel->latestStatus(TestEnum::Approved)->reason
        )->toEqual('reason 2');
});

it('can handle getting a status when there are none set')
    ->expect(fn () => $this->testModel->status())
    ->toBeNull();

it('can handle an empty reason when setting a status')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::Approved))
    ->expect(fn () => $this->testModel->status()->name)
    ->toEqual(TestEnum::Approved->value);

it('allows null for an empty reason when setting a status')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::Approved, null))
    ->expect(fn () => $this->testModel->status()->reason)
    ->toBeNull();

it('can return the latest status', function () {
    $this->testModel
        ->setStatus(TestEnum::Pending)
        ->setStatus(TestEnum::Rejected)
        ->setStatus(TestEnum::Approved)
        ->setStatus(TestEnum::Pending)
        ->setStatus(TestEnum::Approved);

    $model = $this->testModel->latestStatus(TestEnum::Pending, TestEnum::Rejected);
    expect($model->name)->toBe(TestEnum::Pending->value);

    $model = $this->testModel->latestStatus([TestEnum::Pending, TestEnum::Rejected]);
    expect($model->name)->toBe(TestEnum::Pending->value);

    $model = $this->testModel->latestStatus(TestEnum::Pending, TestEnum::Approved, TestEnum::Rejected);
    expect($model->name)->toBe(TestEnum::Approved->value);

    $model = $this->testModel->latestStatus(TestEnum::UnusedStatus);
    expect($model)->toBeNull();
});

it('will return `true` if specific status is found')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::Pending))
    ->expect(fn () => $this->testModel->hasEverHadStatus(TestEnum::Pending))
    ->toBeTrue();

it('will return `false` if specific status is not found')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::Pending))
    ->expect(fn () => $this->testModel->hasEverHadStatus(TestEnum::Approved))
    ->toBeFalse();

it('can delete a specific status', function () {
    $this->testModel->setStatus(TestEnum::Rejected);

    expect($this->testModel->statuses()->count())->toEqual(1);

    $this->testModel->deleteStatus(TestEnum::Rejected);

    expect($this->testModel->statuses()->count())->toEqual(0);
});

it('can delete a multiple statuses at once', function () {
    $this->testModel->setStatus(TestEnum::Rejected)
        ->setStatus(TestEnum::Approved);

    expect($this->testModel->statuses()->count())->toEqual(2);

    $this->testModel->deleteStatus(TestEnum::Rejected, TestEnum::Approved);

    expect($this->testModel->statuses()->count())->toEqual(0);
});

it('will keep status when invalid delete status is given', function () {
    $this->testModel->setStatus(TestEnum::Approved);

    expect($this->testModel->statuses()->count())->toEqual(1);

    $this->testModel->deleteStatus();

    expect($this->testModel->statuses()->count())->toEqual(1);
});

it('can handle a different status model')
    ->tap(
        fn () => config()->set('model-status.status_model', AlternativeStatusModel::class)
    )
    ->tap(
        fn () => $this->testModel->setStatus(TestEnum::Approved, 'waiting on action')
    )
    ->expect(fn () => $this->testModel->status())
    ->toBeInstanceOf(AlternativeStatusModel::class);

it('can find all models that have a last status with the given enum', function () {
    $model1 = TestModel::create(['name' => 'model1']);
    $model2 = TestModel::create(['name' => 'model2']);
    $model3 = TestModel::create(['name' => 'model3']);
    $model4 = TestModel::create(['name' => 'model4']);

    $model1
        ->setStatus(TestEnum::Pending)
        ->setStatus(TestEnum::Approved)
        ->setStatus(TestEnum::Rejected)
        ->setStatus(TestEnum::Approved);

    $model2->setStatus(TestEnum::Rejected);

    $model3->setStatus(TestEnum::Approved);

    $model4->setStatus(TestEnum::Pending);

    expect([
        TestModel::currentStatus(TestEnum::Pending)->get()->pluck('name')->toArray(),
        TestModel::currentStatus(TestEnum::Approved)->get()->pluck('name')->toArray(),
        TestModel::currentStatus(TestEnum::Rejected)->get()->pluck('name')->toArray(),
        TestModel::currentStatus(TestEnum::UnusedStatus)->get()->pluck('name')->toArray(),
    ])->sequence(['model4'], ['model1', 'model3'], ['model2'], []);
});

it('can return an enum instance when calling the attribute', function () {
    $this
        ->testModel
        ->setStatus(TestEnum::Pending)
        ->setStatus(TestEnum::Rejected, 'waiting for a change');

    expect($this->testModel->status)->toEqual(TestEnum::Rejected)
        ->and($this->testModel->status()->name)->toEqual(TestEnum::Rejected->value)
        ->and($this->testModel->status()->reason)->toEqual('waiting for a change');
});

it('can handle a different status attribute', function () {
    $this->testModel
        ->setStatus(TestEnum::Pending)
        ->setStatus(TestEnum::Approved, 'waiting for a change');

    config()->set('model-status.status_attribute', 'alternative_status');

    expect($this->testModel->alternative_status)
        ->toEqual(TestEnum::Approved);
});

it('can find all models that do not a certain status', function () {
    $model1 = TestModel::create(['name' => 'model1']);
    $model2 = TestModel::create(['name' => 'model2']);
    $model3 = TestModel::create(['name' => 'model3']);
    $model4 = TestModel::create(['name' => 'model4']);
    $model5 = TestModel::create(['name' => 'model5']);

    $this->testModel->setStatus(TestEnum::Pending);
    $model1->setStatus(TestEnum::Pending);

    $model2->setStatus(TestEnum::Approved);
    $model3->setStatus(TestEnum::Rejected);
    $model4->setStatus(TestEnum::UnusedStatus);

    expect(TestModel::otherCurrentStatus(TestEnum::Pending)->get())->toHaveCount(4)
        ->and(TestModel::otherCurrentStatus(TestEnum::Pending, TestEnum::Approved)->get())->toHaveCount(3)
        ->and(TestModel::otherCurrentStatus([TestEnum::Pending, TestEnum::Approved])->get())->toHaveCount(3);
});

it('supports custom polymorphic model types')
    ->tap(fn () => Relation::morphMap(['custom-test-model' => TestModel::class]))
    ->tap(fn () => $this->testModel->setStatus(TestEnum::Pending))
    ->expect(fn () => TestModel::currentStatus(TestEnum::Pending)->get())
    ->toHaveCount(1);

it('can use a custom name for the relationship id column', function () {
    config()->set('model-status.status_model', CustomModelKeyStatusModel::class);
    config()->set('model-status.model_primary_key_attribute',  'model_custom_fk');

    $model = TestModel::create(['name' => 'model1']);
    $model->setStatus(TestEnum::Pending);

    expect($model->status)->toEqual(TestEnum::Pending)
        ->and($model->status()->model_custom_fk)->toEqual($model->id)
        ->and($model->status()->is(CustomModelKeyStatusModel::first()))->toBeTrue();
});

it('uses the default relationship id column when configuration value is', function () {
    config()->offsetUnset('model-status.model_primary_key_attribute');

    $model = TestModel::create(['name' => 'model1']);
    $model->setStatus(TestEnum::Pending);

    expect($model->status)->toEqual(TestEnum::Pending)
        ->and($model->status()->model_id)->toEqual($model->id);
});


it('checks if the model has a specific status', function () {
    $model = TestModel::create(['name' => 'model1']);
    // Set up some test statuses
    $model->setStatus(TestEnum::Pending);
    $model->setStatus(TestEnum::Approved);
    // Assert that the model has the specified status
    expect($model->hasStatus(TestEnum::Pending))->toBeTrue();
    // Assert that the model does not have a different status
    expect($model->hasStatus(TestEnum::Rejected))->toBeFalse();
});


it('throws an exception if the enum is not string backed', function () {
    $model = TestModelInvalidEnumType::create(['name' => 'model1']);
    $model->setStatus(TestEnumNotBacked::TestStatus1);
})->throws(InvalidEnumType::class);
