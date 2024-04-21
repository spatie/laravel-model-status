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
    $this->testModel->setStatus(TestEnum::PENDING, 'waiting on action');

    $name = $this->testModel->statuses->first()->name;
    $reason = $this->testModel->statuses->first()->reason;

    expect($name)->toEqual(TestEnum::PENDING->value)
        ->and($reason)->toEqual('waiting on action');
});

test('a reason can be set')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::PENDING, 'waiting on action'))
    ->expect(fn () => $this->testModel->status()->reason)
    ->toEqual('waiting on action');

it('throws an exception when setting an invalid status', function () {
    $validationUser = ValidationTestModel::create([
        'name' => 'name',
    ]);

    $validationUser->setStatus(TestEnum::INVALID_STATUS);
})->throws(InvalidStatus::class);

it('can force set an invalid status', function () {
    $validationUser = ValidationTestModel::create([
        'name' => 'name',
    ]);

    $validationUser->forceSetStatus(TestEnum::INVALID_STATUS);

    $status = $validationUser->statuses->first()->name;

    expect($status)->toEqual(TestEnum::INVALID_STATUS->value);
});

it('throws an exception if status enum instance was from a different enum', function () {
    $this->testModel->setStatus(TestEnum2::TestStatus, 'test');
})->throws(InvalidEnumClass::class);;


it('can find the last status by enum', function () {
    $this->testModel
        ->setStatus(TestEnum::PENDING, 'reason 1')
        ->setStatus(TestEnum::APPROVED, 'reason 2')
        ->setStatus(TestEnum::PENDING, 'reason 3');

    expect(
        $this->testModel->latestStatus(TestEnum::PENDING)->reason
    )->toEqual('reason 3')
        ->and(
            $this->testModel->latestStatus(TestEnum::APPROVED)->reason
        )->toEqual('reason 2');
});

it('can handle getting a status when there are none set')
    ->expect(fn () => $this->testModel->status())
    ->toBeNull();

it('can handle an empty reason when setting a status')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::APPROVED))
    ->expect(fn () => $this->testModel->status()->name)
    ->toEqual(TestEnum::APPROVED->value);

it('allows null for an empty reason when setting a status')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::APPROVED, null))
    ->expect(fn () => $this->testModel->status()->reason)
    ->toBeNull();

it('can return the latest status', function () {
    $this->testModel
        ->setStatus(TestEnum::PENDING)
        ->setStatus(TestEnum::REJECTED)
        ->setStatus(TestEnum::APPROVED)
        ->setStatus(TestEnum::PENDING)
        ->setStatus(TestEnum::APPROVED);

    $model = $this->testModel->latestStatus(TestEnum::PENDING, TestEnum::REJECTED);
    expect($model->name)->toBe(TestEnum::PENDING->value);

    $model = $this->testModel->latestStatus([TestEnum::PENDING, TestEnum::REJECTED]);
    expect($model->name)->toBe(TestEnum::PENDING->value);

    $model = $this->testModel->latestStatus(TestEnum::PENDING, TestEnum::APPROVED, TestEnum::REJECTED);
    expect($model->name)->toBe(TestEnum::APPROVED->value);

    $model = $this->testModel->latestStatus(TestEnum::UNUSED_STATUS);
    expect($model)->toBeNull();
});

it('will return `true` if specific status is found')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::PENDING))
    ->expect(fn () => $this->testModel->hasEverHadStatus(TestEnum::PENDING))
    ->toBeTrue();

it('will return `false` if specific status is not found')
    ->tap(fn () => $this->testModel->setStatus(TestEnum::PENDING))
    ->expect(fn () => $this->testModel->hasEverHadStatus(TestEnum::APPROVED))
    ->toBeFalse();

it('can delete a specific status', function () {
    $this->testModel->setStatus(TestEnum::REJECTED);

    expect($this->testModel->statuses()->count())->toEqual(1);

    $this->testModel->deleteStatus(TestEnum::REJECTED);

    expect($this->testModel->statuses()->count())->toEqual(0);
});

it('can delete a multiple statuses at once', function () {
    $this->testModel->setStatus(TestEnum::REJECTED)
        ->setStatus(TestEnum::APPROVED);

    expect($this->testModel->statuses()->count())->toEqual(2);

    $this->testModel->deleteStatus(TestEnum::REJECTED, TestEnum::APPROVED);

    expect($this->testModel->statuses()->count())->toEqual(0);
});

it('will keep status when invalid delete status is given', function () {
    $this->testModel->setStatus(TestEnum::APPROVED);

    expect($this->testModel->statuses()->count())->toEqual(1);

    $this->testModel->deleteStatus();

    expect($this->testModel->statuses()->count())->toEqual(1);
});

it('can handle a different status model')
    ->tap(
        fn () => config()->set('model-status.status_model', AlternativeStatusModel::class)
    )
    ->tap(
        fn () => $this->testModel->setStatus(TestEnum::APPROVED, 'waiting on action')
    )
    ->expect(fn () => $this->testModel->status())
    ->toBeInstanceOf(AlternativeStatusModel::class);

it('can find all models that have a last status with the given enum', function () {
    $model1 = TestModel::create(['name' => 'model1']);
    $model2 = TestModel::create(['name' => 'model2']);
    $model3 = TestModel::create(['name' => 'model3']);
    $model4 = TestModel::create(['name' => 'model4']);

    $model1
        ->setStatus(TestEnum::PENDING)
        ->setStatus(TestEnum::APPROVED)
        ->setStatus(TestEnum::REJECTED)
        ->setStatus(TestEnum::APPROVED);

    $model2->setStatus(TestEnum::REJECTED);

    $model3->setStatus(TestEnum::APPROVED);

    $model4->setStatus(TestEnum::PENDING);

    expect([
        TestModel::currentStatus(TestEnum::PENDING)->get()->pluck('name')->toArray(),
        TestModel::currentStatus(TestEnum::APPROVED)->get()->pluck('name')->toArray(),
        TestModel::currentStatus(TestEnum::REJECTED)->get()->pluck('name')->toArray(),
        TestModel::currentStatus(TestEnum::UNUSED_STATUS)->get()->pluck('name')->toArray(),
    ])->sequence(['model4'], ['model1', 'model3'], ['model2'], []);
});

it('can return an enum instance when calling the attribute', function () {
    $this
        ->testModel
        ->setStatus(TestEnum::PENDING)
        ->setStatus(TestEnum::REJECTED, 'waiting for a change');

    expect($this->testModel->status)->toEqual(TestEnum::REJECTED)
        ->and($this->testModel->status()->name)->toEqual(TestEnum::REJECTED->value)
        ->and($this->testModel->status()->reason)->toEqual('waiting for a change');
});

it('can handle a different status attribute', function () {
    $this->testModel
        ->setStatus(TestEnum::PENDING)
        ->setStatus(TestEnum::APPROVED, 'waiting for a change');

    config()->set('model-status.status_attribute', 'alternative_status');

    expect($this->testModel->alternative_status)
        ->toEqual(TestEnum::APPROVED);
});

it('can find all models that do not a certain status', function () {
    $model1 = TestModel::create(['name' => 'model1']);
    $model2 = TestModel::create(['name' => 'model2']);
    $model3 = TestModel::create(['name' => 'model3']);
    $model4 = TestModel::create(['name' => 'model4']);
    $model5 = TestModel::create(['name' => 'model5']);

    $this->testModel->setStatus(TestEnum::PENDING);
    $model1->setStatus(TestEnum::PENDING);

    $model2->setStatus(TestEnum::APPROVED);
    $model3->setStatus(TestEnum::REJECTED);
    $model4->setStatus(TestEnum::UNUSED_STATUS);

    expect(TestModel::otherCurrentStatus(TestEnum::PENDING)->get())->toHaveCount(4)
        ->and(TestModel::otherCurrentStatus(TestEnum::PENDING, TestEnum::APPROVED)->get())->toHaveCount(3)
        ->and(TestModel::otherCurrentStatus([TestEnum::PENDING, TestEnum::APPROVED])->get())->toHaveCount(3);
});

it('supports custom polymorphic model types')
    ->tap(fn () => Relation::morphMap(['custom-test-model' => TestModel::class]))
    ->tap(fn () => $this->testModel->setStatus(TestEnum::PENDING))
    ->expect(fn () => TestModel::currentStatus(TestEnum::PENDING)->get())
    ->toHaveCount(1);

it('can use a custom name for the relationship id column', function () {
    config()->set('model-status.status_model', CustomModelKeyStatusModel::class);
    config()->set('model-status.model_primary_key_attribute',  'model_custom_fk');

    $model = TestModel::create(['name' => 'model1']);
    $model->setStatus(TestEnum::PENDING);

    expect($model->status)->toEqual(TestEnum::PENDING)
        ->and($model->status()->model_custom_fk)->toEqual($model->id)
        ->and($model->status()->is(CustomModelKeyStatusModel::first()))->toBeTrue();
});

it('uses the default relationship id column when configuration value is', function () {
    config()->offsetUnset('model-status.model_primary_key_attribute');

    $model = TestModel::create(['name' => 'model1']);
    $model->setStatus(TestEnum::PENDING);

    expect($model->status)->toEqual(TestEnum::PENDING)
        ->and($model->status()->model_id)->toEqual($model->id);
});


it('checks if the model has a specific status', function () {
    $model = TestModel::create(['name' => 'model1']);
    // Set up some test statuses
    $model->setStatus(TestEnum::PENDING);
    $model->setStatus(TestEnum::APPROVED);
    // Assert that the model has the specified status
    expect($model->hasStatus(TestEnum::PENDING))->toBeTrue();
    // Assert that the model does not have a different status
    expect($model->hasStatus(TestEnum::REJECTED))->toBeFalse();
});


it('throws an exception if the enum is not string backed', function () {
    $model = TestModelInvalidEnumType::create(['name' => 'model1']);
    $model->setStatus(TestEnumNotBacked::TestStatus1);
})->throws(InvalidEnumType::class);
