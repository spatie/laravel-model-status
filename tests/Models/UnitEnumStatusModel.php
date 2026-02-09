<?php

namespace Spatie\ModelStatus\Tests\Models;

class UnitEnumStatusModel extends TestModel
{
    protected $table = 'test_models';

    public function statusEnumClass(): ?string
    {
        return UnitUserStatus::class;
    }
}
