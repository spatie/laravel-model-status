<?php

namespace Spatie\ModelStatus\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\HasStatuses;

class TestModelInvalidEnumType extends Model
{
    use HasStatuses;

    protected $guarded = [];

    protected $table = "test_models";

    public static function getStatusEnumClass(): string
    {
        return TestEnumNotBacked::class;
    }
}
