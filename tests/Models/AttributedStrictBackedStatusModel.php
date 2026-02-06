<?php

namespace Spatie\ModelStatus\Tests\Models;

use Spatie\ModelStatus\Attributes\UseStatus;

#[UseStatus(UserStatus::class, strict: true)]
class AttributedStrictBackedStatusModel extends TestModel
{
    protected $table = 'test_models';
}
