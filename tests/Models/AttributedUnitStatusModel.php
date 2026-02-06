<?php

namespace Spatie\ModelStatus\Tests\Models;

use Spatie\ModelStatus\Attributes\UseStatus;

#[UseStatus(UnitUserStatus::class)]
class AttributedUnitStatusModel extends TestModel
{
    protected $table = 'test_models';
}
