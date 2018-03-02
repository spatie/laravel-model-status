<?php

namespace Spatie\ModelStatus\Tests;

use Spatie\ModelStatus\Tests\Models\TestModel;
use Spatie\ModelStatus\Exceptions\InvalidStatus;
use Spatie\ModelStatus\Tests\Models\ValidationTestModel;
use Spatie\ModelStatus\Tests\Models\AlternativeStatusModel;

class HasStatusesTest extends TestCase
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

        $validationUser->setStatus('');
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
}
