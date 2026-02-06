<?php

use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Spatie\ModelStatus\Tests\Models\AlternativeStatusModel;
use Spatie\ModelStatus\Tests\Models\AlternativeUserStatus;
use Spatie\ModelStatus\Tests\Models\AttributedBackedStatusModel;
use Spatie\ModelStatus\Tests\Models\AttributedStrictBackedStatusModel;
use Spatie\ModelStatus\Tests\Models\AttributedUnitStatusModel;
use Spatie\ModelStatus\Tests\Models\CustomModelKeyStatusModel;
use Spatie\ModelStatus\Tests\Models\TestModel;
use Spatie\ModelStatus\Tests\Models\UnitUserStatus;
use Spatie\ModelStatus\Tests\Models\UserStatus;
use Spatie\ModelStatus\Tests\Models\ValidationTestModel;

beforeEach(function () {
    $this->testModel = TestModel::create([
        'name' => 'name',
    ]);
});

it('can get and set a status', function () {
    $this->testModel->setStatus('pending', 'waiting on action');

    $name = $this->testModel->statuses->first()->name;
    $reason = $this->testModel->statuses->first()->reason;

    expect($name)->toEqual('pending')
        ->and($reason)->toEqual('waiting on action');
});

test('a reason can be set')
    ->defer(fn () => $this->testModel->setStatus('pending', 'waiting on action'))
    ->expect(fn () => $this->testModel->status()->reason)
    ->toEqual('waiting on action');

it('throws an exception when setting an invalid status', function () {
    $validationUser = ValidationTestModel::create([
        'name' => 'name',
    ]);

    $validationUser->setStatus('InvalidStatus');
})->throws(InvalidStatus::class);

it('can force set an invalid status', function () {
    $validationUser = ValidationTestModel::create([
        'name' => 'name',
    ]);

    $validationUser->forceSetStatus('InvalidStatus');

    $name = $validationUser->statuses->first()->name;

    expect($name)->toEqual('InvalidStatus');
});

it('can set a status using a backed enum', function () {
    $this->testModel->setStatus(UserStatus::pending, 'waiting on action');

    expect($this->testModel->status()->name)->toEqual('pending')
        ->and($this->testModel->status()->reason)->toEqual('waiting on action');
});

it('can set a status using a unit enum', function () {
    $model = AttributedUnitStatusModel::create(['name' => 'name']);
    $model->setStatus(UnitUserStatus::pending);

    expect($model->status()->name)->toEqual('pending');
});

it('accepts matching string values when a status enum is configured', function () {
    $model = AttributedBackedStatusModel::create(['name' => 'name']);
    $model->setStatus('pending');

    expect($model->status()->name)->toEqual('pending');
});

it('rejects string values outside the configured status enum', function () {
    $model = AttributedBackedStatusModel::create(['name' => 'name']);
    $model->setStatus('non-existing-status');
})->throws(InvalidStatus::class);

it('rejects enum cases from a different enum class', function () {
    $model = AttributedBackedStatusModel::create(['name' => 'name']);
    $model->setStatus(AlternativeUserStatus::pending);
})->throws(InvalidStatus::class);

it('allows forceSetStatus to bypass configured enum validation when non strict', function () {
    $model = AttributedBackedStatusModel::create(['name' => 'name']);
    $model->forceSetStatus('outside-enum');

    expect($model->status()->name)->toEqual('outside-enum');
});

it('enforces configured enum validation in forceSetStatus when strict', function () {
    $model = AttributedStrictBackedStatusModel::create(['name' => 'name']);
    $model->forceSetStatus('outside-enum');
})->throws(InvalidStatus::class);

it('accepts matching enum values in forceSetStatus when strict', function () {
    $model = AttributedStrictBackedStatusModel::create(['name' => 'name']);
    $model->forceSetStatus(UserStatus::pending);

    expect($model->status()->name)->toEqual('pending');
});

it('returns the configured enum from statusEnum', function () {
    $model = AttributedBackedStatusModel::create(['name' => 'name']);
    $model->setStatus(UserStatus::pending);

    expect($model->statusEnum())->toBe(UserStatus::pending);
});

it('returns null from statusEnum when there is no status', function () {
    $model = AttributedBackedStatusModel::create(['name' => 'name']);

    expect($model->statusEnum())->toBeNull();
});

it('returns null from statusEnum when no status enum is configured', function () {
    $this->testModel->setStatus('pending');

    expect($this->testModel->statusEnum())->toBeNull();
});

it('returns null from statusEnum when latest status is outside the configured enum', function () {
    $model = AttributedBackedStatusModel::create(['name' => 'name']);
    $model->forceSetStatus('outside-enum');

    expect($model->statusEnum())->toBeNull();
});

it('passes a normalized string to isValidStatus when enum input is given', function () {
    $validationUser = ValidationTestModel::create([
        'name' => 'name',
    ]);

    $validationUser->setStatus(UserStatus::invalid);
})->throws(InvalidStatus::class);

it('can find the last status by name', function () {
    $this->testModel
        ->setStatus('status a', 'reason 1')
        ->setStatus('status b', 'reason 2')
        ->setStatus('status a', 'reason 3');

    expect(
        $this->testModel->latestStatus('status a')->reason
    )->toEqual('reason 3')
        ->and(
            $this->testModel->latestStatus('status b')->reason
        )->toEqual('reason 2');
});

it('can handle getting a status when there are none set')
    ->expect(fn () => $this->testModel->status())
    ->toBeNull();

it('can handle an empty reason when setting a status')
    ->defer(fn () => $this->testModel->setStatus('status'))
    ->expect(fn () => $this->testModel->status()->name)
    ->toEqual('status');

it('allows null for an empty reason when setting a status')
    ->defer(fn () => $this->testModel->setStatus('status', null))
    ->expect(fn () => $this->testModel->status()->reason)
    ->toBeNull();

it('can return the latest status', function (

) {
    $this->testModel
        ->setStatus('status 1')
        ->setStatus('status 3')
        ->setStatus('status 2')
        ->setStatus('status 1')
        ->setStatus('status 2');

    $model = $this->testModel->latestStatus('status 1', 'status 3');
    expect($model->name)->toBe('status 1');

    $model = $this->testModel->latestStatus(['status 1', 'status 3']);
    expect($model->name)->toBe('status 1');

    $model = $this->testModel->latestStatus('status 1', 'status 2', 'status 3');
    expect($model->name)->toBe('status 2');

    $model = $this->testModel->latestStatus('non existing status');
    expect($model)->toBeNull();
});

it('will return `true` if specific status is found')
    ->defer(fn () => $this->testModel->setStatus('status 1'))
    ->expect(fn () => $this->testModel->hasEverHadStatus('status 1'))
    ->toBeTrue();

it('will return `false` if specific status is not found')
    ->defer(fn () => $this->testModel->setStatus('status 1'))
    ->expect(fn () => $this->testModel->hasEverHadStatus('status 2'))
    ->toBeFalse();

it('will return `false` if specific status is found')
    ->defer(fn () => $this->testModel->setStatus('status 1'))
    ->expect(fn () => $this->testModel->hasNeverHadStatus('status 1'))
    ->toBeFalse();

it('will return `true` if specific status is not found')
    ->defer(fn () => $this->testModel->setStatus('status 1'))
    ->expect(fn () => $this->testModel->hasNeverHadStatus('status 2'))
    ->toBeTrue();

it('can delete a specific status', function () {
    $this->testModel->setStatus('status to delete');

    expect($this->testModel->statuses()->count())->toEqual(1);

    $this->testModel->deleteStatus('status to delete');

    expect($this->testModel->statuses()->count())->toEqual(0);
});

it('can delete a multiple statuses at once', function () {
    $this->testModel->setStatus('status to delete 1')
        ->setStatus('status to delete 2');

    expect($this->testModel->statuses()->count())->toEqual(2);

    $this->testModel->deleteStatus('status to delete 1', 'status to delete 2');

    expect($this->testModel->statuses()->count())->toEqual(0);
});

it('will keep status when invalid delete status is given', function () {
    $this->testModel->setStatus('status to delete');

    expect($this->testModel->statuses()->count())->toEqual(1);

    $this->testModel->deleteStatus();

    expect($this->testModel->statuses()->count())->toEqual(1);
});

it('can handle a different status model')
    ->defer(
        fn () => config()->set('model-status.status_model', AlternativeStatusModel::class)
    )
    ->defer(
        fn () => $this->testModel->setStatus('pending', 'waiting on action')
    )
    ->expect(fn () => $this->testModel->status())
    ->toBeInstanceOf(AlternativeStatusModel::class);

it('can find all models that have a last status with the given name', function () {
    $model1 = TestModel::create(['name' => 'model1']);
    $model2 = TestModel::create(['name' => 'model2']);
    $model3 = TestModel::create(['name' => 'model3']);
    $model4 = TestModel::create(['name' => 'model4']);

    $model1
        ->setStatus('status-a')
        ->setStatus('status-b')
        ->setStatus('status-c')
        ->setStatus('status-b');

    $model2->setStatus('status-c');

    $model3->setStatus('status-b');

    $model4->setStatus('status-a');

    expect([
        TestModel::currentStatus('status-a')->get()->pluck('name')->toArray(),
        TestModel::currentStatus('status-b')->get()->pluck('name')->toArray(),
        TestModel::currentStatus('status-c')->get()->pluck('name')->toArray(),
        TestModel::currentStatus('status-d')->get()->pluck('name')->toArray(),
    ])->sequence(['model4'], ['model1', 'model3'], ['model2'], []);
});

it('can return a string when calling the attribute', function () {
    $this
        ->testModel
        ->setStatus('free')
        ->setStatus('pending', 'waiting for a change');

    expect($this->testModel->status)->toEqual('pending')
        ->and($this->testModel->status()->name)->toEqual('pending')
        ->and($this->testModel->status()->reason)->toEqual('waiting for a change');
});

it('can handle a different status attribute', function () {
    $this->testModel
        ->setStatus('free')
        ->setStatus('pending', 'waiting for a change');

    config()->set('model-status.status_attribute', 'alternative_status');

    expect($this->testModel->alternative_status)
        ->toEqual('pending');
});

it('can find all models that do not have a status with a given name', function () {
    $model1 = TestModel::create(['name' => 'model1']);
    $model2 = TestModel::create(['name' => 'model2']);
    $model3 = TestModel::create(['name' => 'model3']);
    $model4 = TestModel::create(['name' => 'model4']);
    $model5 = TestModel::create(['name' => 'model5']);

    $this->testModel->setStatus('initiated');
    $model1->setStatus('initiated');

    $model2->setStatus('pending');
    $model3->setStatus('ready');
    $model4->setStatus('complete');

    expect(TestModel::otherCurrentStatus('initiated')->get())->toHaveCount(4)
        ->and(TestModel::otherCurrentStatus('initiated', 'pending')->get())->toHaveCount(3)
        ->and(TestModel::otherCurrentStatus(['initiated', 'pending'])->get())->toHaveCount(3);
});

it('supports custom polymorphic model types')
    ->defer(fn () => Relation::morphMap(['custom-test-model' => TestModel::class]))
    ->defer(fn () => $this->testModel->setStatus('initiated'))
    ->expect(fn () => TestModel::currentStatus('initiated')->get())
    ->toHaveCount(1);

it('can use a custom name for the relationship id column', function () {
    config()->set('model-status.status_model', CustomModelKeyStatusModel::class);
    config()->set('model-status.model_primary_key_attribute',  'model_custom_fk');

    $model = TestModel::create(['name' => 'model1']);
    $model->setStatus('pending');

    expect($model->status)->toEqual('pending')
        ->and($model->status()->model_custom_fk)->toEqual($model->id)
        ->and($model->status()->is(CustomModelKeyStatusModel::first()))->toBeTrue();
});

it('uses the default relationship id column when configuration value is', function () {
    config()->offsetUnset('model-status.model_primary_key_attribute');

    $model = TestModel::create(['name' => 'model1']);
    $model->setStatus('pending');

    expect($model->status)->toEqual('pending')
        ->and($model->status()->model_id)->toEqual($model->id);
});

it('returns all available status names', function () {

    $model = TestModel::create(['name' => 'model1']);
    // Set up some test statuses
    $model->setStatus('status1');
    $model->setStatus('status2');
    $model->setStatus('status3');

    // Get the status names
    $statusNames = $model->getStatusNames();

    // Assert the returned status names
    expect($statusNames)->toContain('status1')
        ->toContain('status2')
        ->toContain('status3');
});

it('returns an empty collection when there are no statuses', function () {
    $model = TestModel::create(['name' => 'model1']);

    // Get the status names
    $statusNames = $model->getStatusNames();

    // Assert the returned status names
    expect($statusNames)->toBeEmpty();
});

it('checks if the model has a specific status', function () {
    $model = TestModel::create(['name' => 'model1']);
    // Set up some test statuses
    $model->setStatus('status1');
    $model->setStatus('status3');
    // Assert that the model has the specified status
    expect($model->hasStatus('status1'))->toBeTrue();
    // Assert that the model does not have a different status
    expect($model->hasStatus('status2'))->toBeFalse();
});
