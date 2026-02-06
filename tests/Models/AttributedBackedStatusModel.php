<?php

namespace Spatie\ModelStatus\Tests\Models;

use Spatie\ModelStatus\Attributes\UseStatus;

#[UseStatus(UserStatus::class)]
class AttributedBackedStatusModel extends TestModel
{
    protected $table = 'test_models';
}
