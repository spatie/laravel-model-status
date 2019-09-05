<?php

namespace Spatie\ModelStatus\Tests;

use Spatie\ModelStatus\Tests\Models\TestModel;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\ModelStatus\Tests\Models\ValidationTestModel;
use Spatie\ModelStatus\Tests\Models\AlternativeStatusModel;
use Spatie\ModelStatus\Tests\Models\CustomModelKeyStatusModel;

class HasStatusesTest extends TestCase
{
    /** @var TestModel */
    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testModel = TestModel::create([
            'name' => 'name',
        ]);
    }

    /** @test */
    public function it_can_get_and_set_a_status()
    {
        $this->testModel->setStatus('pending', 'waiting on action');

        $name = $this->testModel->statuses->first()->name;

        $reason = $this->testModel->statuses->first()->reason;

        $this->assertEquals('pending', $name);

        $this->assertEquals('waiting on action', $reason);
    }

    /** @test */
    public function a_reason_can_be_set()
    {
        $this->testModel->setStatus('pending', 'waiting on action');

        $this->assertEquals('waiting on action', $this->testModel->status()->reason);
    }

    /** @test */
    public function it_throws_an_exception_when_setting_an_invalid_status()
    {
        $validationUser = ValidationTestModel::create([
            'name' => 'name',
        ]);

        $this->expectException(InvalidStatus::class);

        $validationUser->setStatus('InvalidStatus');
    }

    /** @test */
    public function it_can_force_set_an_invalid_status()
    {
        $validationUser = ValidationTestModel::create([
            'name' => 'name',
        ]);

        $validationUser->forceSetStatus('InvalidStatus');

        $name = $validationUser->statuses->first()->name;

        $this->assertEquals('InvalidStatus', $name);
    }

    /** @test */
    public function it_can_find_the_last_status_by_name()
    {
        $this->testModel
            ->setStatus('status a', 'reason 1')
            ->setStatus('status b', 'reason 2')
            ->setStatus('status a', 'reason 3');

        $this->assertEquals(
            'reason 3',
            $this->testModel->latestStatus('status a')->reason
        );

        $this->assertEquals(
            'reason 2',
            $this->testModel->latestStatus('status b')->reason
        );
    }

    /** @test */
    public function it_can_handle_getting_a_status_when_there_are_none_set()
    {
        $this->assertNull($this->testModel->status());
    }

    /** @test */
    public function it_can_handle_an_empty_reason_when_setting_a_status()
    {
        $this->testModel->setStatus('status');

        $this->assertEquals('status', $this->testModel->status()->name);
    }

    /** @test */
    public function it_allows_null_for_an_empty_reason_when_setting_a_status()
    {
        $this->testModel->setStatus('status', null);

        $this->assertNull($this->testModel->status()->reason);
    }

    /** @test */
    public function it_can_return_the_latest_status()
    {
        $this->testModel
            ->setStatus('status 1')
            ->setStatus('status 3')
            ->setStatus('status 2')
            ->setStatus('status 1')
            ->setStatus('status 2');

        $this->assertEquals(
            'status 1',
            $this->testModel->latestStatus('status 1', 'status 3')
        );

        $this->assertEquals(
            'status 1',
            $this->testModel->latestStatus(['status 1', 'status 3'])
        );

        $this->assertEquals(
            'status 2',
            $this->testModel->latestStatus('status 1', 'status 2', 'status 3')
        );

        $this->assertNull($this->testModel->latestStatus('non existing status'));
    }

    /** @test */
    public function it_will_return_true_if_specific_status_is_found()
    {
        $this->testModel->setStatus('status 1');

        $this->assertTrue($this->testModel->hasEverHadStatus('status 1'));
    }

    /** @test */
    public function it_will_return_false_if_specific_status_is_not_found()
    {
        $this->testModel->setStatus('status 1');

        $this->assertFalse($this->testModel->hasEverHadStatus('status 2'));
    }

    /** @test */
    public function it_can_delete_a_specific_status()
    {
        $this->testModel->setStatus('status to delete');

        $this->assertEquals(1, $this->testModel->statuses()->count());
        $this->testModel->deleteStatus('status to delete');
        $this->assertEquals(0, $this->testModel->statuses()->count());
    }

    /** @test */
    public function it_can_delete_a_multiple_statuses_at_once()
    {
        $this->testModel->setStatus('status to delete 1')
            ->setStatus('status to delete 2');

        $this->assertEquals(2, $this->testModel->statuses()->count());
        $this->testModel->deleteStatus('status to delete 1', 'status to delete 2');
        $this->assertEquals(0, $this->testModel->statuses()->count());
    }

    /** @test */
    public function it_will_keep_status_when_invalid_delete_status_is_given()
    {
        $this->testModel->setStatus('status to delete');

        $this->assertEquals(1, $this->testModel->statuses()->count());
        $this->testModel->deleteStatus();
        $this->assertEquals(1, $this->testModel->statuses()->count());
    }

    /** @test */
    public function it_can_handle_a_different_status_model()
    {
        $this->app['config']->set(
            'model-status.status_model',
            AlternativeStatusModel::class
        );

        $this->testModel->setStatus('pending', 'waiting on action');

        $this->assertInstanceOf(AlternativeStatusModel::class, $this->testModel->status());
    }

    /** @test */
    public function it_can_find_all_models_that_have_a_last_status_with_the_given_name()
    {
        $model1 = TestModel::create(['name' => 'model1']);
        $model2 = TestModel::create(['name' => 'model2']);
        $model3 = TestModel::create(['name' => 'model3']);
        $model4 = TestModel::create(['name' => 'model4']);
        $model5 = TestModel::create(['name' => 'model4']);

        $model1
            ->setStatus('status-a')
            ->setStatus('status-b')
            ->setStatus('status-c')
            ->setStatus('status-b');

        $model2->setStatus('status-c');

        $model3->setStatus('status-b');

        $model4->setStatus('status-a');

        $this->assertEquals(
            ['model4'],
            TestModel::currentStatus('status-a')->get()->pluck('name')->toArray());

        $this->assertEquals(
            ['model1', 'model3'],
            TestModel::currentStatus('status-b')->get()->pluck('name')->toArray());

        $this->assertEquals(
            ['model2'],
            TestModel::currentStatus('status-c')->get()->pluck('name')->toArray());

        $this->assertEquals(
            [],
            TestModel::currentStatus('status-d')->get()->pluck('name')->toArray());
    }

    /** @test */
    public function it_can_return_a_string_when_calling_the_attribute()
    {
        $this
            ->testModel
            ->setStatus('free')
            ->setStatus('pending', 'waiting for a change');

        $this->assertEquals('pending', $this->testModel->status);

        $this->assertEquals('pending', $this->testModel->status()->name);

        $this->assertEquals('waiting for a change', $this->testModel->status()->reason);
    }

    /** @test */
    public function it_can_find_all_models_that_do_not_have_a_status_with_the_given_name()
    {
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

        $this->assertCount(4, TestModel::otherCurrentStatus('initiated')->get());
        $this->assertCount(3, TestModel::otherCurrentStatus('initiated', 'pending')->get());
        $this->assertCount(3, TestModel::otherCurrentStatus(['initiated', 'pending'])->get());
    }

    /** @test */
    public function it_supports_custom_polymorphic_model_types()
    {
        Relation::morphMap(['custom-test-model' => TestModel::class]);

        $this->testModel->setStatus('initiated');

        $this->assertCount(1, TestModel::currentStatus('initiated')->get());
    }

    /** @test */
    public function it_can_use_a_custom_name_for_the_relationship_id_column()
    {
        $this->app['config']->set(
            'model-status.status_model',
            CustomModelKeyStatusModel::class
        );

        $this->app['config']->set(
            'model-status.model_primary_key_attribute',
            'model_custom_fk'
        );

        $model = TestModel::create(['name' => 'model1']);
        $model->setStatus('pending');

        $this->assertEquals('pending', $model->status);
        $this->assertEquals($model->id, $model->status()->model_custom_fk);
        $this->assertTrue($model->status()->is(CustomModelKeyStatusModel::first()));
    }

    /** @test */
    public function it_uses_the_default_relationship_id_column_when_configuration_value_is_missing()
    {
        $this->app['config']->offsetUnset('model-status.model_primary_key_attribute');

        $model = TestModel::create(['name' => 'model1']);
        $model->setStatus('pending');

        $this->assertEquals('pending', $model->status);
        $this->assertEquals($model->id, $model->status()->model_id);
    }
}
