<?php

namespace Spatie\ModelStatus\Tests\Models;

class BackedEnumStatusModel extends TestModel
{
    protected $table = 'test_models';

    public function statusEnumClass(): ?string
    {
        return UserStatus::class;
    }
}
